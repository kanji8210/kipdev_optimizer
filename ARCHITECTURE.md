# KipDev Optimizer - Architecture Documentation

## Overview

KipDev Optimizer is a WordPress plugin designed with a modular architecture that follows WordPress coding standards and best practices. The plugin is structured to provide clear separation of concerns across three main layers.

## Architecture Layers

### 1. Dashboard (Admin UI)

The Dashboard layer handles all user interactions and provides an intuitive interface for managing optimization settings.

#### Components:

**Performance Overview** (`admin/class-performance-overview.php`)
- Displays real-time performance metrics
- Shows performance score (0-100)
- Displays optimization statistics (images, videos)
- Shows cache status and size
- Provides quick action links

**Optimization Controls** (`admin/class-optimization-controls.php`)
- Provides settings management interface
- Handles AJAX requests for optimization actions
- Configures image quality settings
- Manages video optimization settings
- Controls asset minification
- Manages cache settings
- Includes lazy loading toggle

**Results Dashboard** (`admin/class-results-dashboard.php`)
- Displays detailed optimization reports
- Shows performance metrics history
- Provides trend analysis
- Displays optimization progress
- Generates recommendations

#### Template Files:
- `admin/partials/performance-overview-display.php` - Overview page template
- `admin/partials/optimization-controls-display.php` - Controls page template
- `admin/partials/results-dashboard-display.php` - Results page template

### 2. Optimization Engine

The Optimization Engine performs all optimization tasks on site assets.

#### Components:

**Image Optimizer** (`includes/optimization-engine/class-image-optimizer.php`)
- Optimizes images on upload
- Supports JPEG, PNG, and GIF formats
- Configurable quality settings (50-100%)
- Batch optimization for existing images
- Adds lazy loading attributes
- Uses PHP GD library for processing

**Video Processor** (`includes/optimization-engine/class-video-processor.php`)
- Processes videos on upload
- Stores video metadata
- Batch optimization for existing videos
- Adds lazy loading and preload attributes
- Tracks optimization status

**Asset Minifier** (`includes/optimization-engine/class-asset-minifier.php`)
- Minifies CSS files
- Minifies JavaScript files
- Removes query strings from static resources
- Provides hooks for combining assets
- Tracks minification status

**Cache Manager** (`includes/optimization-engine/class-cache-manager.php`)
- Manages browser caching
- Adds appropriate cache headers
- Creates and manages cache directory
- Provides cache clearing functionality
- Monitors cache size
- Integrates with WordPress object cache

### 3. Metrics System

The Metrics System monitors, tracks, and reports on optimization activities.

#### Components:

**Performance Scanner** (`includes/metrics-system/class-performance-scanner.php`)
- Scans site performance metrics
- Calculates performance score
- Estimates page load time
- Counts resources (images, scripts, styles)
- Measures total page size
- Stores scan results for comparison

**Progress Tracker** (`includes/metrics-system/class-progress-tracker.php`)
- Tracks optimization task progress
- Records task status (in_progress, completed, failed)
- Provides progress statistics
- Calculates overall completion percentage
- Stores task details and timestamps

**Results Reporter** (`includes/metrics-system/class-results-reporter.php`)
- Generates comprehensive reports
- Provides optimization recommendations
- Compares current vs. previous results
- Calculates improvement metrics
- Exports reports in multiple formats (JSON, CSV)

## Core Plugin Files

### Main Plugin File
**kipdev-optimizer.php**
- WordPress plugin header information
- Defines plugin constants
- Initializes the main plugin class
- Entry point for WordPress

### Core Classes

**KipDev_Optimizer** (`includes/class-kipdev-optimizer.php`)
- Main plugin orchestrator
- Loads all dependencies
- Registers admin and optimization hooks
- Coordinates component initialization

**KipDev_Optimizer_Loader** (`includes/class-kipdev-optimizer-loader.php`)
- Manages WordPress hooks and filters
- Registers actions and filters
- Executes registered hooks

**KipDev_Optimizer_Admin** (`admin/class-kipdev-optimizer-admin.php`)
- Handles admin area functionality
- Enqueues styles and scripts
- Creates admin menu structure
- Routes admin page requests

## Assets

### Stylesheets
**assets/css/admin.css**
- Admin interface styling
- Dashboard card layouts
- Responsive grid system
- Status indicators
- Form controls styling

### JavaScript
**assets/js/admin.js**
- AJAX handlers for optimization actions
- Real-time UI updates
- Form interactions
- Range slider controls
- Result message display

## Data Flow

### Optimization Workflow

