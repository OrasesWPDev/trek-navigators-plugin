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
        $('.trek-navigators-video-responsive iframe').each(function() {
            // Add wrapper if not already wrapped
            if (!$(this).parent('.video-wrapper').length) {
                $(this).wrap('<div class="trek-navigators-video-wrapper"></div>');
            }
        });
    }

})(jQuery);