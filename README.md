# KipDev Simple Optimizer

Lightweight, high-quality performance plugin for WordPress with **Jetpack compatibility**.

## Features

### Dashboard (Admin UI)
- **Performance Overview**: View recent scan results and optimization metrics
- **Optimization Controls**: Configure image quality, HTML minification, lazy loading, caching, and more
- **Results Dashboard**: View optimization logs with severity levels (info, warning, error)

### Optimization Engine
- **Image Optimizer**: 
  - Automatic JPEG/PNG compression on upload (using GD)
  - **WebP generation** for modern browsers (30% smaller than JPEG)
  - Memory-aware processing to prevent crashes on large images
  - **Jetpack Photon detection** - automatically defers to Jetpack's Image CDN
  - Counter tracking for optimized images
  
- **Asset Minifier**: 
  - HTML output minification (removes comments, whitespace)
  - **JavaScript deferral** for faster page loads
  - **Lazy loading** for images (auto-disabled if Jetpack lazy loading is active)
  - **Resource hints** (preconnect, preload) for critical assets
  
- **Cache Manager**: 
  - Clear plugin transients and logs
  - **Page caching** for non-logged-in users
  - **Database optimization** (clean revisions, transients, orphaned metadata)
  - Auto-clear cache on content updates
  - SQL injection vulnerability fixed

### Metrics System
- **Performance Scanner**: Basic page load time measurement
- **Progress Tracker**: Enhanced logs with severity levels
- **Results Reporter**: Color-coded admin interface with timestamps

### WP-CLI Support
Bulk operations via command line:
```bash
wp kipdev optimize-images [--limit=50] [--force]
wp kipdev clear-cache
wp kipdev optimize-db
wp kipdev scan
```

## Installation

1. Upload the `kipdev_optimizer` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Simple Optimizer' in the admin menu

## Usage

### Basic Configuration
1. Go to **Simple Optimizer > Optimization Controls**
2. Configure options:
   - **Image quality** (10-100, default: 82)
   - **Generate WebP** images (recommended)
   - **Minify HTML** output
   - **Enable lazy loading** (auto-disabled with Jetpack)
   - **Defer JavaScript** for faster loads
   - **Enable page caching** (off by default)
   - **Jetpack compatibility** (auto-detect and defer)
3. Save settings

### Performance Monitoring
1. Go to **Simple Optimizer > Performance Overview**
2. Click "Run Performance Scan" to measure current load time
3. Click "Optimize Database" to clean up database
4. View color-coded results in the Results Dashboard

### Image Optimization
- **Automatic**: Images are optimized when uploaded
- **Bulk**: Use WP-CLI for existing images: `wp kipdev optimize-images`
- **JPEG**: Quality-based compression with WebP generation
- **PNG**: Level-based compression with transparency preservation
- **GIF**: Not processed (preserved as-is for animations)
- **WebP**: 30% smaller than JPEG, generated automatically

### Jetpack Compatibility
The plugin automatically detects Jetpack and:
- Defers image optimization to Jetpack Photon/Image CDN
- Disables lazy loading if Jetpack's is active
- Adds preconnect hints for Jetpack CDN domains
- Works alongside Jetpack without conflicts

## Technical Notes

### Requirements
- WordPress 5.0+
- PHP 7.4+
- GD extension (for image processing)
- Optional: WP-CLI for bulk operations

### Security Improvements
- ✅ Fixed SQL injection vulnerability in cache clearing
- ✅ Proper escaping with `$wpdb->esc_like()`
- ✅ Nonce verification for all AJAX actions
- ✅ Capability checks for admin operations
- PHP 7.4+
- GD extension (for image processing)

