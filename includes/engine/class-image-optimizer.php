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
        
        // Hook WebP serving if enabled and browser supports it
        $opts = \Kipdev\Optimizer\Helpers\get_options();
        $serve_webp = isset( $opts['serve_webp'] ) ? intval( $opts['serve_webp'] ) : 1;
        
        if ( $serve_webp && $this->browser_supports_webp() && ! is_admin() ) {
            add_filter( 'the_content', array( $this, 'replace_images_with_webp' ), 10 );
            add_filter( 'post_thumbnail_html', array( $this, 'replace_images_with_webp' ), 10 );
            add_filter( 'wp_get_attachment_image', array( $this, 'replace_images_with_webp' ), 10 );
            add_filter( 'wp_calculate_image_srcset', array( $this, 'add_webp_to_srcset' ), 10, 5 );
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
                $optimize_gifs = isset( $opts['optimize_gifs'] ) ? intval( $opts['optimize_gifs'] ) : 1;
                
                if ( ! $optimize_gifs ) {
                    $this->log( "Skipping GIF (optimization disabled): $file" );
                    return false;
                }
                
                // Check if GIF is animated
                $is_animated = $this->is_animated_gif( $file );
                
                if ( $is_animated ) {
                    $this->log( "Skipping animated GIF: $file" );
                    return false;
                }
                
                // Process static GIF
                $img = @imagecreatefromgif( $file );
                if ( $img ) {
                    // Convert static GIF to WebP for better compression
                    if ( $generate_webp && function_exists( 'imagewebp' ) ) {
                        $webp_file = preg_replace( '/\.gif$/i', '.webp', $file );
                        if ( @imagewebp( $img, $webp_file, $quality ) ) {
                            $this->log( "Converted static GIF to WebP: $webp_file" );
                            $optimized = true;
                        }
                    }
                    
                    imagedestroy( $img );
                }
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
    
    /**
     * Check if GIF is animated (contains multiple frames)
     */
    private function is_animated_gif( $file ) {
        if ( ! file_exists( $file ) ) {
            return false;
        }
        
        $file_contents = @file_get_contents( $file );
        if ( ! $file_contents ) {
            return false;
        }
        
        // Count the number of frames
        // Animated GIFs have multiple image descriptor blocks (0x2C)
        $str_loc = 0;
        $count = 0;
        
        while ( $count < 2 ) {
            $where1 = strpos( $file_contents, "\x00\x21\xF9\x04", $str_loc );
            if ( $where1 === false ) {
                break;
            }
            $str_loc = $where1 + 1;
            $where2 = strpos( $file_contents, "\x00\x2C", $str_loc );
            if ( $where2 === false ) {
                break;
            }
            if ( $where1 + 8 === $where2 ) {
                $count++;
            }
            $str_loc = $where2 + 1;
        }
        
        // If count >= 2, it's animated
        return $count >= 2;
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
    
    /**
     * Check if browser supports WebP format
     */
    private function browser_supports_webp() {
        if ( ! isset( $_SERVER['HTTP_ACCEPT'] ) ) {
            return false;
        }
        return strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false;
    }
    
    /**
     * Replace image URLs with WebP versions in HTML content
     */
    public function replace_images_with_webp( $content ) {
        if ( ! $content ) {
            return $content;
        }
        
        // Find all image URLs in content (PNG, JPEG, GIF)
        $pattern = '/<img([^>]+)src=["\']([^"\']+\.(png|jpe?g|gif))["\']([^>]*)>/i';
        
        $content = preg_replace_callback( $pattern, function( $matches ) {
            $full_match = $matches[0];
            $before_src = $matches[1];
            $img_url = $matches[2];
            $extension = $matches[3];
            $after_src = $matches[4];
            
            // Convert URL to file path
            $webp_url = $this->get_webp_url( $img_url );
            
            if ( $webp_url ) {
                // Replace the src with WebP version
                return '<img' . $before_src . 'src="' . esc_url( $webp_url ) . '"' . $after_src . ' data-original-src="' . esc_url( $img_url ) . '">';
            }
            
            return $full_match;
        }, $content );
        
        // Also handle srcset attributes
        $content = preg_replace_callback( '/srcset=["\']([^"\']+)["\']/i', function( $matches ) {
            $srcset = $matches[1];
            $sources = explode( ',', $srcset );
            $new_sources = array();
            
            foreach ( $sources as $source ) {
                $source = trim( $source );
                if ( preg_match( '/^(.+\.(png|jpe?g|gif))\s+(.+)$/i', $source, $src_match ) ) {
                    $url = trim( $src_match[1] );
                    $descriptor = trim( $src_match[3] );
                    
                    $webp_url = $this->get_webp_url( $url );
                    if ( $webp_url ) {
                        $new_sources[] = $webp_url . ' ' . $descriptor;
                    } else {
                        $new_sources[] = $source;
                    }
                } else {
                    $new_sources[] = $source;
                }
            }
            
            return 'srcset="' . implode( ', ', $new_sources ) . '"';
        }, $content );
        
        return $content;
    }
    
    /**
     * Get WebP URL if file exists
     */
    private function get_webp_url( $img_url ) {
        // Convert URL to file path
        $upload_dir = wp_upload_dir();
        $base_url = $upload_dir['baseurl'];
        
        // Check if this is an upload URL
        if ( strpos( $img_url, $base_url ) !== 0 ) {
            return false;
        }
        
        // Get file path
        $relative_path = str_replace( $base_url, '', $img_url );
        $file_path = $upload_dir['basedir'] . $relative_path;
        
        // Get WebP path
        $webp_path = preg_replace( '/\.(png|jpe?g|gif)$/i', '.webp', $file_path );
        
        // Check if WebP file exists
        if ( file_exists( $webp_path ) ) {
            $webp_url = preg_replace( '/\.(png|jpe?g|gif)$/i', '.webp', $img_url );
            return $webp_url;
        }
        
        return false;
    }
    
    /**
     * Add WebP versions to srcset
     */
    public function add_webp_to_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
        if ( empty( $sources ) ) {
            return $sources;
        }
        
        foreach ( $sources as $width => $source ) {
            $webp_url = $this->get_webp_url( $source['url'] );
            if ( $webp_url ) {
                $sources[ $width ]['url'] = $webp_url;
            }
        }
        
        return $sources;
    }
}
