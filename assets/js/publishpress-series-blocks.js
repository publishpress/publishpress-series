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
    var RangeControl = wp.components.RangeControl;
    var SelectControl = wp.components.SelectControl;
    var ToggleControl = wp.components.ToggleControl;
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
        },
        {
            name: 'publishpress-series/series-list',
            key: 'seriesList',
            title: __('Series List', 'organize-series'),
            description: __('Display a customizable list of Series or Series Categories.', 'organize-series'),
            icon: 'screenoptions',
            isSeriesList: true
        }
    ];

    function getOptions(definition) {
        return (data.layouts && data.layouts[definition.key]) || [{
            label: __('Default layout', 'organize-series'),
            value: '0'
        }];
    }

    function getCurrentPostId(props) {
        var postId = props.context && parseInt(props.context.postId || 0, 10);
        var editorStore;

        if (postId) {
            return postId;
        }

        if (!wp.data || !wp.data.select) {
            return 0;
        }

        editorStore = wp.data.select('core/editor');

        return editorStore && editorStore.getCurrentPostId
            ? parseInt(editorStore.getCurrentPostId() || 0, 10)
            : 0;
    }

    function getServerSidePreview(definition, attributes, props) {
        var postId = getCurrentPostId(props);
        var serverSideRenderProps;

        if (!ServerSideRender) {
            return createElement(Placeholder, {
                icon: definition.icon,
                label: definition.title
            }, definition.description);
        }

        serverSideRenderProps = {
            key: definition.name + ':' + JSON.stringify(attributes),
            block: definition.name,
            attributes: attributes
        };

        if (postId) {
            serverSideRenderProps.urlQueryArgs = { post_id: postId };
        }

        return createElement(ServerSideRender, serverSideRenderProps);
    }

    function editBlock(definition, props) {
        var attributes = props.attributes || {};
        var layoutId = parseInt(attributes.layoutId || 0, 10) || 0;
        var seriesId = parseInt(attributes.seriesId || 0, 10) || 0;
        var blockProps = {
            className: 'wp-block-publishpress-series-' + definition.key.replace(/([A-Z])/g, '-$1').toLowerCase()
        };
        var preview = getServerSidePreview(definition, attributes, props);

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
                preview
            )
        );
    }

    function editSeriesListBlock(definition, props) {
        var attributes = props.attributes || {};
        var contentType = attributes.contentType || 'series';
        var view = attributes.view || 'grid';
        var columns = parseInt(attributes.columns || 3, 10) || 3;
        var numberOfItems = parseInt(attributes.numberOfItems || 6, 10) || 6;
        var orderBy = attributes.orderBy || 'name';
        var order = attributes.order || 'asc';
        var isSeriesCategory = contentType === 'series_group';
        var preview = getServerSidePreview(definition, attributes, props);

        return createElement(
            Fragment,
            null,
            InspectorControls ? createElement(
                InspectorControls,
                null,
                createElement(
                    PanelBody,
                    {
                        title: __('Query settings', 'organize-series'),
                        initialOpen: true
                    },
                    createElement(SelectControl, {
                        label: __('Display', 'organize-series'),
                        value: contentType,
                        options: [
                            { label: __('Individual Series', 'organize-series'), value: 'series' },
                            { label: __('Series Categories', 'organize-series'), value: 'series_group' }
                        ],
                        onChange: function (value) {
                            props.setAttributes({ contentType: value });
                        }
                    }),
                    createElement(SelectControl, {
                        label: __('Order by', 'organize-series'),
                        value: orderBy + '/' + order,
                        options: [
                            { label: __('Name: A to Z', 'organize-series'), value: 'name/asc' },
                            { label: __('Name: Z to A', 'organize-series'), value: 'name/desc' },
                            {
                                label: isSeriesCategory
                                    ? __('Series count: High to low', 'organize-series')
                                    : __('Post count: High to low', 'organize-series'),
                                value: 'count/desc'
                            },
                            {
                                label: isSeriesCategory
                                    ? __('Series count: Low to high', 'organize-series')
                                    : __('Post count: Low to high', 'organize-series'),
                                value: 'count/asc'
                            },
                            {
                                label: isSeriesCategory
                                    ? __('Series Category ID: High to low', 'organize-series')
                                    : __('Series ID: High to low', 'organize-series'),
                                value: 'term_id/desc'
                            },
                            {
                                label: isSeriesCategory
                                    ? __('Series Category ID: Low to high', 'organize-series')
                                    : __('Series ID: Low to high', 'organize-series'),
                                value: 'term_id/asc'
                            }
                        ],
                        onChange: function (value) {
                            var values = value.split('/');
                            props.setAttributes({ orderBy: values[0], order: values[1] });
                        }
                    }),
                    RangeControl ? createElement(RangeControl, {
                        label: __('Number of items', 'organize-series'),
                        value: numberOfItems,
                        min: 1,
                        max: 50,
                        onChange: function (value) {
                            props.setAttributes({ numberOfItems: parseInt(value, 10) || 1 });
                        }
                    }) : null,
                    ToggleControl ? createElement(ToggleControl, {
                        label: isSeriesCategory
                            ? __('Hide empty Series Categories', 'organize-series')
                            : __('Hide empty Series', 'organize-series'),
                        checked: attributes.hideEmpty !== false,
                        onChange: function (value) {
                            props.setAttributes({ hideEmpty: value });
                        }
                    }) : null
                ),
                createElement(
                    PanelBody,
                    {
                        title: __('Display settings', 'organize-series'),
                        initialOpen: true
                    },
                    createElement(SelectControl, {
                        label: __('View', 'organize-series'),
                        value: view,
                        options: [
                            { label: __('Grid', 'organize-series'), value: 'grid' },
                            { label: __('List', 'organize-series'), value: 'list' }
                        ],
                        onChange: function (value) {
                            props.setAttributes({ view: value });
                        }
                    }),
                    view === 'grid' && RangeControl ? createElement(RangeControl, {
                        label: __('Columns', 'organize-series'),
                        value: columns,
                        min: 1,
                        max: 4,
                        onChange: function (value) {
                            props.setAttributes({ columns: parseInt(value, 10) || 1 });
                        }
                    }) : null,
                    !isSeriesCategory && ToggleControl ? createElement(ToggleControl, {
                        label: __('Show Featured image', 'organize-series'),
                        checked: attributes.showIcon !== false,
                        onChange: function (value) {
                            props.setAttributes({ showIcon: value });
                        }
                    }) : null,
                    ToggleControl ? createElement(ToggleControl, {
                        label: __('Show description', 'organize-series'),
                        checked: attributes.showDescription !== false,
                        onChange: function (value) {
                            props.setAttributes({ showDescription: value });
                        }
                    }) : null,
                    ToggleControl ? createElement(ToggleControl, {
                        label: isSeriesCategory
                            ? __('Show Series count', 'organize-series')
                            : __('Show post count', 'organize-series'),
                        checked: attributes.showPostCount !== false,
                        onChange: function (value) {
                            props.setAttributes({ showPostCount: value });
                        }
                    }) : null
                )
            ) : null,
            createElement(
                'div',
                { className: 'wp-block-publishpress-series-series-list' },
                createElement('div', { className: 'pps-series-block-editor-label' }, definition.title),
                preview
            )
        );
    }

    definitions.forEach(function (definition) {
        var settings = {
            title: definition.title,
            description: definition.description,
            category: 'publishpress-series',
            icon: definition.icon,
            save: function () {
                return null;
            }
        };

        if (definition.isSeriesList) {
            settings.example = {
                attributes: {
                    contentType: 'series',
                    view: 'grid',
                    columns: 3,
                    numberOfItems: 6
                },
                viewportWidth: 900
            };
            settings.attributes = {
                contentType: { type: 'string', default: 'series' },
                view: { type: 'string', default: 'grid' },
                columns: { type: 'number', default: 3 },
                numberOfItems: { type: 'number', default: 6 },
                orderBy: { type: 'string', default: 'name' },
                order: { type: 'string', default: 'asc' },
                hideEmpty: { type: 'boolean', default: true },
                showIcon: { type: 'boolean', default: true },
                showDescription: { type: 'boolean', default: true },
                showPostCount: { type: 'boolean', default: true }
            };
            settings.edit = function (props) {
                return editSeriesListBlock(definition, props);
            };
        } else {
            settings.usesContext = ['postId', 'postType'];
            settings.example = {
                attributes: {
                    layoutId: 0,
                    seriesId: 0
                },
                viewportWidth: 600
            };
            settings.attributes = {
                layoutId: { type: 'number', default: 0 },
                seriesId: { type: 'number', default: 0 }
            };
            settings.edit = function (props) {
                return editBlock(definition, props);
            };
        }

        registerBlockType(definition.name, settings);
    });
}(window.wp, window.publishPressSeriesBlocks || {}));
