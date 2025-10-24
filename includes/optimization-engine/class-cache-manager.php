<?php
/**
 * Cache Manager component.
 *
 * @package KipDev_Optimizer
 */

class KipDev_Cache_Manager {

    /**
     * Cache directory path.
     */
    private $cache_dir;

    /**
     * Initialize the cache manager.
     */
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->cache_dir = $upload_dir['basedir'] . '/kipdev-optimizer-cache/';
    }

    /**
     * Initialize cache system.
     */
    public function init() {
        if (!get_option('kipdev_optimizer_cache_enabled', true)) {
            return;
        }

        // Create cache directory if it doesn't exist
        if (!file_exists($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
        }

        // Add cache headers
        add_action('send_headers', array($this, 'add_cache_headers'));
    }

    /**
     * Add cache headers.
     */
    public function add_cache_headers() {
        if (is_admin() || is_user_logged_in()) {
            return;
        }

        $cache_time = 86400; // 24 hours
        header('Cache-Control: public, max-age=' . $cache_time);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_time) . ' GMT');
    }

    /**
     * Clear all cache.
     */
    public function clear_cache() {
        $cleared = 0;

        if (file_exists($this->cache_dir)) {
            $files = glob($this->cache_dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $cleared++;
                }
            }
        }

        // Clear WordPress object cache
        wp_cache_flush();

        return array(
            'cleared' => $cleared,
            'message' => sprintf('Cleared %d cached files', $cleared)
        );
    }

    /**
     * Get cache size.
     */
    public function get_cache_size() {
        $size = 0;

        if (file_exists($this->cache_dir)) {
            $files = glob($this->cache_dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    $size += filesize($file);
                }
            }
        }

        return $this->format_bytes($size);
    }

    /**
     * Format bytes to human readable.
     */
    private function format_bytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get cache status.
     */
    public function get_status() {
        return array(
            'enabled' => get_option('kipdev_optimizer_cache_enabled', true),
            'cache_size' => $this->get_cache_size(),
            'cache_dir' => $this->cache_dir
        );
    }

    /**
     * Enable cache.
     */
    public function enable() {
        update_option('kipdev_optimizer_cache_enabled', true);
        $this->init();
    }

    /**
     * Disable cache.
     */
    public function disable() {
        update_option('kipdev_optimizer_cache_enabled', false);
        $this->clear_cache();
    }
}
