<?php
/**
 * Image Optimizer component.
 *
 * @package KipDev_Optimizer
 */

class KipDev_Image_Optimizer {

    /**
     * Initialize the image optimizer.
     */
    public function __construct() {
        add_filter('wp_handle_upload', array($this, 'optimize_on_upload'));
    }

    /**
     * Optimize image on upload.
     */
    public function optimize_on_upload($upload) {
        if (!get_option('kipdev_optimizer_image_optimization', true)) {
            return $upload;
        }

        $file_path = $upload['file'];
        $file_type = $upload['type'];

        // Only process images
        if (strpos($file_type, 'image/') !== 0) {
            return $upload;
        }

        $this->optimize_image($file_path);

        return $upload;
    }

    /**
     * Optimize a single image.
     */
    public function optimize_image($file_path) {
        if (!file_exists($file_path)) {
            return false;
        }

        $image_type = exif_imagetype($file_path);
        $quality = get_option('kipdev_optimizer_image_quality', 85);

        switch ($image_type) {
            case IMAGETYPE_JPEG:
                return $this->optimize_jpeg($file_path, $quality);
            case IMAGETYPE_PNG:
                return $this->optimize_png($file_path);
            case IMAGETYPE_GIF:
                return $this->optimize_gif($file_path);
            default:
                return false;
        }
    }

    /**
     * Optimize JPEG image.
     */
    private function optimize_jpeg($file_path, $quality) {
        $image = imagecreatefromjpeg($file_path);
        if (!$image) {
            return false;
        }

        $result = imagejpeg($image, $file_path, $quality);
        imagedestroy($image);

        return $result;
    }

    /**
     * Optimize PNG image.
     */
    private function optimize_png($file_path) {
        $image = imagecreatefrompng($file_path);
        if (!$image) {
            return false;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);
        
        $result = imagepng($image, $file_path, 9);
        imagedestroy($image);

        return $result;
    }

    /**
     * Optimize GIF image.
     */
    private function optimize_gif($file_path) {
        // GIF optimization is limited without external tools
        return true;
    }

    /**
     * Optimize all existing images.
     */
    public function optimize_all() {
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1
        );

        $images = get_posts($args);
        $optimized = 0;
        $failed = 0;

        foreach ($images as $image) {
            $file_path = get_attached_file($image->ID);
            if ($this->optimize_image($file_path)) {
                $optimized++;
            } else {
                $failed++;
            }
        }

        // Update optimized count
        update_option('kipdev_optimizer_images_optimized', $optimized);

        return array(
            'optimized' => $optimized,
            'failed' => $failed,
            'total' => count($images)
        );
    }

    /**
     * Add lazy loading to images.
     */
    public function add_lazy_loading($content) {
        if (!get_option('kipdev_optimizer_lazy_load', true)) {
            return $content;
        }

        return preg_replace('/<img(.*?)src=/i', '<img$1loading="lazy" src=', $content);
    }
}
