<?php
/**
 * Results Dashboard component.
 *
 * @package KipDev_Optimizer
 */

class KipDev_Results_Dashboard {

    /**
     * Get optimization results.
     */
    public function get_results() {
        $reporter = new KipDev_Results_Reporter();
        return $reporter->get_report();
    }

    /**
     * Get optimization history.
     */
    public function get_history() {
        return get_option('kipdev_optimizer_history', array());
    }

    /**
     * Get performance trends.
     */
    public function get_trends() {
        $history = $this->get_history();
        $trends = array(
            'performance_scores' => array(),
            'images_optimized' => array(),
            'videos_optimized' => array(),
            'dates' => array()
        );

        foreach ($history as $entry) {
            $trends['performance_scores'][] = isset($entry['performance_score']) ? $entry['performance_score'] : 0;
            $trends['images_optimized'][] = isset($entry['images_optimized']) ? $entry['images_optimized'] : 0;
            $trends['videos_optimized'][] = isset($entry['videos_optimized']) ? $entry['videos_optimized'] : 0;
            $trends['dates'][] = isset($entry['date']) ? $entry['date'] : '';
        }

        return $trends;
    }

    /**
     * Get detailed optimization report.
     */
    public function get_detailed_report() {
        $tracker = new KipDev_Progress_Tracker();
        $reporter = new KipDev_Results_Reporter();

        return array(
            'current_results' => $reporter->get_report(),
            'progress' => $tracker->get_progress(),
            'trends' => $this->get_trends(),
            'history' => $this->get_history()
        );
    }

    /**
     * Save optimization snapshot.
     */
    public function save_snapshot() {
        $overview = new KipDev_Performance_Overview();
        $summary = $overview->get_summary();
        $summary['date'] = current_time('mysql');

        $history = $this->get_history();
        $history[] = $summary;

        // Keep only last 30 entries
        if (count($history) > 30) {
            $history = array_slice($history, -30);
        }

        update_option('kipdev_optimizer_history', $history);
    }
}
