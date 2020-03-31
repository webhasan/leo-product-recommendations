const path = require( 'path' );
const defaultConfig = require("@wordpress/scripts/config/webpack.config");

module.exports = {
    ...defaultConfig,
    entry: {
		panel: path.resolve( __dirname, 'assets/js', 'panel.dev.js' ),
		modal: path.resolve( __dirname, 'assets/js', 'modal.dev.js' )
	},
	output: {
		filename: '[name].js',
		path: path.resolve( __dirname, 'assets/js' ),
	}
};