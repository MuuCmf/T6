const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin');
const { merge } = require('webpack-merge');

const modules = ['admin', 'index', 'common', 'ucenter', 'channel', 'articles'];

function createConfig(module, isProduction = false) {
  const srcPath = path.resolve(__dirname, `app/${module}/_src`);
  const distPath = path.resolve(__dirname, `public/static/${module}`);

  const entry = {};

  const jsPath = path.join(srcPath, 'js');
  const cssPath = path.join(srcPath, 'css');

  if (module === 'admin' || module === 'common' || module === 'ucenter' || module === 'channel' || module === 'index') {
    entry['admin'] = path.join(srcPath, 'index.js');
  } else {
    // articles 模块特殊处理: admin, pc 和 diy 子目录分别构建
    entry['admin'] = path.join(srcPath, 'admin/index.js');
    entry['pc'] = path.join(srcPath, 'pc/index.js');
    // diy 目录下的文件
    entry['diy/pc/articles_list'] = path.join(srcPath, 'diy/pc/index.js');
    entry['diy/mobile/articles_list'] = path.join(srcPath, 'diy/mobile/articles_list.js');
    entry['diy/link/articles_detail'] = path.join(srcPath, 'diy/link/articles_detail.js');
    entry['diy/link/articles_list'] = path.join(srcPath, 'diy/link/articles_list.js');
  }
  
  const baseConfig = {
    mode: isProduction ? 'production' : 'development',
    entry,
    output: {
      path: distPath,
      filename: (pathData) => {
        // articles 模块保持目录结构
        if (module === 'articles') {
          // diy 目录下的文件保持原始文件名,只添加 .min
          if (pathData.chunk.name.startsWith('diy/')) {
            const chunkName = pathData.chunk.name.replace('diy/', '');
            return `diy/${chunkName}.min.js`;
          }
          return `${pathData.chunk.name}/js/main.min.js`;
        }
        return 'js/main.min.js';
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
                url: false  // 禁用 CSS 中对 URL 的处理
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
                url: false  // 禁用 CSS 中对 URL 的处理
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
        cleanOnceBeforeBuildPatterns: ['**/*', '!lib/**', '!images/**', '!diy/**'],
        verbose: false
      }),
      new MiniCssExtractPlugin({
        filename: (pathData) => {
          // articles 模块保持目录结构
          if (module === 'articles') {
            // diy 目录下的文件保持原始文件名,只添加 .min
            if (pathData.chunk.name.startsWith('diy/')) {
              const chunkName = pathData.chunk.name.replace('diy/', '');
              return `diy/${chunkName}.min.css`;
            }
            return `${pathData.chunk.name}/css/main.min.css`;
          }
          return 'css/main.min.css';
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
          },
          {
            from: path.join(srcPath, 'css/AdminLTE.min.css'),
            to: path.join(distPath, 'css'),
            noErrorOnMissing: true
          },
          {
            from: path.join(srcPath, 'css/skins'),
            to: path.join(distPath, 'css/skins'),
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
        new TerserPlugin({
          terserOptions: {
            compress: {
              drop_console: true
            }
          }
        }),
        new CssMinimizerPlugin()
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

module.exports = (env, argv) => {
  const isProduction = argv.mode === 'production';
  const targetModule = env.module;

  if (targetModule && modules.includes(targetModule)) {
    return createConfig(targetModule, isProduction);
  }

  const configs = modules.map(module => createConfig(module, isProduction));
  return configs;
};
