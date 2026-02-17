<?php
/**
 * Example implementations for Advanced Video Embed
 * 
 * This file contains code examples showing how to use the
 * Advanced Video Embed element programmatically.
 * 
 * DO NOT include this file in production - it's for reference only!
 */

// ============================================
// Example 1: Basic YouTube Video
// ============================================

echo do_shortcode( '[kipdev_advanced_video video_source="youtube" video_id="dQw4w9WgXcQ"]' );


// ============================================
// Example 2: Privacy-Mode YouTube with Autoplay
// ============================================

echo do_shortcode( '
    [kipdev_advanced_video 
        video_source="youtube" 
        video_id="dQw4w9WgXcQ" 
        privacy_mode="yes" 
        autoplay="yes" 
        mute="yes"
        controls="show"]
' );


// ============================================
// Example 3: Vimeo Video with Animation
// ============================================

echo do_shortcode( '
    [kipdev_advanced_video 
        video_source="vimeo" 
        video_id="123456789" 
        animation="fade-in" 
        animation_duration="800"
        aspect_ratio="16-9"]
' );


// ============================================
// Example 4: Looping Background Video
// ============================================

echo do_shortcode( '
    [kipdev_advanced_video 
        video_source="youtube" 
        video_id="dQw4w9WgXcQ" 
        autoplay="yes" 
        mute="yes" 
        loop="yes" 
        controls="hide"
        object_fit="cover"
        custom_class="hero-background-video"]
' );


// ============================================
// Example 5: Square Social Media Video
// ============================================

echo do_shortcode( '
    [kipdev_advanced_video 
        video_source="youtube" 
        video_id="dQw4w9WgXcQ" 
        aspect_ratio="1-1"
        max_width="600px"
        alignment="center"
        animation="zoom-in"]
' );


// ============================================
// Example 6: Ultrawide Cinematic Video
// ============================================

echo do_shortcode( '
    [kipdev_advanced_video 
        video_source="vimeo" 
        video_id="123456789" 
        aspect_ratio="21-9"
        max_width="1400px"
        alignment="center"
        animation="slide-up"
        custom_class="cinematic-video"]
' );


// ============================================
// Example 7: Custom Aspect Ratio Video
// ============================================

echo do_shortcode( '
    [kipdev_advanced_video 
        video_source="youtube" 
        video_id="dQw4w9WgXcQ" 
        aspect_ratio="custom"
        custom_aspect="2:1"
        element_id="custom-ratio-video"]
' );


// ============================================
// Example 8: Programmatic Usage in Template
// ============================================

function my_theme_video_section() {
    if ( ! function_exists( 'do_shortcode' ) ) {
        return;
    }
    
    $video_atts = array(
        'video_source' => 'youtube',
        'video_id' => 'dQw4w9WgXcQ',
        'privacy_mode' => 'yes',
        'autoplay' => 'yes',
        'mute' => 'yes',
        'animation' => 'fade-in',
        'aspect_ratio' => '16-9',
        'max_width' => '1200px',
        'alignment' => 'center',
    );
    
    $shortcode_atts = array();
    foreach ( $video_atts as $key => $value ) {
        $shortcode_atts[] = $key . '="' . esc_attr( $value ) . '"';
    }
    
    echo do_shortcode( '[kipdev_advanced_video ' . implode( ' ', $shortcode_atts ) . ']' );
}


// ============================================
// Example 9: Dynamic Video from Custom Field
// ============================================

function my_acf_video_field() {
    if ( ! function_exists( 'get_field' ) || ! function_exists( 'do_shortcode' ) ) {
        return;
    }
    
    $video_id = get_field( 'youtube_video_id' );
    $video_source = get_field( 'video_platform' ); // 'youtube' or 'vimeo'
    
    if ( empty( $video_id ) ) {
        return;
    }
    
    echo do_shortcode( sprintf(
        '[kipdev_advanced_video video_source="%s" video_id="%s" animation="fade-in"]',
        esc_attr( $video_source ),
        esc_attr( $video_id )
    ) );
}


// ============================================
// Example 10: Multiple Videos in Grid
// ============================================

function my_video_grid() {
    $videos = array(
        array( 'id' => 'dQw4w9WgXcQ', 'source' => 'youtube' ),
        array( 'id' => '123456789', 'source' => 'vimeo' ),
        array( 'id' => 'aBcDeFgHiJk', 'source' => 'youtube' ),
    );
    
    echo '<div class="video-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">';
    
    foreach ( $videos as $video ) {
        echo do_shortcode( sprintf(
            '[kipdev_advanced_video video_source="%s" video_id="%s" aspect_ratio="16-9" animation="fade-in"]',
            esc_attr( $video['source'] ),
            esc_attr( $video['id'] )
        ) );
    }
    
    echo '</div>';
}


// ============================================
// Example 11: Conditional Video Loading
// ============================================

function my_conditional_video() {
    // Only show video on desktop
    if ( wp_is_mobile() ) {
        echo '<div class="video-placeholder">Video available on desktop</div>';
        return;
    }
    
    echo do_shortcode( '
        [kipdev_advanced_video 
            video_source="youtube" 
            video_id="dQw4w9WgXcQ" 
            autoplay="yes" 
            mute="yes"]
    ' );
}


// ============================================
// Example 12: Video with Wrapper
// ============================================

function my_styled_video() {
    ?>
    <div class="custom-video-section" style="background: #000; padding: 60px 20px;">
        <div class="container" style="max-width: 1200px; margin: 0 auto;">
            <h2 style="color: #fff; text-align: center; margin-bottom: 30px;">Watch Our Story</h2>
            <?php echo do_shortcode( '
                [kipdev_advanced_video 
                    video_source="youtube" 
                    video_id="dQw4w9WgXcQ" 
                    animation="zoom-in"
                    aspect_ratio="16-9"]
            ' ); ?>
        </div>
    </div>
    <?php
}


// ============================================
// Example 13: WPBakery Programmatic Registration
// ============================================

// If you need to modify the element registration
add_filter( 'vc_before_init', 'my_modify_video_element', 20 );

function my_modify_video_element() {
    // Get existing element params
    $element = vc_get_shortcode( 'kipdev_advanced_video' );
    
    if ( ! $element ) {
        return;
    }
    
    // Add custom parameter
    $element['params'][] = array(
        'type' => 'textfield',
        'heading' => __( 'My Custom Field', 'my-theme' ),
        'param_name' => 'my_custom_field',
        'description' => __( 'Add my custom field', 'my-theme' ),
    );
    
    // Update element
    vc_map_update( 'kipdev_advanced_video', $element );
}


// ============================================
// Example 14: Add Custom CSS via PHP
// ============================================

add_action( 'wp_head', 'my_video_custom_styles' );

function my_video_custom_styles() {
    ?>
    <style>
        /* Custom video styling */
        .my-video-class .kipdev-video-wrapper {
            border: 5px solid #2271b1;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        /* Hover effect */
        .my-video-class:hover .kipdev-video-wrapper {
            transform: scale(1.05);
            transition: transform 0.4s ease;
        }
        
        /* Mobile adjustments */
        @media (max-width: 768px) {
            .my-video-class .kipdev-video-wrapper {
                border-width: 3px;
                border-radius: 8px;
            }
        }
    </style>
    <?php
}


// ============================================
// Example 15: JavaScript Interaction
// ============================================

add_action( 'wp_footer', 'my_video_js' );

function my_video_js() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Target specific video
        var videoContainer = $('#my-video-id');
        
        // Add click event
        videoContainer.on('click', function() {
            console.log('Video clicked!');
        });
        
        // Intersection Observer for lazy loading animation
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                    }
                });
            });
            
            document.querySelectorAll('.kipdev-video-container').forEach(function(el) {
                observer.observe(el);
            });
        }
    });
    </script>
    <?php
}
