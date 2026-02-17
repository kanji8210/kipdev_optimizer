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
    if ( isset( $data['jetpack_compatibility'] ) ) {
        $allowed['jetpack_compatibility'] = intval( $data['jetpack_compatibility'] ) ? 1 : 0;
    }
    $opts = get_option( 'kipdev_opt_options', array() );
    $opts = array_merge( $opts, $allowed );
    update_option( 'kipdev_opt_options', $opts );
}
