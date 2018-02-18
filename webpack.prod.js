const merge = require('webpack-merge');
let common = require('./webpack.common.js');
const webpack = require('webpack');
const UglifyJSPlugin = require('uglifyjs-webpack-plugin');
common.forEach((config, index) => {
    common[index] = merge(config,{
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