const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const path = require('path');

/**
 * Articles 模块独立配置
 *
 * 这个模块具有特殊的构建需求：
 * - 包含多个入口：admin, pc, diy/pc, diy/mobile, diy/link
 * - 需要保持特殊的目录结构
 * - diy 目录下的文件保持原始文件名
 *
 * @param {boolean} isProduction - 是否为生产环境
 * @returns {Object} webpack 配置对象
 */
function createArticlesConfig(isProduction = false) {
  const srcPath = path.resolve(__dirname, '_src');
  const distPath = path.resolve(__dirname, '../../public/static/articles');

  const entry = {
    'admin': path.join(srcPath, 'admin/index.js'),
    'pc': path.join(srcPath, 'pc/index.js'),
    'diy/pc/articles_list': path.join(srcPath, 'diy/pc/index.js'),
    'diy/mobile/articles_list': path.join(srcPath, 'diy/mobile/index.js'),
    'diy/link/articles_detail': path.join(srcPath, 'diy/link/articles_detail.js'),
    'diy/link/articles_list': path.join(srcPath, 'diy/link/articles_list.js')
  };

  const MiniCssExtractPlugin = require('mini-css-extract-plugin');
  const CopyPlugin = require('copy-webpack-plugin');

  const baseConfig = {
    mode: isProduction ? 'production' : 'development',
    entry,
    output: {
      path: distPath,
      filename: (pathData) => {
        // diy 目录下的文件保持原始文件名,只添加 .min
        if (pathData.chunk.name.startsWith('diy/')) {
          const chunkName = pathData.chunk.name.replace('diy/', '');
          return `diy/${chunkName}.min.js`;
        }
        return `${pathData.chunk.name}/js/main.min.js`;
      },
      clean: false
    },
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env']
            }
          }
        },
        {
          test: /\.scss$/,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader',
              options: {
                url: false
              }
            },
            {
              loader: 'sass-loader',
              options: {
                api: 'modern',
                sassOptions: {
                  silenceDeprecations: ['legacy-js-api', 'import']
                }
              }
            }
          ]
        },
        {
          test: /\.css$/,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader',
              options: {
                url: false
              }
            }
          ]
        },
        {
          test: /\.(png|jpe?g|gif|svg|bmp|webp)$/i,
          type: 'asset/resource',
          generator: {
            filename: 'images/[name][ext]'
          }
        },
        {
          test: /\.(woff|woff2|eot|ttf|otf)$/i,
          type: 'asset/resource',
          generator: {
            filename: 'fonts/[name][ext]'
          }
        }
      ]
    },
    plugins: [
      new CleanWebpackPlugin({
        cleanOnceBeforeBuildPatterns: ['**/*', '!lib/**', '!images/**', '!diy/**', '!admin/assets/**', '!admin/index.html'],
        verbose: false
      }),
      new MiniCssExtractPlugin({
        filename: (pathData) => {
          // diy 目录下的文件保持原始文件名,只添加 .min
          if (pathData.chunk.name.startsWith('diy/')) {
            const chunkName = pathData.chunk.name.replace('diy/', '');
            return `diy/${chunkName}.min.css`;
          }
          return `${pathData.chunk.name}/css/main.min.css`;
        }
      }),
      new CopyPlugin({
        patterns: [
          {
            from: path.join(srcPath, 'lib'),
            to: path.join(distPath, 'lib'),
            noErrorOnMissing: true
          },
          {
            from: path.join(srcPath, 'images'),
            to: path.join(distPath, 'images'),
            noErrorOnMissing: true
          }
        ]
      })
    ],
    resolve: {
      extensions: ['.js', '.json', '.scss', '.css'],
      alias: {
        '@': srcPath
      }
    },
    devtool: isProduction ? false : 'eval-source-map',
    watch: !isProduction,
    watchOptions: {
      ignored: /node_modules/,
      aggregateTimeout: 300,
      poll: 1000
    }
  };

  if (isProduction) {
    baseConfig.optimization = {
      minimize: true,
      minimizer: [
        new (require('terser-webpack-plugin'))({
          terserOptions: {
            compress: {
              drop_console: true
            }
          }
        }),
        new (require('css-minimizer-webpack-plugin'))()
      ],
      splitChunks: {
        chunks: 'all',
        cacheGroups: {
          vendor: {
            test: /[\\/]node_modules[\\/]/,
            name: 'vendor',
            chunks: 'all'
          }
        }
      }
    };
  }

  return baseConfig;
}

module.exports = createArticlesConfig;
