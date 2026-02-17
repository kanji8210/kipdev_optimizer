<?php
namespace Kipdev\Admin;

use function Kipdev\Optimizer\Helpers\get_options;
use function Kipdev\Optimizer\Helpers\update_options;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Kipdev_Admin {
    protected static $instance = null;

    public static function init() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
        add_action( 'wp_ajax_kipdev_opt_action', array( $this, 'handle_ajax' ) );
        add_action( 'admin_post_kipdev_save_options', array( $this, 'save_options' ) );
    }

    public function add_menu() {
        add_menu_page(
            __( 'Simple Optimizer', 'kipdev-optimizer' ),
            __( 'Simple Optimizer', 'kipdev-optimizer' ),
            'manage_options',
            'kipdev-optimizer',
            array( $this, 'render_dashboard' ),
            'dashicons-performance'
        );
    }

    public function enqueue( $hook ) {
        if ( strpos( $hook, 'kipdev-optimizer' ) === false ) {
            return;
        }
        wp_enqueue_style( 'kipdev-opt-admin', KIPDEV_OPT_URL . 'assets/css/admin.css', array(), '0.1' );
        wp_enqueue_script( 'kipdev-opt-admin', KIPDEV_OPT_URL . 'assets/js/admin.js', array( 'jquery' ), '0.1', true );
        wp_localize_script( 'kipdev-opt-admin', 'KIPDEV_OPT', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'kipdev_opt_nonce' ),
        ) );
    }

    public function render_dashboard() {
        $opts = get_options();
        ?>
        <div class="kipdev-admin-wrap">
            <h1><?php esc_html_e( 'Simple Optimizer', 'kipdev-optimizer' ); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a class="nav-tab nav-tab-active" href="#overview"><?php esc_html_e( 'Performance Overview', 'kipdev-optimizer' ); ?></a>
                <a class="nav-tab" href="#controls"><?php esc_html_e( 'Optimization Controls', 'kipdev-optimizer' ); ?></a>
                <a class="nav-tab" href="#results"><?php esc_html_e( 'Results Dashboard', 'kipdev-optimizer' ); ?></a>
            </h2>

            <div id="overview" class="kipdev-tab-panel">
                <h3><?php esc_html_e( 'Performance Overview', 'kipdev-optimizer' ); ?></h3>
                <p><?php esc_html_e( 'Basic site metrics and recent scan results.', 'kipdev-optimizer' ); ?></p>
                <?php $this->render_overview(); ?>
            </div>

            <div id="controls" class="kipdev-tab-panel" style="display:none">
                <h3><?php esc_html_e( 'Optimization Controls', 'kipdev-optimizer' ); ?></h3>
                <form id="kipdev-options-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'kipdev_save_opts', 'kipdev_nonce' ); ?>
                    <input type="hidden" name="action" value="kipdev_save_options">
                    <table class="form-table">
                        <tr>
                            <th><label for="image_quality"><?php esc_html_e( 'Image quality (JPEG)', 'kipdev-optimizer' ); ?></label></th>
                            <td>
                                <input type="number" id="image_quality" name="image_quality" value="<?php echo esc_attr( $opts['image_quality'] ); ?>" min="10" max="100">
                                <p class="description"><?php esc_html_e( 'Quality for JPEG compression (10-100, recommended: 82)', 'kipdev-optimizer' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Generate WebP images', 'kipdev-optimizer' ); ?></th>
                            <td>
                                <input type="checkbox" name="generate_webp" value="1" <?php checked( $opts['generate_webp'], 1 ); ?>>
                                <p class="description"><?php esc_html_e( 'Create WebP versions of images (30% smaller than JPEG)', 'kipdev-optimizer' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Minify HTML output', 'kipdev-optimizer' ); ?></th>
                            <td>
                                <input type="checkbox" name="minify_html" value="1" <?php checked( $opts['minify_html'], 1 ); ?>>
                                <p class="description"><?php esc_html_e( 'Remove whitespace and comments from HTML', 'kipdev-optimizer' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Enable lazy loading', 'kipdev-optimizer' ); ?></th>
                            <td>
                                <input type="checkbox" name="enable_lazy_loading" value="1" <?php checked( $opts['enable_lazy_loading'], 1 ); ?>>
                                <p class="description"><?php esc_html_e( 'Lazy load images (auto-disabled if Jetpack lazy loading is active)', 'kipdev-optimizer' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Defer JavaScript', 'kipdev-optimizer' ); ?></th>
                            <td>
                                <input type="checkbox" name="defer_js" value="1" <?php checked( $opts['defer_js'], 1 ); ?>>
                                <p class="description"><?php esc_html_e( 'Defer non-critical JavaScript loading', 'kipdev-optimizer' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Enable page caching', 'kipdev-optimizer' ); ?></th>
                            <td>
                                <input type="checkbox" name="enable_page_cache" value="1" <?php checked( $opts['enable_page_cache'], 1 ); ?>>
                                <p class="description"><?php esc_html_e( 'Cache full pages for faster loading (not for logged-in users)', 'kipdev-optimizer' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Jetpack compatibility', 'kipdev-optimizer' ); ?></th>
                            <td>
                                <input type="checkbox" name="jetpack_compatibility" value="1" <?php checked( $opts['jetpack_compatibility'], 1 ); ?>>
                                <p class="description"><?php esc_html_e( 'Auto-detect Jetpack and defer to its optimization features', 'kipdev-optimizer' ); ?></p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit"><button class="button button-primary" type="submit"><?php esc_html_e( 'Save settings', 'kipdev-optimizer' ); ?></button></p>
                </form>

                <p>
                    <button id="kipdev-run-scan" class="button"><?php esc_html_e( 'Run Performance Scan', 'kipdev-optimizer' ); ?></button>
                    <button id="kipdev-clear-cache" class="button"><?php esc_html_e( 'Clear Plugin Cache', 'kipdev-optimizer' ); ?></button>
                    <button id="kipdev-optimize-db" class="button"><?php esc_html_e( 'Optimize Database', 'kipdev-optimizer' ); ?></button>
                </p>
            </div>

            <div id="results" class="kipdev-tab-panel" style="display:none">
                <h3><?php esc_html_e( 'Results Dashboard', 'kipdev-optimizer' ); ?></h3>
                <div id="kipdev-results">
                    <?php $this->render_results(); ?>
                </div>
            </div>
        </div>
        <?php
    }

    protected function render_overview() {
        $last = get_option( 'kipdev_last_scan', false );
        if ( $last ) {
            echo '<p>' . esc_html__( 'Last scan:', 'kipdev-optimizer' ) . ' ' . esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last['timestamp'] ) ) . '</p>';
            echo '<ul>';
            echo '<li>' . esc_html__( 'Page load time (s):', 'kipdev-optimizer' ) . ' ' . esc_html( $last['load_time'] ) . '</li>';
            echo '<li>' . esc_html__( 'Images optimized:', 'kipdev-optimizer' ) . ' ' . esc_html( intval( $last['images'] ) ) . '</li>';
            echo '</ul>';
        } else {
            echo '<p>' . esc_html__( 'No scans yet. Run a scan to collect metrics.', 'kipdev-optimizer' ) . '</p>';
        }
    }

    protected function render_results() {
        $log = get_option( 'kipdev_opt_log', array() );
        if ( empty( $log ) ) {
            echo '<p>' . esc_html__( 'No results yet.', 'kipdev-optimizer' ) . '</p>';
            return;
        }
        echo '<ul class="kipdev-results-list">';
        foreach ( array_reverse( $log ) as $entry ) {
            // Handle both old string format and new array format
            if ( is_array( $entry ) ) {
                $time = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $entry['time'] );
                $level = isset( $entry['level'] ) ? $entry['level'] : 'info';
                $level_class = 'log-' . esc_attr( $level );
                echo '<li class="' . $level_class . '"><strong>[' . esc_html( $time ) . ']</strong> <span class="level-' . esc_attr( $level ) . '">' . ucfirst( esc_html( $level ) ) . ':</span> ' . esc_html( $entry['message'] ) . '</li>';
            } else {
                // Old format - just plain string
                echo '<li>' . esc_html( $entry ) . '</li>';
            }
        }
        echo '</ul>';
    }

    public function handle_ajax() {
        check_ajax_referer( 'kipdev_opt_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'no-permission' );
        }
        $action = isset( $_POST['do'] ) ? sanitize_text_field( wp_unslash( $_POST['do'] ) ) : '';
        if ( 'run_scan' === $action ) {
            $res = \Kipdev\Metrics\Performance_Scanner::run_scan();
            wp_send_json_success( $res );
        }
        if ( 'clear_cache' === $action ) {
            \Kipdev\Optimizer\Cache_Manager::clear_all();
            wp_send_json_success( array( 'cleared' => true ) );
        }
        if ( 'optimize_db' === $action ) {
            $res = \Kipdev\Optimizer\Cache_Manager::optimize_database();
            wp_send_json_success( $res );
        }
        wp_send_json_error( 'unknown' );
    }

    public function save_options() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Insufficient permissions.', 'kipdev-optimizer' ) );
        }
        if ( ! isset( $_POST['kipdev_nonce'] ) || ! wp_verify_nonce( $_POST['kipdev_nonce'], 'kipdev_save_opts' ) ) {
            wp_die( __( 'Security check failed.', 'kipdev-optimizer' ) );
        }
        update_options( $_POST );
        wp_redirect( admin_url( 'admin.php?page=kipdev-optimizer&updated=1' ) );
        exit;
    }
}
