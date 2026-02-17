(function($){
    $(function(){
        $('.nav-tab').on('click', function(e){
            e.preventDefault();
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            var href = $(this).attr('href');
            $('.kipdev-tab-panel').hide();
            $(href).show();
        });

        $('#kipdev-run-scan').on('click', function(e){
            e.preventDefault();
            var btn = $(this);
            btn.prop('disabled', true).text('Scanning...');
            $.post(KIPDEV_OPT.ajax_url, { action: 'kipdev_opt_action', nonce: KIPDEV_OPT.nonce, do: 'run_scan' }, function(r){
                btn.prop('disabled', false).text('Run Performance Scan');
                if ( r.success ) {
                    // Show success message with load time
                    var loadTime = r.data.load_time || 0;
                    var message = 'Scan complete: ' + loadTime + 's';
                    
                    // Add performance hint
                    if (loadTime < 1) {
                        message += '\n✅ Excellent performance!';
                    } else if (loadTime < 2) {
                        message += '\n✅ Good performance!';
                    } else if (loadTime < 3) {
                        message += '\n⚠️ Average performance';
                    } else {
                        message += '\n❌ Consider enabling more optimizations';
                    }
                    
                    alert(message);
                    location.reload();
                } else {
                    alert('Scan failed');
                }
            });
        });

        $('#kipdev-clear-cache').on('click', function(e){
            e.preventDefault();
            if (!confirm('Clear optimizer cache and logs?')) return;
            var btn = $(this);
            btn.prop('disabled', true).text('Clearing...');
            $.post(KIPDEV_OPT.ajax_url, { action: 'kipdev_opt_action', nonce: KIPDEV_OPT.nonce, do: 'clear_cache' }, function(r){
                btn.prop('disabled', false).text('Clear Plugin Cache');
                if ( r.success ) {
                    alert('Cleared');
                    location.reload();
                } else {
                    alert('Failed');
                }
            });
        });
        
        $('#kipdev-optimize-db').on('click', function(e){
            e.preventDefault();
            if (!confirm('Optimize database? This will clean up revisions, transients, and orphaned data.')) return;
            var btn = $(this);
            btn.prop('disabled', true).text('Optimizing...');
            $.post(KIPDEV_OPT.ajax_url, { action: 'kipdev_opt_action', nonce: KIPDEV_OPT.nonce, do: 'optimize_db' }, function(r){
                btn.prop('disabled', false).text('Optimize Database');
                if ( r.success ) {
                    alert(r.data.message || 'Database optimized successfully');
                    location.reload();
                } else {
                    alert('Failed to optimize database');
                }
            });
        });

        $('#kipdev-options-form').on('submit', function(e){
            // simple post via fetch to same page — degrade to normal submit
            // handle via normal POST to update options on server if implemented
        });
        
        // ===== IMAGE CONVERTER FUNCTIONALITY =====
        var currentImages = [];
        var selectedImages = [];
        
        // Load PNG images
        $('#kipdev-load-images').on('click', function(e){
            e.preventDefault();
            var btn = $(this);
            btn.prop('disabled', true).text('Loading...');
            $('#conversion-status').text('');
            
            $.post(KIPDEV_OPT.ajax_url, { 
                action: 'kipdev_opt_action', 
                nonce: KIPDEV_OPT.nonce, 
                do: 'load_png_images' 
            }, function(r){
                btn.prop('disabled', false).text('Reload Images');
                
                if ( r.success ) {
                    currentImages = r.data.images;
                    var needsConversion = currentImages.filter(img => !img.has_webp).length;
                    
                    renderImageGrid(currentImages);
                    
                    if (currentImages.length === 0) {
                        $('#kipdev-convert-selected').hide();
                        $('#kipdev-convert-all').hide();
                        $('#conversion-status').html('<div class="no-images-notice">📁 No PNG or GIF images found in your media library.</div>');
                    } else {
                        $('#kipdev-convert-selected').show();
                        $('#kipdev-convert-all').show();
                        
                        var statusHtml = '<div class="images-found-notice">';
                        statusHtml += '<strong>Found ' + r.data.total + ' image' + (r.data.total !== 1 ? 's' : '') + ' (PNG & GIF)</strong>';
                        
                        if (needsConversion > 0) {
                            statusHtml += ' • <span class="needs-conversion">' + needsConversion + ' need' + (needsConversion !== 1 ? '' : 's') + ' WebP conversion</span>';
                        } else {
                            statusHtml += ' • <span class="all-converted">✓ All have WebP versions!</span>';
                        }
                        
                        statusHtml += '</div>';
                        $('#conversion-status').html(statusHtml);
                    }
                } else {
                    $('#conversion-status').html('<div class="error-notice">✗ Failed to load images</div>');
                }
            });
        });
        
        // Render image grid
        function renderImageGrid(images) {
            var html = '';
            
            if (images.length === 0) {
                html = '<div class="empty-state">';
                html += '  <div class="empty-icon">🖼️</div>';
                html += '  <h3>No Images Found</h3>';
                html += '  <p>Your media library doesn\'t contain any PNG or GIF images yet.</p>';
                html += '  <p class="empty-hint">Upload some PNG or GIF images to start converting them to WebP format!</p>';
                html += '</div>';
            } else {
                html = '<div class="image-grid">';
                
                images.forEach(function(img) {
                    var statusClass = img.has_webp ? 'has-webp' : 'no-webp';
                    var statusIcon = img.has_webp ? '✓' : '○';
                    var statusText = img.has_webp ? 'WebP: ' + img.webp_size : 'No WebP';
                    var savingsText = img.has_webp ? ' (Saved ' + img.savings + '%)' : '';
                    
                    html += '<div class="image-item ' + statusClass + '" data-id="' + img.id + '">';
                    html += '  <div class="image-checkbox">';
                    html += '    <input type="checkbox" class="image-select" data-id="' + img.id + '" ' + (img.has_webp ? '' : 'checked') + '>';
                    html += '  </div>';
                    html += '  <div class="image-preview">';
                    html += '    <img src="' + (img.thumb || img.url) + '" alt="' + img.title + '">';
                    html += '  </div>';
                    html += '  <div class="image-info">';
                    html += '    <div class="image-title">' + img.title + '</div>';
                    html += '    <div class="image-meta"><strong>' + img.file_type + '</strong>: ' + img.file_size + '</div>';
                    html += '    <div class="image-status ' + statusClass + '">' + statusIcon + ' ' + statusText + savingsText + '</div>';
                    html += '    <div class="image-date">' + img.date + '</div>';
                    html += '  </div>';
                    html += '  <div class="image-convert-status" id="status-' + img.id + '" style="display:none;"></div>';
                    html += '</div>';
                });
                
                html += '</div>';
            }
            
            $('#images-container').html(html);
            
            // Handle checkbox changes
            $('.image-select').on('change', function(){
                updateSelectedImages();
            });
            
            updateSelectedImages();
        }
        
        // Update selected images list
        function updateSelectedImages() {
            selectedImages = [];
            $('.image-select:checked').each(function(){
                selectedImages.push(parseInt($(this).data('id')));
            });
            
            var btnText = selectedImages.length > 0 ? 
                'Convert Selected (' + selectedImages.length + ')' : 
                'Convert Selected';
            $('#kipdev-convert-selected').text(btnText);
        }
        
        // Convert selected images
        $('#kipdev-convert-selected').on('click', function(e){
            e.preventDefault();
            
            if (selectedImages.length === 0) {
                alert('Please select at least one image to convert.');
                return;
            }
            
            if (!confirm('Convert ' + selectedImages.length + ' selected image(s) to WebP?')) {
                return;
            }
            
            convertImages(selectedImages);
        });
        
        // Convert all images
        $('#kipdev-convert-all').on('click', function(e){
            e.preventDefault();
            
            var allIds = currentImages.filter(img => !img.has_webp).map(img => img.id);
            
            if (allIds.length === 0) {
                alert('All images already have WebP versions!');
                return;
            }
            
            if (!confirm('Convert all ' + allIds.length + ' image(s) to WebP?')) {
                return;
            }
            
            convertImages(allIds);
        });
        
        // Convert images with progress animation
        function convertImages(imageIds) {
            var total = imageIds.length;
            var completed = 0;
            var successful = 0;
            var skipped = 0;
            var failed = 0;
            
            // Disable buttons
            $('#kipdev-load-images, #kipdev-convert-selected, #kipdev-convert-all').prop('disabled', true);
            
            // Show progress bar
            $('#conversion-progress').show();
            $('#conversion-progress-bar').css('width', '0%');
            $('#conversion-progress-text').text('Converting 0 of ' + total + ' images...');
            
            // Process images one by one
            function processNext(index) {
                if (index >= imageIds.length) {
                    // All done!
                    $('#conversion-progress-bar').css('width', '100%');
                    $('#conversion-progress-text').html(
                        '<strong>Complete!</strong> ✓ ' + successful + ' converted, ' + 
                        skipped + ' skipped, ' + 
                        (failed > 0 ? '✗ ' + failed + ' failed' : '')
                    );
                    
                    $('#kipdev-load-images, #kipdev-convert-selected, #kipdev-convert-all').prop('disabled', false);
                    
                    // Reload images after 2 seconds
                    setTimeout(function(){
                        $('#kipdev-load-images').click();
                        $('#conversion-progress').fadeOut();
                    }, 2000);
                    
                    return;
                }
                
                var imageId = imageIds[index];
                var $statusDiv = $('#status-' + imageId);
                $statusDiv.show().html('<div class="converting-spinner">⟳ Converting...</div>');
                
                $.post(KIPDEV_OPT.ajax_url, {
                    action: 'kipdev_opt_action',
                    nonce: KIPDEV_OPT.nonce,
                    do: 'convert_to_webp',
                    attachment_id: imageId
                }, function(r){
                    completed++;
                    var progress = Math.round((completed / total) * 100);
                    
                    $('#conversion-progress-bar').css('width', progress + '%');
                    $('#conversion-progress-text').text('Converting ' + completed + ' of ' + total + ' images... (' + progress + '%)');
                    
                    if (r.success) {
                        if (r.data.already_exists) {
                            skipped++;
                            $statusDiv.html('<div class="convert-skipped">✓ Already exists</div>');
                        } else {
                            successful++;
                            $statusDiv.html('<div class="convert-success">✓ Saved ' + r.data.savings + '%</div>');
                        }
                    } else {
                        failed++;
                        $statusDiv.html('<div class="convert-failed">✗ Failed</div>');
                    }
                    
                    // Process next image
                    setTimeout(function(){
                        processNext(index + 1);
                    }, 200); // Small delay for visual effect
                });
            }
            
            // Start processing
            processNext(0);
        }
    });
})(jQuery);