1. **User Action** → Dashboard (Admin UI)
2. **AJAX Request** → Optimization Controls
3. **Processing** → Optimization Engine
4. **Tracking** → Progress Tracker
5. **Scanning** → Performance Scanner
6. **Reporting** → Results Reporter
7. **Display** → Results Dashboard

### Settings Management

1. User modifies settings in Optimization Controls
2. Settings saved to WordPress options table
3. Optimization Engine components read settings
4. Components apply optimizations based on settings

### Performance Monitoring

1. Performance Scanner collects metrics
2. Metrics stored in WordPress options
3. Results Dashboard retrieves and displays metrics
4. Trends calculated from historical data

## Database Schema

The plugin uses WordPress options table for all data storage:

### Options Stored:
- `kipdev_optimizer_image_optimization` - Image optimization enabled
- `kipdev_optimizer_video_optimization` - Video optimization enabled
- `kipdev_optimizer_asset_minification` - Asset minification enabled
- `kipdev_optimizer_cache_enabled` - Cache enabled status
- `kipdev_optimizer_image_quality` - Image quality setting (50-100)
- `kipdev_optimizer_lazy_load` - Lazy loading enabled
- `kipdev_optimizer_images_optimized` - Count of optimized images
- `kipdev_optimizer_videos_optimized` - Count of optimized videos
- `kipdev_optimizer_page_load_time` - Last measured page load time
- `kipdev_optimizer_last_scan` - Last performance scan results
- `kipdev_optimizer_progress` - Current optimization progress
- `kipdev_optimizer_history` - Historical optimization data (last 30 entries, auto-pruned using array_slice)
- `kipdev_optimizer_video_{hash}` - Individual video metadata (MD5 hash of file path)

## WordPress Integration

### Hooks Used

**Actions:**
- `admin_enqueue_scripts` - Enqueue admin assets
- `admin_menu` - Register admin pages
- `init` - Initialize cache manager
- `send_headers` - Add cache headers
- `wp_ajax_*` - Handle AJAX requests

**Filters:**
- `wp_handle_upload` - Filter uploaded files for optimization
- `style_loader_tag` - Modify CSS tags
- `script_loader_tag` - Modify JS tags
- `the_content` - Add lazy loading to content

## Security Considerations

1. **Nonce Verification** - All AJAX requests verify nonces
2. **Capability Checks** - Admin functions check `manage_options` capability
3. **Input Sanitization** - All user input is sanitized
4. **Output Escaping** - All output is escaped appropriately
5. **Direct Access Prevention** - Files check for `WPINC` constant

## Performance Considerations

1. **Lazy Loading** - Defers loading of off-screen resources
2. **On-Demand Processing** - Heavy operations triggered manually or on upload
3. **Efficient Queries** - Uses WordPress caching mechanisms
4. **Asset Optimization** - Minifies and caches static assets
5. **Browser Caching** - Leverages browser cache for static resources

## Extensibility

The plugin is designed to be extensible:

1. **Filter Hooks** - Custom filters for modification
2. **Action Hooks** - Custom actions for extensions
3. **Modular Architecture** - Easy to add new optimization components
4. **Object-Oriented Design** - Classes can be extended

## Future Enhancements

Potential areas for expansion:
1. Database optimization
2. CDN integration
3. WebP image format support
4. Critical CSS generation
5. Advanced caching strategies
6. Integration with external optimization APIs

## File Structure Summary

```
kipdev-optimizer/
├── kipdev-optimizer.php          # Main plugin file
├── README.md                      # User documentation
├── .gitignore                     # Git ignore rules
├── admin/                         # Admin UI layer
│   ├── class-kipdev-optimizer-admin.php
│   ├── class-performance-overview.php
│   ├── class-optimization-controls.php
│   ├── class-results-dashboard.php
│   └── partials/                  # View templates
│       ├── performance-overview-display.php
│       ├── optimization-controls-display.php
│       └── results-dashboard-display.php
├── includes/                      # Core plugin classes
│   ├── class-kipdev-optimizer.php
│   ├── class-kipdev-optimizer-loader.php
│   ├── optimization-engine/       # Optimization components
│   │   ├── class-image-optimizer.php
│   │   ├── class-video-processor.php
│   │   ├── class-asset-minifier.php
│   │   └── class-cache-manager.php
│   └── metrics-system/            # Metrics components
│       ├── class-performance-scanner.php
│       ├── class-progress-tracker.php
│       └── class-results-reporter.php
└── assets/                        # Static assets
    ├── css/
    │   └── admin.css
    └── js/
        └── admin.js
```

## Coding Standards

The plugin follows:
- WordPress Coding Standards
- PHP PSR-12 compatible where appropriate
- WordPress Security Best Practices
- WordPress Accessibility Guidelines
