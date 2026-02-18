<?php
namespace Kipdev\Optimizer\Integrations\WPBakery;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Advanced Video Embed Element for WPBakery Page Builder
 */
class Advanced_Video_Embed {
    
    /**
     * Initialize the element
     */
    public static function init() {
        // Register element with WPBakery
        add_action( 'vc_before_init', array( __CLASS__, 'register_element' ) );
        
        // Register shortcode
        add_shortcode( 'kipdev_advanced_video', array( __CLASS__, 'render_shortcode' ) );
        
        // Enqueue frontend assets
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
    }
    
    /**
     * Register element with WPBakery
     */
    public static function register_element() {
        if ( ! function_exists( 'vc_map' ) ) {
            return;
        }
        
        vc_map( array(
            'name' => __( 'Advanced Video Embed', 'kipdev-optimizer' ),
            'description' => __( 'Embed YouTube and Vimeo videos with advanced controls', 'kipdev-optimizer' ),
            'base' => 'kipdev_advanced_video',
            'icon' => 'icon-wpb-film-youtube',
            'category' => __( 'KipDev Performance', 'kipdev-optimizer' ),
            'params' => array(
                
                // Video Source
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Video Source', 'kipdev-optimizer' ),
                    'param_name' => 'video_source',
                    'value' => array(
                        __( 'YouTube', 'kipdev-optimizer' ) => 'youtube',
                        __( 'Vimeo', 'kipdev-optimizer' ) => 'vimeo',
                    ),
                    'std' => 'youtube',
                    'description' => __( 'Select video platform', 'kipdev-optimizer' ),
                ),
                
                // Video ID
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Video ID', 'kipdev-optimizer' ),
                    'param_name' => 'video_id',
                    'description' => __( 'Enter YouTube video ID (e.g., dQw4w9WgXcQ) or Vimeo ID (e.g., 123456789)', 'kipdev-optimizer' ),
                    'admin_label' => true,
                ),
                
                // Privacy Mode
                array(
                    'type' => 'checkbox',
                    'heading' => __( 'Privacy Mode', 'kipdev-optimizer' ),
                    'param_name' => 'privacy_mode',
                    'value' => array( __( 'Enable YouTube Privacy Mode (no cookies)', 'kipdev-optimizer' ) => 'yes' ),
                    'description' => __( 'Use youtube-nocookie.com for GDPR compliance', 'kipdev-optimizer' ),
                    'dependency' => array(
                        'element' => 'video_source',
                        'value' => array( 'youtube' ),
                    ),
                ),
                
                // Autoplay
                array(
                    'type' => 'checkbox',
                    'heading' => __( 'Autoplay', 'kipdev-optimizer' ),
                    'param_name' => 'autoplay',
                    'value' => array( __( 'Enable autoplay', 'kipdev-optimizer' ) => 'yes' ),
                    'description' => __( 'Video will start playing automatically', 'kipdev-optimizer' ),
                ),
                
                // Mute
                array(
                    'type' => 'checkbox',
                    'heading' => __( 'Mute', 'kipdev-optimizer' ),
                    'param_name' => 'mute',
                    'value' => array( __( 'Mute video by default', 'kipdev-optimizer' ) => 'yes' ),
                    'description' => __( 'Required for autoplay in most browsers', 'kipdev-optimizer' ),
                ),
                
                // Loop
                array(
                    'type' => 'checkbox',
                    'heading' => __( 'Loop', 'kipdev-optimizer' ),
                    'param_name' => 'loop',
                    'value' => array( __( 'Loop video playback', 'kipdev-optimizer' ) => 'yes' ),
                ),
                
                // Player Controls
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Player Controls', 'kipdev-optimizer' ),
                    'param_name' => 'controls',
                    'value' => array(
                        __( 'Show controls', 'kipdev-optimizer' ) => 'show',
                        __( 'Hide controls', 'kipdev-optimizer' ) => 'hide',
                        __( 'Auto-hide controls', 'kipdev-optimizer' ) => 'autohide',
                    ),
                    'std' => 'show',
                ),
                
                // Animation
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Animation', 'kipdev-optimizer' ),
                    'param_name' => 'animation',
                    'value' => array(
                        __( 'None', 'kipdev-optimizer' ) => 'none',
                        __( 'Fade In', 'kipdev-optimizer' ) => 'fade-in',
                        __( 'Slide Up', 'kipdev-optimizer' ) => 'slide-up',
                        __( 'Slide Down', 'kipdev-optimizer' ) => 'slide-down',
                        __( 'Slide Left', 'kipdev-optimizer' ) => 'slide-left',
                        __( 'Slide Right', 'kipdev-optimizer' ) => 'slide-right',
                        __( 'Zoom In', 'kipdev-optimizer' ) => 'zoom-in',
                        __( 'Zoom Out', 'kipdev-optimizer' ) => 'zoom-out',
                    ),
                    'std' => 'none',
                    'group' => __( 'Design', 'kipdev-optimizer' ),
                ),
                
