path = require('path');
module.exports = {
	entry: {
		'backend/js/admin/admin'     : "./backend/js/admin/admin.js",
		'frontend/js/front'          : "./frontend/js/front.js",
		'backend/js/block/block'     : "./backend/js/block/block.js",
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
