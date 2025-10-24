<?php
/**
 * Performance Scanner component.
 *
 * @package KipDev_Optimizer
 */

class KipDev_Performance_Scanner {

    /**
     * Scan performance metrics.
     */
    public function scan() {
        $metrics = array(
            'score' => $this->calculate_score(),
            'page_load_time' => $this->get_page_load_time(),
            'total_size' => $this->get_total_size(),
            'requests_count' => $this->get_requests_count(),
            'images_count' => $this->get_images_count(),
            'scripts_count' => $this->get_scripts_count(),
            'styles_count' => $this->get_styles_count(),
            'cache_status' => $this->get_cache_status(),
            'timestamp' => current_time('mysql')
        );

        // Store metrics
        update_option('kipdev_optimizer_last_scan', $metrics);

        return $metrics;
    }

    /**
     * Calculate performance score.
     */
    private function calculate_score() {
        $score = 100;

        // Deduct points based on various factors
        $images_count = $this->get_images_count();
        $scripts_count = $this->get_scripts_count();
        $cache_enabled = get_option('kipdev_optimizer_cache_enabled', true);

        // Deduct for too many images
        if ($images_count > 50) {
            $score -= min(20, ($images_count - 50) * 0.5);
        }

        // Deduct for too many scripts
        if ($scripts_count > 10) {
            $score -= min(15, ($scripts_count - 10) * 1.5);
        }

        // Deduct if cache is disabled
        if (!$cache_enabled) {
            $score -= 10;
        }

        return max(0, round($score));
    }

    /**
     * Get page load time.
     */
    private function get_page_load_time() {
        // Simulate page load time calculation
        // In production, this would be measured using real user monitoring
        $base_time = 1.5; // seconds
        
        $images_count = $this->get_images_count();
        $scripts_count = $this->get_scripts_count();
        
        $time = $base_time + ($images_count * 0.01) + ($scripts_count * 0.05);
        
        return round($time, 2);
    }

    /**
     * Get total page size.
     */
    private function get_total_size() {
        // Estimate total page size
        $images_count = $this->get_images_count();
        $scripts_count = $this->get_scripts_count();
        $styles_count = $this->get_styles_count();

        $size = 50000; // Base HTML size in bytes
        $size += $images_count * 50000; // Average image size
        $size += $scripts_count * 30000; // Average script size
        $size += $styles_count * 20000; // Average stylesheet size

        return $this->format_bytes($size);
    }

    /**
     * Get requests count.
     */
    private function get_requests_count() {
        $count = 1; // HTML
        $count += $this->get_images_count();
        $count += $this->get_scripts_count();
        $count += $this->get_styles_count();

        return $count;
    }

    /**
     * Get images count.
     */
    private function get_images_count() {
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1
        );

        $images = get_posts($args);
        return count($images);
    }

    /**
     * Get scripts count.
     */
    private function get_scripts_count() {
        global $wp_scripts;
        return isset($wp_scripts->registered) ? count($wp_scripts->registered) : 0;
    }

    /**
     * Get styles count.
     */
    private function get_styles_count() {
        global $wp_styles;
        return isset($wp_styles->registered) ? count($wp_styles->registered) : 0;
    }

    /**
     * Get cache status.
     */
    private function get_cache_status() {
        return get_option('kipdev_optimizer_cache_enabled', true) ? 'enabled' : 'disabled';
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
     * Get last scan results.
     */
    public function get_last_scan() {
        return get_option('kipdev_optimizer_last_scan', array());
    }
}
