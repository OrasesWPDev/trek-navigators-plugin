/**
 * Trek Navigators - AJAX Pagination
 *
 * Handles pagination for Trek Navigators without page reload
 */
(function($) {
    'use strict';

    // Document ready
    $(document).ready(function() {
        // Initialize AJAX pagination for Trek Navigators
        initTrekNavigatorsAjaxPagination();
        console.log('Trek Navigators AJAX pagination script loaded');
    });

    /**
     * Initialize AJAX pagination for Trek Navigators
     */
    function initTrekNavigatorsAjaxPagination() {
        // Delegate click event to pagination links to capture future links
        $(document).on('click', '.trek-navigators-pagination a.page-numbers', function(e) {
            e.preventDefault();

            var $this = $(this);
            var targetUrl = $this.attr('href');
            var $container = $this.closest('.trek-navigators-grid-container');
            var $grid = $container.find('.trek-navigators-grid');
            var $pagination = $container.find('.trek-navigators-pagination');

            console.log('Pagination link clicked:', targetUrl);
            console.log('Container found:', $container.length > 0);
            console.log('Grid element found:', $grid.length > 0);

            // Get shortcode data
            var shortcodeData = $container.data('shortcode');
            console.log('Shortcode data:', shortcodeData);

            // Extract page number
            var pageNum = getPageNumberFromUrl(targetUrl);
            console.log('Page number extracted:', pageNum);

            // Show loading indicator
            $container.append('<div class="trek-navigators-loading">Loading...</div>');

            // AJAX request to get new content
            $.ajax({
                url: trek_navigators_ajax.ajax_url,
                type: 'post',
                data: {
                    action: 'trek_navigators_load_page',
                    page: pageNum,
                    shortcode_atts: shortcodeData,
                    nonce: trek_navigators_ajax.nonce
                },
                success: function(response) {
                    console.log('AJAX response received:', response);
                    if(response.success) {
                        console.log('AJAX success, updating content');
                        // Update grid content
                        $grid.html(response.data.grid_html);

                        // Update pagination
                        $pagination.html(response.data.pagination_html);

                        // Update URL without reloading page
                        updateBrowserUrl(targetUrl, pageNum);

                        console.log('Content updated successfully');
                    } else {
                        console.error('Error loading Trek Navigators page:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                },
                complete: function() {
                    // Remove loading indicator
                    $container.find('.trek-navigators-loading').remove();
                    console.log('Loading indicator removed');
                }
            });
        });
    }

    /**
     * Extract page number from URL
     */
    function getPageNumberFromUrl(url) {
        // Try to extract from /page/X/ format
        var matches = url.match(/\/page\/(\d+)/);
        if (matches && matches[1]) {
            return parseInt(matches[1], 10);
        }

        // Try to extract from ?paged=X format
        var pageParam = new URL(url).searchParams.get('paged');
        if (pageParam) {
            return parseInt(pageParam, 10);
        }

        return 1;
    }

    /**
     * Update browser URL without page reload
     */
    function updateBrowserUrl(url, pageNum) {
        if (history.pushState) {
            window.history.pushState(
                { pageNum: pageNum },
                'Page ' + pageNum,
                url
            );
        }
    }

})(jQuery);