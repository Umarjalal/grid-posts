<?php

//custom filters
class ACF_Loop_Grid_Filters {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_acf_filter_posts', array($this, 'ajax_filter_posts'));
        add_action('wp_ajax_nopriv_acf_filter_posts', array($this, 'ajax_filter_posts'));
        add_shortcode('acf_loop_filters', array($this, 'render_filters_shortcode'));
        
        // Widget registration
        add_action('widgets_init', array($this, 'register_widget'));
    }
    
    public function init() {
        // Initialize the system
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        
        // Enqueue Flatpickr
        wp_enqueue_script('flatpickr', 'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js', array(), '4.6.13', true);
        wp_enqueue_style('flatpickr', 'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css', array(), '4.6.13');
        
        wp_localize_script('jquery', 'acf_filter_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('acf_filter_nonce')
        ));
        
        // Add inline styles and scripts
        add_action('wp_footer', array($this, 'add_inline_script'));
        add_action('wp_head', array($this, 'add_inline_styles'));
    }
    
    public function add_inline_styles() {
        ?>
        <style>
		.acf-filters-wrapper {
			background: #ffffff;
			overflow: auto;
/* 			overflow-y: auto; */
			height: 85vh;
		}

		.light-theme .acf-filters-wrapper {
			background: #001319;
		}

		.acf-selected-filters {
			padding-bottom: 20px;
			border-bottom: 1px solid #f0f0f0;
			display: none;
		}

		.acf-selected-filters.has-selections {
			display: block;
		}

		.acf-selected-tag {
			display: inline-flex;
			font-family: "Oswald", Sans-serif;
			flex-direction: row-reverse;
			align-items: center;
			cursor: pointer;
			color: #001319;
			padding: 6px 8px;
			margin: 4px 8px 4px 0;
			border-radius: 16px;
			font-size: 12px;
			font-weight: 600;
			border: 1px solid #EBEDEE;
		}

		.light-theme .acf-selected-tag {
			color: white;
			border-color: #1F2F35;
		}

		.acf-selected-tag .remove-tag {
			margin-right: 8px;
			cursor: pointer;
			font-weight: 600;
			color: #001319;
			font-size: 12px;
			line-height: 1;
		}

		.light-theme .acf-selected-tag .remove-tag {
			color: white;
		}

		.acf-selected-tag:hover .remove-tag svg path {
			stroke: #d32f2f;
		}

		.acf-filter-accordion {
			border-top: 1px solid #EBEDEE;
			overflow: hidden;
			transition: border-top-color 0.3s ease;
		}
			.light-theme .acf-filter-accordion{
				border-top-color:#1F2F35;
			}

		/* Blue border-top when accordion is open */
		.acf-filter-accordion.open {
			border-top: 1px solid #00AAD8;
		}

		.acf-filter-accordion:last-child {
			border-bottom: none;
		}

		.acf-accordion-header {
			background: #ffffff;
			padding: 24px 0px;
			cursor: pointer;
			display: flex;
			justify-content: space-between;
			align-items: center;
			transition: background-color 0.2s ease;
			user-select: none;
		}

		.light-theme .acf-accordion-header {
			background: #001319;
		}

		.acf-accordion-title {
			font-size: 20px;
			font-family: "Oswald", Sans-serif;
			font-weight: 500;
			color: #001319;
			text-transform: uppercase;
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.light-theme .acf-accordion-title {
			color: white;
		}

		.acf-filters-inner__wrapper {
			height: 100%;
			display: flex;
			width: 100%;
			flex-direction: column;
			justify-content: space-between;
		}

		.acf-filter-count {
			color: #7A8487;
			font-family: "Oswald", Sans-serif;
			font-size: 12px;
			font-weight: 500;
			padding: 2px 8px;
			border-radius: 12px;
			min-width: 20px;
			text-align: center;
		}

		.acf-accordion-icon {
			font-size: 18px;
			color: #7A8487;
			transition: transform 0.3s ease;
		}

		.acf-accordion-icon.open {
			transform: rotate(180deg);
		}

		.acf-accordion-content {
			background: #fff;
			padding: 0;
			max-height: 0;
			overflow: hidden;
			transition: max-height 0.3s ease;
		}

		.light-theme .acf-accordion-content {
			background: #001319;
		}

		.acf-accordion-content.open {
			max-height: 400px;
		}

		.acf-filter-options {
			padding-bottom: 24px;
			background: #fff;
		}

		.light-theme .acf-filter-options {
			background: #001319;
		}

		.acf-filter-item {
			display: flex;
			align-items: center;
			padding: 5px 0;
			cursor: pointer;
			transition: background-color 0.2s ease;
		}

		.acf-filter-checkbox {
			width: 20px;
			height: 20px;
			border: 2px solid #EBEDEE;
			border-radius: 100%;
			margin-right: 8px;
			position: relative;
			flex-shrink: 0;
			transition: all 0.2s ease;
		}
			.light-theme .acf-filter-checkbox{
				border-color: #7A8487;
			}

		.acf-filter-checkbox.checked {
			background: #00AAD8;
			border-color: #00AAD8;
		}

		.acf-filter-checkbox.checked::after {
			content: '✓';
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			color: white;
			font-size: 12px;
			font-weight: bold;
		}

		.acf-filter-label {
			font-size: 15px;
			color: #001319;
			flex: 1;
		}

		.light-theme .acf-filter-label {
			color: white;
		}

		.acf-date-filter {
			padding-bottom: 20px;
		}

		.acf-date-inputs {
			display: flex;
			gap: 15px;
			flex-wrap: wrap;
		}

		.acf-date-field {
			flex: 1;
			min-width: 140px;
		}

		.acf-date-label {
			display: block;
			font-size: 14px;
			font-weight: 600;
			color: #001319;
			margin-bottom: 8px;
			text-transform: uppercase;
			font-family: "Oswald", Sans-serif;
		}

		.light-theme .acf-date-label {
			color: white;
		}

		.acf-date-input {
			width: 100%;
			padding: 12px;
			border: 1px solid #EBEDEE;
			border-radius: 6px;
			font-size: 14px;
			transition: border-color 0.2s ease;
			background: #ffffff;
			color: #001319;
			font-family: "Oswald", Sans-serif;
			font-size: 14px;
			font-weight: 500;
			text-transform: uppercase;
			line-height: 100%;
		}

		.light-theme .acf-date-input {
			border-color: #7A8487;
			background: #001319;
			color: white;
		}

		.acf-date-input:focus {
			outline: none;
			border-color: #00AAD8;
		}

		/* Flatpickr theme customization */
		.flatpickr-calendar {
			font-family: "Oswald", Sans-serif;
		}

		.flatpickr-day.selected {
			background: #00AAD8;
			border-color: #00AAD8;
		}

		.flatpickr-day:hover {
			background: rgba(0, 170, 216, 0.1);
		}

		.light-theme .flatpickr-calendar {
			background: #001319;
			color: white;
			border-color: #7A8487;
		}

		.light-theme .flatpickr-day {
			color: white;
		}

		.light-theme .flatpickr-day:hover {
			background: rgba(0, 170, 216, 0.2);
		}

		.acf-filter-actions {
			padding: 16px;
			background: #ffffff;
			border-top: 1px solid #EBEDEE;
			display: none;
			gap: 12px;
			position: fixed;
			bottom: 0;
			right:0;
			width: 100%;
			max-width:640px;
		}

		.light-theme .acf-filter-actions {
			background: #001319;
			border-top-color:#7A8487;
		}

		.acf-filter-actions.show {
			display: flex;
			justify-content: center;
		}

		.acf-action-btn {
			padding: 14px 20px;
			border: none;
			border-radius: 8px;
			font-size: 16px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.2s ease;
			text-transform: uppercase;
		}

		.acf-reset-btn {
			background: transparent;
			color: #001319;
			height: 43px;
			font-size: 14px;
			font-weight: 600;
			border: 1px solid #EBEDEE;
			border-radius: 999999px;
		}

		.light-theme .acf-reset-btn {
			color: white;
			border-color:#1F2F35;
		}

		.acf-confirm-btn {
			background: #00AAD8;
			color: white;
			width: 147px;
			font-size: 14px;
			height: 43px;
			font-weight: 600;
			border-radius: 999999px;
			border: 1px solid #00AAD8;
		}

		.acf-loading {
			display: none;
			text-align: center;
			padding: 40px 20px;
			background: rgba(255, 255, 255, 0.9);
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			z-index: 1000;
		}
			.light-theme .acf-loading {
				background:rgb(0, 19, 25 , 0.9)
			}
			.light-theme .acf-loading p{
				color: white;
			}

		.acf-loading-spinner {
			width: 40px;
			height: 40px;
			border: 4px solid #f3f3f3;
			border-top: 4px solid #00AAD8;
			border-radius: 50%;
			animation: spin 1s linear infinite;
			margin: 0 auto 15px;
		}

		@keyframes spin {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}

		@media (max-width: 768px) {
			.acf-date-inputs {
				flex-direction: column;
			}

			.acf-date-field {
				min-width: 100%;
			}

			.acf-accordion-content.open {
				max-height: 300px;
			}
		}

		.blog-filters.has-filters::before {
			content: '';
			position: absolute;
			top: 0px;
			right: 0px;
			width: 10px;
			height: 10px;
			background: #00AEEF;
			border-radius: 50%;
			z-index: 100;
		}

		.acf-filters_inner_section {
			padding-bottom: 70px;
		}
			
			.light-theme .flatpickr-months .flatpickr-next-month svg path, .flatpickr-months .flatpickr-prev-month svg path{
				fill: white;
			}
			.light-theme span.flatpickr-weekday, .light-theme .flatpickr-monthDropdown-months, .light-theme .flatpickr-current-month input.cur-year{
				color:white;
			}

	</style>
        <?php
    }
    
    public function add_inline_script() {
        ?>
        <script>
			jQuery(document).ready(function($) {
                
                // Store original querydata on page load
                var originalQueryData = null;
                var savedFilters = {};
				var fromDatePicker = null;
				var toDatePicker = null;
				
				// Initialize Flatpickr date pickers
				function initializeDatePickers() {
					// Destroy existing instances first
					if (fromDatePicker) {
						fromDatePicker.destroy();
						fromDatePicker = null;
					}
					if (toDatePicker) {
						toDatePicker.destroy();
						toDatePicker = null;
					}

					// Wait for DOM elements to be ready
					setTimeout(function() {
						if ($('.acf-date-from').length) {
							fromDatePicker = flatpickr('.acf-date-from', {
								dateFormat: 'm-d-y',
								placeholder: 'Select start date',
								allowInput: true,
								disableMobile: true,
								onChange: function(selectedDates, dateStr, instance) {
									updateFilterActions();
									// Update max date for "to" picker
									if (toDatePicker && dateStr) {
										toDatePicker.set('minDate', dateStr);
									}
								}
							});
						}

						if ($('.acf-date-to').length) {
							toDatePicker = flatpickr('.acf-date-to', {
								dateFormat: 'm-d-y',
								placeholder: 'Select end date',
								allowInput: true,
								disableMobile: true,
								onChange: function(selectedDates, dateStr, instance) {
									updateFilterActions();
									// Update min date for "from" picker
									if (fromDatePicker && dateStr) {
										fromDatePicker.set('maxDate', dateStr);
									}
								}
							});
						}
					}, 50);
				}

				// Initialize date pickers on page load
				setTimeout(initializeDatePickers, 100);

				// Re-initialize when popup opens
				$(document).on('elementor/popup/show', function(event, id, instance) {
					setTimeout(initializeDatePickers, 300);
				});
                
                // Store original querydata when filters are first initialized
                function storeOriginalQueryData() {
                    var $gridContainer = $('.ue-grid, .ue-loop-grid, .ue-grid-wrapper, [class*="ue-grid"]').first();
                    if ($gridContainer.length && !originalQueryData) {
                        var currentQueryData = $gridContainer.attr('querydata');
                        try {
                            originalQueryData = JSON.parse(currentQueryData || '{}');
                            console.log('Stored original querydata:', originalQueryData);
                        } catch (e) {
                            console.warn('Could not parse original querydata:', e);
                            originalQueryData = {
                                "count_posts": $gridContainer.find('.ue-grid-item').length,
                                "total_posts": $gridContainer.find('.ue-grid-item').length,
                                "page": 1,
                                "num_pages": 1,
                                "orderdir": "asc"
                            };
                        }
                    }
                }
                
                // Call this on page load
                storeOriginalQueryData();
                
                // Accordion functionality
                $(document).on('click', '.acf-accordion-header', function() {
                    var $header = $(this);
                    var $content = $header.next('.acf-accordion-content');
                    var $icon = $header.find('.acf-accordion-icon');
                    var $accordion = $header.parent('.acf-filter-accordion');
                    
                    // Toggle current accordion
                    $content.toggleClass('open');
                    $icon.toggleClass('open');
                    $accordion.toggleClass('open');
                    
                    // Update max-height for smooth animation
                    if ($content.hasClass('open')) {
                        $content.css('max-height', $content[0].scrollHeight + 'px');
                    } else {
                        $content.css('max-height', '0');
                    }
                });
                
                // Filter item click functionality
                $(document).on('click', '.acf-filter-item', function(e) {
                    e.preventDefault();
                    var $item = $(this);
                    var $checkbox = $item.find('.acf-filter-checkbox');
                    
                    // Check if this is the last checked checkbox
                    var isLastChecked = $('.acf-filter-checkbox.checked').length === 1 && $checkbox.hasClass('checked');
                    
                    // Toggle selection
                    $checkbox.toggleClass('checked');
                    
                    updateSelectedFilters();
                    updateFilterActions();
                    
                    // Auto-apply filters if unchecking the last checked checkbox
                    if (isLastChecked) {
                        applyFilters();
                    }
                    // Otherwise, wait for confirm button
                });
                
                // Date input change (for direct input)
                $(document).on('change input', '.acf-date-input', function() {
                    updateFilterActions();
                });
                
                // Remove selected tag
                $(document).on('click', '.remove-tag', function(e) {
                    e.stopPropagation();
                    var $tag = $(this).closest('.acf-selected-tag');
                    var fieldKey = $tag.data('field');
                    var value = $tag.data('value');
                    
                    // Find and uncheck the corresponding filter item
                    $('.acf-filter-item').each(function() {
                        var $item = $(this);
                        var itemField = $item.closest('.acf-filter-accordion').data('field-key');
                        var itemValue = $item.data('value');
                        
                        if (itemField === fieldKey && itemValue === value) {
                            $item.find('.acf-filter-checkbox').removeClass('checked');
                            return false;
                        }
                    });
                    
                    updateSelectedFilters();
                    updateFilterActions();
                    applyFilters(); // Auto-apply filters after removing tag
                });
                
                // Reset all filters
                $(document).on('click', '.acf-reset-btn', function() {
                    $('.acf-filter-checkbox').removeClass('checked');
                    
                    // Clear Flatpickr date inputs
                    if (fromDatePicker) {
                        fromDatePicker.clear();
                    }
                    if (toDatePicker) {
                        toDatePicker.clear();
                    }
                    
                    // Also clear regular date inputs as fallback
                    $('.acf-date-input').val('');
                    
                    updateSelectedFilters();
                    updateFilterActions();
                    applyFilters(); // Auto-apply empty filters to show all posts
					setTimeout(function() {
						$('.dialog-close-button.dialog-lightbox-close-button').trigger('click');
					}, 500); // delay in ms
                });
                
                // Confirm/Apply filters
                $(document).on('click', '.acf-confirm-btn', function() {
                    applyFilters();
					setTimeout(function() {
						$('.dialog-close-button.dialog-lightbox-close-button').trigger('click');
					}, 500); // delay in ms
                });
                
                function updateSelectedFilters() {
                    var $selectedContainer = $('.acf-selected-filters');
                    var $tagsContainer = $selectedContainer.find('.acf-selected-tags');
                    
                    if (!$tagsContainer.length) {
                        $selectedContainer.append('<div class="acf-selected-tags"></div>');
                        $tagsContainer = $selectedContainer.find('.acf-selected-tags');
                    }
                    
                    $tagsContainer.empty();
                    
                    // Add selected filter tags
                    $('.acf-filter-checkbox.checked').each(function() {
                        var $checkbox = $(this);
                        var $item = $checkbox.closest('.acf-filter-item');
                        var $accordion = $item.closest('.acf-filter-accordion');
                        var fieldKey = $accordion.data('field-key');
                        var value = $item.data('value');
                        var label = $item.find('.acf-filter-label').text();
                        
                        var $tag = $(`
                        <div class="acf-selected-tag" data-field="${fieldKey}" data-value="${value}">
                            ${label}
                            <span class="remove-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                <path d="M9.375 2.625L2.625 9.375" stroke="#001319" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.375 9.375L2.625 2.625" stroke="#001319" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            </span>
                        </div>
                        `);

                        $tagsContainer.append($tag);
                    });

                    // Add date range tags
                    var dateFrom = $('.acf-date-from').val();
                    var dateTo = $('.acf-date-to').val();

                    if (dateFrom) {
                        var $dateTag = $(`
                        <div class="acf-selected-tag" data-field="publish_date_from" data-value="${dateFrom}">
                            From: ${dateFrom}
                            <span class="remove-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                <path d="M9.375 2.625L2.625 9.375" stroke="#001319" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.375 9.375L2.625 2.625" stroke="#001319" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            </span>
                        </div>
                        `);
                        $tagsContainer.append($dateTag);
                    }

                    if (dateTo) {
                        var $dateTag = $(`
                        <div class="acf-selected-tag" data-field="publish_date_to" data-value="${dateTo}">
                            To: ${dateTo}
                            <span class="remove-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                <path d="M9.375 2.625L2.625 9.375" stroke="#001319" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.375 9.375L2.625 2.625" stroke="#001319" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            </span>
                        </div>
                        `);
                        $tagsContainer.append($dateTag);
                    }
                    
                    // Show/hide selected filters container
                    if ($tagsContainer.children().length > 0) {
                        $selectedContainer.addClass('has-selections');
                    } else {
                        $selectedContainer.removeClass('has-selections');
                    }
                }
                
                function updateFilterActions() {
                    var hasSelections = $('.acf-filter-checkbox.checked').length > 0 ||
                        $('.acf-date-input').filter(function() { return $(this).val() !== ''; }).length > 0;

                    // Toggle .has-filters 
                        if (hasSelections) {
							$('.acf-filter-actions').addClass('show');
						} else {
							$('.acf-filter-actions').removeClass('show');
						}
                }
                
                function collectFilterData() {
                    var filters = {};
                    
                    // Collect ACF field filters
                    $('.acf-filter-accordion[data-field-key]').each(function() {
                        var $accordion = $(this);
                        var fieldKey = $accordion.data('field-key');
                        
                        // Skip publish_date accordion - handle separately
                        if (fieldKey === 'publish_date') return;
                        
                        var selectedValues = [];
                        
                        $accordion.find('.acf-filter-checkbox.checked').each(function() {
                            var $item = $(this).closest('.acf-filter-item');
                            selectedValues.push($item.data('value'));
                        });
                        
                        if (selectedValues.length > 0) {
                            filters[fieldKey] = selectedValues;
                        }
                    });
                    
                    // Collect publish date filters
                    var dateFrom = $('.acf-date-from').val();
                    var dateTo = $('.acf-date-to').val();
                    
                    if (dateFrom) filters['publish_date_from'] = dateFrom;
                    if (dateTo) filters['publish_date_to'] = dateTo;
                    
                    return filters;
                }
                
                function applyFilters() {
                    var $wrapper = $('.acf-filters-wrapper');
                    var $loading = $wrapper.find('.acf-loading');
                    
                    if (!$loading.length) {
                        $wrapper.append('<div class="acf-loading"><div class="acf-loading-spinner"></div><p>Loading filtered results...</p></div>');
                        $loading = $wrapper.find('.acf-loading');
                    }
                    
                    $wrapper.css('position', 'relative');
                    $loading.show();
                    
                    var filters = collectFilterData();
                    savedFilters = filters;
					if (Object.keys(filters).length > 0) {
						$('.blog-filters').addClass('has-filters');
					} else {
						$('.blog-filters').removeClass('has-filters');
					}
                    // Find the grid container
                    var $gridContainer = $('.ue-grid, .ue-loop-grid, .ue-grid-wrapper, [class*="ue-grid"]').first();
                    if (!$gridContainer.length) {
                        $gridContainer = $wrapper.nextAll().first();
                        if (!$gridContainer.length) {
                            $loading.hide();
                            console.error('Grid container not found');
                            alert('Error: Could not find the grid container.');
                            return;
                        }
                    }
                    
                    // If no filters, restore original querydata and show all items
                    if (Object.keys(filters).length === 0) {
                        var $items = $gridContainer.find('.ue-grid-item');
                        $items.css('display', ''); // Remove inline display:none to show
                        $gridContainer.find('.empty-message-container').remove(); // Remove no-results message
                        
                        // Restore original querydata
                        if (originalQueryData) {
                            var restoredQueryData = {
                                ...originalQueryData,
                                "filtered": false,
                                "filter_applied": false
                            };
                            $gridContainer.attr('querydata', JSON.stringify(restoredQueryData));
                            $gridContainer.data('querydata', restoredQueryData);
                            console.log('Restored original querydata:', restoredQueryData);
                        }
						$('.archive_pagination.uc-filter-pagination').show();
                        
                        refreshGrid($gridContainer);
                        $loading.hide();
                        return;
                    }
					
					
                    $.ajax({
                        url: acf_filter_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'acf_filter_posts',
                            filters: filters,
                            post_type: $wrapper.data('post-type'),
                            posts_per_page: -1, // Hardcoded to -1
                            nonce: acf_filter_ajax.nonce
                        },
                        success: function(response) {
                            $loading.hide();
                            
                            if (response.success) {
								console.log(response);
                                var postIds = response.data.post_ids;
                                var totalFoundPosts = response.data.found_posts;
                                var $items = $gridContainer.find('.ue-grid-item');
                                
                                $items.css('display', 'none'); // Hide all with inline style
                                $gridContainer.find('.empty-message-container').remove(); // Remove any existing no-results message
                                
                                var visibleCount = 0;
                                
                                if (postIds.length > 0) {
                                    postIds.forEach(function(id) {
                                        var $matchingItems = $items.filter('[data-postid="' + id + '"]');
                                        if ($matchingItems.length > 0) {
                                            $matchingItems.css('display', ''); // Show matching by removing display:none
                                            visibleCount++;
                                        }
                                    });
                                } else {
                                    // Add no-results message if none found
                                    if (!$gridContainer.find('.empty-message-container').length) {
                                        $gridContainer.append(`<div class="empty-message-container"><?php echo do_shortcode('[elementor-template id="33937"]'); ?></div>`);
                                    }
                                }
                                
                                // Create updated querydata based on original structure
                                var baseQueryData = originalQueryData || {};
                                var updatedQueryData = {
                                    ...baseQueryData, // Preserve original structure
                                    "count_posts": visibleCount,
                                    "total_posts": totalFoundPosts,
                                    "page": 1, // Reset to first page after filtering
                                    "num_pages": visibleCount > 0 ? 1 : 0,
                                    "filtered": true,
                                    "filter_applied": true,
                                    "applied_filters": filters, // Store which filters were applied
                                    "visible_post_ids": postIds // Store filtered post IDs
                                };
                                
                                // Update the querydata attribute
                                $gridContainer.attr('querydata', JSON.stringify(updatedQueryData));
                                $gridContainer.data('querydata', updatedQueryData);
								$('.archive_pagination.uc-filter-pagination').hide();

                                
                                console.log('Updated querydata:', updatedQueryData);
                                
                                refreshGrid($gridContainer);
                                
                                // Trigger custom events for other scripts that might be listening
                                $gridContainer.trigger('querydata_updated', [updatedQueryData, postIds]);
                                $gridContainer.trigger('acf_filter_applied', [filters, updatedQueryData]);
                                
                            } else {
                                console.error('Filter error:', response.data || 'Unknown error');
                                alert('Error loading filtered content: ' + (response.data || 'Unknown error'));
                            }
                        },
                        error: function(xhr, status, error) {
                            $loading.hide();
                            console.error('AJAX Error:', error);
                            alert('Error loading filtered content. Please check console for details.');
                        }
                    });
                }

                
                function refreshGrid($gridContainer) {
                    // Trigger refresh events for the loop grid
                    $gridContainer.trigger('uc_ajax_refreshed');
					
                    
                    // Re-initialize masonry and other grid layouts
                    setTimeout(function() {
                        // Unlimited Elements specific refresh
                        if (typeof initializeMasonryLayout === 'function') {
                            initializeMasonryLayout();
                        }
                        
                        if (typeof UEMasonryGrid !== 'undefined' && `UEMasonryGrid`.layout) {
                            UEMasonryGrid.layout();
                        }
                        
                        // Additional triggers for UE grid
                        $(window).trigger('resize');
                        $gridContainer.trigger('layoutComplete');
                        
                        // Ensure UE filterable grid reinitializes
                        if ($gridContainer.hasClass('uc-filterable-grid')) {
                            $gridContainer.trigger('filterablegrid.init');
                        }
                    }, 300); // Increased delay for stability
                }
                
                // Initialize filter counts
                updateFilterCounts();
                
                function updateFilterCounts() {
                    $('.acf-filter-accordion').each(function() {
                        var $accordion = $(this);
                        var count = $accordion.find('.acf-filter-item').length;
                        $accordion.find('.acf-filter-count').text(count);
                    });
                }
                
                // Function to get current querydata (useful for debugging or other scripts)
                window.getCurrentQueryData = function() {
                    var $gridContainer = $('.ue-grid, .ue-loop-grid, .ue-grid-wrapper, [class*="ue-grid"]').first();
                    if ($gridContainer.length) {
                        var querydata = $gridContainer.attr('querydata');
                        try {
                            return JSON.parse(querydata || '{}');
                        } catch (e) {
                            return {};
                        }
                    }
                    return {};
                };
                
                // Function to get original querydata
                window.getOriginalQueryData = function() {
                    return originalQueryData;
                };
                
                // Function to check if filters are currently applied
                window.areFiltersApplied = function() {
                    var currentData = getCurrentQueryData();
                    return currentData.filter_applied === true;
                };
                
                // Function to get currently applied filters
                window.getCurrentFilters = function() {
                    var currentData = getCurrentQueryData();
                    return currentData.applied_filters || {};
                };
                
                // Function to get visible post IDs
                window.getVisiblePostIds = function() {
                    var currentData = getCurrentQueryData();
                    return currentData.visible_post_ids || [];
                };
                
                // Ensure all posts are visible on page load
                var $initialGrid = $('.ue-grid, .ue-loop-grid, .ue-grid-wrapper, [class*="ue-grid"]').first();
                if ($initialGrid.length) {
                    $initialGrid.find('.ue-grid-item').css('display', '');
                    refreshGrid($initialGrid);
                }
                

				jQuery(document).on('elementor/popup/show', function(event, id, instance) {
					if (Object.keys(savedFilters).length > 0) {
						// Clear all checkboxes first
						jQuery('.acf-filter-checkbox').removeClass('checked');

						// Restore checkboxes for each field
						for (var fieldKey in savedFilters) {
							var fieldValues = savedFilters[fieldKey];

							// Handle publish date filters separately
							if (fieldKey === 'publish_date_from') {
								jQuery('.acf-date-from').val(fieldValues);
								if (fromDatePicker) {
									fromDatePicker.setDate(fieldValues);
								}
								continue;
							}
							if (fieldKey === 'publish_date_to') {
								jQuery('.acf-date-to').val(fieldValues);
								if (toDatePicker) {
									toDatePicker.setDate(fieldValues);
								}
								continue;
							}

							// Handle regular ACF field filters (arrays)
							if (Array.isArray(fieldValues)) {
								fieldValues.forEach(function(val) {
									jQuery('.acf-filter-accordion[data-field-key="'+fieldKey+'"] .acf-filter-item[data-value="'+val+'"] .acf-filter-checkbox').addClass('checked');
								});
							} else {
								// Handle single values (fallback)
								jQuery('.acf-filter-accordion[data-field-key="'+fieldKey+'"] .acf-filter-item[data-value="'+fieldValues+'"] .acf-filter-checkbox').addClass('checked');
							}
						}

						updateSelectedFilters();
						updateFilterActions();
					}
				});

				// Handle removal of date tags
				$(document).on('click', '.acf-selected-tag[data-field="publish_date_from"] .remove-tag, .acf-selected-tag[data-field="publish_date_to"] .remove-tag', function(e) {
					e.stopPropagation();
					var $tag = $(this).closest('.acf-selected-tag');
					var fieldKey = $tag.data('field');

					if (fieldKey === 'publish_date_from') {
						$('.acf-date-from').val('');
						if (fromDatePicker) {
							fromDatePicker.clear();
						}
					} else if (fieldKey === 'publish_date_to') {
						$('.acf-date-to').val('');
						if (toDatePicker) {
							toDatePicker.clear();
						}
					}

					updateSelectedFilters();
					updateFilterActions();
					applyFilters();
				});
			
			});
        </script>
        <?php
    }
    
    public function render_filters_shortcode($atts) {
        $atts = shortcode_atts(array(
            'post_type' => 'post',
            'fields' => '', // Comma separated ACF field names
            'show_date_filter' => 'true',
            'filter_titles' => '' // Comma separated titles for fields
        ), $atts);
        
        return $this->generate_filters_html($atts);
    }
    
    private function generate_filters_html($args) {
        $post_type = sanitize_text_field($args['post_type']);
        $fields = !empty($args['fields']) ? array_map('trim', explode(',', $args['fields'])) : array();
        $show_date_filter = $args['show_date_filter'] === 'true';
        $filter_titles = !empty($args['filter_titles']) ? array_map('trim', explode(',', $args['filter_titles'])) : array();
        
        if (empty($fields) && !$show_date_filter) {
            return '<p>No filters configured. Please specify ACF fields or enable date filter.</p>';
        }
        
        ob_start();
        ?>
        <div class="acf-filters-wrapper" data-post-type="<?php echo esc_attr($post_type); ?>" data-posts-per-page="-1">
			<div class="acf-filters-inner__wrapper">
            
				<div class="acf-filters_inner_section">
					<!-- Selected Filters Display -->
					<div class="acf-selected-filters">
						<div class="acf-selected-tags"></div>
					</div>

					<div class="acf-filters-acc">
						<?php foreach ($fields as $index => $field_key): 
							$field_key = trim($field_key);
							$field_values = $this->get_field_values($field_key, $post_type);

							if (empty($field_values)) {
								echo '<!-- Field "' . esc_html($field_key) . '" has no values or doesn\'t exist -->';
								continue;
							}

							$field_title = isset($filter_titles[$index]) && !empty($filter_titles[$index]) ? 
										$filter_titles[$index] : 
										$this->get_field_label($field_key, $post_type);
							?>

							<div class="acf-filter-accordion" data-field-key="<?php echo esc_attr($field_key); ?>">
								<div class="acf-accordion-header">
									<div class="acf-accordion-title">
										<?php echo esc_html(strtoupper($field_title)); ?>
										<span class="acf-filter-count"><?php echo count($field_values); ?></span>
									</div>
									<div class="acf-accordion-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2.5V13.5" stroke="#001319" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M3.5 9L8 13.5L12.5 9" stroke="#001319" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg></div>
								</div>

								<div class="acf-accordion-content">
									<div class="acf-filter-options">
										<?php foreach ($field_values as $value): ?>
											<div class="acf-filter-item" data-value="<?php echo esc_attr($value); ?>">
												<div class="acf-filter-checkbox"></div>
												<div class="acf-filter-label"><?php echo esc_html($value); ?></div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							</div>

						<?php endforeach; 

						if ($show_date_filter): ?>
							<div class="acf-filter-accordion" data-field-key="publish_date" >
								<div class="acf-accordion-header">
									<div class="acf-accordion-title">
									 DATE
									</div>
									<div class="acf-accordion-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2.5V13.5" stroke="#001319" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M3.5 9L8 13.5L12.5 9" stroke="#001319" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg></div>
								</div>

								<div class="acf-accordion-content">
									<div class="acf-date-filter">
										<div class="acf-date-inputs">
											<div class="acf-date-field">
												<label class="acf-date-label">From</label>
												<input type="text" class="acf-date-input acf-date-from" placeholder="Select start date" readonly>
											</div>
											<div class="acf-date-field">
												<label class="acf-date-label">To</label>
												<input type="text" class="acf-date-input acf-date-to" placeholder="Select end date" readonly>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php endif;?>
					</div>
				</div>
				<!-- Action Buttons -->
				<div class="acf-filter-actions">
					<button class="acf-action-btn acf-reset-btn">						<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
								<path d="M9.375 2.625L2.625 9.375" stroke="#001319" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M9.375 9.375L2.625 2.625" stroke="#001319" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg> RESET ALL FILTERS</button>
					<button class="acf-action-btn acf-confirm-btn">CONFIRM</button>
				</div>
			</div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    private function get_field_label($field_key, $post_type) {
        // Try to get field object
        $field_object = get_field_object($field_key);
        
        if ($field_object && isset($field_object['label'])) {
            return $field_object['label'];
        }
        
        // Fallback: get from any post of this type
        $posts = get_posts(array(
            'post_type' => $post_type,
            'posts_per_page' => 1,
            'post_status' => 'publish'
        ));
        
        if (!empty($posts)) {
            $field_object = get_field_object($field_key, $posts[0]->ID);
            if ($field_object && isset($field_object['label'])) {
                return $field_object['label'];
            }
        }
        
        // Final fallback: use field key
        return ucwords(str_replace(array('_', '-'), ' ', $field_key));
    }
    
    private function get_field_values($field_key, $post_type) {
        $values = array();
        
        // Get all published posts of this type
        $posts = get_posts(array(
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => $field_key,
                    'compare' => 'EXISTS'
                )
            )
        ));
        
        foreach ($posts as $post) {
            $field_value = get_field($field_key, $post->ID);
            
            if (!empty($field_value)) {
                if (is_array($field_value)) {
                    // Handle multiple values (checkboxes, multi-select)
                    foreach ($field_value as $val) {
                        if (is_object($val)) {
                            if (isset($val->name)) {
                                $values[] = $val->name; // Taxonomy term
                            } elseif (isset($val->post_title)) {
                                $values[] = $val->post_title; // Post object
                            } elseif (isset($val->label)) {
                                $values[] = $val->label; // Choice field
                            }
                        } elseif (is_string($val) && !empty(trim($val))) {
                            $values[] = trim($val);
                        }
                    }
                } elseif (is_object($field_value)) {
                    if (isset($field_value->name)) {
                        $values[] = $field_value->name; // Single taxonomy term
                    } elseif (isset($field_value->post_title)) {
                        $values[] = $field_value->post_title; // Single post object
                    } elseif (isset($field_value->label)) {
                        $values[] = $field_value->label; // Single choice
                    }
                } elseif (is_string($field_value) && !empty(trim($field_value))) {
                    $values[] = trim($field_value);
                }
            }
        }
        
        // Remove duplicates and empty values
        $values = array_unique(array_filter($values, function($value) {
            return !empty(trim($value));
        }));
        
        // Sort alphabetically
        sort($values);
        
        return $values;
    }
    
    public function ajax_filter_posts() {
    check_ajax_referer('acf_filter_nonce', 'nonce');

    $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'post';

    // Debug logging
    error_log('ACF Filter Debug - Filters received: ' . print_r($filters, true));

    // Build query args
    $query_args = array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'post_status' => 'publish'
    );

    $meta_queries = array('relation' => 'AND');
    $has_meta_filters = false;

    // Add ACF field filters
    foreach ($filters as $field_key => $values) {
        if (in_array($field_key, array('publish_date_from', 'publish_date_to'))) continue;

        if (!empty($values) && is_array($values)) {
            $has_meta_filters = true;
            
            // Handle different ACF field types
            $meta_queries[] = array(
                'relation' => 'OR',
                // For single values
                array(
                    'key' => $field_key,
                    'value' => $values,
                    'compare' => 'IN'
                ),
                // For serialized arrays (common with ACF)
                array(
                    'key' => $field_key,
                    'value' => '"' . implode('"|"', array_map('esc_sql', $values)) . '"',
                    'compare' => 'REGEXP'
                ),
                // For comma-separated values
                array(
                    'key' => $field_key,
                    'value' => implode('|', array_map('esc_sql', $values)),
                    'compare' => 'REGEXP'
                )
            );
        }
    }

    // Only add meta_query if we have filters
    if ($has_meta_filters) {
        $query_args['meta_query'] = $meta_queries;
    }

    // Add publish date filters with proper format conversion
    if (isset($filters['publish_date_from']) || isset($filters['publish_date_to'])) {
        $date_query = array();

        if (isset($filters['publish_date_from']) && !empty($filters['publish_date_from'])) {
            // Convert from m-d-y to Y-m-d format
            $from_date = $this->convert_date_format($filters['publish_date_from']);
            if ($from_date) {
                $date_query['after'] = $from_date;
                error_log('ACF Filter Debug - Date From converted: ' . $filters['publish_date_from'] . ' -> ' . $from_date);
            }
        }

        if (isset($filters['publish_date_to']) && !empty($filters['publish_date_to'])) {
            // Convert from m-d-y to Y-m-d format
            $to_date = $this->convert_date_format($filters['publish_date_to']);
            if ($to_date) {
                $date_query['before'] = $to_date;
                error_log('ACF Filter Debug - Date To converted: ' . $filters['publish_date_to'] . ' -> ' . $to_date);
            }
        }

        if (!empty($date_query)) {
            $date_query['inclusive'] = true; // Include the boundary dates
            $query_args['date_query'] = array($date_query);
        }
    }

    // Debug logging
    error_log('ACF Filter Debug - Query args: ' . print_r($query_args, true));

    // Execute query
    $query = new WP_Query($query_args);

    // Debug logging
    error_log('ACF Filter Debug - SQL Query: ' . $query->request);
    error_log('ACF Filter Debug - Found posts: ' . $query->found_posts);

    // Collect post IDs
    $post_ids = wp_list_pluck($query->posts, 'ID');

    wp_send_json_success(array(
        'post_ids' => $post_ids,
        'found_posts' => $query->found_posts,
        'debug_query' => $query->request // Remove this in production
    ));
}

