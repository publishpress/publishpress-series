const merge = require('webpack-merge');
const common = require('./webpack.common.js');
const webpack = require('webpack');
const UglifyJSPlugin = require('uglifyjs-webpack-plugin');
common.forEach(config => {
    merge(config,{
        devtool: 'source-map',
        plugins: [
            new UglifyJSPlugin({
                sourceMap: true
            }),
            new webpack.DefinePlugin({
                'process.env': {
                    'NODE_ENV': JSON.stringify('production')
                }
            })
        ]
    })
});
module.exports = common;