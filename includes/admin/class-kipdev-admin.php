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
        wp_enqueue_style( 'kipdev-opt-admin', KIPDEV_OPT_URL . 'assets/css/admin.css', array(), '0.2.2' );
        wp_enqueue_script( 'kipdev-opt-admin', KIPDEV_OPT_URL . 'assets/js/admin.js', array( 'jquery' ), '0.2.2', true );
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
                <a class="nav-tab" href="#converter"><?php esc_html_e( 'Image Converter', 'kipdev-optimizer' ); ?></a>
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
                            <th><?php esc_html_e( 'Serve WebP to browsers', 'kipdev-optimizer' ); ?></th>
                            <td>
                                <input type="checkbox" name="serve_webp" value="1" <?php checked( $opts['serve_webp'], 1 ); ?>>
                                <p class="description"><?php esc_html_e( 'Automatically serve WebP images to supporting browsers (Chrome, Firefox, Edge)', 'kipdev-optimizer' ); ?></p>
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

            <div id="converter" class="kipdev-tab-panel" style="display:none">
                <h3><?php esc_html_e( 'PNG to WebP Converter', 'kipdev-optimizer' ); ?></h3>
                <p><?php esc_html_e( 'Convert your PNG images to WebP format for better performance. WebP images are 30-40% smaller while maintaining quality and transparency.', 'kipdev-optimizer' ); ?></p>
                
                <div class="converter-actions">
                    <button id="kipdev-load-images" class="button button-primary"><?php esc_html_e( 'Load PNG Images', 'kipdev-optimizer' ); ?></button>
                    <button id="kipdev-convert-selected" class="button button-secondary" style="display:none;"><?php esc_html_e( 'Convert Selected to WebP', 'kipdev-optimizer' ); ?></button>
                    <button id="kipdev-convert-all" class="button button-secondary" style="display:none;"><?php esc_html_e( 'Convert All to WebP', 'kipdev-optimizer' ); ?></button>
                    <span id="conversion-status" style="margin-left: 15px; font-weight: 500;"></span>
                </div>
                
                <div id="conversion-progress" style="display:none; margin: 20px 0;">
                    <div class="progress-bar large">
                        <div id="conversion-progress-bar" class="progress-fill" style="width: 0%; background-color: #2271b1;"></div>
                    </div>
                    <p id="conversion-progress-text" style="margin-top: 10px; text-align: center; color: #666;"></p>
                </div>
                
                <div id="images-container" style="margin-top: 20px;">
                    <p style="color: #666; font-style: italic;"><?php esc_html_e( 'Click "Load PNG Images" to see available images.', 'kipdev-optimizer' ); ?></p>
                </div>
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
            echo '<h4>' . esc_html__( 'Last Performance Scan', 'kipdev-optimizer' ) . '</h4>';
            echo '<p style="color: #666; font-size: 13px;">' . esc_html__( 'Scanned:', 'kipdev-optimizer' ) . ' ' . esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last['timestamp'] ) ) . '</p>';
            
            // Page Load Time with progress bar
            $load_time = floatval( $last['load_time'] );
            $load_score = $this->calculate_load_score( $load_time );
            $load_color = $this->get_score_color( $load_score );
            
            echo '<div class="metric-item">';
            echo '<div class="metric-header">';
            echo '<span class="metric-label">' . esc_html__( 'Page Load Time', 'kipdev-optimizer' ) . '</span>';
            echo '<span class="metric-value"><strong>' . esc_html( $load_time ) . 's</strong> <span class="metric-score" style="color: ' . esc_attr( $load_color ) . ';">' . esc_html( $load_score ) . '%</span></span>';
            echo '</div>';
            echo '<div class="progress-bar">';
            echo '<div class="progress-fill" style="width: ' . esc_attr( $load_score ) . '%; background-color: ' . esc_attr( $load_color ) . ';"></div>';
            echo '</div>';
            echo '<p class="metric-hint">' . $this->get_load_hint( $load_time ) . '</p>';
            echo '</div>';
            
            // Images Optimized with progress bar
            $images_optimized = intval( $last['images'] );
            $total_images = $this->get_total_images();
            $image_score = $total_images > 0 ? min( 100, round( ( $images_optimized / $total_images ) * 100 ) ) : 0;
            $image_color = $this->get_score_color( $image_score );
            
            echo '<div class="metric-item">';
            echo '<div class="metric-header">';
            echo '<span class="metric-label">' . esc_html__( 'Images Optimized', 'kipdev-optimizer' ) . '</span>';
            echo '<span class="metric-value"><strong>' . esc_html( $images_optimized ) . '</strong> / ' . esc_html( $total_images ) . ' <span class="metric-score" style="color: ' . esc_attr( $image_color ) . ';">' . esc_html( $image_score ) . '%</span></span>';
            echo '</div>';
            echo '<div class="progress-bar">';
            echo '<div class="progress-fill" style="width: ' . esc_attr( $image_score ) . '%; background-color: ' . esc_attr( $image_color ) . ';"></div>';
            echo '</div>';
            if ( $image_score < 100 && $total_images > $images_optimized ) {
                echo '<p class="metric-hint">💡 ' . sprintf( esc_html__( '%d images pending optimization. Use WP-CLI: wp kipdev optimize-images', 'kipdev-optimizer' ), $total_images - $images_optimized ) . '</p>';
            }
            echo '</div>';
            
            // Overall Performance Score
            $overall_score = round( ( $load_score + $image_score ) / 2 );
            $overall_color = $this->get_score_color( $overall_score );
            
            echo '<div class="metric-item overall-score">';
            echo '<div class="metric-header">';
            echo '<span class="metric-label"><strong>' . esc_html__( 'Overall Performance Score', 'kipdev-optimizer' ) . '</strong></span>';
            echo '<span class="metric-value"><strong style="font-size: 24px; color: ' . esc_attr( $overall_color ) . ';">' . esc_html( $overall_score ) . '%</strong></span>';
            echo '</div>';
            echo '<div class="progress-bar large">';
            echo '<div class="progress-fill" style="width: ' . esc_attr( $overall_score ) . '%; background-color: ' . esc_attr( $overall_color ) . ';"></div>';
            echo '</div>';
            echo '</div>';
            
        } else {
            echo '<h4>' . esc_html__( 'Performance Scan', 'kipdev-optimizer' ) . '</h4>';
            echo '<p>' . esc_html__( 'No scans yet. Run a scan to collect metrics.', 'kipdev-optimizer' ) . '</p>';
        }
        
        // Display Jetpack status
        $jetpack = \Kipdev\Optimizer\Helpers\get_jetpack_status();
        echo '<hr style="margin: 20px 0;">';
        echo '<h4>' . esc_html__( 'Jetpack Integration Status', 'kipdev-optimizer' ) . '</h4>';
        
        if ( $jetpack['active'] ) {
            echo '<p style="color: #00a32a;">✅ <strong>' . esc_html__( 'Jetpack is active', 'kipdev-optimizer' ) . '</strong>';
            if ( $jetpack['version'] ) {
                echo ' (v' . esc_html( $jetpack['version'] ) . ')';
            }
            echo '</p>';
            
            // Show active features
            if ( ! empty( $jetpack['features'] ) ) {
                echo '<h5>' . esc_html__( 'Active Jetpack Features:', 'kipdev-optimizer' ) . '</h5>';
                echo '<ul class="jetpack-features">';
                foreach ( $jetpack['features'] as $module => $label ) {
                    echo '<li><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> ' . esc_html( $label ) . '</li>';
                }
                echo '</ul>';
            }
            
            // Show CDN domains
            if ( ! empty( $jetpack['cdn_domains'] ) ) {
                echo '<h5>' . esc_html__( 'CDN Domains in Use:', 'kipdev-optimizer' ) . '</h5>';
                echo '<ul class="jetpack-cdn">';
                foreach ( $jetpack['cdn_domains'] as $domain ) {
                    echo '<li><code>' . esc_html( $domain ) . '</code></li>';
                }
                echo '</ul>';
            }
            
            // Show conflicts/recommendations
            $conflicts = \Kipdev\Optimizer\Helpers\get_jetpack_conflicts();
            if ( ! empty( $conflicts ) ) {
                echo '<h5>' . esc_html__( 'Integration Notes:', 'kipdev-optimizer' ) . '</h5>';
                foreach ( $conflicts as $conflict ) {
                    $color = ( $conflict['type'] === 'warning' ) ? '#dba617' : '#2271b1';
                    $icon = ( $conflict['type'] === 'warning' ) ? 'warning' : 'info';
                    echo '<div class="jetpack-conflict" style="background: #f0f0f1; padding: 10px; margin: 10px 0; border-left: 4px solid ' . esc_attr( $color ) . ';">';
                    echo '<p style="margin: 0 0 5px 0;"><span class="dashicons dashicons-' . esc_attr( $icon ) . '" style="color: ' . esc_attr( $color ) . ';"></span> <strong>' . esc_html( $conflict['feature'] ) . ':</strong> ' . esc_html( $conflict['message'] ) . '</p>';
                    echo '<p style="margin: 5px 0 0 24px; font-size: 12px; color: #666;">' . esc_html( $conflict['recommendation'] ) . '</p>';
                    echo '</div>';
                }
            }
            
        } else {
            echo '<p style="color: #666;">ℹ️ ' . esc_html__( 'Jetpack is not active. All KipDev optimization features are fully enabled.', 'kipdev-optimizer' ) . '</p>';
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
        if ( 'load_png_images' === $action ) {
            $images = $this->get_png_images();
            wp_send_json_success( $images );
        }
        if ( 'convert_to_webp' === $action ) {
            $attachment_id = isset( $_POST['attachment_id'] ) ? intval( $_POST['attachment_id'] ) : 0;
            if ( ! $attachment_id ) {
                wp_send_json_error( 'invalid-id' );
            }
            $result = $this->convert_image_to_webp( $attachment_id );
            wp_send_json_success( $result );
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
    
    /**
     * Calculate load time score (0-100, higher is better)
     */
    private function calculate_load_score( $load_time ) {
        // Perfect: < 1s = 100%
        // Good: 1-2s = 80-100%
        // Average: 2-3s = 60-80%
        // Poor: 3-5s = 40-60%
        // Very Poor: > 5s = 0-40%
        if ( $load_time < 1 ) {
            return 100;
        } elseif ( $load_time < 2 ) {
            return 100 - ( ( $load_time - 1 ) * 20 );
        } elseif ( $load_time < 3 ) {
            return 80 - ( ( $load_time - 2 ) * 20 );
        } elseif ( $load_time < 5 ) {
            return 60 - ( ( $load_time - 3 ) * 10 );
        } else {
            return max( 0, 40 - ( ( $load_time - 5 ) * 8 ) );
        }
    }
    
    /**
     * Get color based on score
     */
    private function get_score_color( $score ) {
        if ( $score >= 80 ) {
            return '#00a32a'; // Green - Good
        } elseif ( $score >= 60 ) {
            return '#dba617'; // Orange - Average
        } else {
            return '#d63638'; // Red - Poor
        }
    }
    
    /**
     * Get hint text based on load time
     */
    private function get_load_hint( $load_time ) {
        if ( $load_time < 1 ) {
            return '✅ ' . esc_html__( 'Excellent! Your site loads very fast.', 'kipdev-optimizer' );
        } elseif ( $load_time < 2 ) {
            return '✅ ' . esc_html__( 'Good load time. Consider enabling page caching for further improvement.', 'kipdev-optimizer' );
        } elseif ( $load_time < 3 ) {
            return '⚠️ ' . esc_html__( 'Average load time. Enable page caching and defer JavaScript.', 'kipdev-optimizer' );
        } else {
            return '❌ ' . esc_html__( 'Slow load time. Enable all optimizations and consider a CDN.', 'kipdev-optimizer' );
        }
    }
    
    /**
     * Get total images in media library
     */
    private function get_total_images() {
        $count = wp_cache_get( 'kipdev_total_images', 'kipdev_optimizer' );
        if ( false === $count ) {
            $count = wp_count_posts( 'attachment' );
            // Get image attachments only
            global $wpdb;
            $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'" );
            $count = intval( $count );
            wp_cache_set( 'kipdev_total_images', $count, 'kipdev_optimizer', HOUR_IN_SECONDS );
        }
        return $count;
    }
    
    /**
     * Get all PNG images from media library
     */
    private function get_png_images() {
        global $wpdb;
        
        $query = "SELECT ID, post_title, post_date 
                  FROM {$wpdb->posts} 
                  WHERE post_type = 'attachment' 
                  AND post_mime_type = 'image/png' 
                  ORDER BY post_date DESC 
                  LIMIT 100";
        
        $attachments = $wpdb->get_results( $query );
        $images = array();
        
        foreach ( $attachments as $attachment ) {
            $file = get_attached_file( $attachment->ID );
            if ( ! $file || ! file_exists( $file ) ) {
                continue;
            }
            
            $file_size = filesize( $file );
            $webp_file = preg_replace( '/\.png$/i', '.webp', $file );
            $has_webp = file_exists( $webp_file );
            $webp_size = $has_webp ? filesize( $webp_file ) : 0;
            
            $images[] = array(
                'id' => $attachment->ID,
                'title' => $attachment->post_title ? $attachment->post_title : basename( $file ),
                'url' => wp_get_attachment_url( $attachment->ID ),
                'thumb' => wp_get_attachment_image_url( $attachment->ID, 'thumbnail' ),
                'file_size' => size_format( $file_size, 2 ),
                'file_size_bytes' => $file_size,
                'has_webp' => $has_webp,
                'webp_size' => $has_webp ? size_format( $webp_size, 2 ) : '',
                'webp_size_bytes' => $webp_size,
                'savings' => $has_webp ? round( ( ( $file_size - $webp_size ) / $file_size ) * 100 ) : 0,
                'date' => date_i18n( get_option( 'date_format' ), strtotime( $attachment->post_date ) ),
            );
        }
        
        return array(
            'images' => $images,
            'total' => count( $images ),
        );
    }
    
    /**
     * Convert a single image to WebP
     */
    private function convert_image_to_webp( $attachment_id ) {
        $file = get_attached_file( $attachment_id );
        
        if ( ! $file || ! file_exists( $file ) ) {
            return array(
                'success' => false,
                'message' => 'File not found',
            );
        }
        
        $mime = get_post_mime_type( $attachment_id );
        if ( $mime !== 'image/png' ) {
            return array(
                'success' => false,
                'message' => 'Not a PNG file',
            );
        }
        
        // Check if WebP already exists
        $webp_file = preg_replace( '/\.png$/i', '.webp', $file );
        if ( file_exists( $webp_file ) ) {
            $original_size = filesize( $file );
            $webp_size = filesize( $webp_file );
            return array(
                'success' => true,
                'message' => 'WebP already exists',
                'already_exists' => true,
                'webp_size' => size_format( $webp_size, 2 ),
                'savings' => round( ( ( $original_size - $webp_size ) / $original_size ) * 100 ),
            );
        }
        
        // Load image
        $img = @imagecreatefrompng( $file );
        if ( ! $img ) {
            return array(
                'success' => false,
                'message' => 'Failed to load PNG',
            );
        }
        
        // Preserve transparency
        imagealphablending( $img, false );
        imagesavealpha( $img, true );
        
        // Get quality setting
        $opts = \Kipdev\Optimizer\Helpers\get_options();
        $quality = isset( $opts['image_quality'] ) ? intval( $opts['image_quality'] ) : 82;
        
        // Generate WebP
        $result = @imagewebp( $img, $webp_file, $quality );
        imagedestroy( $img );
        
        if ( ! $result ) {
            return array(
                'success' => false,
                'message' => 'Failed to generate WebP',
            );
        }
        
        // Calculate savings
        $original_size = filesize( $file );
        $webp_size = filesize( $webp_file );
        $savings = round( ( ( $original_size - $webp_size ) / $original_size ) * 100 );
        
        return array(
            'success' => true,
            'message' => 'Converted successfully',
            'webp_size' => size_format( $webp_size, 2 ),
            'webp_size_bytes' => $webp_size,
            'savings' => $savings,
        );
    }
}