/**
 * Convert date format from m-d-y to Y-m-d for WordPress queries
 */
private function convert_date_format($date_string) {
    if (empty($date_string)) {
        return false;
    }
    
    // Try to parse the date in m-d-y format
    $date = DateTime::createFromFormat('m-d-y', $date_string);
    
    // If that fails, try other common formats as fallback
    if (!$date) {
        $date = DateTime::createFromFormat('n-j-y', $date_string); // Single digit month/day
    }
    
    if (!$date) {
        $date = DateTime::createFromFormat('m/d/y', $date_string); // Forward slashes
    }
    
    if (!$date) {
        $date = DateTime::createFromFormat('n/j/y', $date_string); // Single digit with slashes
    }
    
    if (!$date) {
        error_log('ACF Filter Debug - Could not parse date: ' . $date_string);
        return false;
    }
    
    // Return in WordPress-compatible Y-m-d format
    return $date->format('Y-m-d');
}
    
    public function register_widget() {
        register_widget('ACF_Loop_Grid_Filter_Widget');
    }
}

// Widget Class
class ACF_Loop_Grid_Filter_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'acf_loop_grid_filter_widget',
            'ACF Loop Grid Filters',
            array('description' => 'Advanced ACF-based filters for Loop Grid posts')
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $filter_args = array(
            'post_type' => $instance['post_type'] ?? 'post',
            'fields' => $instance['acf_fields'] ?? '',
            'show_date_filter' => $instance['show_date_filter'] ?? 'true',
            'layout' => $instance['layout'] ?? 'horizontal',
            'filter_titles' => $instance['filter_titles'] ?? ''
        );
        
        $acf_filters = new ACF_Loop_Grid_Filters();
        echo $acf_filters->render_filters_shortcode($filter_args);
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Filter Posts';
        $post_type = !empty($instance['post_type']) ? $instance['post_type'] : 'post';
        $acf_fields = !empty($instance['acf_fields']) ? $instance['acf_fields'] : '';
        $filter_titles = !empty($instance['filter_titles']) ? $instance['filter_titles'] : '';
        $show_date_filter = !empty($instance['show_date_filter']) ? $instance['show_date_filter'] : 'true';
        $layout = !empty($instance['layout']) ? $instance['layout'] : 'horizontal';
        ?>
        
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('post_type'); ?>">Post Type:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>">
                <?php
                $post_types = get_post_types(array('public' => true), 'objects');
                foreach ($post_types as $pt) {
                    echo '<option value="' . esc_attr($pt->name) . '"' . selected($post_type, $pt->name, false) . '>' . esc_html($pt->label) . '</option>';
                }
                ?>
            </select>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('acf_fields'); ?>">ACF Fields (comma-separated):</label>
            <input class="widefat" id="<?php echo $this->get_field_id('acf_fields'); ?>" name="<?php echo $this->get_field_name('acf_fields'); ?>" type="text" value="<?php echo esc_attr($acf_fields); ?>">
            <small>Example: location, category, tags</small>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('filter_titles'); ?>">Filter Titles (comma-separated):</label>
            <input class="widefat" id="<?php echo $this->get_field_id('filter_titles'); ?>" name="<?php echo $this->get_field_name('filter_titles'); ?>" type="text" value="<?php echo esc_attr($filter_titles); ?>">
            <small>Custom titles for each filter. Leave empty to use ACF field labels.</small>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('layout'); ?>">Layout:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('layout'); ?>" name="<?php echo $this->get_field_name('layout'); ?>">
                <option value="horizontal" <?php selected($layout, 'horizontal'); ?>>Horizontal</option>
                <option value="vertical" <?php selected($layout, 'vertical'); ?>>Vertical</option>
                <option value="accordion" <?php selected($layout, 'accordion'); ?>>Accordion</option>
            </select>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_date_filter, 'true'); ?> id="<?php echo $this->get_field_id('show_date_filter'); ?>" name="<?php echo $this->get_field_name('show_date_filter'); ?>" value="true">
            <label for="<?php echo $this->get_field_id('show_date_filter'); ?>">Show Date Filter</label>
        </p>
        
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['post_type'] = (!empty($new_instance['post_type'])) ? sanitize_text_field($new_instance['post_type']) : 'post';
        $instance['acf_fields'] = (!empty($new_instance['acf_fields'])) ? sanitize_text_field($new_instance['acf_fields']) : '';
        $instance['filter_titles'] = (!empty($new_instance['filter_titles'])) ? sanitize_text_field($new_instance['filter_titles']) : '';
        $instance['show_date_filter'] = (!empty($new_instance['show_date_filter'])) ? 'true' : 'false';
        $instance['layout'] = (!empty($new_instance['layout'])) ? sanitize_text_field($new_instance['layout']) : 'horizontal';
        
        return $instance;
    }
}

// Initialize the system
new ACF_Loop_Grid_Filters();