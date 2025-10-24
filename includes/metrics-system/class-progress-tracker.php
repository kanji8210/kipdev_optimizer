<?php
/**
 * Progress Tracker component.
 *
 * @package KipDev_Optimizer
 */

class KipDev_Progress_Tracker {

    /**
     * Track optimization progress.
     */
    public function track($task, $status, $details = array()) {
        $progress = get_option('kipdev_optimizer_progress', array());

        $progress[$task] = array(
            'status' => $status,
            'details' => $details,
            'timestamp' => current_time('mysql')
        );

        update_option('kipdev_optimizer_progress', $progress);

        return $progress[$task];
    }

    /**
     * Get progress for a specific task.
     */
    public function get_task_progress($task) {
        $progress = get_option('kipdev_optimizer_progress', array());
        return isset($progress[$task]) ? $progress[$task] : null;
    }

    /**
     * Get all progress.
     */
    public function get_progress() {
        return get_option('kipdev_optimizer_progress', array());
    }

    /**
     * Get overall progress percentage.
     */
    public function get_overall_percentage() {
        $progress = $this->get_progress();

        if (empty($progress)) {
            return 0;
        }

        $total = count($progress);
        $completed = 0;

        foreach ($progress as $task) {
            if (isset($task['status']) && $task['status'] === 'completed') {
                $completed++;
            }
        }

        return round(($completed / $total) * 100);
    }

    /**
     * Start tracking a task.
     */
    public function start_task($task, $details = array()) {
        return $this->track($task, 'in_progress', $details);
    }

    /**
     * Complete a task.
     */
    public function complete_task($task, $details = array()) {
        return $this->track($task, 'completed', $details);
    }

    /**
     * Fail a task.
     */
    public function fail_task($task, $error_message) {
        return $this->track($task, 'failed', array('error' => $error_message));
    }

    /**
     * Clear all progress.
     */
    public function clear_progress() {
        delete_option('kipdev_optimizer_progress');
    }

    /**
     * Get task statistics.
     */
    public function get_statistics() {
        $progress = $this->get_progress();

        $stats = array(
            'total_tasks' => count($progress),
            'completed' => 0,
            'in_progress' => 0,
            'failed' => 0
        );

        foreach ($progress as $task) {
            if (isset($task['status'])) {
                switch ($task['status']) {
                    case 'completed':
                        $stats['completed']++;
                        break;
                    case 'in_progress':
                        $stats['in_progress']++;
                        break;
                    case 'failed':
                        $stats['failed']++;
                        break;
                }
            }
        }

        return $stats;
    }
}
