const path = require('path');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const osassets = './src/assets/src/';
/** see below for multiple configurations.
 * - one configuration would be to have os-common.js built as a library with the target being osjs.[name]
 *   (see how gutenberg exported wpjs object: https://github.com/WordPress/gutenberg/blob/master/webpack.config.js)
 * - the other configuration will be something similar to below where we setup the "main" js files that would be
 *   enqueued by wp.
 * - a problem I will encounter is that I'm using webpack-merge to do a dev and a production build.  That makes things
 *   more tricky with the multiple configurations.
 *
 */
/** https://webpack.js.org/configuration/configuration-types/#exporting-multiple-configurations */

module.exports = [
    {
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
            filename: 'osjs.dist.js',
            path: path.resolve(__dirname, 'src/assets/dist'),
            library: ['osjs'],
            libraryTarget: 'this'
        },
    },
    {
        entry: {
            'os-admin-global': [
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
            filename: '[name].dist.js',
            path: path.resolve(__dirname, 'src/assets/dist'),
            library: ['osAdminGlobal'],
            libraryTarget: 'this'
        },
    },
    {
        entry: {
            'os-frontend-global': [
                osassets + 'frontend.js'
            ],
            'os-admin-settings' : [
                osassets + 'admin-settings.js',
                osassets + 'license-management.js'
            ]
        },
        externals: {
            jquery : "jQuery",
            osjs : 'osjs',
            osAdminGlobal : 'osAdminGlobal'

        },
        plugins: [
            new CleanWebpackPlugin(['src/assets/dist'])
        ],
        output: {
            filename: '[name].dist.js',
            path: path.resolve(__dirname, 'src/assets/dist')
        },
        module: {
            rules: [
                { test: /\.js$/, exclude: /node_modules/, loader: "babel-loader" }
            ]
        }
    }
];