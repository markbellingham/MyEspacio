const path = require('path');
const {SRC, DIST, ASSETS} = require('./paths');

const ManifestPlugin = require('webpack-manifest-plugin');

module.exports = {
    // We'll place webpack configuration for all environments here

    entry: {
        scripts: path.resolve(SRC, 'js', 'index.js'),
    },
    output: {
        // Put all the bundled stuff in your dist folder
        path: DIST,

        // Our single entry point from above will be named "scripts.js"
        filename: "[name].js",

        // The output path as seen from the domain we're visiting in the browser
        publicPath: ASSETS
    },
    module: {
        rules: [
            {
                test: /\.css$/i,
                use: ['style-loader', 'css-loader'],
            },
        ],
    },
};