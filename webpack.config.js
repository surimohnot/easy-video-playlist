path = require('path');
module.exports = {
	entry: {
		'assets/scripts/admin/admin' : "./assets/scripts/admin/admin.js",
		'assets/scripts/front/front' : "./assets/scripts/front/front.js",
		'assets/scripts/block/block' : "./assets/scripts/block/block.js",
	},
	output: {
   		path: path.resolve( __dirname, './' ),
		filename: '[name].build.js',
	},
	module: {
    	rules: [
    		{
				test: /\.(js|jsx)$/,
				use: { loader: "babel-loader" },
				exclude: /(node_modules|bower_components)/
			}
    	]
  	}
};
