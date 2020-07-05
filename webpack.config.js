const path = require("path");
const defaultConfig = require("@wordpress/scripts/config/webpack.config");

module.exports = {
  ...defaultConfig,
  entry: {
    panel: path.resolve(__dirname, "assets/js", "panel.js"),
    settings: path.resolve(__dirname, "assets/js", "settings.js"),
    modal: path.resolve(__dirname, "assets/js", "modal.js"),
    "ajax-add-to-cart": path.resolve(
      __dirname,
      "assets/js",
      "ajax-add-to-cart.js"
    ),
  },
  output: {
    filename: "[name].min.js",
    path: path.resolve(__dirname, "assets/js"),
  },
};