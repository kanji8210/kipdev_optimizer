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
    });
})(jQuery);
