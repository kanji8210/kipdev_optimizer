<?php
/**
 * Results Dashboard Display Template
 *
 * @package KipDev_Optimizer
 */

if (!defined('ABSPATH')) {
    exit;
}

$detailed_report = $results_dashboard->get_detailed_report();
$current_results = $detailed_report['current_results'];
$progress = $detailed_report['progress'];
$trends = $detailed_report['trends'];
?>

<div class="wrap kipdev-optimizer-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="kipdev-optimizer-results">
        <div class="kipdev-results-header">
            <h2><?php _e('Optimization Results', 'kipdev-optimizer'); ?></h2>
            <p class="description">
                <?php 
                printf(
                    __('Report generated on %s', 'kipdev-optimizer'),
                    isset($current_results['generated_at']) ? 
                        esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($current_results['generated_at']))) : 
                        __('N/A', 'kipdev-optimizer')
                );
                ?>
            </p>
        </div>

        <div class="kipdev-results-summary">
            <h3><?php _e('Summary', 'kipdev-optimizer'); ?></h3>
            <div class="kipdev-summary-grid">
                <?php if (isset($current_results['summary'])): ?>
                    <div class="kipdev-summary-item">
                        <span class="dashicons dashicons-format-image"></span>
                        <strong><?php _e('Images Optimized:', 'kipdev-optimizer'); ?></strong>
                        <?php echo esc_html($current_results['summary']['images_optimized']); ?>
                    </div>
                    <div class="kipdev-summary-item">
                        <span class="dashicons dashicons-video-alt3"></span>
                        <strong><?php _e('Videos Optimized:', 'kipdev-optimizer'); ?></strong>
                        <?php echo esc_html($current_results['summary']['videos_optimized']); ?>
                    </div>
                    <div class="kipdev-summary-item">
                        <span class="dashicons dashicons-database"></span>
                        <strong><?php _e('Cache Status:', 'kipdev-optimizer'); ?></strong>
                        <?php echo $current_results['summary']['cache_enabled'] ? __('Enabled', 'kipdev-optimizer') : __('Disabled', 'kipdev-optimizer'); ?>
                    </div>
                    <div class="kipdev-summary-item">
                        <span class="dashicons dashicons-editor-code"></span>
                        <strong><?php _e('Asset Minification:', 'kipdev-optimizer'); ?></strong>
                        <?php echo $current_results['summary']['asset_minification'] ? __('Enabled', 'kipdev-optimizer') : __('Disabled', 'kipdev-optimizer'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($current_results['metrics'])): ?>
        <div class="kipdev-results-metrics">
            <h3><?php _e('Performance Metrics', 'kipdev-optimizer'); ?></h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Metric', 'kipdev-optimizer'); ?></th>
                        <th><?php _e('Value', 'kipdev-optimizer'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php _e('Performance Score', 'kipdev-optimizer'); ?></td>
                        <td>
                            <strong><?php echo esc_html($current_results['metrics']['score']); ?>/100</strong>
                            <?php 
                            if ($current_results['metrics']['score'] >= 90) {
                                echo ' <span class="status-excellent">(' . __('Excellent', 'kipdev-optimizer') . ')</span>';
                            } elseif ($current_results['metrics']['score'] >= 70) {
                                echo ' <span class="status-good">(' . __('Good', 'kipdev-optimizer') . ')</span>';
                            } else {
                                echo ' <span class="status-needs-improvement">(' . __('Needs Improvement', 'kipdev-optimizer') . ')</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('Page Load Time', 'kipdev-optimizer'); ?></td>
                        <td><?php echo esc_html($current_results['metrics']['page_load_time']); ?>s</td>
                    </tr>
                    <tr>
                        <td><?php _e('Total Page Size', 'kipdev-optimizer'); ?></td>
                        <td><?php echo esc_html($current_results['metrics']['total_size']); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('Total Requests', 'kipdev-optimizer'); ?></td>
                        <td><?php echo esc_html($current_results['metrics']['requests_count']); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('Images Count', 'kipdev-optimizer'); ?></td>
                        <td><?php echo esc_html($current_results['metrics']['images_count']); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('Scripts Count', 'kipdev-optimizer'); ?></td>
                        <td><?php echo esc_html($current_results['metrics']['scripts_count']); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('Stylesheets Count', 'kipdev-optimizer'); ?></td>
                        <td><?php echo esc_html($current_results['metrics']['styles_count']); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (!empty($current_results['recommendations'])): ?>
        <div class="kipdev-results-recommendations">
            <h3><?php _e('Recommendations', 'kipdev-optimizer'); ?></h3>
            <?php foreach ($current_results['recommendations'] as $recommendation): ?>
                <div class="notice notice-<?php echo esc_attr($recommendation['type']); ?> inline">
                    <p><?php echo esc_html($recommendation['message']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($progress)): ?>
        <div class="kipdev-results-progress">
            <h3><?php _e('Optimization Progress', 'kipdev-optimizer'); ?></h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Task', 'kipdev-optimizer'); ?></th>
                        <th><?php _e('Status', 'kipdev-optimizer'); ?></th>
                        <th><?php _e('Last Updated', 'kipdev-optimizer'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($progress as $task_name => $task_data): ?>
                    <tr>
                        <td><?php echo esc_html(ucwords(str_replace('_', ' ', $task_name))); ?></td>
                        <td>
                            <?php 
                            $status_class = 'status-' . esc_attr($task_data['status']);
                            echo '<span class="' . $status_class . '">' . esc_html(ucfirst($task_data['status'])) . '</span>';
                            ?>
                        </td>
                        <td><?php echo esc_html($task_data['timestamp']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
