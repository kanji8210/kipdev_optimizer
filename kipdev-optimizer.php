<?php
/**
 * Plugin Name: KipDev Simple Optimizer
 * Plugin URI:  https://example.com/kipdev-optimizer
 * Description: Lightweight, high-quality performance plugin scaffold — image optimizer, asset minifier, cache manager and metrics.
 * Version:     0.1.0
 * Author:      KipDev
 * Text Domain: kipdev-optimizer
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define( 'KIPDEV_OPT_DIR', plugin_dir_path( __FILE__ ) );
define( 'KIPDEV_OPT_URL', plugin_dir_url( __FILE__ ) );

// Autoload simple loader
require_once KIPDEV_OPT_DIR . 'includes/helpers.php';
require_once KIPDEV_OPT_DIR . 'includes/admin/class-kipdev-admin.php';
require_once KIPDEV_OPT_DIR . 'includes/engine/class-image-optimizer.php';
require_once KIPDEV_OPT_DIR . 'includes/engine/class-video-processor.php';
require_once KIPDEV_OPT_DIR . 'includes/engine/class-asset-minifier.php';
require_once KIPDEV_OPT_DIR . 'includes/engine/class-cache-manager.php';
require_once KIPDEV_OPT_DIR . 'includes/metrics/class-performance-scanner.php';

// Activation / deactivation
function kipdev_opt_activate() {
    // create default options
    add_option( 'kipdev_opt_options', array(
        'image_quality' => 82,
        'minify_html'   => 1,
    ) );
}
register_activation_hook( __FILE__, 'kipdev_opt_activate' );

function kipdev_opt_deactivate() {
    // keep options — allow manual removal
}
register_deactivation_hook( __FILE__, 'kipdev_opt_deactivate' );

// Instantiate components
function kipdev_opt_init() {
    // instantiate engine classes
    Kipdev\Optimizer\Image_Optimizer::init();
    Kipdev\Optimizer\Video_Processor::init();
    Kipdev\Optimizer\Asset_Minifier::init();
    Kipdev\Optimizer\Cache_Manager::init();
    Kipdev\Metrics\Performance_Scanner::init();
    Kipdev\Admin\Kipdev_Admin::init();
}
add_action( 'plugins_loaded', 'kipdev_opt_init' );
