const path = require( 'path' );
const defaultConfig = require("@wordpress/scripts/config/webpack.config");

module.exports = {
    ...defaultConfig,
    entry: {
		index: path.resolve( __dirname, 'assects/js', 'panel.dev.js' ),
	},
	output: {
		filename: 'panel.build.js',
		path: path.resolve( __dirname, 'assects/js' ),
	}
};