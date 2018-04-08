const path = require('path');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const combineLoaders = require('webpack-combine-loaders');
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
                'babel-polyfill',
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
            path: path.resolve(__dirname, 'assets/dist')
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
                    loader: ExtractTextPlugin.extract(
                        combineLoaders([
                            {
                                loader: 'css-loader',
                                query: {
                                    modules: true,
                                    localIdentName: '[local]',
                                },
                                //can't use minimize because cssnano (the
                                // dependency) doesn't parser the browserlist
                                // extension in package.json correctly, there's
                                // a pending update for it but css-loader
                                // doesn't have the latest yet.
                                // options: {
                                //     minimize: true
                                // }
                            },
                            {
                                loader: 'postcss-loader',
                                options: {
                                    plugins: function() {
                                        return [autoprefixer];
                                    },
                                    sourceMap: true,
                                },
                            },
                        ])
                    )
                },
            ]
        },
        watchOptions: {
            poll: 1000,
        },
    }
];