const path = require( 'path' );
const defaultConfig = require("@wordpress/scripts/config/webpack.config");

module.exports = {
    ...defaultConfig,
    entry: {
		panel: path.resolve( __dirname, 'assects/js', 'panel.dev.js' ),
	},
	output: {
		filename: '[name].js',
		path: path.resolve( __dirname, 'assects/js' ),
	}
};