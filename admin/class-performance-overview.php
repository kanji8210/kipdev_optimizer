<?php
/**
 * Performance Overview dashboard component.
 *
 * @package KipDev_Optimizer
 */

class KipDev_Performance_Overview {

    /**
     * Get performance metrics.
     */
    public function get_metrics() {
        $scanner = new KipDev_Performance_Scanner();
        return $scanner->scan();
    }

    /**
     * Get page load time.
     */
    public function get_page_load_time() {
        return get_option('kipdev_optimizer_page_load_time', 0);
    }

    /**
     * Get total optimized images.
     */
    public function get_optimized_images_count() {
        return get_option('kipdev_optimizer_images_optimized', 0);
    }

    /**
     * Get total optimized videos.
     */
    public function get_optimized_videos_count() {
        return get_option('kipdev_optimizer_videos_optimized', 0);
    }

    /**
     * Get cache status.
     */
    public function get_cache_status() {
        $cache_manager = new KipDev_Cache_Manager();
        return $cache_manager->get_status();
    }

    /**
     * Get performance score.
     */
    public function get_performance_score() {
        $metrics = $this->get_metrics();
        return isset($metrics['score']) ? $metrics['score'] : 0;
    }

    /**
     * Get optimization summary.
     */
    public function get_summary() {
        return array(
            'images_optimized' => $this->get_optimized_images_count(),
            'videos_optimized' => $this->get_optimized_videos_count(),
            'cache_status' => $this->get_cache_status(),
            'performance_score' => $this->get_performance_score(),
            'page_load_time' => $this->get_page_load_time()
        );
    }
}
