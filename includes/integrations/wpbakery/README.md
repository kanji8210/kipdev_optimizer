# WPBakery Page Builder Integration

## Advanced Video Embed Element

The KipDev Optimizer plugin automatically extends WPBakery Page Builder with a powerful "Advanced Video Embed" element.

### Features

✅ **YouTube & Vimeo Support** - Embed videos from both platforms  
✅ **Privacy Mode** - YouTube no-cookie option for GDPR compliance  
✅ **Playback Controls** - Autoplay, mute, loop, and player visibility  
✅ **Animations** - 8 entrance animations (fade, slide, zoom)  
✅ **Responsive Design** - Multiple aspect ratios and object-fit options  
✅ **Custom Styling** - Add CSS classes and IDs for advanced customization  
✅ **Performance Optimized** - Lightweight CSS, no JavaScript dependencies  

---

## Installation

1. Install and activate **WPBakery Page Builder** plugin
2. Install and activate **KipDev Optimizer** plugin
3. The "Advanced Video Embed" element automatically appears in WPBakery

---

## Usage

### Adding the Element

1. Edit any page with WPBakery Page Builder
2. Click **"Add Element"**
3. Find **"Advanced Video Embed"** under the **"KipDev Performance"** category
4. Drag and drop it into your layout

### Configuration

#### Basic Settings

**Video Source**
- Choose between YouTube or Vimeo

**Video ID**
- YouTube: Enter the video ID (e.g., `dQw4w9WgXcQ`)
- Vimeo: Enter the numeric ID (e.g., `123456789`)

**Privacy Mode** (YouTube only)
- Enable to use `youtube-nocookie.com` for GDPR compliance
- Prevents YouTube from setting cookies until user plays the video

**Autoplay**
- Video starts playing automatically when page loads
- Note: Most browsers require mute to be enabled for autoplay

**Mute**
- Video starts muted
- Required for autoplay in Chrome, Firefox, Safari

**Loop**
- Video repeats continuously

**Player Controls**
- Show controls: Default player controls visible
- Hide controls: No player controls shown
- Auto-hide: Controls appear on hover

#### Design Settings

**Animation**
- None
- Fade In
- Slide Up / Down / Left / Right
- Zoom In / Out

**Animation Duration**
- Time in milliseconds (default: 600ms)

**Aspect Ratio**
- 16:9 (Standard) - Most common for YouTube/Vimeo
- 4:3 (Classic) - Older video format
- 21:9 (Ultrawide) - Cinematic format
- 1:1 (Square) - Social media format
- Custom - Enter your own ratio (e.g., 2:1)

**Object Fit**
- Default: Video maintains aspect ratio
- Cover: Video fills container, may crop
- Contain: Video fits within container
- Fill: Video stretches to container

**Max Width**
- Limit video width (e.g., `800px`, `50%`)
- Leave empty for full width

**Alignment**
- Left, Center, or Right alignment

**Custom CSS Class**
- Add your own CSS classes for styling

**Element ID**
- Unique ID for JavaScript targeting

---

## Examples

### Basic YouTube Video

```
Video Source: YouTube
Video ID: dQw4w9WgXcQ
Privacy Mode: Enabled
Aspect Ratio: 16:9
```

### Autoplay Vimeo Video

```
Video Source: Vimeo
Video ID: 123456789
Autoplay: Yes
Mute: Yes
Loop: Yes
Controls: Hide
```

### Animated Video Hero

```
Video Source: YouTube
Video ID: dQw4w9WgXcQ
Animation: Zoom In
Animation Duration: 1000
Aspect Ratio: 21:9
Max Width: 1200px
Alignment: Center
```

### Square Social Video

```
Video Source: YouTube
Video ID: dQw4w9WgXcQ
Aspect Ratio: 1:1
Object Fit: Cover
Max Width: 600px
```

---

## CSS Customization

### Container Classes

```css
/* Target all video containers */
.kipdev-video-container {
    margin: 40px 0;
}

/* Target specific video by ID */
#my-hero-video {
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

/* Custom animation timing */
.my-video-class {
    animation-duration: 800ms !important;
}
```

