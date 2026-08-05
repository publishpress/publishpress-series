const { merge } = require('webpack-merge');
const AssetsPlugin = require('assets-webpack-plugin');
const path = require('path');
let common = require('./webpack.common.js');
const webpack = require('webpack');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
common.forEach((config, index) => {
    if (common[index].configName === 'base') {
        common[index].plugins = [
            new CleanWebpackPlugin(),
            new MiniCssExtractPlugin({
                filename: 'os-[name].[contenthash].dist.css',
            }),
        ]
    }
    common[index] = merge(config,{
        mode: 'production',
        devtool: 'source-map',
        plugins: [
            new webpack.DefinePlugin({
                'process.env': {
                    'NODE_ENV': JSON.stringify('production')
                }
            }),
            new AssetsPlugin({
                filename: 'build-manifest.json',
                path: path.resolve(__dirname, 'assets/dist'),
                update: true
            })
        ],
        optimization: {
            minimize: true,
            minimizer: [new TerserPlugin({
                extractComments: false,
                terserOptions: {
                    format: {
                        comments: false,
                    },
                },
            })],
        },
    });
    //delete temporary named config item so no config errors
    delete common[index].configName;
});
module.exports = common;
