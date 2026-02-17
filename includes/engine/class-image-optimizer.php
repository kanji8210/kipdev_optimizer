<?php
namespace Kipdev\Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Image_Optimizer {
    protected static $instance = null;

    public static function init() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Only hook if Jetpack image optimization is not active
        if ( ! $this->is_jetpack_image_cdn_active() ) {
            add_action( 'add_attachment', array( $this, 'maybe_optimize_attachment' ), 20 );
            add_filter( 'wp_generate_attachment_metadata', array( $this, 'optimize_generated_metadata' ), 10, 2 );
        } else {
            $this->log( 'Jetpack Image CDN detected - deferring image optimization to Jetpack', 'info' );
        }
    }
    
    /**
     * Check if Jetpack's image CDN (Photon) is active
     */
    private function is_jetpack_image_cdn_active() {
        if ( ! class_exists( '\Jetpack' ) ) {
            return false;
        }
        // Check if Photon/Image CDN module is active
        return method_exists( '\Jetpack', 'is_module_active' ) && 
               ( \Jetpack::is_module_active( 'photon' ) || \Jetpack::is_module_active( 'photon-cdn' ) );
    }

    public function maybe_optimize_attachment( $post_id ) {
        // lightweight; process only images
        $file = get_attached_file( $post_id );
        if ( ! $file || ! file_exists( $file ) ) {
            return;
        }
        $mime = get_post_mime_type( $post_id );
        if ( ! $mime || strpos( $mime, 'image/' ) !== 0 ) {
            return;
        }
        $this->optimize_file( $file );
    }

    public function optimize_generated_metadata( $metadata, $attachment_id ) {
        $file = get_attached_file( $attachment_id );
        if ( $file && file_exists( $file ) ) {
            $this->optimize_file( $file );
        }
        // also try sizes
        if ( ! empty( $metadata['sizes'] ) ) {
            foreach ( $metadata['sizes'] as $size ) {
                if ( ! empty( $size['file'] ) ) {
                    $path = path_join( dirname( $file ), $size['file'] );
                    if ( file_exists( $path ) ) {
                        $this->optimize_file( $path );
                    }
                }
            }
        }
        return $metadata;
    }

    protected function optimize_file( $file ) {
        $opts = \Kipdev\Optimizer\Helpers\get_options();
        $quality = isset( $opts['image_quality'] ) ? intval( $opts['image_quality'] ) : 82;
        $generate_webp = isset( $opts['generate_webp'] ) ? intval( $opts['generate_webp'] ) : 1;

        $info = @getimagesize( $file );
        if ( ! $info ) {
            return false;
        }
        
        // Check image dimensions and memory requirements
        list( $width, $height ) = $info;
        $required_memory = $width * $height * 4 * 1.8; // Estimate memory needed
        $memory_limit = ini_get( 'memory_limit' );
        $memory_limit_bytes = $this->convert_memory_to_bytes( $memory_limit );
        
        if ( $required_memory > $memory_limit_bytes * 0.7 ) {
            $this->log( "Skipping large image ({$width}x{$height}): insufficient memory", 'warning' );
            return false;
        }
        
        $mime = $info['mime'];
        $optimized = false;
        
        try {
            if ( 'image/jpeg' === $mime || 'image/jpg' === $mime ) {
                $img = @imagecreatefromjpeg( $file );
                if ( $img ) {
                    // Optimize JPEG
                    @imagejpeg( $img, $file, $quality );
                    $this->log( "Optimized JPEG: $file (q=$quality)" );
                    $optimized = true;
                    
                    // Generate WebP if supported and enabled
                    if ( $generate_webp && function_exists( 'imagewebp' ) ) {
                        $webp_file = preg_replace( '/\.(jpe?g)$/i', '.webp', $file );
                        if ( @imagewebp( $img, $webp_file, $quality ) ) {
                            $this->log( "Generated WebP: $webp_file" );
                        }
                    }
                    
                    imagedestroy( $img );
                }
            } elseif ( 'image/png' === $mime ) {
                $img = @imagecreatefrompng( $file );
                if ( $img ) {
                    // Preserve transparency
                    imagealphablending( $img, false );
                    imagesavealpha( $img, true );
                    
                    // PNG quality: 0 (no compression) - 9
                    $png_quality = max( 0, min( 9, (int) round( (100 - $quality) / 11 ) ) );
                    @imagepng( $img, $file, $png_quality );
                    $this->log( "Optimized PNG: $file (level=$png_quality)" );
                    $optimized = true;
                    
                    // Generate WebP if supported and enabled
                    if ( $generate_webp && function_exists( 'imagewebp' ) ) {
                        $webp_file = preg_replace( '/\.png$/i', '.webp', $file );
                        if ( @imagewebp( $img, $webp_file, $quality ) ) {
                            $this->log( "Generated WebP: $webp_file" );
                        }
                    }
                    
                    imagedestroy( $img );
                }
            } elseif ( 'image/gif' === $mime ) {
                // Leave animated GIFs as is
                return false;
            }
            
            if ( $optimized ) {
                // Increment counter
                $count = intval( get_option( 'kipdev_images_optimized', 0 ) );
                update_option( 'kipdev_images_optimized', $count + 1 );
            }
            
        } catch ( \Throwable $e ) {
            $this->log( 'Image optimization error: ' . $e->getMessage(), 'error' );
        }
        
        return $optimized;
    }
    
    /**
     * Convert PHP memory limit string to bytes
     */
    private function convert_memory_to_bytes( $value ) {
        if ( ! $value || $value === '-1' ) {
            return PHP_INT_MAX; // Unlimited memory
        }
        $value = trim( $value );
        if ( empty( $value ) ) {
            return 128 * 1024 * 1024; // Default 128MB
        }
        $last = strtolower( $value[ strlen( $value ) - 1 ] );
        $value = (int) $value;
        switch ( $last ) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        return $value;
    }

    protected function log( $msg, $level = 'info' ) {
        $log = get_option( 'kipdev_opt_log', array() );
        $log[] = array(
            'time' => time(),
            'level' => $level,
            'message' => $msg,
        );
        // keep last 200 entries
        if ( count( $log ) > 200 ) {
            $log = array_slice( $log, -200 );
        }
        update_option( 'kipdev_opt_log', $log );
        
        // Show admin notice for errors
        if ( 'error' === $level && is_admin() ) {
            add_action( 'admin_notices', function() use ( $msg ) {
                echo '<div class="notice notice-error"><p>KipDev Optimizer: ' . esc_html( $msg ) . '</p></div>';
            });
        }
    }
}
