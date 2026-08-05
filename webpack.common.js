const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const autoprefixer = require('autoprefixer');
const osassets = './assets/src/';
/** see below for multiple configurations.
 */
/** https://webpack.js.org/configuration/configuration-types/#exporting-multiple-configurations */

module.exports = [
    {
        configName: 'os-common',
        entry: {
            'common' : [
                'core-js/stable',
                'regenerator-runtime/runtime',
                osassets + 'os-common.js'
            ]
        },
        externals: {
            jquery : "jQuery"
        },
        module: {
            rules: [
                { test: /\.js$/, exclude: /node_modules/, loader: "babel-loader" }
            ]
        },
        output: {
            filename: 'osjs.[chunkhash].dist.js',
            path: path.resolve(__dirname, 'assets/dist'),
            publicPath: '',
            library: ['osjs'],
            libraryTarget: 'this'
        },
    },
    {
        configName: 'os-admin-global',
        entry: {
            'admin-global': [
                osassets + 'admin-main.js'
            ]
        },
        externals: {
            jquery : "jQuery",
            osjs : 'osjs'
        },
        module: {
            rules: [
                { test: /\.js$/, exclude: /node_modules/, loader: "babel-loader" }
            ]
        },
        output: {
            filename: 'os-[name].[chunkhash].dist.js',
            path: path.resolve(__dirname, 'assets/dist'),
            publicPath: '',
            library: ['osAdminGlobal'],
            libraryTarget: 'this'
        },
    },
    {
        configName: 'base',
        entry: {
            'frontend-global': [
                osassets + 'frontend.js'
            ],
            'admin-settings' : [
                osassets + 'admin-settings.js',
                osassets + 'license-management.js'
            ]
        },
        externals: {
            jquery : "jQuery",
            osjs : 'osjs',
            osAdminGlobal : 'osAdminGlobal'
        },
        output: {
            filename: '[name].[chunkhash].dist.js',
            path: path.resolve(__dirname, 'assets/dist'),
            publicPath: ''
        },
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    loader: "babel-loader"
                },
                {
                    test: /\.css$/,
                    use: [
                        MiniCssExtractPlugin.loader,
                        {
                            loader: 'css-loader',
                            options: {
                                modules: true,
                                localIdentName: '[local]',
                            },
                        },
                        {
                            loader: 'postcss-loader',
                            options: {
                                postcssOptions: {
                                    plugins: [autoprefixer()],
                                },
                                sourceMap: true,
                            },
                        },
                    ]
                },
            ]
        },
        optimization: {
            runtimeChunk: {
                name: 'runner',
            },
        },
        watchOptions: {
            poll: 1000,
        },
    }
];
