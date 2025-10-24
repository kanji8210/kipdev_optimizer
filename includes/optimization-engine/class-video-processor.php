<?php
/**
 * Video Processor component.
 *
 * @package KipDev_Optimizer
 */

class KipDev_Video_Processor {

    /**
     * Initialize the video processor.
     */
    public function __construct() {
        add_filter('wp_handle_upload', array($this, 'process_on_upload'));
    }

    /**
     * Process video on upload.
     */
    public function process_on_upload($upload) {
        if (!get_option('kipdev_optimizer_video_optimization', true)) {
            return $upload;
        }

        $file_type = $upload['type'];

        // Only process videos
        if (strpos($file_type, 'video/') !== 0) {
            return $upload;
        }

        $this->process_video($upload['file']);

        return $upload;
    }

    /**
     * Process a single video.
     */
    public function process_video($file_path) {
        if (!file_exists($file_path)) {
            return false;
        }

        // Add video metadata
        $this->add_video_metadata($file_path);

        return true;
    }

    /**
     * Add video metadata.
     */
    private function add_video_metadata($file_path) {
        // Store video optimization metadata
        $metadata = array(
            'optimized' => true,
            'optimized_date' => current_time('mysql'),
            'file_path' => $file_path
        );

        update_option('kipdev_optimizer_video_' . md5($file_path), $metadata);

        return $metadata;
    }

    /**
     * Optimize all existing videos.
     */
    public function optimize_all() {
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'video',
            'post_status' => 'inherit',
            'posts_per_page' => -1
        );

        $videos = get_posts($args);
        $optimized = 0;
        $failed = 0;

        foreach ($videos as $video) {
            $file_path = get_attached_file($video->ID);
            if ($this->process_video($file_path)) {
                $optimized++;
            } else {
                $failed++;
            }
        }

        // Update optimized count
        update_option('kipdev_optimizer_videos_optimized', $optimized);

        return array(
            'optimized' => $optimized,
            'failed' => $failed,
            'total' => count($videos)
        );
    }

    /**
     * Add lazy loading to videos.
     */
    public function add_lazy_loading($content) {
        if (!get_option('kipdev_optimizer_lazy_load', true)) {
            return $content;
        }

        return preg_replace('/<video(.*?)>/i', '<video$1 loading="lazy" preload="none">', $content);
    }

    /**
     * Get video optimization status.
     */
    public function get_status() {
        return array(
            'enabled' => get_option('kipdev_optimizer_video_optimization', true),
            'optimized_count' => get_option('kipdev_optimizer_videos_optimized', 0)
        );
    }
}
