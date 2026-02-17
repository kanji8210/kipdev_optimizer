<?php
/**
 * Plugin Name: KipDev Simple Optimizer
 * Plugin URI:  https://example.com/kipdev-optimizer
 * Description: Lightweight, high-quality performance plugin — image optimizer with WebP, asset minifier, page cache, database optimization, and Jetpack compatibility.
 * Version:     0.2.0
 * Author:      KipDev
 * Text Domain: kipdev-optimizer
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define( 'KIPDEV_OPT_DIR', plugin_dir_path( __FILE__ ) );
define( 'KIPDEV_OPT_URL', plugin_dir_url( __FILE__ ) );

// Autoload simple loader
require_once KIPDEV_OPT_DIR . 'includes/helpers.php';
require_once KIPDEV_OPT_DIR . 'includes/admin/class-kipdev-admin.php';
require_once KIPDEV_OPT_DIR . 'includes/engine/class-image-optimizer.php';
require_once KIPDEV_OPT_DIR . 'includes/engine/class-video-processor.php';
require_once KIPDEV_OPT_DIR . 'includes/engine/class-asset-minifier.php';
require_once KIPDEV_OPT_DIR . 'includes/engine/class-cache-manager.php';
require_once KIPDEV_OPT_DIR . 'includes/metrics/class-performance-scanner.php';

// Activation / deactivation
function kipdev_opt_activate() {
    // create default options
    add_option( 'kipdev_opt_options', array(
        'image_quality'         => 82,
        'minify_html'           => 1,
        'enable_lazy_loading'   => 1,
        'enable_page_cache'     => 0,  // Off by default
        'defer_js'              => 1,
        'generate_webp'         => 1,
        'jetpack_compatibility' => 1,
    ) );
    
    // Initialize counters
    add_option( 'kipdev_images_optimized', 0 );
}
register_activation_hook( __FILE__, 'kipdev_opt_activate' );

function kipdev_opt_deactivate() {
    // keep options — allow manual removal
}
register_deactivation_hook( __FILE__, 'kipdev_opt_deactivate' );

// Instantiate components
function kipdev_opt_init() {
    // instantiate engine classes
    Kipdev\Optimizer\Image_Optimizer::init();
    Kipdev\Optimizer\Video_Processor::init();
    Kipdev\Optimizer\Asset_Minifier::init();
    Kipdev\Optimizer\Cache_Manager::init();
    Kipdev\Metrics\Performance_Scanner::init();
    Kipdev\Admin\Kipdev_Admin::init();
}
add_action( 'plugins_loaded', 'kipdev_opt_init' );

// WP-CLI Commands
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    
    /**
     * Optimize images in the media library
     * 
     * ## OPTIONS
     * 
     * [--limit=<limit>]
     * : Number of images to optimize. Default: all
     * 
     * [--force]
     * : Re-optimize already optimized images
     * 
     * ## EXAMPLES
     * 
     *     wp kipdev optimize-images
     *     wp kipdev optimize-images --limit=50
     *     wp kipdev optimize-images --force
     */
    WP_CLI::add_command( 'kipdev optimize-images', function( $args, $assoc_args ) {
        $limit = isset( $assoc_args['limit'] ) ? intval( $assoc_args['limit'] ) : -1;
        $force = isset( $assoc_args['force'] );
        
        $query_args = array(
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => $limit,
            'post_status'    => 'any',
        );
        
        $query = new WP_Query( $query_args );
        
        if ( ! $query->have_posts() ) {
            WP_CLI::error( 'No images found.' );
        }
        
        WP_CLI::log( sprintf( 'Found %d images to optimize...', $query->post_count ) );
        
        $optimizer = Kipdev\Optimizer\Image_Optimizer::init();
        $optimized = 0;
        $skipped = 0;
        
        foreach ( $query->posts as $attachment ) {
            $file = get_attached_file( $attachment->ID );
            
            if ( ! $file || ! file_exists( $file ) ) {
                $skipped++;
                continue;
            }
            
            WP_CLI::log( sprintf( 'Optimizing: %s (ID: %d)', basename( $file ), $attachment->ID ) );
            
            // Get metadata to optimize all sizes
            $metadata = wp_get_attachment_metadata( $attachment->ID );
            
            // Call the optimize method via reflection to access protected method
            $reflection = new ReflectionClass( $optimizer );
            $method = $reflection->getMethod( 'optimize_file' );
            $method->setAccessible( true );
            
            if ( $method->invoke( $optimizer, $file ) ) {
                $optimized++;
                
                // Also optimize thumbnails
                if ( ! empty( $metadata['sizes'] ) ) {
                    foreach ( $metadata['sizes'] as $size ) {
                        if ( ! empty( $size['file'] ) ) {
                            $thumb_path = path_join( dirname( $file ), $size['file'] );
                            if ( file_exists( $thumb_path ) ) {
                                $method->invoke( $optimizer, $thumb_path );
                            }
                        }
                    }
                }
            } else {
                $skipped++;
            }
        }
        
        WP_CLI::success( sprintf( 'Optimized %d images, skipped %d.', $optimized, $skipped ) );
    });
    
    /**
     * Clear plugin cache
     * 
     * ## EXAMPLES
     * 
     *     wp kipdev clear-cache
     */
    WP_CLI::add_command( 'kipdev clear-cache', function() {
        Kipdev\Optimizer\Cache_Manager::clear_all();
        WP_CLI::success( 'Cache cleared successfully.' );
    });
    
    /**
     * Optimize database
     * 
     * ## EXAMPLES
     * 
     *     wp kipdev optimize-db
     */
    WP_CLI::add_command( 'kipdev optimize-db', function() {
        WP_CLI::log( 'Optimizing database...' );
        $result = Kipdev\Optimizer\Cache_Manager::optimize_database();
        if ( $result['success'] ) {
            WP_CLI::success( $result['message'] );
        } else {
            WP_CLI::error( 'Failed to optimize database.' );
        }
    });
    
    /**
     * Run performance scan
     * 
     * ## EXAMPLES
     * 
     *     wp kipdev scan
     */
    WP_CLI::add_command( 'kipdev scan', function() {
        WP_CLI::log( 'Running performance scan...' );
        $result = Kipdev\Metrics\Performance_Scanner::run_scan();
        WP_CLI::success( sprintf( 'Scan complete: %s seconds', $result['load_time'] ) );
        WP_CLI::log( sprintf( 'Images optimized: %d', $result['images'] ) );
    });
}
