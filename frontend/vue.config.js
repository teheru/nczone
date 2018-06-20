const path = require('path')
const HtmlWebpackPlugin = require('html-webpack-plugin')

const resolve = (dir) => path.resolve(path.join(__dirname, dir))

module.exports = {
  configureWebpack: {
    plugins: [
      new HtmlWebpackPlugin({
        title: 'nczone',
        template: resolve('src/index.html'),
        inject: 'head'
      })
    ],
    output: {
      library: 'nczone',
      libraryTarget: 'umd'
    }
  },
  css: {
    extract: false
  },
  chainWebpack: config => {
    config.optimization.delete('splitChunks')
  }
}