                // Animation Duration
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Animation Duration (ms)', 'kipdev-optimizer' ),
                    'param_name' => 'animation_duration',
                    'value' => '600',
                    'description' => __( 'Animation duration in milliseconds', 'kipdev-optimizer' ),
                    'group' => __( 'Design', 'kipdev-optimizer' ),
                    'dependency' => array(
                        'element' => 'animation',
                        'value_not_equal_to' => array( 'none' ),
                    ),
                ),
                
                // Aspect Ratio
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Aspect Ratio', 'kipdev-optimizer' ),
                    'param_name' => 'aspect_ratio',
                    'value' => array(
                        __( '16:9 (Standard)', 'kipdev-optimizer' ) => '16-9',
                        __( '4:3 (Classic)', 'kipdev-optimizer' ) => '4-3',
                        __( '21:9 (Ultrawide)', 'kipdev-optimizer' ) => '21-9',
                        __( '1:1 (Square)', 'kipdev-optimizer' ) => '1-1',
                        __( 'Custom', 'kipdev-optimizer' ) => 'custom',
                    ),
                    'std' => '16-9',
                    'group' => __( 'Design', 'kipdev-optimizer' ),
                ),
                
                // Custom Aspect Ratio
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Custom Aspect Ratio', 'kipdev-optimizer' ),
                    'param_name' => 'custom_aspect',
                    'description' => __( 'Enter custom ratio (e.g., 16:9)', 'kipdev-optimizer' ),
                    'group' => __( 'Design', 'kipdev-optimizer' ),
                    'dependency' => array(
                        'element' => 'aspect_ratio',
                        'value' => array( 'custom' ),
                    ),
                ),
                
                // Object Fit
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Object Fit', 'kipdev-optimizer' ),
                    'param_name' => 'object_fit',
                    'value' => array(
                        __( 'Default', 'kipdev-optimizer' ) => 'default',
                        __( 'Cover', 'kipdev-optimizer' ) => 'cover',
                        __( 'Contain', 'kipdev-optimizer' ) => 'contain',
                        __( 'Fill', 'kipdev-optimizer' ) => 'fill',
                    ),
                    'std' => 'default',
                    'description' => __( 'How video fits in container', 'kipdev-optimizer' ),
                    'group' => __( 'Design', 'kipdev-optimizer' ),
                ),
                
                // Max Width
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Max Width', 'kipdev-optimizer' ),
                    'param_name' => 'max_width',
                    'description' => __( 'Maximum width (e.g., 800px, 100%, leave empty for full width)', 'kipdev-optimizer' ),
                    'group' => __( 'Design', 'kipdev-optimizer' ),
                ),
                
                // Alignment
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Alignment', 'kipdev-optimizer' ),
                    'param_name' => 'alignment',
                    'value' => array(
                        __( 'Left', 'kipdev-optimizer' ) => 'left',
                        __( 'Center', 'kipdev-optimizer' ) => 'center',
                        __( 'Right', 'kipdev-optimizer' ) => 'right',
                    ),
                    'std' => 'center',
                    'group' => __( 'Design', 'kipdev-optimizer' ),
                ),
                
                // Custom CSS Class
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Custom CSS Class', 'kipdev-optimizer' ),
                    'param_name' => 'custom_class',
                    'description' => __( 'Add custom CSS classes for styling', 'kipdev-optimizer' ),
                    'group' => __( 'Design', 'kipdev-optimizer' ),
                ),
                
                // Element ID
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Element ID', 'kipdev-optimizer' ),
                    'param_name' => 'element_id',
                    'description' => __( 'Unique element ID for targeting', 'kipdev-optimizer' ),
                    'group' => __( 'Design', 'kipdev-optimizer' ),
                ),
                
            ),
        ) );
    }
    
    /**
     * Render shortcode output
     */
    public static function render_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'video_source' => 'youtube',
            'video_id' => '',
            'privacy_mode' => '',
            'autoplay' => '',
            'mute' => '',
            'loop' => '',
            'controls' => 'show',
            'animation' => 'none',
            'animation_duration' => '600',
            'aspect_ratio' => '16-9',
            'custom_aspect' => '',
            'object_fit' => 'default',
            'max_width' => '',
            'alignment' => 'center',
            'custom_class' => '',
            'element_id' => '',
        ), $atts );
        
        // Validate video ID
        if ( empty( $atts['video_id'] ) ) {
            return '<div class="kipdev-video-error" style="padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 4px;">
                <strong>Advanced Video Embed:</strong> Please enter a video ID.
            </div>';
        }
        
        // Generate unique ID
        $unique_id = ! empty( $atts['element_id'] ) ? $atts['element_id'] : 'kipdev-video-' . uniqid();
        
        // Build container classes
        $container_classes = array( 'kipdev-video-container' );
        if ( $atts['animation'] !== 'none' ) {
            $container_classes[] = 'kipdev-video-animation';
            $container_classes[] = 'kipdev-animation-' . $atts['animation'];
        }
        if ( ! empty( $atts['custom_class'] ) ) {
            $container_classes[] = esc_attr( $atts['custom_class'] );
        }
        
        // Build wrapper classes
        $wrapper_classes = array( 'kipdev-video-wrapper' );
        $wrapper_classes[] = 'kipdev-aspect-' . $atts['aspect_ratio'];
        $wrapper_classes[] = 'kipdev-align-' . $atts['alignment'];
        if ( $atts['object_fit'] !== 'default' ) {
            $wrapper_classes[] = 'kipdev-fit-' . $atts['object_fit'];
        }
        
        // Build embed URL
        $embed_url = self::build_embed_url( $atts );
        
        // Build inline styles
        $container_styles = array();
        if ( ! empty( $atts['max_width'] ) ) {
            $container_styles[] = 'max-width: ' . esc_attr( $atts['max_width'] );
        }
        if ( $atts['animation'] !== 'none' && ! empty( $atts['animation_duration'] ) ) {
            $container_styles[] = 'animation-duration: ' . intval( $atts['animation_duration'] ) . 'ms';
        }
        
        $wrapper_styles = array();
        if ( $atts['aspect_ratio'] === 'custom' && ! empty( $atts['custom_aspect'] ) ) {
            $ratio_parts = explode( ':', $atts['custom_aspect'] );
            if ( count( $ratio_parts ) === 2 ) {
                $padding = ( floatval( $ratio_parts[1] ) / floatval( $ratio_parts[0] ) ) * 100;
                $wrapper_styles[] = 'padding-bottom: ' . $padding . '%';
            }
        }
        
        // Build output
        ob_start();
        ?>
        <div id="<?php echo esc_attr( $unique_id ); ?>" 
             class="<?php echo esc_attr( implode( ' ', $container_classes ) ); ?>"
             <?php if ( ! empty( $container_styles ) ) : ?>
             style="<?php echo esc_attr( implode( '; ', $container_styles ) ); ?>"
             <?php endif; ?>>
            <div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
                 <?php if ( ! empty( $wrapper_styles ) ) : ?>
                 style="<?php echo esc_attr( implode( '; ', $wrapper_styles ) ); ?>"
                 <?php endif; ?>>
                <?php if ( isset( $atts['autoplay'] ) && $atts['autoplay'] === 'yes' ) : ?>
                    <iframe class="kipdev-video-iframe"
                        src="<?php echo esc_url( $embed_url ); ?>"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                <?php else : ?>
                    <iframe class="kipdev-video-iframe kipdev-lazy-iframe"
                        src="about:blank"
                        data-src="<?php echo esc_url( $embed_url ); ?>"
                        loading="lazy"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Build embed URL with parameters
     */
    private static function build_embed_url( $atts ) {
        $video_id = trim( $atts['video_id'] );
        $params = array();
        
        if ( $atts['video_source'] === 'youtube' ) {
            // YouTube URL
            $domain = ( $atts['privacy_mode'] === 'yes' ) ? 'youtube-nocookie.com' : 'youtube.com';
            $base_url = "https://www.{$domain}/embed/{$video_id}";
            
            // YouTube parameters
            // Add playsinline to help mobile autoplay
            $params['playsinline'] = '1';
            if ( $atts['autoplay'] === 'yes' ) {
                $params['autoplay'] = '1';
            }
            if ( $atts['mute'] === 'yes' ) {
                $params['mute'] = '1';
            }
            if ( $atts['loop'] === 'yes' ) {
                $params['loop'] = '1';
                $params['playlist'] = $video_id; // Required for loop
            }
            if ( $atts['controls'] === 'hide' ) {
                $params['controls'] = '0';
            } elseif ( $atts['controls'] === 'autohide' ) {
                $params['autohide'] = '1';
            }
            
        } else {
            // Vimeo URL
            $base_url = "https://player.vimeo.com/video/{$video_id}";
            
            // Vimeo parameters
            // Add playsinline to help mobile autoplay
            $params['playsinline'] = '1';
            if ( $atts['autoplay'] === 'yes' ) {
                $params['autoplay'] = '1';
            }
            if ( $atts['mute'] === 'yes' ) {
                $params['muted'] = '1';
            }
            if ( $atts['loop'] === 'yes' ) {
                $params['loop'] = '1';
            }
            if ( $atts['controls'] === 'hide' ) {
                $params['controls'] = '0';
            }
        }
        
        // Build final URL
        if ( ! empty( $params ) ) {
            $base_url .= '?' . http_build_query( $params );
        }
        
        return $base_url;
    }
    
    /**
     * Enqueue frontend assets
     */
    public static function enqueue_assets() {
        wp_enqueue_style( 
            'kipdev-video-embed', 
            KIPDEV_OPT_PLUGIN_URL . 'assets/css/video-embed.css', 
            array(), 
            '0.2.2' 
        );

        // Lazy media loader for iframes, video and audio
        wp_enqueue_script(
            'kipdev-lazy-media',
            KIPDEV_OPT_PLUGIN_URL . 'assets/js/lazy-media.js',
            array(),
            '0.1.0',
            true
        );
    }
}
