/**
 * Series Post Navigation Editor JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        var $seriesSelect = $('#pps-series-post-navigation-preview-select');
        var $previewContent = $('#pps-series-post-navigation-preview-content');
        var i18n = ppsSeriesPostNavigationEditor.i18n || {};
        var loadingText = i18n.loading_preview || 'Loading preview...';
        var errorText = i18n.error_loading_preview || 'Error updating preview.';

        function setPreviewMessage(message) {
            if (!$previewContent.length) {
                return;
            }

            var text = message || '';
            $previewContent.empty().append($('<p></p>').text(text));
        }

        // Shared helper for conditional row visibility
        function setRowVisibility($row, shouldShow, activeTab) {
            if (shouldShow && $row.data('tab') === activeTab) {
                $row.show();
            } else {
                $row.hide();
            }
        }

        // Tab switching
        $('.pps-series-post-navigation-editor-tabs a').on('click', function(e) {
            e.preventDefault();
            var tab = $(this).data('tab');
            
            // Update active tab
            $('.pps-series-post-navigation-editor-tabs a').removeClass('active');
            $(this).addClass('active');
            
            // Show/hide fields
            $('.pps-series-post-navigation-editor-table tr').each(function() {
                var fieldTab = $(this).data('tab');
                if (fieldTab === tab) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            // Re-apply all field visibility rules when returning to tab
            toggleLabelFields();
            toggleFeaturedImageFields();
            toggleArrowFields();
        });

        // Toggle label field visibility based on dropdown selections
        function toggleLabelFields() {
            var activeTab = $('.pps-series-post-navigation-editor-tabs a.active').data('tab') || 'general';

            // Previous label - only show when 'custom' is selected
            var prevType = $('#previous_link_type').val();
            var $prevLabelRow = $('#previous_label').closest('tr');
            setRowVisibility($prevLabelRow, prevType === 'custom', activeTab);

            // Next label - only show when 'custom' is selected
            var nextType = $('#next_link_type').val();
            var $nextLabelRow = $('#next_label').closest('tr');
            setRowVisibility($nextLabelRow, nextType === 'custom', activeTab);

            // First label - only show when 'custom' is selected
            var firstType = $('#first_link_type').val();
            var $firstLabelRow = $('#first_label').closest('tr');
            setRowVisibility($firstLabelRow, firstType === 'custom', activeTab);
        }

        // Toggle featured image related fields
        function toggleFeaturedImageFields() {
            var activeTab = $('.pps-series-post-navigation-editor-tabs a.active').data('tab') || 'general';

            // Previous featured image fields
            var prevShowImage = $('#previous_show_featured_image').is(':checked');
            setRowVisibility($('#previous_image_position').closest('tr'), prevShowImage, activeTab);
            setRowVisibility($('#previous_image_width').closest('tr'), prevShowImage, activeTab);
            setRowVisibility($('#previous_image_height').closest('tr'), prevShowImage, activeTab);

            // Next featured image fields
            var nextShowImage = $('#next_show_featured_image').is(':checked');
            setRowVisibility($('#next_image_position').closest('tr'), nextShowImage, activeTab);
            setRowVisibility($('#next_image_width').closest('tr'), nextShowImage, activeTab);
            setRowVisibility($('#next_image_height').closest('tr'), nextShowImage, activeTab);

            // First featured image fields
            var firstShowImage = $('#first_show_featured_image').is(':checked');
            setRowVisibility($('#first_image_position').closest('tr'), firstShowImage, activeTab);
            setRowVisibility($('#first_image_width').closest('tr'), firstShowImage, activeTab);
            setRowVisibility($('#first_image_height').closest('tr'), firstShowImage, activeTab);
        }

        // Toggle arrow related fields
        function toggleArrowFields() {
            var activeTab = $('.pps-series-post-navigation-editor-tabs a.active').data('tab') || 'general';

            // Previous arrow fields
            var prevShowArrow = $('#previous_show_arrow').is(':checked');
            var prevArrowType = $('#previous_arrow_type').val();
            setRowVisibility($('#previous_arrow_type').closest('tr'), prevShowArrow, activeTab);
            setRowVisibility($('#previous_arrow_position').closest('tr'), prevShowArrow, activeTab);
            setRowVisibility($('#previous_arrow_size').closest('tr'), prevShowArrow, activeTab);
            setRowVisibility(
                $('#previous_custom_arrow_image').closest('tr'),
                prevShowArrow && prevArrowType === 'custom',
                activeTab
            );

            // Next arrow fields
            var nextShowArrow = $('#next_show_arrow').is(':checked');
            var nextArrowType = $('#next_arrow_type').val();
            setRowVisibility($('#next_arrow_type').closest('tr'), nextShowArrow, activeTab);
            setRowVisibility($('#next_arrow_position').closest('tr'), nextShowArrow, activeTab);
            setRowVisibility($('#next_arrow_size').closest('tr'), nextShowArrow, activeTab);
            setRowVisibility(
                $('#next_custom_arrow_image').closest('tr'),
                nextShowArrow && nextArrowType === 'custom',
                activeTab
            );
        }

        // Initialize field visibility on page load
        toggleLabelFields();
        toggleFeaturedImageFields();
        toggleArrowFields();

        // Update label field visibility when dropdowns change
        $('#previous_link_type, #next_link_type, #first_link_type').on('change', function() {
            toggleLabelFields();
        });

        // Update featured image field visibility when checkboxes change
        $('#previous_show_featured_image, #next_show_featured_image, #first_show_featured_image').on('change', function() {
            toggleFeaturedImageFields();
        });

        // Update arrow field visibility when checkboxes change
        $('#previous_show_arrow, #next_show_arrow, #previous_arrow_type, #next_arrow_type').on('change', function() {
            toggleArrowFields();
        });

        // Color picker
        $('.pps-color-picker').wpColorPicker({
            change: function() {
                updatePreview();
            },
            clear: function() {
                updatePreview();
            }
        });

        // Form field changes - update preview
        $('.pps-series-post-navigation-editor-table input, .pps-series-post-navigation-editor-table select, .pps-series-post-navigation-editor-table textarea').on('change', function() {
            updatePreview();
        });

        if ($seriesSelect.length) {
            $seriesSelect.on('change', function(e) {
                e.preventDefault();
                updatePreview();
            });
        }

        // Update preview via AJAX
        function updatePreview() {
            var postId = ppsSeriesPostNavigationEditor.post_id;
            var formData = $('.pps-series-post-navigation-editor-table').find('input, select, textarea').serialize();
            var seriesId = $seriesSelect.length ? $seriesSelect.val() : 0;

            if ($previewContent.length && seriesId) {
                setPreviewMessage(loadingText);
            }

            $.ajax({
                url: ppsSeriesPostNavigationEditor.ajax_url,
                type: 'POST',
                data: {
                    action: 'pps_update_series_post_navigation_preview',
                    post_id: postId,
                    settings: formData,
                    nonce: ppsSeriesPostNavigationEditor.nonce,
                    series_id: seriesId
                },
                success: function(response) {
                    if (response.success) {
                        $previewContent.html(response.data.preview);
                    } else {
                        var message = response && response.data && response.data.message ? response.data.message : errorText;
                        setPreviewMessage(message);
                    }
                },
                error: function() {
                    setPreviewMessage(errorText);
                    console.error('Error updating preview');
                }
            });
        }

        // Media uploader for custom arrow images
        $(document).on('click', '.pps-media-upload-button', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var fieldId = button.data('field-id');
            var $wrapper = button.closest('.pps-media-field-wrapper');
            var $input = $wrapper.find('#' + fieldId);
            var $preview = $wrapper.find('.pps-media-preview');
            var $removeButton = $wrapper.find('.pps-media-remove-button');
            
            // Create a fresh media frame for this field
            var mediaUploader = wp.media({
                title: 'Select Arrow Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            // When an image is selected, run a callback
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                
                // Set the attachment ID
                $input.val(attachment.id);
                
                // Update preview
                var imageUrl = attachment.sizes && attachment.sizes.thumbnail 
                    ? attachment.sizes.thumbnail.url 
                    : attachment.url;
                    
                $preview.html('<img src="' + imageUrl + '" style="max-width: 150px; max-height: 150px; display: block; margin-bottom: 10px;" />');
                
                // Update button text
                button.text('Change Image');
                
                // Show remove button if not already visible
                if ($removeButton.length === 0) {
                    button.after(' <button type="button" class="button pps-media-remove-button" data-field-id="' + fieldId + '">Remove Image</button>');
                } else {
                    $removeButton.show();
                }
                
                // Trigger preview update
                updatePreview();
            });
            
            // Open the media frame
            mediaUploader.open();
        });
        
        // Remove media
        $(document).on('click', '.pps-media-remove-button', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var fieldId = button.data('field-id');
            var $wrapper = button.closest('.pps-media-field-wrapper');
            var $input = $wrapper.find('#' + fieldId);
            var $preview = $wrapper.find('.pps-media-preview');
            var $uploadButton = $wrapper.find('.pps-media-upload-button');
            
            // Clear the input
            $input.val('0');
            
            // Clear preview
            $preview.html('');
            
            // Update button text
            $uploadButton.text('Select Image');
            
            // Hide remove button
            button.hide();
            
            // Trigger preview update
            updatePreview();
        });
    });
})(jQuery);
