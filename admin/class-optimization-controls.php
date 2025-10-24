<?php
/**
 * Optimization Controls dashboard component.
 *
 * @package KipDev_Optimizer
 */

class KipDev_Optimization_Controls {

    /**
     * Initialize optimization controls.
     */
    public function __construct() {
        add_action('wp_ajax_kipdev_optimize_images', array($this, 'ajax_optimize_images'));
        add_action('wp_ajax_kipdev_optimize_videos', array($this, 'ajax_optimize_videos'));
        add_action('wp_ajax_kipdev_minify_assets', array($this, 'ajax_minify_assets'));
        add_action('wp_ajax_kipdev_clear_cache', array($this, 'ajax_clear_cache'));
    }

    /**
     * Get optimization settings.
     */
    public function get_settings() {
        return array(
            'image_optimization_enabled' => get_option('kipdev_optimizer_image_optimization', true),
            'video_optimization_enabled' => get_option('kipdev_optimizer_video_optimization', true),
            'asset_minification_enabled' => get_option('kipdev_optimizer_asset_minification', true),
            'cache_enabled' => get_option('kipdev_optimizer_cache_enabled', true),
            'image_quality' => get_option('kipdev_optimizer_image_quality', 85),
            'lazy_load_enabled' => get_option('kipdev_optimizer_lazy_load', true)
        );
    }

    /**
     * Update optimization settings.
     */
    public function update_settings($settings) {
        foreach ($settings as $key => $value) {
            update_option('kipdev_optimizer_' . $key, $value);
        }
    }

    /**
     * AJAX handler for image optimization.
     */
    public function ajax_optimize_images() {
        check_ajax_referer('kipdev_optimizer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $image_optimizer = new KipDev_Image_Optimizer();
        $result = $image_optimizer->optimize_all();

        wp_send_json_success($result);
    }

    /**
     * AJAX handler for video optimization.
     */
    public function ajax_optimize_videos() {
        check_ajax_referer('kipdev_optimizer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $video_processor = new KipDev_Video_Processor();
        $result = $video_processor->optimize_all();

        wp_send_json_success($result);
    }

    /**
     * AJAX handler for asset minification.
     */
    public function ajax_minify_assets() {
        check_ajax_referer('kipdev_optimizer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $asset_minifier = new KipDev_Asset_Minifier();
        $result = $asset_minifier->minify_all();

        wp_send_json_success($result);
    }

    /**
     * AJAX handler for clearing cache.
     */
    public function ajax_clear_cache() {
        check_ajax_referer('kipdev_optimizer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $cache_manager = new KipDev_Cache_Manager();
        $result = $cache_manager->clear_cache();

        wp_send_json_success($result);
    }
}
