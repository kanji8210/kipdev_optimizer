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
        if ( empty( $html ) ) {
            return $html;
        }
        // remove HTML comments (but keep IE conditional comments)
        $html = preg_replace( '/<!--(?!\s*\[if)(.*?)-->/s', '', $html );
        // collapse multiple spaces
        $html = preg_replace( '/\s{2,}/', ' ', $html );
        // remove spaces between tags
        $html = preg_replace( '/>\s+</', '><', $html );
        return $html;
    }
}
