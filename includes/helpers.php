<?php
namespace Kipdev\Optimizer\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function get_options() {
    $defaults = array(
        'image_quality'         => 82,
        'minify_html'           => 1,
        'enable_lazy_loading'   => 1,
        'enable_page_cache'     => 0,  // Off by default to avoid conflicts
        'defer_js'              => 1,
        'generate_webp'         => 1,
        'serve_webp'            => 1,  // Automatically serve WebP to supporting browsers
        'optimize_gifs'         => 1,  // Convert static GIFs to WebP
        'jetpack_compatibility' => 1,  // Auto-detect and defer to Jetpack
    );
    $opts = get_option( 'kipdev_opt_options', array() );
    return wp_parse_args( $opts, $defaults );
}

function update_options( $data ) {
    $allowed = array();
    if ( isset( $data['image_quality'] ) ) {
        $allowed['image_quality'] = intval( $data['image_quality'] );
    }
    if ( isset( $data['minify_html'] ) ) {
        $allowed['minify_html'] = intval( $data['minify_html'] ) ? 1 : 0;
    }
    if ( isset( $data['enable_lazy_loading'] ) ) {
        $allowed['enable_lazy_loading'] = intval( $data['enable_lazy_loading'] ) ? 1 : 0;
    }
    if ( isset( $data['enable_page_cache'] ) ) {
        $allowed['enable_page_cache'] = intval( $data['enable_page_cache'] ) ? 1 : 0;
    }
    if ( isset( $data['defer_js'] ) ) {
        $allowed['defer_js'] = intval( $data['defer_js'] ) ? 1 : 0;
    }
    if ( isset( $data['generate_webp'] ) ) {
        $allowed['generate_webp'] = intval( $data['generate_webp'] ) ? 1 : 0;
    }
    if ( isset( $data['serve_webp'] ) ) {
        $allowed['serve_webp'] = intval( $data['serve_webp'] ) ? 1 : 0;
    }
    if ( isset( $data['optimize_gifs'] ) ) {
        $allowed['optimize_gifs'] = intval( $data['optimize_gifs'] ) ? 1 : 0;
    }
    if ( isset( $data['jetpack_compatibility'] ) ) {
        $allowed['jetpack_compatibility'] = intval( $data['jetpack_compatibility'] ) ? 1 : 0;
    }
    $opts = get_option( 'kipdev_opt_options', array() );
    $opts = array_merge( $opts, $allowed );
    update_option( 'kipdev_opt_options', $opts );
}

/**
 * Check Jetpack status and configuration
 * 
 * @return array Jetpack status information
 */
function get_jetpack_status() {
    $status = array(
        'active' => false,
        'version' => null,
        'modules' => array(),
        'cdn_active' => false,
        'cdn_domains' => array(),
        'features' => array(),
    );
    
    // Check if Jetpack is active
    if ( ! class_exists( '\Jetpack' ) ) {
        return $status;
    }
    
    $status['active'] = true;
    
    // Get Jetpack version
    if ( defined( 'JETPACK__VERSION' ) ) {
        $status['version'] = JETPACK__VERSION;
    }
    
    // Check active modules
    if ( method_exists( '\Jetpack', 'get_active_modules' ) ) {
        $status['modules'] = \Jetpack::get_active_modules();
    }
    
    // Check specific features
    if ( method_exists( '\Jetpack', 'is_module_active' ) ) {
        $features = array(
            'photon' => 'Image CDN (Photon)',
            'photon-cdn' => 'Image CDN (Photon)',
            'lazy-images' => 'Lazy Loading',
            'carousel' => 'Image Carousel',
            'tiled-gallery' => 'Tiled Galleries',
            'videopress' => 'VideoPress',
            'site-accelerator' => 'Site Accelerator',
            'asset-cdn' => 'Asset CDN',
        );
        
        foreach ( $features as $module => $label ) {
            if ( \Jetpack::is_module_active( $module ) ) {
                $status['features'][ $module ] = $label;
                
                // Check if it's a CDN feature
                if ( in_array( $module, array( 'photon', 'photon-cdn', 'site-accelerator', 'asset-cdn' ) ) ) {
                    $status['cdn_active'] = true;
                }
            }
        }
    }
    
    // Detect CDN domains in use
    if ( $status['cdn_active'] ) {
        $cdn_domains = array(
            'i0.wp.com', 'i1.wp.com', 'i2.wp.com', // Photon image CDN
            'c0.wp.com', // Stats/Assets CDN
            's0.wp.com', 's1.wp.com', 's2.wp.com', // Static assets CDN
        );
        
        // Check if any image in media library uses these domains
        $sample_image = get_posts( array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => 1,
            'fields' => 'ids',
        ) );
        
        if ( ! empty( $sample_image ) ) {
            $image_url = wp_get_attachment_url( $sample_image[0] );
            foreach ( $cdn_domains as $domain ) {
                if ( strpos( $image_url, $domain ) !== false ) {
                    $status['cdn_domains'][] = $domain;
                    break;
                }
            }
        }
        
        // If no images found in DB, check if functions exist
        if ( empty( $status['cdn_domains'] ) ) {
            if ( function_exists( 'jetpack_photon_url' ) ) {
                $status['cdn_domains'][] = 'i0.wp.com (available)';
            }
        }
    }
    
    return $status;
}

/**
 * Get optimization conflicts/overlaps with Jetpack
 * 
 * @return array List of conflicts
 */
function get_jetpack_conflicts() {
    $conflicts = array();
    $jetpack = get_jetpack_status();
    $opts = get_options();
    
    if ( ! $jetpack['active'] ) {
        return $conflicts;
    }
    
    // Check for overlapping features
    if ( isset( $jetpack['features']['photon'] ) || isset( $jetpack['features']['photon-cdn'] ) ) {
        $conflicts[] = array(
            'type' => 'info',
            'feature' => 'Image Optimization',
            'message' => 'Jetpack Image CDN is active - KipDev defers image optimization to Jetpack',
            'recommendation' => 'Keep both enabled. Jetpack handles CDN delivery, KipDev can still generate WebP locally.',
        );
    }
    
    if ( isset( $jetpack['features']['lazy-images'] ) && ! empty( $opts['enable_lazy_loading'] ) ) {
        $conflicts[] = array(
            'type' => 'warning',
            'feature' => 'Lazy Loading',
            'message' => 'Both Jetpack and KipDev lazy loading are enabled',
            'recommendation' => 'Disable one to avoid conflicts. Jetpack\'s implementation is recommended.',
        );
    }
    
    if ( isset( $jetpack['features']['site-accelerator'] ) ) {
        $conflicts[] = array(
            'type' => 'info',
            'feature' => 'Asset Acceleration',
            'message' => 'Jetpack Site Accelerator is serving assets from CDN',
            'recommendation' => 'KipDev optimizations work alongside Jetpack CDN.',
        );
    }
    
    return $conflicts;
}
