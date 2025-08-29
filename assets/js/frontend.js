jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize search functionality
    initSearchFilter();
    initACFFilters();
    
    function initSearchFilter() {
        var searchInput = $('#pgs-search-input');
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
                
                // Real-time search with debounce
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    performSearch(value);
                }, 500);
            } else {
                clearButton.hide();
                clearSearch.trigger('click');
            }
        });
        
        // Clear search input
        clearButton.on('click', function() {
            searchInput.val('').trigger('input').focus();
        });
        
        // Enter key search
        searchInput.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
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
        
        // Enhanced pagination click handler
        $(document).on('click', '.pgs-pagination-btn:not(.pgs-pagination-current)', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var page = $btn.data('page') || 1;
            var searchQuery = searchInput.val();
            
            // Add loading state to clicked button
            $btn.addClass('pgs-loading-btn');
            
            // Perform search with pagination
            performSearch(searchQuery, page, function() {
                $btn.removeClass('pgs-loading-btn');
            });
            
            // Smooth scroll to posts grid
            $('html, body').animate({
                scrollTop: $('.pgs-posts-grid').offset().top - 100
            }, 600, 'easeInOutCubic');
        });
    }
    
    function initACFFilters() {
        var $acfFilters = $('.pgs-acf-filters');
        if ($acfFilters.length === 0) return;
        
        // Auto-filter on change
        $(document).on('change', '.pgs-filter-select, .pgs-filter-input', function() {
            var $container = $(this).closest('.pgs-acf-filters');
            performACFFilter($container);
        });
        
        // Clear filters
        $(document).on('click', '.pgs-filter-clear', function() {
            var $container = $(this).closest('.pgs-acf-filters');
            $container.find('.pgs-filter-select, .pgs-filter-input').val('');
            performACFFilter($container);
        });
        
        // ACF pagination
        $(document).on('click', '.pgs-acf-pagination-btn:not(.pgs-pagination-current)', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var page = $btn.data('page') || 1;
            var filters = $btn.data('filters') || {};
            var $container = $btn.closest('.pgs-acf-filters');
            
            performACFFilter($container, page, filters);
            
            // Smooth scroll
            $('html, body').animate({
                scrollTop: $container.offset().top - 100
            }, 600);
        });
    }
    
    function performSearch(query, page, callback) {
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
        
        // Enhanced loading state
        postsContainer.addClass('pgs-loading');
        if (postsContainer.find('.pgs-loading-overlay').length === 0) {
            postsContainer.append(
                '<div class="pgs-loading-overlay">' +
                    '<div class="pgs-spinner"></div>' +
                    '<div style="margin-top: 10px; font-size: 12px; color: #666;">Searching...</div>' +
                '</div>'
            );
        }
        
        // AJAX request with enhanced error handling
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
                    // Smooth content update
                    postsContainer.fadeOut(200, function() {
                        $(this).html(response.data.posts).fadeIn(300);
                    });
                    
                    // Update pagination with animation
                    if (response.data.pagination) {
                        pagination.html(response.data.pagination);
                        pagination.hide().fadeIn(300);
                    } else {
                        pagination.fadeOut(200);
                    }
                    
                    // Update results info with animation
                    if (query) {
                        resultsCount.text('Found ' + response.data.total_posts + ' posts for "' + query + '"');
                        resultsInfo.slideDown(300);
                    } else {
                        resultsInfo.slideUp(200);
                    }
                    
                    // Add success animation
                    postsContainer.addClass('pgs-search-success');
                    setTimeout(function() {
                        postsContainer.removeClass('pgs-search-success');
                    }, 1000);
                } else {
                    console.error('Search failed:', response.data);
                    showErrorMessage('Search failed. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                showErrorMessage('Connection error. Please check your internet connection.');
            },
            complete: function() {
                // Remove loading state
                postsContainer.removeClass('pgs-loading');
                postsContainer.find('.pgs-loading-overlay').remove();
                
                if (callback) callback();
            }
        });
    }
    
    function performACFFilter($container, page, filters) {
        page = page || 1;
        filters = filters || {};
        
        var postsPerPage = $container.data('posts-per-page') || 6;
        var $resultsContainer = $container.find('.pgs-filtered-posts-container');
        
        // Collect current filter values if not provided
        if (Object.keys(filters).length === 0) {
            $container.find('.pgs-filter-select, .pgs-filter-input').each(function() {
                var $input = $(this);
                var name = $input.attr('name');
                var value = $input.val();
                
                if (name && value) {
                    filters[name] = value;
                }
            });
        }
        
        // Show loading
        $resultsContainer.addClass('pgs-loading');
        if ($resultsContainer.find('.pgs-loading-overlay').length === 0) {
            $resultsContainer.append('<div class="pgs-loading-overlay"><div class="pgs-spinner"></div></div>');
        }
        
        // AJAX request for ACF filtering
        $.ajax({
            url: pgs_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'pgs_acf_filter_posts',
                filters: filters,
                posts_per_page: postsPerPage,
                page: page,
                nonce: pgs_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update posts with smooth animation
                    $resultsContainer.fadeOut(200, function() {
                        $(this).html(response.data.posts).fadeIn(300);
                        
                        // Add pagination if needed
                        if (response.data.pagination) {
                            $(this).append('<div class="pgs-filter-pagination">' + response.data.pagination + '</div>');
                        }
                    });
                } else {
                    console.error('ACF Filter failed:', response.data);
                    showErrorMessage('Filter failed. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('ACF Filter AJAX error:', error);
                showErrorMessage('Connection error. Please try again.');
            },
            complete: function() {
                $resultsContainer.removeClass('pgs-loading');
                $resultsContainer.find('.pgs-loading-overlay').remove();
            }
        });
    }
    
    function showErrorMessage(message) {
        var $errorDiv = $('<div class="pgs-error-message">' + message + '</div>');
        
        $errorDiv.css({
            'position': 'fixed',
            'top': '20px',
            'right': '20px',
            'background': '#dc3545',
            'color': 'white',
            'padding': '12px 20px',
            'border-radius': '6px',
            'font-size': '14px',
            'z-index': 9999,
            'box-shadow': '0 4px 12px rgba(220, 53, 69, 0.3)',
            'animation': 'slideInRight 0.3s ease'
        });
        
        $('body').append($errorDiv);
        
        setTimeout(function() {
            $errorDiv.fadeOut(300, function() {
                $(this).remove();
            });
        }, 4000);
    }
    
    // Initialize enhanced post interactions
    initPostInteractions();
    
    function initPostInteractions() {
        // Enhanced hover effects with professional animations
        $(document).on('mouseenter', '.pgs-post-card, .pgs-post-list, .pgs-filtered-post', function() {
            $(this).addClass('pgs-hover-active');
        });
        
        $(document).on('mouseleave', '.pgs-post-card, .pgs-post-list, .pgs-filtered-post', function() {
            $(this).removeClass('pgs-hover-active');
        });
        
        // Add click ripple effect
        $(document).on('click', '.pgs-post-link', function(e) {
            var $link = $(this);
            var $ripple = $('<div class="pgs-ripple"></div>');
            
            var rect = this.getBoundingClientRect();
            var size = Math.max(rect.width, rect.height);
            var x = e.clientX - rect.left - size / 2;
            var y = e.clientY - rect.top - size / 2;
            
            $ripple.css({
                'position': 'absolute',
                'width': size + 'px',
                'height': size + 'px',
                'left': x + 'px',
                'top': y + 'px',
                'background': 'rgba(20, 184, 166, 0.3)',
                'border-radius': '50%',
                'transform': 'scale(0)',
                'animation': 'ripple 0.6s ease-out',
                'pointer-events': 'none',
                'z-index': 1
            });
            
            $link.css('position', 'relative').append($ripple);
            
            setTimeout(function() {
                $ripple.remove();
            }, 600);
        });
    }
    
    // Add ripple animation CSS
    $('<style>' +
        '@keyframes ripple { ' +
            'to { transform: scale(2); opacity: 0; } ' +
        '}' +
        '.pgs-hover-active { ' +
            'z-index: 10; ' +
        '}' +
        '.pgs-search-success { ' +
            'animation: successPulse 0.6s ease; ' +
        '}' +
        '@keyframes successPulse { ' +
            '0% { transform: scale(1); } ' +
            '50% { transform: scale(1.02); } ' +
            '100% { transform: scale(1); } ' +
        '}' +
        '.pgs-loading-btn { ' +
            'opacity: 0.6; ' +
            'pointer-events: none; ' +
        '}' +
        '.pgs-loading-btn::after { ' +
            'content: ""; ' +
            'position: absolute; ' +
            'width: 16px; height: 16px; ' +
            'border: 2px solid transparent; ' +
            'border-top: 2px solid currentColor; ' +
            'border-radius: 50%; ' +
            'animation: spin 1s linear infinite; ' +
        '}' +
        '@keyframes spin { ' +
            '0% { transform: rotate(0deg); } ' +
            '100% { transform: rotate(360deg); } ' +
        '}' +
    '</style>').appendTo('head');
    
    // Add smooth scrolling for better UX
    if (typeof $.easing === 'undefined') {
        $.easing.easeInOutCubic = function(x, t, b, c, d) {
            if ((t /= d / 2) < 1) return c / 2 * t * t * t + b;
            return c / 2 * ((t -= 2) * t * t + 2) + b;
        };
    }
});