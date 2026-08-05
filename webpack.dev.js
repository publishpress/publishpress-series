const { merge } = require('webpack-merge');
const AssetsPlugin = require('assets-webpack-plugin');
const path = require('path');
let common = require('./webpack.common.js');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
common.forEach((config, index) => {
    if (common[index].configName === 'base') {
        common[index].plugins = [
            new CleanWebpackPlugin(),
            new MiniCssExtractPlugin({
                filename: 'os-[name].[contenthash].dist.css',
            }),
        ]
    }
    common[index] = merge(config, {
        mode: 'development',
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
