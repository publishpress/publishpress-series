jQuery(document).ready(function ($) {

    function ppsSeriesPostDetailsEditorInit() {
        if (typeof ppsSeriesPostDetailsEditor === 'undefined') {
            return;
        }


        // Tab switching functionality
        $('.pps-series-post-details-editor-tabs a').on('click', function (e) {
            e.preventDefault();

            var tab = $(this).data('tab');

            $('.pps-series-post-details-editor-tabs a').removeClass('active');
            $(this).addClass('active');

            $('.pps-series-post-details-editor-table tbody tr').hide();
            $('.pps-series-post-details-editor-table tbody tr[data-tab="' + tab + '"]').show();
            

            checkConditionalFields();
        });

        // Conditional field visibility handler
        function checkConditionalFields() {
            $('.pps-series-post-details-editor-table tbody tr[data-depends-on]').each(function() {
                var $row = $(this);
                var dependsOn = $row.data('depends-on');
                var dependsValue = $row.data('depends-value');
                
                if (!dependsOn) {
                    return;
                }
                
                var $controlField = $('#' + dependsOn);
                if (!$controlField.length) {
                    return;
                }
                
                var currentValue = '';
                if ($controlField.is(':checkbox')) {
                    currentValue = $controlField.is(':checked') ? '1' : '0';
                } else {
                    currentValue = $controlField.val();
                }
                
                // Convert dependsValue to string for comparison
                dependsValue = String(dependsValue);
                
                // Only show if visible in current tab
                var currentTab = $('.pps-series-post-details-editor-tabs a.active').data('tab');
                var rowTab = $row.data('tab');
                
                if (currentValue === dependsValue && rowTab === currentTab) {
                    $row.show();
                } else if (rowTab !== currentTab) {
                    // Keep hidden if not in current tab
                    $row.hide();
                } else {
                    $row.hide();
                }
            });
        }

        // Watch for changes on fields that control conditional visibility
        $(document).on('change', '.publishpress-series-meta-box-editor :input', function() {
            checkConditionalFields();
        });

        // Initial check on page load
        checkConditionalFields();

        // Color picker initialization
        $('.pps-color-picker').wpColorPicker({
            change: function(event, ui) {
                debouncedUpdate();
            },
            clear: function() {
                updatePreview();
            }
        });

        // Disable color picker interactions for PRO-locked fields
        $('.pps-color-picker:disabled').each(function() {
            var $input = $(this);
            var $wrapper = $input.closest('.wp-picker-container');
            
            // Disable the color picker button
            $wrapper.find('.wp-color-result').css({
                'pointer-events': 'none',
                'opacity': '0.6',
                'cursor': 'not-allowed'
            });
            
            // Disable the clear button if it exists
            $wrapper.find('.wp-picker-clear').prop('disabled', true).css({
                'pointer-events': 'none',
                'opacity': '0.6'
            });
        });

        // Preview functionality
        function updatePreview() {
            var postId = ppsSeriesPostDetailsEditor.post_id;
            var formData = $('#post').serialize();

            $('.pps-series-post-details-preview').addClass('loading');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pps_update_series_post_details_preview',
                    post_id: postId,
                    settings: formData,
                    nonce: ppsSeriesPostDetailsEditor.nonce
                },
                success: function (response) {
                    if (response && response.success && response.data && response.data.preview) {
                        $('#pps-series-post-details-preview-content').html(response.data.preview);
                    }
                    $('.pps-series-post-details-preview').removeClass('loading');
                },
                error: function (xhr, status, error) {
                    $('.pps-series-post-details-preview').removeClass('loading');
                    $('#pps-series-post-details-preview-content').html('<div class="notice notice-error"><p>Failed to update preview. Please try again.</p></div>');
                }
            });
        }

        // Update preview when form fields change (with debounce)
        var previewTimeout;
        function debouncedUpdate() {
            clearTimeout(previewTimeout);
            previewTimeout = setTimeout(updatePreview, 300);
        }

        // Delegate to capture dynamically added inputs
        $(document)
            .on('change input keyup', '.pps-series-post-details-editor-fields input, .pps-series-post-details-editor-fields select, .pps-series-post-details-editor-fields textarea', debouncedUpdate)
            .on('change input keyup', '.pps-series-post-details-editor-table input, .pps-series-post-details-editor-table select, .pps-series-post-details-editor-table textarea', debouncedUpdate)
            .on('change input keyup', '.publishpress-series-post-details-editor :input', debouncedUpdate);

       

        // Form validation
        $('form#post').on('submit', function (e) {
            var title = $('#title').val();
            if (!title || title.trim() === '') {
                alert('Please enter a title for the Series Post Details.');
                e.preventDefault();
                return false;
            }
        });
    }

    // Initialize the editor
    ppsSeriesPostDetailsEditorInit();

    // Trigger an initial preview
    jQuery(function(){
        if (typeof ppsSeriesPostDetailsEditor !== 'undefined') {
            setTimeout(function(){
                jQuery(document).trigger('series-post-details-editor-reinit');
            }, 50);
        }
    });

    // Re-initialize when needed
    $(document).on('series-post-details-editor-reinit', ppsSeriesPostDetailsEditorInit);
});
