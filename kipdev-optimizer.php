<?php
/**
 * Plugin Name: KipDev Optimizer
 * Plugin URI: https://github.com/kanji8210/kipdev_optimizer
 * Description: Lightweight performance optimization plugin that speeds up your site by optimizing images, videos, and implementing best practices.
 * Version: 1.0.0
 * Author: KipDev
 * Author URI: https://github.com/kanji8210
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kipdev-optimizer
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('KIPDEV_OPTIMIZER_VERSION', '1.0.0');
define('KIPDEV_OPTIMIZER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KIPDEV_OPTIMIZER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KIPDEV_OPTIMIZER_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Require the autoloader
require_once KIPDEV_OPTIMIZER_PLUGIN_DIR . 'includes/class-kipdev-optimizer.php';

/**
 * Initialize the plugin
 */
function run_kipdev_optimizer() {
    $plugin = new KipDev_Optimizer();
    $plugin->run();
}

// Run the plugin
run_kipdev_optimizer();
