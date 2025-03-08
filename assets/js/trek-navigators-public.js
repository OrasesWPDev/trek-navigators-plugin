/**
 * Trek Navigators - Public JavaScript
 *
 * Handles front-end functionality for Trek Navigators plugin
 */
(function($) {
    'use strict';

    // Document ready
    $(document).ready(function() {
        // Initialize responsive video embeds
        initResponsiveVideos();
    });

    /**
     * Make video embeds responsive
     */
    function initResponsiveVideos() {
        $('.navigator-video-responsive iframe').each(function() {
            // Add wrapper if not already wrapped
            if (!$(this).parent('.video-wrapper').length) {
                $(this).wrap('<div class="video-wrapper"></div>');
            }
        });
    }

})(jQuery);