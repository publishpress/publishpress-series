const merge = require('webpack-merge');
const AssetsPlugin = require('assets-webpack-plugin');
const path = require('path');
const webpack = require('webpack');
let common = require('./webpack.common.js');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
common.forEach((config, index) => {
    if (common[index].configName === 'base') {
        common[index].plugins = [
            new CleanWebpackPlugin(['assets/dist']),
            new ExtractTextPlugin('os-[name].[contenthash].dist.css'),
            new webpack.NamedModulesPlugin(),
            new webpack.optimize.CommonsChunkPlugin({
                name: 'runner',
                minChunks: Infinity,
            }),
        ]
    }
    common[index] = merge(config, {
        devtool:'inline-source-map',
        plugins: [
            new AssetsPlugin({
                filename: 'build-manifest.json',
                path: path.resolve(__dirname, 'assets/dist'),
                prettyPrint: true,
                update: true,
            }),
        ],
    });
    //delete temporary named config item so no config parse errors
    delete common[index].configName;
});
module.exports = common;