### Aspect Ratios

```css
/* Add custom aspect ratio */
.kipdev-video-wrapper.my-custom-ratio {
    padding-bottom: 75%; /* 4:3 */
}
```

### Hover Effects

```css
.kipdev-video-container:hover .kipdev-video-wrapper {
    transform: scale(1.02);
    transition: transform 0.3s ease;
}
```

---

## Shortcode Usage

You can also use the shortcode directly:

```php
[kipdev_advanced_video 
    video_source="youtube" 
    video_id="dQw4w9WgXcQ" 
    privacy_mode="yes" 
    autoplay="yes" 
    mute="yes"
    animation="fade-in"
    aspect_ratio="16-9"]
```

### Shortcode Parameters

| Parameter | Values | Default |
|-----------|--------|---------|
| `video_source` | youtube, vimeo | youtube |
| `video_id` | Video ID | (required) |
| `privacy_mode` | yes, no | no |
| `autoplay` | yes, no | no |
| `mute` | yes, no | no |
| `loop` | yes, no | no |
| `controls` | show, hide, autohide | show |
| `animation` | none, fade-in, slide-up, slide-down, slide-left, slide-right, zoom-in, zoom-out | none |
| `animation_duration` | Number (ms) | 600 |
| `aspect_ratio` | 16-9, 4-3, 21-9, 1-1, custom | 16-9 |
| `custom_aspect` | Ratio (e.g., 16:9) | - |
| `object_fit` | default, cover, contain, fill | default |
| `max_width` | CSS value | - |
| `alignment` | left, center, right | center |
| `custom_class` | CSS classes | - |
| `element_id` | Unique ID | auto |

---

## Troubleshooting

### Video Not Showing

1. **Check Video ID**: Ensure you've entered the correct video ID (not full URL)
2. **YouTube**: Use the ID from `youtube.com/watch?v=VIDEO_ID`
3. **Vimeo**: Use the numeric ID from `vimeo.com/VIDEO_ID`

### Autoplay Not Working

1. **Enable Mute**: Browsers require videos to be muted for autoplay
2. **Check Browser**: Some browsers block autoplay even when muted
3. **Privacy Mode**: Disable privacy mode if autoplay issues persist

### Animation Not Playing

1. **Check Duration**: Ensure animation duration is set (default: 600ms)
2. **CSS Conflicts**: Check for theme CSS overriding animations
3. **Browser Support**: Ensure browser supports CSS animations

### Responsive Issues

1. **Container Width**: Check parent container isn't restricting width
2. **Aspect Ratio**: Try different aspect ratio settings
3. **Mobile**: Test on actual devices, not just browser resize

---

## Performance Tips

✅ **Use Privacy Mode** for faster initial load (fewer cookies)  
✅ **Lazy Load** - Place videos below the fold when possible  
✅ **Limit Autoplay** - Only autoplay hero/above-fold videos  
✅ **Optimize Thumbnails** - Let YouTube/Vimeo serve optimized thumbnails  
✅ **Test Mobile** - Video embeds can be bandwidth-intensive  

---

## Browser Support

| Feature | Chrome | Firefox | Safari | Edge |
|---------|--------|---------|--------|------|
| Basic Embed | ✅ | ✅ | ✅ | ✅ |
| Autoplay (muted) | ✅ | ✅ | ✅ | ✅ |
| Animations | ✅ | ✅ | ✅ | ✅ |
| Privacy Mode | ✅ | ✅ | ✅ | ✅ |
| Object Fit | ✅ | ✅ | ✅ | ✅ |

---

## Support

For issues or feature requests, please visit:
- Plugin support forum
- GitHub repository (if available)
- Contact KipDev support

---

## Changelog

### Version 0.2.2
- Initial release of WPBakery integration
- Advanced Video Embed element
- Full YouTube and Vimeo support
- 8 animation options
- Responsive design system
- Privacy mode for GDPR compliance
