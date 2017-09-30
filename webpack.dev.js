const merge = require('webpack-merge');
const common = require('./webpack.common.js');
common.forEach(config => {
    merge(config, {
        devtool:'inline-source-map'
    })
});
module.exports = common;