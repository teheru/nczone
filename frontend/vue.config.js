const path = require('path')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const devMode = process.env.NODE_ENV !== 'production'

const resolve = (dir) => path.resolve(path.join(__dirname, dir))

module.exports = {
  configureWebpack: {
    entry: devMode ? {
      nczone: resolve('src/main.js'),
      style: resolve('src/css/styles/' + process.env.VUE_APP_STYLE_NAME + '/zone.scss')
    } : {
    },
    plugins: devMode
      ? [
        new HtmlWebpackPlugin({
          title: 'nczone',
          template: resolve('src/index.html'),
          inject: false
        })
      ]
      : [
      ],
    output: devMode ? {
      filename: '[name].js',
      library: '[name]',
      libraryTarget: 'umd'
    } : {
      filename: 'nczone.js',
      library: 'nczone',
      libraryTarget: 'umd'
    }
  },
  chainWebpack: config => {
    config.optimization.delete('splitChunks')
  }
}
