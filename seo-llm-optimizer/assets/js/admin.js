/**
 * Admin JavaScript
 *
 * @package SEO_LLM_Optimizer
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Tab switching functionality
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();

            const tab = $(this).data('tab');

            // Update nav tabs
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');

            // Update content visibility
            $('.slo-tab-content').hide().removeClass('active');
            $('#tab-' + tab).show().addClass('active');

            // Update URL hash without page jump
            if (history.pushState) {
                history.pushState(null, null, '#' + tab);
            } else {
                location.hash = '#' + tab;
            }
        });

        // Handle initial hash on page load
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            const tabLink = $('.nav-tab[data-tab="' + hash + '"]');

            if (tabLink.length) {
                tabLink.trigger('click');
            }
        }

        // Clear cache button
        $('#slo-clear-cache').on('click', function() {
            const button = $(this);
            const statusEl = $('#slo-cache-status');

            // Disable button and update text
            button.prop('disabled', true).text(button.data('loading-text') || 'Clearing...');
            statusEl.text('').removeClass('success error');

            // Make AJAX request
            $.post(sloAdminData.ajaxUrl, {
                action: 'slo_clear_cache',
                nonce: sloAdminData.nonce
            }, function(response) {
                if (response.success) {
                    statusEl
                        .text(response.data.message || 'Cache cleared successfully!')
                        .addClass('success')
                        .css('color', '#46b450');

                    // Reset status after 3 seconds
                    setTimeout(function() {
                        statusEl.fadeOut(function() {
                            $(this).text('').css('display', '').css('color', '');
                        });
                    }, 3000);
                } else {
                    statusEl
                        .text(response.data.message || 'Failed to clear cache.')
                        .addClass('error')
                        .css('color', '#dc3232');
                }
            }).fail(function() {
                statusEl
                    .text('Network error. Please try again.')
                    .addClass('error')
                    .css('color', '#dc3232');
            }).always(function() {
                // Re-enable button and restore text
                button.prop('disabled', false).text(button.data('original-text') || 'Clear All Caches');
            });
        });

        // Store original button text
        const clearCacheBtn = $('#slo-clear-cache');
        if (clearCacheBtn.length) {
            clearCacheBtn.data('original-text', clearCacheBtn.text());
        }

        console.log('SEO & LLM Optimizer admin scripts loaded');
    });

})(jQuery);
