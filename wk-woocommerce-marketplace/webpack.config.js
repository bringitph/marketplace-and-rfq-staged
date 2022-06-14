const path           = require('path');
const MiniCssExtract = require('mini-css-extract-plugin');
const nodeEnv        = process.env.nodeEnv || 'development';

module.exports = {
	mode: nodeEnv,
	devtool: nodeEnv !== 'production' ? 'source-map' : false,
	watch: true,
	entry: {
		adminJS: './assets/admin/build/js/admin.js',
		frontJS: './assets/front/build/js/front.js',
	},
	output: {
		filename: ( data ) => {
			return data.chunk.name === 'adminJS' ? 'assets/admin/dist/js/admin.min.js' : data.chunk.name === 'frontJS' ? 'assets/front/dist/js/front.min.js' : [];
		},
		path: path.resolve(__dirname),
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: {
					loader: "babel-loader",
					options: {
						presets: ['@babel/preset-env']
					}
				}
		},
			{
				test: /\.less$/,
				use: [
					MiniCssExtract.loader,
					{
						loader: 'css-loader', // translates CSS into CommonJS
						options: {
							url: false,
							sourceMap: false
						}
				},
					{
						loader: 'less-loader', // compiles Less to CSS
						options: {
							sourceMap: false
						}
				},
				],
		}
		]
	},
	plugins: [
		new MiniCssExtract({
			filename: "[name]",
		}),
	]
}
