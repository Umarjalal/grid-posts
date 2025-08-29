jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize admin functionality
    initWidgetSettings();
    
    function initWidgetSettings() {
        // Color picker initialization
        $(document).on('widget-added widget-updated', function() {
            initColorPickers();
            initTemplatePreview();
        });
        
        // Initialize on page load
        initColorPickers();
        initTemplatePreview();
        
        // Live preview updates
        $(document).on('change', '.pgs-widget-form input, .pgs-widget-form select', function() {
            updateLivePreview($(this));
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
            });
        });
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
        switch (template) {
            case 'card':
                previewHtml = '<div style="border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin: 10px 0;">' +
                    '<div style="background: #f0f0f0; height: 60px; margin-bottom: 8px; border-radius: 2px;"></div>' +
                    '<div style="font-weight: 600; margin-bottom: 4px;">Post Title</div>' +
                    '<div style="font-size: 12px; color: #666; margin-bottom: 6px;">Post excerpt...</div>' +
                    '<div style="font-size: 10px; color: #999; display: flex; justify-content: space-between;">' +
                    '<span>Author</span><span>Date</span></div>' +
                    '</div>';
                break;
            case 'list':
                previewHtml = '<div style="border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin: 10px 0; display: flex; gap: 10px;">' +
                    '<div style="background: #f0f0f0; width: 60px; height: 45px; border-radius: 2px; flex-shrink: 0;"></div>' +
                    '<div style="flex: 1;">' +
                    '<div style="font-weight: 600; margin-bottom: 4px; font-size: 13px;">Post Title</div>' +
                    '<div style="font-size: 10px; color: #666; margin-bottom: 4px;">Post excerpt...</div>' +
                    '<div style="font-size: 9px; color: #999;">Author • Date</div>' +
                    '</div></div>';
                break;
            case 'minimal':
                previewHtml = '<div style="border-bottom: 1px solid #eee; padding: 8px 0; margin: 10px 0;">' +
                    '<div style="font-weight: 600; margin-bottom: 4px; font-size: 13px;">Post Title</div>' +
                    '<div style="font-size: 9px; color: #999;">Author • Date</div>' +
                    '</div>';
                break;
        }
        
        $preview.html('<div style="font-size: 11px; color: #666; margin-bottom: 5px;">Preview:</div>' + previewHtml);
    }
    
    function updateLivePreview($input) {
        // This function could be expanded to show real-time preview changes
        var inputId = $input.attr('id');
        var value = $input.val();
        
        // Example: Update pagination color preview
        if (inputId && inputId.indexOf('pagination') !== -1) {
            // Update color preview elements
            console.log('Updating pagination preview for:', inputId, value);
        }
    }
    
    // Widget save enhancement
    $(document).on('click', '.widget-control-save', function() {
        var $widget = $(this).closest('.widget');
        $widget.find('.pgs-widget-form').addClass('pgs-saving');
        
        setTimeout(function() {
            $widget.find('.pgs-widget-form').removeClass('pgs-saving');
        }, 1000);
    });
    
    // Help tooltips
    initHelpTooltips();
    
    function initHelpTooltips() {
        // Add help icons to complex settings
        $('.pgs-widget-form label').each(function() {
            var $label = $(this);
            var text = $label.text();
            
            if (text.indexOf('Target Posts Grid Widget ID') !== -1) {
                $label.append(' <span class="pgs-help-icon" title="Leave empty to target all Posts Grid widgets on the same page">?</span>');
            }
        });
        
        // Style help icons
        $('<style>.pgs-help-icon { display: inline-block; width: 14px; height: 14px; background: #666; color: white; border-radius: 50%; text-align: center; font-size: 10px; line-height: 14px; cursor: help; margin-left: 4px; }</style>').appendTo('head');
    }
});