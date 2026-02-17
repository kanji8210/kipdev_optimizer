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
        add_action( 'add_attachment', array( $this, 'maybe_optimize_attachment' ), 20 );
        add_filter( 'wp_generate_attachment_metadata', array( $this, 'optimize_generated_metadata' ), 10, 2 );
    }

    public function maybe_optimize_attachment( $post_id ) {
        // lightweight; process only images
        $file = get_attached_file( $post_id );
        if ( ! $file || ! file_exists( $file ) ) {
            return;
        }
        $mime = get_post_mime_type( $post_id );
        if ( strpos( $mime, 'image/' ) !== 0 ) {
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

        $info = @getimagesize( $file );
        if ( ! $info ) {
            return false;
        }
        $mime = $info['mime'];
        try {
            if ( 'image/jpeg' === $mime || 'image/jpg' === $mime ) {
                $img = @imagecreatefromjpeg( $file );
                if ( $img ) {
                    @imagejpeg( $img, $file, $quality );
                    imagedestroy( $img );
                    $this->log( "Optimized JPEG: $file (q=$quality)" );
                    return true;
                }
            }
            if ( 'image/png' === $mime ) {
                $img = @imagecreatefrompng( $file );
                if ( $img ) {
                    // PNG quality: 0 (no compression) - 9
                    $png_quality = max( 0, min( 9, (int) round( (100 - $quality) / 11 ) ) );
                    @imagepng( $img, $file, $png_quality );
                    imagedestroy( $img );
                    $this->log( "Optimized PNG: $file (level=$png_quality)" );
                    return true;
                }
            }
            if ( 'image/gif' === $mime ) {
                // leave gifs as is for now
                return false;
            }
        } catch ( \Throwable $e ) {
            // fail silently, but log minimal
            $this->log( 'Image optimization error: ' . $e->getMessage() );
        }
        return false;
    }

    protected function log( $msg ) {
        $log = get_option( 'kipdev_opt_log', array() );
        $log[] = '[' . date_i18n( 'Y-m-d H:i:s' ) . '] ' . $msg;
        // keep last 200 entries
        if ( count( $log ) > 200 ) {
            $log = array_slice( $log, -200 );
        }
        update_option( 'kipdev_opt_log', $log );
    }
}
