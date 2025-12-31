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

  if (module === 'admin' || module === 'common' || module === 'ucenter') {
    entry['main'] = path.join(jsPath, 'index.js');
    entry['app'] = path.join(cssPath, module === 'common' ? 'main.scss' : 'index.scss');
  } else if (module === 'channel') {
    // channel 模块有 admin 和 pc 两个子目录
    entry['admin/main'] = path.join(srcPath, 'admin/js/index.js');
    entry['admin/app'] = path.join(srcPath, 'admin/css/index.scss');
    entry['pc/main'] = path.join(srcPath, 'pc/js/index.js');
    entry['pc/app'] = path.join(srcPath, 'pc/css/index.scss');
  } else {
    const glob = require('glob');
    
    const jsFiles = glob.sync('**/*.js', { cwd: jsPath, ignore: ['**/node_modules/**'] });
    jsFiles.forEach(file => {
      const name = file.replace(/\.js$/, '');
      entry[name] = path.join(jsPath, file);
    });

    const scssFiles = glob.sync('**/*.scss', { cwd: cssPath, ignore: ['**/node_modules/**'] });
    scssFiles.forEach(file => {
      const name = file.replace(/\.scss$/, '');
      entry[`scss-${name}`] = path.join(cssPath, file);
    });
  }

  const baseConfig = {
    mode: isProduction ? 'production' : 'development',
    entry,
    output: {
      path: distPath,
      filename: 'js/[name].min.js',
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
        cleanOnceBeforeBuildPatterns: ['**/*', '!lib/**', '!images/**'],
        verbose: false
      }),
      new MiniCssExtractPlugin({
        filename: 'css/[name].min.css'
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
