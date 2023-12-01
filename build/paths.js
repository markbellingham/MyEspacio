const path = require('path');

module.exports = {
    // The base path of your source files, especially of your index.js
    SRC: path.resolve(__dirname, '..', 'web'),

    // The path to put the generated bundles
    DIST: path.resolve(__dirname, '..', 'public', 'dist'),

    // This is your public path
    ASSETS: '/dist'
};
