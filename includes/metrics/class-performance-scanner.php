<?php
namespace Kipdev\Metrics;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Performance_Scanner {
    public static function init() {
        add_action( 'init', array( __CLASS__, 'attach_timer' ) );
    }

    public static function attach_timer() {
        if ( ! is_admin() ) {
            global $kipdev_start_time;
            $kipdev_start_time = microtime( true );
            add_action( 'shutdown', array( __CLASS__, 'maybe_record' ) );
        }
    }

    public static function maybe_record() {
        global $kipdev_start_time;
        if ( empty( $kipdev_start_time ) ) {
            return;
        }
        $load = max( 0, microtime( true ) - $kipdev_start_time );
        // store last scan as transient (very lightweight)
        $data = array(
            'timestamp' => time(),
            'load_time' => round( $load, 3 ),
            'images'    => intval( get_option( 'kipdev_images_optimized', 0 ) ),
        );
        update_option( 'kipdev_last_scan', $data );
    }

    public static function run_scan() {
        // Very simple: make a request to home page to measure load
        $home = home_url( '/' );
        $start = microtime( true );
        $resp = wp_remote_get( $home, array( 'timeout' => 15 ) );
        $load = round( microtime( true ) - $start, 3 );
        $data = array(
            'timestamp' => time(),
            'load_time' => $load,
            'images'    => intval( get_option( 'kipdev_images_optimized', 0 ) ),
        );
        update_option( 'kipdev_last_scan', $data );
        // add to log
        $log = get_option( 'kipdev_opt_log', array() );
        $log[] = array(
            'time' => time(),
            'level' => 'info',
            'message' => 'Performance scan: ' . $load . 's',
        );
        if ( count( $log ) > 200 ) {
            $log = array_slice( $log, -200 );
        }
        update_option( 'kipdev_opt_log', $log );
        return $data;
    }
}
