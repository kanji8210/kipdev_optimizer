<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package KipDev_Optimizer
 */

class KipDev_Optimizer_Admin {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            KIPDEV_OPTIMIZER_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            KIPDEV_OPTIMIZER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            $this->version,
            false
        );

        // Localize script for AJAX
        wp_localize_script($this->plugin_name, 'kipdevOptimizer', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kipdev_optimizer_nonce')
        ));
    }

    /**
     * Add admin menu pages.
     */
    public function add_admin_menu() {
        add_menu_page(
            __('KipDev Optimizer', 'kipdev-optimizer'),
            __('KipDev Optimizer', 'kipdev-optimizer'),
            'manage_options',
            'kipdev-optimizer',
            array($this, 'display_admin_page'),
            'dashicons-performance',
            30
        );

        add_submenu_page(
            'kipdev-optimizer',
            __('Performance Overview', 'kipdev-optimizer'),
            __('Overview', 'kipdev-optimizer'),
            'manage_options',
            'kipdev-optimizer',
            array($this, 'display_admin_page')
        );

        add_submenu_page(
            'kipdev-optimizer',
            __('Optimization Controls', 'kipdev-optimizer'),
            __('Controls', 'kipdev-optimizer'),
            'manage_options',
            'kipdev-optimizer-controls',
            array($this, 'display_controls_page')
        );

        add_submenu_page(
            'kipdev-optimizer',
            __('Results Dashboard', 'kipdev-optimizer'),
            __('Results', 'kipdev-optimizer'),
            'manage_options',
            'kipdev-optimizer-results',
            array($this, 'display_results_page')
        );
    }

    /**
     * Display the main admin page with performance overview.
     */
    public function display_admin_page() {
        $performance_overview = new KipDev_Performance_Overview();
        include_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'admin/partials/performance-overview-display.php';
    }

    /**
     * Display the optimization controls page.
     */
    public function display_controls_page() {
        $optimization_controls = new KipDev_Optimization_Controls();
        include_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'admin/partials/optimization-controls-display.php';
    }

    /**
     * Display the results dashboard page.
     */
    public function display_results_page() {
        $results_dashboard = new KipDev_Results_Dashboard();
        include_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'admin/partials/results-dashboard-display.php';
    }
}
