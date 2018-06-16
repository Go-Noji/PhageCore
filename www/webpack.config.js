const MODE = 'development';

module.exports = {
  entry: {
    install: './js/src/install/main.ts'
  },
  mode: MODE,
  devtool: 'source-map',
  module: {
    rules: [
      {
        test: /\.scss/,
        use: [
          'style-loader',
          {
            loader: 'css-loader',
            options: {
              url: false,
              minimize: true,
              importLoaders: 2
            },
          },
          {
            loader: 'postcss-loader',
            options: {
              plugin: [require('autoprefixer')({grid: true})]
            }
          },
          {
            loader: 'sass-loader'
          }
        ],
      },
      {
        test: /\.css/,
        use: [
          'style-loader',
          {
            loader: 'css-loader',
            options: {
              url: false,
              minimize: true
            },
          },
          {
            loader: 'sass-loader'
          }
        ],
      },
      {
        test: /\.ts$/,
        use: 'ts-loader'
      }
    ]
  },
  resolve: {
    extensions: ['.js', '.ts', '.scss', '.css'],
    alias: {
      vue: 'vue/dist/vue.js'
    }
  },
  output: {
    filename: '[name].bundle.js',
    path: `${__dirname}/js/dist`
  }
}