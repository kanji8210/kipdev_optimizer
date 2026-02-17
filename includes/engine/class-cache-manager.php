<?php
namespace Kipdev\Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Cache_Manager {
    public static function init() {
        // nothing heavy for now
    }

    public static function clear_all() {
        // Clear plugin transients and logs
        global $wpdb;
        // Remove transients with kipdev_ prefix
        $prefix = '_transient_kipdev_';
        $sql = $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $prefix . '%' );
        $wpdb->query( $sql );

        // Clear log
        update_option( 'kipdev_opt_log', array() );
        // Optionally clear last scan
        delete_option( 'kipdev_last_scan' );
    }
}
