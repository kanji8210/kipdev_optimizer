<?php
namespace Kipdev\Optimizer;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Cache_Manager {
    protected static $instance = null;
    
    public static function init() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $opts = \Kipdev\Optimizer\Helpers\get_options();
        
        // Enable page caching if option is set
        if ( ! empty( $opts['enable_page_cache'] ) ) {
            add_action( 'template_redirect', array( $this, 'maybe_serve_cache' ), -1 );
        }
        
        // Auto-clear cache on content updates
        add_action( 'save_post', array( __CLASS__, 'clear_page_cache' ) );
        add_action( 'deleted_post', array( __CLASS__, 'clear_page_cache' ) );
        add_action( 'switch_theme', array( __CLASS__, 'clear_page_cache' ) );
        add_action( 'activated_plugin', array( __CLASS__, 'clear_page_cache' ) );
        add_action( 'deactivated_plugin', array( __CLASS__, 'clear_page_cache' ) );
    }
    
    /**
     * Maybe serve cached page
     */
    public function maybe_serve_cache() {
        // Don't cache for logged-in users, admin, POST requests
        if ( is_user_logged_in() || is_admin() || $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
            return;
        }
        
        // Don't cache search, 404, etc.
        if ( is_search() || is_404() || is_feed() ) {
            return;
        }
        
        $cache_key = 'kipdev_page_' . md5( $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING'] );
        $cached = get_transient( $cache_key );
        
        if ( $cached ) {
            echo '<!-- Served from KipDev cache -->';
            echo $cached;
            exit;
        }
        
        // Start output buffering to cache the page
        ob_start( function( $html ) use ( $cache_key ) {
            if ( strlen( $html ) > 0 && ! is_404() ) {
                // Cache for 1 hour
                set_transient( $cache_key, $html, HOUR_IN_SECONDS );
            }
            return $html;
        });
    }

    public static function clear_all() {
        // Clear plugin transients and logs
        global $wpdb;
        // Remove transients with kipdev_ prefix - properly escape LIKE wildcard
        $like = $wpdb->esc_like( '_transient_kipdev_' ) . '%';
        $sql = $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like );
        $wpdb->query( $sql );
        
        // Also clear timeout transients
        $like_timeout = $wpdb->esc_like( '_transient_timeout_kipdev_' ) . '%';
        $sql_timeout = $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like_timeout );
        $wpdb->query( $sql_timeout );

        // Clear log
        update_option( 'kipdev_opt_log', array() );
        // Optionally clear last scan
        delete_option( 'kipdev_last_scan' );
        // Clear page cache
        self::clear_page_cache();
    }
    
    public static function clear_page_cache() {
        global $wpdb;
        $like = $wpdb->esc_like( '_transient_kipdev_page_' ) . '%';
        $sql = $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like );
        $wpdb->query( $sql );
        
        $like_timeout = $wpdb->esc_like( '_transient_timeout_kipdev_page_' ) . '%';
        $sql_timeout = $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like_timeout );
        $wpdb->query( $sql_timeout );
    }
    
    public static function optimize_database() {
        global $wpdb;
        
        // Clean up old post revisions (keep last 5)
        $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'revision' 
            AND ID NOT IN (
                SELECT * FROM (
                    SELECT ID FROM {$wpdb->posts} 
                    WHERE post_type = 'revision' 
                    ORDER BY post_modified DESC 
                    LIMIT 100
                ) AS keep_revisions
            )" );
        
        // Clean expired transients
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE %s 
            AND option_value < %d",
            $wpdb->esc_like( '_transient_timeout_' ) . '%',
            time()
        ) );
        
        // Remove orphaned metadata
        $wpdb->query( "DELETE pm FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE p.ID IS NULL" );
        
        // Optimize tables
        $tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}%'" );
        foreach ( $tables as $table ) {
            $wpdb->query( "OPTIMIZE TABLE {$table}" );
        }
        
        return array(
            'success' => true,
            'message' => 'Database optimized successfully',
        );
    }
}