### File Structure
```
kipdev_optimizer/
├── kipdev-optimizer.php           # Main plugin file + WP-CLI commands
├── includes/
│   ├── helpers.php               # Options utilities
│   ├── admin/
│   │   └── class-kipdev-admin.php # Admin interface with enhanced UI
│   ├── engine/
│   │   ├── class-image-optimizer.php  # Image processing + WebP + Jetpack detection
│   │   ├── class-asset-minifier.php   # HTML/JS optimization + lazy loading
│   │   ├── class-cache-manager.php    # Cache + page caching + DB optimization
│   │   └── class-video-processor.php  # Video optimization (placeholder)
│   └── metrics/
│       └── class-performance-scanner.php # Performance monitoring
└── assets/
    ├── css/admin.css             # Admin styles with log level colors
    └── js/admin.js               # Admin JavaScript with DB optimization
```

## Performance Improvements Implemented

✅ **WebP Support**: 30% smaller images for modern browsers  
✅ **Lazy Loading**: Faster initial page loads  
✅ **JavaScript Deferral**: Non-blocking script loading  
✅ **Page Caching**: Full page caching for guests  
✅ **Database Optimization**: Clean revisions, transients, optimize tables  
✅ **Resource Hints**: Preconnect and preload for critical assets  
✅ **Memory Protection**: Skip large images that could cause crashes  
✅ **Jetpack Integration**: Smart detection and deferral to Jetpack features  
✅ **Enhanced Logging**: Severity levels (info, warning, error)  
✅ **WP-CLI Commands**: Bulk operations via command line  

## Best Practices for Jetpack Users

1. **Enable Jetpack Photon/Image CDN**: Let Jetpack handle image optimization and CDN delivery
2. **Enable this plugin's WebP generation**: Even with Jetpack, WebP files benefit local storage
3. **Use page caching carefully**: Test with your Jetpack features to avoid conflicts
4. **Enable lazy loading**: Either in Jetpack OR in this plugin (not both)
5. **Use database optimization monthly**: `wp kipdev optimize-db` or via admin panel

## Next Steps & Improvements

### Future Enhancements
1. **Advanced Video Processing**: FFmpeg integration for video optimization
2. **Critical CSS**: Above-the-fold CSS inlining
3. **Advanced Caching**: WP Object Cache and Redis integration
4. **CRON Jobs**: Scheduled optimization and cleanup tasks
5. **External Services**: Integration with image optimization APIs (Smush, ShortPixel)
6. **Advanced Image Libraries**: ImageMagick support for better quality
7. **Core Web Vitals**: LCP, FID, CLS tracking and reporting
8. **CDN Integration**: Generic CDN support for any provider

### Performance Considerations
- Lightweight footprint (< 100KB with all features)
- Minimal database queries (transients for caching)
- On-demand processing with memory checks
- Graceful error handling and logging
- Jetpack-aware to prevent duplicated work

## Troubleshooting

### Images not optimizing?
- Check if Jetpack Photon is active (plugin defers to Jetpack)
- Verify GD library is installed: `php -m | grep gd`
- Check logs in Results Dashboard for errors
- Ensure proper file permissions

### Page caching issues?
- Caching is disabled by default, enable in settings
- Not active for logged-in users
- Clears automatically on content updates
- Use "Clear Plugin Cache" if needed

### WebP not working?
- Verify PHP supports WebP: `php -r "echo function_exists('imagewebp') ? 'yes' : 'no';"`
- Check server configuration for WebP MIME types
- Use browser DevTools to verify WebP delivery

## Support

For issues and feature requests, please visit the plugin repository.

## Changelog

### Version 0.2.0 (Current)
- ✅ Added WebP generation support
- ✅ Added Jetpack compatibility layer
- ✅ Added lazy loading with Jetpack detection
- ✅ Added JavaScript deferral
- ✅ Added page caching system
- ✅ Added database optimization
- ✅ Added WP-CLI commands
- ✅ Fixed SQL injection vulnerability
- ✅ Enhanced logging system with severity levels
- ✅ Added memory protection for large images
- ✅ Added resource hints (preconnect, preload)
- ✅ Improved HTML minification safety

### Version 0.1.0
- Initial release with basic optimization

## License

GPL v2 or later