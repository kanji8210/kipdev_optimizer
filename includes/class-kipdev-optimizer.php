<?php
/**
 * The core plugin class.
 *
 * @package KipDev_Optimizer
 */

class KipDev_Optimizer {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     */
    protected $version;

    /**
     * Initialize the plugin.
     */
    public function __construct() {
        $this->version = KIPDEV_OPTIMIZER_VERSION;
        $this->plugin_name = 'kipdev-optimizer';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_optimization_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        // Load the loader class
        require_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'includes/class-kipdev-optimizer-loader.php';
        
        // Dashboard components
        require_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'admin/class-kipdev-optimizer-admin.php';
        require_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'admin/class-performance-overview.php';
        require_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'admin/class-optimization-controls.php';
        require_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'admin/class-results-dashboard.php';
        
        // Optimization Engine components
        require_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'includes/optimization-engine/class-image-optimizer.php';
        require_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'includes/optimization-engine/class-video-processor.php';
        require_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'includes/optimization-engine/class-asset-minifier.php';
        require_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'includes/optimization-engine/class-cache-manager.php';
        
        // Metrics System components
        require_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'includes/metrics-system/class-performance-scanner.php';
        require_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'includes/metrics-system/class-progress-tracker.php';
        require_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'includes/metrics-system/class-results-reporter.php';

        $this->loader = new KipDev_Optimizer_Loader();
    }

    /**
     * Register all hooks related to admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new KipDev_Optimizer_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
    }

    /**
     * Register all hooks related to optimization functionality.
     */
    private function define_optimization_hooks() {
        // Initialize optimization components
        $image_optimizer = new KipDev_Image_Optimizer();
        $cache_manager = new KipDev_Cache_Manager();
        
        $this->loader->add_action('init', $cache_manager, 'init');
    }

    /**
     * Run the loader to execute all hooks.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
