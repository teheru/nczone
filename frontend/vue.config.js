const path = require('path')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const devMode = process.env.NODE_ENV !== 'production'

const resolve = (dir) => path.resolve(path.join(__dirname, dir))
const entry = devMode ? {
  nczone: resolve('src/main.js'),
  style: resolve('src/style/' + process.env.VUE_APP_STYLE_NAME + '/zone.scss')
} : {
  nczone: resolve('src/main.js'),
  'style/prosilver/zone': resolve('src/style/prosilver/zone.scss'),
  'style/flat-style/zone': resolve('src/style/flat-style/zone.scss')
}

const plugins = devMode
  ? [
    new HtmlWebpackPlugin({
      title: 'nczone',
      template: resolve('src/index.html'),
      inject: false
    })
  ]
  : [
  ]

module.exports = {
  configureWebpack: {
    entry: entry,
    plugins: plugins,
    output: {
      library: '[name]',
      libraryTarget: 'umd'
    }
  },
  chainWebpack: config => {
    config.optimization.delete('splitChunks')
  }
}
