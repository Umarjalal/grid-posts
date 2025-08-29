jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize admin functionality
    initWidgetSettings();
    initTabSwitching();
    
    function initWidgetSettings() {
        // Color picker initialization
        $(document).on('widget-added widget-updated', function() {
            initColorPickers();
            initTemplatePreview();
            initTabSwitching();
        });
        
        // Initialize on page load
        initColorPickers();
        initTemplatePreview();
        
        // Live preview updates
        $(document).on('change', '.pgs-widget-form input, .pgs-widget-form select', function() {
            updateLivePreview($(this));
        });
    }
    
    function initTabSwitching() {
        $(document).on('click', '.pgs-tab-btn', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var $widget = $btn.closest('.pgs-widget-form');
            var targetTab = $btn.data('tab');
            
            // Update active tab button
            $widget.find('.pgs-tab-btn').removeClass('pgs-tab-active');
            $btn.addClass('pgs-tab-active');
            
            // Update active tab content
            $widget.find('.pgs-tab-content').removeClass('pgs-tab-active');
            $widget.find('.pgs-tab-content[data-tab="' + targetTab + '"]').addClass('pgs-tab-active');
        });
    }
    
    function initColorPickers() {
        $('.pgs-widget-form input[type="color"]').each(function() {
            var $input = $(this);
            
            // Add color preview if not exists
            if ($input.siblings('.pgs-color-preview').length === 0) {
                var currentColor = $input.val();
                $input.after('<div class="pgs-color-preview" style="background-color: ' + currentColor + ';"></div>');
            }
            
            // Update preview on change
            $input.on('change', function() {
                var color = $(this).val();
                $(this).siblings('.pgs-color-preview').css('background-color', color);
                
                // Real-time style update
                updateColorPreview($(this));
            });
        });
    }
    
    function updateColorPreview($colorInput) {
        var inputId = $colorInput.attr('id');
        var color = $colorInput.val();
        var $widget = $colorInput.closest('.widget');
        
        // Create or update preview styles
        var styleId = 'pgs-preview-' + inputId;
        var $existingStyle = $('#' + styleId);
        
        if ($existingStyle.length === 0) {
            $('head').append('<style id="' + styleId + '"></style>');
            $existingStyle = $('#' + styleId);
        }
        
        // Generate preview CSS based on input type
        var previewCSS = '';
        if (inputId.indexOf('pagination_bg') !== -1) {
            previewCSS = '.pgs-pagination-btn { background-color: ' + color + ' !important; }';
        } else if (inputId.indexOf('pagination_active') !== -1) {
            previewCSS = '.pgs-pagination-current { background-color: ' + color + ' !important; }';
        } else if (inputId.indexOf('search_bg') !== -1) {
            previewCSS = '.pgs-search-input { background-color: ' + color + ' !important; }';
        }
        
        $existingStyle.html(previewCSS);
    }
    
    function initTemplatePreview() {
        $('.pgs-widget-form select[id*="template"]').each(function() {
            var $select = $(this);
            var $widget = $select.closest('.widget');
            
            // Add template preview
            if ($widget.find('.pgs-template-preview').length === 0) {
                $select.closest('p').after('<div class="pgs-template-preview"></div>');
            }
            
            updateTemplatePreview($select);
            
            $select.on('change', function() {
                updateTemplatePreview($(this));
            });
        });
    }
    
    function updateTemplatePreview($select) {
        var template = $select.val();
        var $preview = $select.closest('.pgs-widget-form').find('.pgs-template-preview');
        
        var previewHtml = '';
        
        // Check if it's an Elementor template (numeric ID)
        if (!isNaN(template) && parseInt(template) > 0) {
            previewHtml = '<div style="border: 2px solid #9b59b6; border-radius: 4px; padding: 15px; margin: 10px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center;">' +
                '<div style="font-weight: 600; margin-bottom: 8px;">🎨 Elementor Template</div>' +
                '<div style="font-size: 12px; opacity: 0.9;">Custom Elementor design will be used</div>' +
                '<div style="font-size: 10px; margin-top: 5px; opacity: 0.7;">Template ID: ' + template + '</div>' +
                '</div>';
        } else {
            switch (template) {
                case 'card':
                    previewHtml = '<div style="border: 1px solid #ddd; border-radius: 8px; padding: 12px; margin: 10px 0; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">' +
                        '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 80px; margin-bottom: 10px; border-radius: 4px;"></div>' +
                        '<div style="font-weight: 600; margin-bottom: 6px; color: #1a202c;">Post Title</div>' +
                        '<div style="font-size: 12px; color: #4a5568; margin-bottom: 8px; line-height: 1.4;">Post excerpt content goes here...</div>' +
                        '<div style="font-size: 10px; color: #718096; display: flex; justify-content: space-between;">' +
                        '<span>👤 Author</span><span>📅 Date</span></div>' +
                        '</div>';
                    break;
                case 'list':
                    previewHtml = '<div style="border: 1px solid #ddd; border-radius: 8px; padding: 12px; margin: 10px 0; display: flex; gap: 12px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">' +
                        '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 80px; height: 60px; border-radius: 4px; flex-shrink: 0;"></div>' +
                        '<div style="flex: 1;">' +
                        '<div style="font-weight: 600; margin-bottom: 4px; font-size: 13px; color: #1a202c;">Post Title</div>' +
                        '<div style="font-size: 11px; color: #4a5568; margin-bottom: 6px; line-height: 1.3;">Post excerpt...</div>' +
                        '<div style="font-size: 9px; color: #718096;">👤 Author • 📅 Date</div>' +
                        '</div></div>';
                    break;
                case 'minimal':
                    previewHtml = '<div style="border-bottom: 1px solid #e2e8f0; padding: 10px 0; margin: 10px 0;">' +
                        '<div style="font-weight: 600; margin-bottom: 4px; font-size: 13px; color: #1a202c;">📝 Post Title</div>' +
                        '<div style="font-size: 9px; color: #718096;">👤 Author • 📅 Date</div>' +
                        '</div>';
                    break;
            }
        }
        
        $preview.html('<div style="font-size: 11px; color: #666; margin-bottom: 8px; font-weight: 500;">📋 Template Preview:</div>' + previewHtml);
    }
    
    function updateLivePreview($input) {
        var inputId = $input.attr('id');
        var value = $input.val();
        
        // Enhanced live preview for different input types
        if (inputId && inputId.indexOf('pagination') !== -1) {
            updateColorPreview($input);
        } else if (inputId && inputId.indexOf('search') !== -1) {
            updateColorPreview($input);
        }
        
        // Show visual feedback for changes
        $input.addClass('pgs-input-changed');
        setTimeout(function() {
            $input.removeClass('pgs-input-changed');
        }, 1000);
    }
    
    // Widget save enhancement with professional feedback
    $(document).on('click', '.widget-control-save', function() {
        var $widget = $(this).closest('.widget');
        var $form = $widget.find('.pgs-widget-form');
        
        $form.addClass('pgs-saving');
        
        // Add success animation
        setTimeout(function() {
            $form.removeClass('pgs-saving').addClass('pgs-saved');
            setTimeout(function() {
                $form.removeClass('pgs-saved');
            }, 2000);
        }, 1000);
    });
    
    // Enhanced help tooltips
    initAdvancedTooltips();
    
    function initAdvancedTooltips() {
        // Add help icons to complex settings
        $('.pgs-widget-form label').each(function() {
            var $label = $(this);
            var text = $label.text();
            
            var tooltips = {
                'Target Posts Grid Widget ID': 'Leave empty to target all Posts Grid widgets on the same page. Use specific widget ID for targeted filtering.',
                'Template:': 'Choose from built-in templates or select your custom Elementor templates for advanced designs.',
                'Card Shadow:': 'CSS box-shadow property. Example: 0 4px 20px rgba(0, 0, 0, 0.15)',
                'Hover Transform:': 'CSS transform effect applied on hover. Creates engaging micro-interactions.',
                'ACF Field': 'Enter the exact ACF field name as defined in your custom fields setup.'
            };
            
            Object.keys(tooltips).forEach(function(key) {
                if (text.indexOf(key) !== -1) {
                    $label.append(' <span class="pgs-help-icon" title="' + tooltips[key] + '">?</span>');
                }
            });
        });
        
        // Enhanced tooltip styling
        $('<style>' +
            '.pgs-help-icon { ' +
                'display: inline-block; width: 16px; height: 16px; ' +
                'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); ' +
                'color: white; border-radius: 50%; text-align: center; ' +
                'font-size: 11px; line-height: 16px; cursor: help; ' +
                'margin-left: 6px; transition: all 0.3s ease; ' +
                'box-shadow: 0 2px 4px rgba(0,0,0,0.1); ' +
            '} ' +
            '.pgs-help-icon:hover { ' +
                'transform: scale(1.2); ' +
                'box-shadow: 0 4px 8px rgba(0,0,0,0.2); ' +
            '}' +
            '.pgs-input-changed { ' +
                'border-color: #00d084 !important; ' +
                'box-shadow: 0 0 0 2px rgba(0, 208, 132, 0.2) !important; ' +
            '}' +
            '.pgs-widget-form.pgs-saved::after { ' +
                'content: "✓ Saved Successfully"; ' +
                'position: absolute; top: 10px; right: 10px; ' +
                'background: #00d084; color: white; ' +
                'padding: 5px 10px; border-radius: 4px; ' +
                'font-size: 11px; z-index: 1000; ' +
                'animation: slideInRight 0.3s ease; ' +
            '}' +
            '@keyframes slideInRight { ' +
                'from { transform: translateX(100%); opacity: 0; } ' +
                'to { transform: translateX(0); opacity: 1; } ' +
            '}' +
        '</style>').appendTo('head');
    }
    
    // Advanced form validation
    $(document).on('blur', '.pgs-widget-form input[type="number"]', function() {
        var $input = $(this);
        var min = parseInt($input.attr('min')) || 0;
        var max = parseInt($input.attr('max')) || 999;
        var value = parseInt($input.val()) || 0;
        
        if (value < min) {
            $input.val(min).addClass('pgs-input-error');
            setTimeout(function() { $input.removeClass('pgs-input-error'); }, 2000);
        } else if (value > max) {
            $input.val(max).addClass('pgs-input-error');
            setTimeout(function() { $input.removeClass('pgs-input-error'); }, 2000);
        }
    });
    
    // Add error styling
    $('<style>' +
        '.pgs-input-error { ' +
            'border-color: #dc3545 !important; ' +
            'box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2) !important; ' +
            'animation: shake 0.5s ease; ' +
        '}' +
        '@keyframes shake { ' +
            '0%, 100% { transform: translateX(0); } ' +
            '25% { transform: translateX(-5px); } ' +
            '75% { transform: translateX(5px); } ' +
        '}' +
    '</style>').appendTo('head');
});