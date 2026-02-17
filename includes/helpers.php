<?php
namespace Kipdev\Optimizer\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function get_options() {
    $defaults = array(
        'image_quality' => 82,
        'minify_html'   => 1,
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
    $opts = get_option( 'kipdev_opt_options', array() );
    $opts = array_merge( $opts, $allowed );
    update_option( 'kipdev_opt_options', $opts );
}
