const MODE = 'development';
const enabledSourceMap = (MODE === 'development');

module.exports = {
  entry: './src/index.js',
  mode: MODE,
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
              sourceMap: enabledSourceMap,
              importLoaders: 2
            },
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: enabledSourceMap
            }
          }
        ],
      },
      {
        test: /\.css/,
        // ローダー名
        use: [
          'style-loader',
          {
            loader: 'css-loader',
            options: {
              url: false,
              minimize: true,
              sourceMap: enabledSourceMap
            },
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: enabledSourceMap
            }
          }
        ],
      },
    ]

  },
  output: {
    // 出力先のファイル名
    filename: 'bundle.js',
    // 出力先のファイルパス
    path: `${__dirname}/dist`
  }
}