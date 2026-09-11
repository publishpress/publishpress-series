const fs = require('fs');
const path = require('path');

const pluginRoot = path.resolve(__dirname, '../..');
const blocks = {};

function createElement(type, props, ...children) {
    return { type, props: props || {}, children };
}

global.window = {
    publishPressSeriesBlocks: {
        layouts: {
            postListBox: [{ label: 'Default layout', value: '0' }]
        },
        series: [{ label: 'Current series', value: '0' }]
    },
    wp: {
        blockEditor: { InspectorControls: 'InspectorControls' },
        blocks: {
            registerBlockType(name, settings) {
                blocks[name] = settings;
            }
        },
        components: {
            PanelBody: 'PanelBody',
            Placeholder: 'Placeholder',
            RangeControl: 'RangeControl',
            SelectControl: 'SelectControl',
            ToggleControl: 'ToggleControl'
        },
        data: {
            select() {
                return { getCurrentPostId: () => 3269 };
            }
        },
        element: { createElement, Fragment: 'Fragment' },
        i18n: { __: (text) => text },
        serverSideRender: 'ServerSideRender'
    }
};

require(path.join(pluginRoot, 'assets/js/publishpress-series-blocks.js'));

const tree = blocks['publishpress-series/post-list-box'].edit({
    attributes: { layoutId: 3232, seriesId: 59 },
    context: { postId: 3269 },
    setAttributes() {}
});

function containsEditorLabel(node) {
    if (!node || 'object' !== typeof node) {
        return false;
    }

    if (node.props && 'pps-series-block-editor-label' === node.props.className) {
        return true;
    }

    return Array.isArray(node.children) && node.children.some(containsEditorLabel);
}

const failures = [];
if (containsEditorLabel(tree)) {
    failures.push('Post List Box still renders the redundant editor label.');
}

const frontendCss = fs.readFileSync(
    path.join(pluginRoot, 'addons/post-list-box/assets/css/post-list-box-frontend.css'),
    'utf8'
);
if (!/\.pps-post-list-box\s+\.pps-post-list-title\s*\{[^}]*margin:\s*0\s+0\s+20px\s+0;/s.test(frontendCss)) {
    failures.push('The title margin can still be overridden by Gutenberg heading styles.');
}

if (failures.length) {
    failures.forEach((failure) => console.error(failure));
    process.exit(1);
}

console.log('Editor label and extra title spacing are removed.');
