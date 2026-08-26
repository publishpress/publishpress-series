(function (wp, data) {
    'use strict';

    if (!wp || !wp.blocks || !wp.element || !wp.components) {
        return;
    }

    var blockEditor = wp.blockEditor || wp.editor || {};
    var createElement = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var registerBlockType = wp.blocks.registerBlockType;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var Placeholder = wp.components.Placeholder;
    var SelectControl = wp.components.SelectControl;
    var ServerSideRender = wp.serverSideRender;
    var __ = wp.i18n ? wp.i18n.__ : function (text) { return text; };

    var seriesOptions = data.series || [{
        label: __('Current series', 'organize-series'),
        value: '0'
    }];

    var definitions = [
        {
            name: 'publishpress-series/post-list-box',
            key: 'postListBox',
            title: __('Post List Boxes', 'organize-series'),
            description: __('Display a styled list of posts from a Series.', 'organize-series'),
            icon: 'list-view'
        },
        {
            name: 'publishpress-series/post-details',
            key: 'postDetails',
            title: __('Post Details', 'organize-series'),
            description: __('Display the current post details from a Series.', 'organize-series'),
            icon: 'editor-help'
        },
        {
            name: 'publishpress-series/post-navigation',
            key: 'postNavigation',
            title: __('Post Navigation', 'organize-series'),
            description: __('Display navigation links for posts in a Series.', 'organize-series'),
            icon: 'menu'
        }
    ];

    function getOptions(definition) {
        return (data.layouts && data.layouts[definition.key]) || [{
            label: __('Default layout', 'organize-series'),
            value: '0'
        }];
    }

    function editBlock(definition, props) {
        var attributes = props.attributes || {};
        var layoutId = parseInt(attributes.layoutId || 0, 10) || 0;
        var seriesId = parseInt(attributes.seriesId || 0, 10) || 0;
        var blockProps = {
            className: 'wp-block-publishpress-series-' + definition.key.replace(/([A-Z])/g, '-$1').toLowerCase()
        };
        var preview;

        if (ServerSideRender) {
            preview = createElement(ServerSideRender, {
                block: definition.name,
                attributes: attributes
            });
        } else {
            preview = createElement(Placeholder, {
                icon: definition.icon,
                label: definition.title
            }, definition.description);
        }

        return createElement(
            Fragment,
            null,
            InspectorControls ? createElement(
                InspectorControls,
                null,
                createElement(
                    PanelBody,
                    {
                        title: __('Block settings', 'organize-series'),
                        initialOpen: true
                    },
                    createElement(SelectControl, {
                        label: __('Layout', 'organize-series'),
                        value: String(layoutId),
                        options: getOptions(definition),
                        onChange: function (value) {
                            props.setAttributes({ layoutId: parseInt(value, 10) || 0 });
                        }
                    }),
                    createElement(SelectControl, {
                        label: __('Series', 'organize-series'),
                        value: String(seriesId),
                        options: seriesOptions,
                        onChange: function (value) {
                            props.setAttributes({ seriesId: parseInt(value, 10) || 0 });
                        }
                    })
                )
            ) : null,
            createElement(
                'div',
                blockProps,
                createElement('div', { className: 'pps-series-block-editor-label' }, definition.title),
                preview
            )
        );
    }

    definitions.forEach(function (definition) {
        registerBlockType(definition.name, {
            title: definition.title,
            description: definition.description,
            category: 'publishpress-series',
            icon: definition.icon,
            attributes: {
                layoutId: {
                    type: 'number',
                    default: 0
                },
                seriesId: {
                    type: 'number',
                    default: 0
                }
            },
            edit: function (props) {
                return editBlock(definition, props);
            },
            save: function () {
                return null;
            }
        });
    });
}(window.wp, window.publishPressSeriesBlocks || {}));
