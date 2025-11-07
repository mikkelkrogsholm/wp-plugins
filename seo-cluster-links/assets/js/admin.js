/**
 * Admin JavaScript for SEO Cluster Links
 *
 * @package SEO_Cluster_Links
 */

(function($) {
    'use strict';

    /**
     * Initialize when DOM is ready
     */
    $(document).ready(function() {
        // Toggle pillar select dropdown based on post type selection
        $('input[name="scl_post_type"]').on('change', function() {
            if ($(this).val() === 'cluster') {
                $('.scl-pillar-select').slideDown();
            } else {
                $('.scl-pillar-select').slideUp();
            }
        });
    });

})(jQuery);
