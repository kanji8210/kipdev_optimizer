<?php
namespace Kipdev\Optimizer\Integrations\WPBakery;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WPBakery Page Builder Integration
 */
class WPBakery_Integration {
    
    /**
     * Initialize integration
     */
    public static function init() {
        // Check if WPBakery is active
        if ( ! defined( 'WPB_VC_VERSION' ) ) {
            return;
        }
        
        // Load element classes
        self::load_elements();
        
        // Add admin notice
        add_action( 'admin_notices', array( __CLASS__, 'admin_notice' ) );
    }
    
    /**
     * Load element classes
     */
    private static function load_elements() {
        require_once KIPDEV_OPT_PLUGIN_DIR . 'includes/integrations/wpbakery/class-advanced-video-embed.php';
        
        // Initialize elements
        Advanced_Video_Embed::init();
    }
    
    /**
     * Show admin notice when WPBakery integration is active
     */
    public static function admin_notice() {
        static $shown = false;
        
        if ( $shown ) {
            return;
        }
        
        $screen = get_current_screen();
        if ( ! $screen || $screen->id !== 'toplevel_page_kipdev-optimizer' ) {
            return;
        }
        
        $shown = true;
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong><?php esc_html_e( 'KipDev Optimizer:', 'kipdev-optimizer' ); ?></strong>
                <?php esc_html_e( 'WPBakery Page Builder integration is active! You can now use "Advanced Video Embed" element.', 'kipdev-optimizer' ); ?>
            </p>
        </div>
        <?php
    }
}
