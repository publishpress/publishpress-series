jQuery(document).ready(function ($) {
    
    function ppsPostListBoxEditorInit() {
        if (typeof postListBoxEditor === 'undefined') {
            return;
        }

        // Tab switching functionality
        $('.pps-post-list-box-editor-tabs a').on('click', function (e) {
            e.preventDefault();

            var tab = $(this).data('tab');

            $('.pps-post-list-box-editor-tabs a').removeClass('active');
            $(this).addClass('active');

            $('.pps-boxes-editor-tab-content').hide();
            $('.pps-' + tab + '-tab').show();

            // Re-check dependencies after tab switch to ensure proper visibility
            setTimeout(function() {
                if (typeof handleFieldDependencies !== 'undefined') {
                    // Trigger dependency check
                    $('.pps-post-list-box-editor-fields input, .pps-post-list-box-editor-fields select').trigger('change');
                }
            }, 50);
        });

        // Color picker initialization
        $('.pps-editor-color-picker').wpColorPicker({
            change: function(event, ui) {
                // Handle color change
                debouncedUpdate();
            },
            clear: function() {
                // Handle clear button click
                updatePreview();
            }
        });

        // Preview functionality
        function updatePreview() {
            var postId = postListBoxEditor.post_id;
            
            // Collect all current settings from the entire post form for robustness
            // (some fields may live outside the local container depending on filters)
            var formData = $('#post').serialize();
            
            // Get the currently selected series ID from the preview selector
            var seriesId = $('#pps-preview-series-select').val() || 0;
            
            // Show loading state
            $('.pps-post-list-box-preview').addClass('loading');
            try { console.debug('[PPS] updatePreview triggered with series:', seriesId); } catch(e) {}
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pps_update_post_list_box_preview',
                    post_id: postId,
                    settings: formData,
                    series_id: seriesId,
                    nonce: postListBoxEditor.nonce
                },
                success: function (response) {
                    if (response && response.success && response.data && response.data.preview) {
                        // Replace only the preview content, not the entire section
                        $('#pps-preview-content').html(response.data.preview);
                    }
                    $('.pps-post-list-box-preview').removeClass('loading');
                },
                error: function () {
                    $('.pps-post-list-box-preview').removeClass('loading');
                   $('#pps-preview-content').html('<div class="notice notice-error"><p>Failed to update preview. Please try again.</p></div>');
                }
            });
        }

        // Update preview when form fields change (with debounce)
        var previewTimeout;
        function debouncedUpdate() {
            clearTimeout(previewTimeout);
            previewTimeout = setTimeout(updatePreview, 300);
            try { console.debug('[PPS] debouncedUpdate scheduled'); } catch(e) {}
        }

        // Delegate to capture dynamically added inputs as well, but exclude the series selector
        $(document)
            .on('change input keyup click', '.pps-post-list-box-editor-fields input, .pps-post-list-box-editor-fields select, .pps-post-list-box-editor-fields textarea', debouncedUpdate)
            .on('change input keyup click', '.pps-post-list-boxes-editor-table input, .pps-post-list-boxes-editor-table select, .pps-post-list-boxes-editor-table textarea', debouncedUpdate)
            // Extra-safe: watch any input/select/textarea within the editor wrapper, but exclude series selector
            .on('change input keyup click', '.publishpress-post-list-box-editor :input:not(#pps-preview-series-select)', debouncedUpdate);


        // Form validation
        $('form#post').on('submit', function (e) {
            var title = $('#title').val();
            if (!title || title.trim() === '') {
                alert('Please enter a title for the Post List Box.');
                e.preventDefault();
                return false;
            }
        });
    }

    // Initialize the editor
    ppsPostListBoxEditorInit();
    // Trigger an initial preview so UI reflects defaults immediately
    jQuery(function(){
        if (typeof postListBoxEditor !== 'undefined') {
            // Slight delay to ensure DOM is ready
            setTimeout(function(){
                jQuery(document).trigger('post-list-box-editor-reinit');
                // Also invoke update directly if available
                try { 
                    // Fire a direct update to guarantee first render
                    jQuery(document).trigger('pps-post-list-box-update-preview');
                } catch(e) {}
            }, 50);
        }
    });

    // Re-initialize when needed (e.g., after AJAX calls)
    $(document).on('post-list-box-editor-reinit', ppsPostListBoxEditorInit);
    // Explicit event hook to trigger preview updates from anywhere if needed
    $(document).on('pps-post-list-box-update-preview', function(){
        try {
            if (typeof postListBoxEditor !== 'undefined') {
                // call updater directly for reliability
                if (typeof updatePreview === 'function') {
                    updatePreview();
                } else {
                    // fallback to debounced path
                    var evt = jQuery.Event('input');
                    jQuery('.publishpress-post-list-box-editor :input:first').trigger(evt);
                }
            }
        } catch (e) {}
    });

    // Handle responsive behavior
    function handleResponsiveLayout() {
        var windowWidth = $(window).width();
        
        if (windowWidth <= 768) {
            $('.publishpress-post-list-box-editor').addClass('mobile-view');
        } else {
            $('.publishpress-post-list-box-editor').removeClass('mobile-view');
        }
    }

    $(window).on('resize', handleResponsiveLayout);
    handleResponsiveLayout();

    function handleFieldDependencies() {
        // Handle field dependencies based on checkbox/select values
        function toggleDependentFields() {
            // Handle nested dependencies
            for (var i = 0; i < 3; i++) {
                $('tr[data-depends-on]').each(function() {
                    var $row = $(this);
                    var dependsOn = $row.data('depends-on');
                    var dependsValue = $row.data('depends-value');
                    var rowTab = $row.data('tab');
                    var $parentField = $('[name="' + dependsOn + '"]');

                    if ($parentField.length) {
                        var parentValue;
                        if ($parentField.is(':checkbox')) {
                            parentValue = $parentField.is(':checked') ? '1' : '0';
                        } else {
                            parentValue = $parentField.val();
                        }

                        // Convert to string for comparison
                        parentValue = String(parentValue);
                        dependsValue = String(dependsValue);

                        // Check if dependency is met
                        var shouldShow = (parentValue === dependsValue);

                        // For nested dependencies, also check if parent field is visible
                        var $parentRow = $parentField.closest('tr');
                        var parentVisible = true;
                        if ($parentRow.length && $parentRow.attr('data-depends-on')) {
                            parentVisible = $parentRow.hasClass('dependent-field-visible');
                        }

                        if (shouldShow && parentVisible) {
                            $row.addClass('dependent-field-visible');
                            // Check if this row's tab is currently active
                            var activeTab = $('.pps-post-list-box-editor-tabs a.active').data('tab');
                            if (rowTab === activeTab) {
                                $row.show();
                            }
                        } else {
                            $row.removeClass('dependent-field-visible');
                            // Always hide if dependency not met, regardless of tab
                            $row.hide();
                        }
                    }
                });
            }
        }

        // Initial toggle when page loads
        $(document).ready(function() {
            setTimeout(function() {
                toggleDependentFields();
            }, 200);
        });

        // Listen for changes on all form fields
        $('.pps-post-list-box-editor-fields').on('change', 'input, select', function() {
            toggleDependentFields();

            if (typeof debouncedUpdate === 'function') {
                debouncedUpdate();
            }
        });

        // Handle tab switching - re-evaluate dependencies for the new active tab
        $(document).on('click', '.pps-post-list-box-editor-tabs a', function() {
            setTimeout(function() {
                toggleDependentFields();
            }, 100);
        });
    }

    handleFieldDependencies();

});