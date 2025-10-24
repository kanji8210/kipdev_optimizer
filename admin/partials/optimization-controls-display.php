<?php
/**
 * Optimization Controls Display Template
 *
 * @package KipDev_Optimizer
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $optimization_controls->get_settings();
?>

<div class="wrap kipdev-optimizer-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="kipdev-optimizer-controls">
        <form method="post" action="options.php" id="kipdev-settings-form">
            <?php settings_fields('kipdev_optimizer_settings'); ?>
            
            <div class="kipdev-control-section">
                <h2><?php _e('Image Optimization', 'kipdev-optimizer'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable Image Optimization', 'kipdev-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="kipdev_optimizer_image_optimization" value="1" 
                                    <?php checked($settings['image_optimization_enabled'], true); ?>>
                                <?php _e('Automatically optimize images on upload', 'kipdev-optimizer'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Image Quality', 'kipdev-optimizer'); ?></th>
                        <td>
                            <input type="range" name="kipdev_optimizer_image_quality" 
                                min="50" max="100" value="<?php echo esc_attr($settings['image_quality']); ?>"
                                id="image-quality-slider">
                            <span id="image-quality-value"><?php echo esc_html($settings['image_quality']); ?>%</span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Optimize Existing Images', 'kipdev-optimizer'); ?></th>
                        <td>
                            <button type="button" class="button button-primary" id="optimize-images-btn">
                                <?php _e('Optimize All Images', 'kipdev-optimizer'); ?>
                            </button>
                            <span class="spinner" id="optimize-images-spinner"></span>
                            <div id="optimize-images-result"></div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="kipdev-control-section">
                <h2><?php _e('Video Optimization', 'kipdev-optimizer'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable Video Optimization', 'kipdev-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="kipdev_optimizer_video_optimization" value="1"
                                    <?php checked($settings['video_optimization_enabled'], true); ?>>
                                <?php _e('Automatically process videos on upload', 'kipdev-optimizer'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Optimize Existing Videos', 'kipdev-optimizer'); ?></th>
                        <td>
                            <button type="button" class="button button-primary" id="optimize-videos-btn">
                                <?php _e('Optimize All Videos', 'kipdev-optimizer'); ?>
                            </button>
                            <span class="spinner" id="optimize-videos-spinner"></span>
                            <div id="optimize-videos-result"></div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="kipdev-control-section">
                <h2><?php _e('Asset Minification', 'kipdev-optimizer'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable Asset Minification', 'kipdev-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="kipdev_optimizer_asset_minification" value="1"
                                    <?php checked($settings['asset_minification_enabled'], true); ?>>
                                <?php _e('Minify CSS and JavaScript files', 'kipdev-optimizer'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Minify Assets', 'kipdev-optimizer'); ?></th>
                        <td>
                            <button type="button" class="button button-primary" id="minify-assets-btn">
                                <?php _e('Minify All Assets', 'kipdev-optimizer'); ?>
                            </button>
                            <span class="spinner" id="minify-assets-spinner"></span>
                            <div id="minify-assets-result"></div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="kipdev-control-section">
                <h2><?php _e('Cache Management', 'kipdev-optimizer'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable Browser Caching', 'kipdev-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="kipdev_optimizer_cache_enabled" value="1"
                                    <?php checked($settings['cache_enabled'], true); ?>>
                                <?php _e('Enable browser caching for static resources', 'kipdev-optimizer'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Clear Cache', 'kipdev-optimizer'); ?></th>
                        <td>
                            <button type="button" class="button" id="clear-cache-btn">
                                <?php _e('Clear All Cache', 'kipdev-optimizer'); ?>
                            </button>
                            <span class="spinner" id="clear-cache-spinner"></span>
                            <div id="clear-cache-result"></div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="kipdev-control-section">
                <h2><?php _e('Additional Options', 'kipdev-optimizer'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Lazy Loading', 'kipdev-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="kipdev_optimizer_lazy_load" value="1"
                                    <?php checked($settings['lazy_load_enabled'], true); ?>>
                                <?php _e('Enable lazy loading for images and videos', 'kipdev-optimizer'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <?php submit_button(__('Save Settings', 'kipdev-optimizer')); ?>
        </form>
    </div>
</div>
