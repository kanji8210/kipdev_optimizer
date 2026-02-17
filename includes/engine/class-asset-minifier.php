<?php
namespace Kipdev\Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Asset_Minifier {
    protected static $instance = null;
    protected $buffering = false;

    public static function init() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'template_redirect', array( $this, 'maybe_start_buffer' ), 0 );
        
        $opts = \Kipdev\Optimizer\Helpers\get_options();
        
        // Defer JavaScript if enabled (only on frontend, not admin)
        if ( ! empty( $opts['defer_js'] ) && ! is_admin() ) {
            add_filter( 'script_loader_tag', array( $this, 'defer_scripts' ), 10, 2 );
        }
        
        // Add lazy loading if enabled and Jetpack lazy loading is not active
        if ( ! empty( $opts['enable_lazy_loading'] ) && ! $this->is_jetpack_lazy_loading_active() ) {
            add_filter( 'the_content', array( $this, 'add_lazy_loading' ), 20 );
            add_filter( 'post_thumbnail_html', array( $this, 'add_lazy_loading' ), 20 );
        }
        
        // Add resource hints (only on frontend)
        if ( ! is_admin() ) {
            add_action( 'wp_head', array( $this, 'add_resource_hints' ), 1 );
        }
    }
    
    /**
     * Check if Jetpack lazy loading is active
     */
    private function is_jetpack_lazy_loading_active() {
        if ( ! class_exists( '\Jetpack' ) ) {
            return false;
        }
        return method_exists( '\Jetpack', 'is_module_active' ) && 
               \Jetpack::is_module_active( 'lazy-images' );
    }

    public function maybe_start_buffer() {
        if ( is_admin() ) {
            return;
        }
        $opts = \Kipdev\Optimizer\Helpers\get_options();
        if ( empty( $opts['minify_html'] ) ) {
            return;
        }
        if ( ! headers_sent() ) {
            ob_start( array( $this, 'minify_html' ) );
            $this->buffering = true;
        }
    }

    public function minify_html( $html ) {
        // Lightweight minify: remove comments, collapse whitespace
        if ( ! is_string( $html ) || empty( $html ) || strlen( $html ) < 100 ) {
            return $html;
        }
        
        // Protect script and style blocks
        $protected_blocks = array();
        $result = preg_replace_callback(
            '/<(script|style)[^>]*>.*?<\/\1>/is',
            function( $matches ) use ( &$protected_blocks ) {
                $placeholder = '___PROTECTED_BLOCK_' . count( $protected_blocks ) . '___';
                $protected_blocks[ $placeholder ] = $matches[0];
                return $placeholder;
            },
            $html
        );
        if ( $result !== null ) {
            $html = $result;
        }
        
        // Remove HTML comments (but keep IE conditional comments)
        $result = preg_replace( '/<!--(?!\s*\[if)(?!<!)[^\[>].*?-->/s', '', $html );
        if ( $result !== null ) {
            $html = $result;
        }
        
        // Collapse multiple spaces (but not in pre tags)
        $result = preg_replace( '/\s{2,}/', ' ', $html );
        if ( $result !== null ) {
            $html = $result;
        }
        
        // Remove spaces between tags (but be careful with inline elements)
        $result = preg_replace( '/>\s+</', '><', $html );
        if ( $result !== null ) {
            $html = $result;
        }
        
        // Restore protected blocks
        if ( ! empty( $protected_blocks ) && is_string( $html ) ) {
            $html = str_replace( array_keys( $protected_blocks ), array_values( $protected_blocks ), $html );
        }
        
        return $html;
    }
    
    /**
     * Defer JavaScript files
     */
    public function defer_scripts( $tag, $handle ) {
        // Skip jQuery and critical dependencies
        $skip_handles = array( 'jquery', 'jquery-core', 'jquery-migrate' );
        if ( in_array( $handle, $skip_handles, true ) ) {
            return $tag;
        }
        
        // Skip if already has async or defer
        if ( strpos( $tag, 'defer' ) !== false || strpos( $tag, 'async' ) !== false ) {
            return $tag;
        }
        
        // Add defer attribute
        return str_replace( ' src', ' defer src', $tag );
    }
    
    /**
     * Add lazy loading to images
     */
    public function add_lazy_loading( $content ) {
        if ( is_admin() || ! is_string( $content ) || empty( $content ) ) {
            return $content;
        }
        
        // Add loading="lazy" to img tags that don't have it
        $result = preg_replace_callback(
            '/<img([^>]+?)\/?>/',
            function( $matches ) {
                $img_tag = $matches[0];
                
                // Skip if already has loading attribute
                if ( strpos( $img_tag, 'loading=' ) !== false ) {
                    return $img_tag;
                }
                
                // Add loading="lazy"
                return str_replace( '<img', '<img loading="lazy"', $img_tag );
            },
            $content
        );
        
        if ( $result !== null ) {
            $content = $result;
        }
        
        return $content;
    }
    
    /**
     * Add resource hints for performance
     */
    public function add_resource_hints() {
        // Preconnect to Jetpack CDN if active
        if ( class_exists( '\Jetpack' ) && method_exists( '\Jetpack', 'is_module_active' ) ) {
            if ( \Jetpack::is_module_active( 'photon' ) || \Jetpack::is_module_active( 'photon-cdn' ) ) {
                echo '<link rel="preconnect" href="https://i0.wp.com" crossorigin>' . "\n";
                echo '<link rel="preconnect" href="https://i1.wp.com" crossorigin>' . "\n";
                echo '<link rel="preconnect" href="https://i2.wp.com" crossorigin>' . "\n";
            }
        }
        
        // Preload critical CSS
        $theme_uri = get_stylesheet_uri();
        echo '<link rel="preload" as="style" href="' . esc_url( $theme_uri ) . '">' . "\n";
    }
}
