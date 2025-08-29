jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize search functionality
    initSearchFilter();
    
    function initSearchFilter() {
        var searchInput = $('#pgs-search-input');
        var searchButton = $('#pgs-search-button');
        var clearButton = $('#pgs-search-clear');
        var clearSearch = $('#pgs-clear-search');
        var resultsInfo = $('#pgs-search-results-info');
        var resultsCount = $('#pgs-results-count');
        var searchTimeout;
        
        // Show/hide clear button
        searchInput.on('input', function() {
            var value = $(this).val();
            if (value.length > 0) {
                clearButton.show();
            } else {
                clearButton.hide();
                clearSearch.trigger('click');
            }
            
            // Real-time search with debounce
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                performSearch(value);
            }, 300);
        });
        
        // Clear search input
        clearButton.on('click', function() {
            searchInput.val('').trigger('input').focus();
        });
        
        // Search button click
        searchButton.on('click', function() {
            var searchQuery = searchInput.val();
            performSearch(searchQuery);
        });
        
        // Enter key search
        searchInput.on('keypress', function(e) {
            if (e.which === 13) {
                var searchQuery = $(this).val();
                performSearch(searchQuery);
            }
        });
        
        // Clear search results
        clearSearch.on('click', function() {
            searchInput.val('');
            clearButton.hide();
            resultsInfo.hide();
            performSearch('');
        });
        
        // Pagination click handler
        $(document).on('click', '.pgs-pagination-btn:not(.pgs-pagination-current)', function(e) {
            e.preventDefault();
            
            var url = $(this).attr('href');
            if (!url) return;
            
            // Extract page number from URL
            var pageMatch = url.match(/page\/(\d+)/);
            var page = pageMatch ? parseInt(pageMatch[1]) : 1;
            
            // Get current search query
            var searchQuery = searchInput.val();
            
            // Perform search with pagination
            performSearch(searchQuery, page);
            
            // Scroll to top of posts grid
            $('html, body').animate({
                scrollTop: $('.pgs-posts-grid').offset().top - 50
            }, 500);
        });
    }
    
    function performSearch(query, page) {
        page = page || 1;
        
        var postsGrid = $('.pgs-posts-grid');
        var postsContainer = $('.pgs-posts-container');
        var pagination = $('.pgs-pagination');
        var resultsInfo = $('#pgs-search-results-info');
        var resultsCount = $('#pgs-results-count');
        
        if (postsGrid.length === 0) return;
        
        // Get settings from data attributes
        var template = postsGrid.data('template') || 'card';
        var postsPerPage = postsGrid.data('posts-per-page') || 6;
        
        // Show loading state
        postsContainer.addClass('pgs-loading');
        if (postsContainer.find('.pgs-loading-overlay').length === 0) {
            postsContainer.append('<div class="pgs-loading-overlay"><div class="pgs-spinner"></div></div>');
        }
        
        // AJAX request
        $.ajax({
            url: pgs_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'pgs_filter_posts',
                search_query: query,
                posts_per_page: postsPerPage,
                template: template,
                page: page,
                nonce: pgs_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update posts
                    postsContainer.html(response.data.posts);
                    
                    // Update pagination
                    updatePagination(pagination, response.data.current_page, response.data.total_pages);
                    
                    // Update results info
                    if (query) {
                        var totalPosts = response.data.total_pages * postsPerPage;
                        resultsCount.text('Found ' + totalPosts + ' posts for "' + query + '"');
                        resultsInfo.show();
                    } else {
                        resultsInfo.hide();
                    }
                } else {
                    console.error('Search failed:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                postsContainer.html('<div class="pgs-no-posts">Search failed. Please try again.</div>');
            },
            complete: function() {
                // Remove loading state
                postsContainer.removeClass('pgs-loading');
                postsContainer.find('.pgs-loading-overlay').remove();
            }
        });
    }
    
    function updatePagination(paginationContainer, currentPage, totalPages) {
        if (totalPages <= 1) {
            paginationContainer.hide();
            return;
        }
        
        paginationContainer.show();
        
        var paginationHtml = '';
        var prevIcon = '←';
        var nextIcon = '→';
        
        // Previous button
        if (currentPage > 1) {
            paginationHtml += '<a href="#" data-page="' + (currentPage - 1) + '" class="pgs-pagination-btn pgs-pagination-prev">' + prevIcon + '</a>';
        }
        
        // Page numbers
        for (var i = 1; i <= totalPages; i++) {
            if (i === currentPage) {
                paginationHtml += '<span class="pgs-pagination-btn pgs-pagination-current">' + i + '</span>';
            } else {
                paginationHtml += '<a href="#" data-page="' + i + '" class="pgs-pagination-btn">' + i + '</a>';
            }
        }
        
        // Next button
        if (currentPage < totalPages) {
            paginationHtml += '<a href="#" data-page="' + (currentPage + 1) + '" class="pgs-pagination-btn pgs-pagination-next">' + nextIcon + '</a>';
        }
        
        paginationContainer.html(paginationHtml);
    }
    
    // Handle pagination clicks for AJAX search
    $(document).on('click', '.pgs-pagination-btn[data-page]', function(e) {
        e.preventDefault();
        
        var page = parseInt($(this).data('page'));
        var searchQuery = $('#pgs-search-input').val();
        
        performSearch(searchQuery, page);
        
        // Scroll to top of posts grid
        $('html, body').animate({
            scrollTop: $('.pgs-posts-grid').offset().top - 50
        }, 500);
    });
    
    // Initialize any additional interactive features
    initPostHovers();
    
    function initPostHovers() {
        // Enhanced hover effects
        $(document).on('mouseenter', '.pgs-post-card, .pgs-post-list', function() {
            $(this).addClass('pgs-hover');
        });
        
        $(document).on('mouseleave', '.pgs-post-card, .pgs-post-list', function() {
            $(this).removeClass('pgs-hover');
        });
    }
});