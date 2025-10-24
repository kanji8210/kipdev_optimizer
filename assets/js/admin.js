/**
 * KipDev Optimizer Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Image quality slider
        $('#image-quality-slider').on('input', function() {
            $('#image-quality-value').text($(this).val() + '%');
        });

        // Optimize Images Button
        $('#optimize-images-btn').on('click', function() {
            var $button = $(this);
            var $spinner = $('#optimize-images-spinner');
            var $result = $('#optimize-images-result');

            $button.prop('disabled', true);
            $spinner.addClass('is-active');
            $result.html('');

            $.ajax({
                url: kipdevOptimizer.ajax_url,
                type: 'POST',
                data: {
                    action: 'kipdev_optimize_images',
                    nonce: kipdevOptimizer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        var message = 'Successfully optimized ' + data.optimized + ' of ' + data.total + ' images.';
                        if (data.failed > 0) {
                            message += ' ' + data.failed + ' images failed.';
                        }
                        $result.html('<div class="kipdev-result-message kipdev-result-success">' + message + '</div>');
                    } else {
                        $result.html('<div class="kipdev-result-message kipdev-result-error">Error: ' + response.data + '</div>');
                    }
                },
                error: function() {
                    $result.html('<div class="kipdev-result-message kipdev-result-error">An error occurred. Please try again.</div>');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');
                }
            });
        });

        // Optimize Videos Button
        $('#optimize-videos-btn').on('click', function() {
            var $button = $(this);
            var $spinner = $('#optimize-videos-spinner');
            var $result = $('#optimize-videos-result');

            $button.prop('disabled', true);
            $spinner.addClass('is-active');
            $result.html('');

            $.ajax({
                url: kipdevOptimizer.ajax_url,
                type: 'POST',
                data: {
                    action: 'kipdev_optimize_videos',
                    nonce: kipdevOptimizer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        var message = 'Successfully optimized ' + data.optimized + ' of ' + data.total + ' videos.';
                        if (data.failed > 0) {
                            message += ' ' + data.failed + ' videos failed.';
                        }
                        $result.html('<div class="kipdev-result-message kipdev-result-success">' + message + '</div>');
                    } else {
                        $result.html('<div class="kipdev-result-message kipdev-result-error">Error: ' + response.data + '</div>');
                    }
                },
                error: function() {
                    $result.html('<div class="kipdev-result-message kipdev-result-error">An error occurred. Please try again.</div>');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');
                }
            });
        });

        // Minify Assets Button
        $('#minify-assets-btn').on('click', function() {
            var $button = $(this);
            var $spinner = $('#minify-assets-spinner');
            var $result = $('#minify-assets-result');

            $button.prop('disabled', true);
            $spinner.addClass('is-active');
            $result.html('');

            $.ajax({
                url: kipdevOptimizer.ajax_url,
                type: 'POST',
                data: {
                    action: 'kipdev_minify_assets',
                    nonce: kipdevOptimizer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        var message = data.message || 'Assets minification completed.';
                        $result.html('<div class="kipdev-result-message kipdev-result-success">' + message + '</div>');
                    } else {
                        $result.html('<div class="kipdev-result-message kipdev-result-error">Error: ' + response.data + '</div>');
                    }
                },
                error: function() {
                    $result.html('<div class="kipdev-result-message kipdev-result-error">An error occurred. Please try again.</div>');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');
                }
            });
        });

        // Clear Cache Button
        $('#clear-cache-btn').on('click', function() {
            var $button = $(this);
            var $spinner = $('#clear-cache-spinner');
            var $result = $('#clear-cache-result');

            if (!confirm('Are you sure you want to clear all cache?')) {
                return;
            }

            $button.prop('disabled', true);
            $spinner.addClass('is-active');
            $result.html('');

            $.ajax({
                url: kipdevOptimizer.ajax_url,
                type: 'POST',
                data: {
                    action: 'kipdev_clear_cache',
                    nonce: kipdevOptimizer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        var message = data.message || 'Cache cleared successfully.';
                        $result.html('<div class="kipdev-result-message kipdev-result-success">' + message + '</div>');
                    } else {
                        $result.html('<div class="kipdev-result-message kipdev-result-error">Error: ' + response.data + '</div>');
                    }
                },
                error: function() {
                    $result.html('<div class="kipdev-result-message kipdev-result-error">An error occurred. Please try again.</div>');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');
                }
            });
        });

    });

})(jQuery);
