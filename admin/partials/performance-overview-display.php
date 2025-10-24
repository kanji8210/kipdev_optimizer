<?php
/**
 * Performance Overview Display Template
 *
 * @package KipDev_Optimizer
 */

if (!defined('ABSPATH')) {
    exit;
}

$summary = $performance_overview->get_summary();
?>

<div class="wrap kipdev-optimizer-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="kipdev-optimizer-dashboard">
        <div class="kipdev-stats-grid">
            <!-- Performance Score -->
            <div class="kipdev-stat-card">
                <div class="kipdev-stat-icon">
                    <span class="dashicons dashicons-performance"></span>
                </div>
                <div class="kipdev-stat-content">
                    <h3><?php _e('Performance Score', 'kipdev-optimizer'); ?></h3>
                    <p class="kipdev-stat-value"><?php echo esc_html($summary['performance_score']); ?>/100</p>
                    <p class="kipdev-stat-label">
                        <?php 
                        if ($summary['performance_score'] >= 90) {
                            echo '<span class="status-excellent">' . __('Excellent', 'kipdev-optimizer') . '</span>';
                        } elseif ($summary['performance_score'] >= 70) {
                            echo '<span class="status-good">' . __('Good', 'kipdev-optimizer') . '</span>';
                        } else {
                            echo '<span class="status-needs-improvement">' . __('Needs Improvement', 'kipdev-optimizer') . '</span>';
                        }
                        ?>
                    </p>
                </div>
            </div>

            <!-- Images Optimized -->
            <div class="kipdev-stat-card">
                <div class="kipdev-stat-icon">
                    <span class="dashicons dashicons-format-image"></span>
                </div>
                <div class="kipdev-stat-content">
                    <h3><?php _e('Images Optimized', 'kipdev-optimizer'); ?></h3>
                    <p class="kipdev-stat-value"><?php echo esc_html($summary['images_optimized']); ?></p>
                    <p class="kipdev-stat-label"><?php _e('Total images', 'kipdev-optimizer'); ?></p>
                </div>
            </div>

            <!-- Videos Optimized -->
            <div class="kipdev-stat-card">
                <div class="kipdev-stat-icon">
                    <span class="dashicons dashicons-video-alt3"></span>
                </div>
                <div class="kipdev-stat-content">
                    <h3><?php _e('Videos Optimized', 'kipdev-optimizer'); ?></h3>
                    <p class="kipdev-stat-value"><?php echo esc_html($summary['videos_optimized']); ?></p>
                    <p class="kipdev-stat-label"><?php _e('Total videos', 'kipdev-optimizer'); ?></p>
                </div>
            </div>

            <!-- Cache Status -->
            <div class="kipdev-stat-card">
                <div class="kipdev-stat-icon">
                    <span class="dashicons dashicons-database"></span>
                </div>
                <div class="kipdev-stat-content">
                    <h3><?php _e('Cache Status', 'kipdev-optimizer'); ?></h3>
                    <p class="kipdev-stat-value">
                        <?php 
                        echo $summary['cache_status']['enabled'] ? 
                            '<span class="status-enabled">' . __('Enabled', 'kipdev-optimizer') . '</span>' :
                            '<span class="status-disabled">' . __('Disabled', 'kipdev-optimizer') . '</span>';
                        ?>
                    </p>
                    <p class="kipdev-stat-label">
                        <?php 
                        if (isset($summary['cache_status']['cache_size'])) {
                            printf(__('Cache size: %s', 'kipdev-optimizer'), esc_html($summary['cache_status']['cache_size']));
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="kipdev-actions-section">
            <h2><?php _e('Quick Actions', 'kipdev-optimizer'); ?></h2>
            <div class="kipdev-action-buttons">
                <a href="<?php echo admin_url('admin.php?page=kipdev-optimizer-controls'); ?>" class="button button-primary">
                    <?php _e('Optimization Controls', 'kipdev-optimizer'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=kipdev-optimizer-results'); ?>" class="button">
                    <?php _e('View Detailed Results', 'kipdev-optimizer'); ?>
                </a>
            </div>
        </div>

        <div class="kipdev-info-section">
            <h2><?php _e('About KipDev Optimizer', 'kipdev-optimizer'); ?></h2>
            <p><?php _e('KipDev Optimizer helps you improve your website performance by optimizing images, videos, and implementing best practices.', 'kipdev-optimizer'); ?></p>
        </div>
    </div>
</div>
