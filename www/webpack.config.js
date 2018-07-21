const VueLoaderPlugin = require('vue-loader/lib/plugin');
const MODE = 'development';

module.exports = {
  entry: {
    install: './js/src/install/main.ts',
    login: './js/src/login/main.ts',
    admin: './js/src/admin/main.ts'
  },
  mode: MODE,
  devtool: 'source-map',
  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
          loaders: {
            'scss': 'vue-style-loader!css-loader!sass-loader',
            'ts': 'ts-loader'
          }
        }
      },
      {
        test: /\.scss/,
        use: [
          'vue-style-loader',
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
          'vue-style-loader',
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
        loader: 'ts-loader',
        options: {
          appendTsSuffixTo: [/\.vue$/]
        }
      }
    ]
  },
  resolve: {
    extensions: ['.js', '.ts', '.scss', '.css', '.vue'],
    alias: {
      vue: 'vue/dist/vue.js'
    }
  },
  output: {
    filename: '[name].bundle.js',
    path: `${__dirname}/js/dist`
  },
  plugins: [new VueLoaderPlugin()]
}