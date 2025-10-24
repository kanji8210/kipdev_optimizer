<?php
/**
 * Results Reporter component.
 *
 * @package KipDev_Optimizer
 */

class KipDev_Results_Reporter {

    /**
     * Generate optimization report.
     */
    public function get_report() {
        $scanner = new KipDev_Performance_Scanner();
        $tracker = new KipDev_Progress_Tracker();

        $metrics = $scanner->get_last_scan();
        if (empty($metrics)) {
            $metrics = $scanner->scan();
        }

        $report = array(
            'summary' => $this->get_summary(),
            'metrics' => $metrics,
            'optimization_status' => $tracker->get_statistics(),
            'recommendations' => $this->get_recommendations($metrics),
            'generated_at' => current_time('mysql')
        );

        return $report;
    }

    /**
     * Get optimization summary.
     */
    private function get_summary() {
        return array(
            'images_optimized' => get_option('kipdev_optimizer_images_optimized', 0),
            'videos_optimized' => get_option('kipdev_optimizer_videos_optimized', 0),
            'cache_enabled' => get_option('kipdev_optimizer_cache_enabled', true),
            'asset_minification' => get_option('kipdev_optimizer_asset_minification', true),
            'lazy_load_enabled' => get_option('kipdev_optimizer_lazy_load', true)
        );
    }

    /**
     * Get recommendations based on metrics.
     */
    private function get_recommendations($metrics) {
        $recommendations = array();

        // Check performance score
        if (isset($metrics['score']) && $metrics['score'] < 70) {
            $recommendations[] = array(
                'type' => 'warning',
                'message' => 'Performance score is below optimal. Consider optimizing images and reducing scripts.',
                'action' => 'optimize_images'
            );
        }

        // Check cache status
        if (!get_option('kipdev_optimizer_cache_enabled', true)) {
            $recommendations[] = array(
                'type' => 'important',
                'message' => 'Browser caching is disabled. Enable it to improve load times.',
                'action' => 'enable_cache'
            );
        }

        // Check images count
        if (isset($metrics['images_count']) && $metrics['images_count'] > 100) {
            $optimized = get_option('kipdev_optimizer_images_optimized', 0);
            if ($optimized < $metrics['images_count']) {
                $recommendations[] = array(
                    'type' => 'info',
                    'message' => sprintf('You have %d unoptimized images. Run image optimization.', 
                        $metrics['images_count'] - $optimized),
                    'action' => 'optimize_images'
                );
            }
        }

        // Check asset minification
        if (!get_option('kipdev_optimizer_asset_minification', true)) {
            $recommendations[] = array(
                'type' => 'info',
                'message' => 'Asset minification is disabled. Enable it to reduce file sizes.',
                'action' => 'enable_minification'
            );
        }

        return $recommendations;
    }

    /**
     * Export report to file.
     */
    public function export_report($format = 'json') {
        $report = $this->get_report();

        switch ($format) {
            case 'json':
                return json_encode($report, JSON_PRETTY_PRINT);
            case 'csv':
                return $this->convert_to_csv($report);
            default:
                return $report;
        }
    }

    /**
     * Convert report to CSV format.
     */
    private function convert_to_csv($report) {
        $csv = "Metric,Value\n";

        foreach ($report['summary'] as $key => $value) {
            $csv .= sprintf("%s,%s\n", $key, is_bool($value) ? ($value ? 'Yes' : 'No') : $value);
        }

        if (isset($report['metrics'])) {
            foreach ($report['metrics'] as $key => $value) {
                if (!is_array($value)) {
                    $csv .= sprintf("%s,%s\n", $key, $value);
                }
            }
        }

        return $csv;
    }

    /**
     * Get performance comparison.
     */
    public function get_comparison() {
        $current = $this->get_report();
        $history = get_option('kipdev_optimizer_history', array());

        if (empty($history)) {
            return null;
        }

        $previous = end($history);

        return array(
            'current' => $current,
            'previous' => $previous,
            'improvement' => $this->calculate_improvement($current, $previous)
        );
    }

    /**
     * Calculate improvement percentage.
     */
    private function calculate_improvement($current, $previous) {
        $improvements = array();

        if (isset($current['metrics']['score']) && isset($previous['performance_score'])) {
            $score_diff = $current['metrics']['score'] - $previous['performance_score'];
            $improvements['performance_score'] = array(
                'value' => $score_diff,
                'percentage' => $previous['performance_score'] > 0 ? 
                    round(($score_diff / $previous['performance_score']) * 100, 2) : 0
            );
        }

        return $improvements;
    }
}
