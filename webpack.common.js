const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CopyPlugin = require('copy-webpack-plugin');

/**
 * 获取模块的基础路径配置
 * @param {string} module - 模块名称
 * @returns {Object} 包含 srcPath 和 distPath 的对象
 */
function getModulePaths(module) {
  return {
    srcPath: path.resolve(__dirname, `app/${module}/_src`),
    distPath: path.resolve(__dirname, `public/static/${module}`)
  };
}

/**
 * 通用模块规则配置
 */
const commonRules = [
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
];

/**
 * 通用插件配置工厂
 * @param {string} module - 模块名称
 * @param {Object} options - 配置选项
 * @returns {Array} 插件数组
 */
function createCommonPlugins(module, options = {}) {
  const { srcPath, distPath } = getModulePaths(module);
  const { excludeFromClean = [] } = options;

  const cleanPatterns = ['**/*', '!lib/**', '!images/**', '!diy/**', ...excludeFromClean];

  return [
    new MiniCssExtractPlugin({
      filename: (pathData) => {
        // 特殊模块可以自定义文件名
        if (options.customCssFilename) {
          return options.customCssFilename(pathData, module);
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
  ];
}

/**
 * 生产环境优化配置
 */
const productionOptimization = {
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

/**
 * 通用解析配置
 */
const commonResolve = {
  extensions: ['.js', '.json', '.scss', '.css'],
  alias: {
    '@': path.resolve(__dirname)
  }
};

/**
 * 通用开发服务器配置
 */
const commonDevOptions = {
  watchOptions: {
    ignored: /node_modules/,
    aggregateTimeout: 300,
    poll: 1000
  }
};

module.exports = {
  getModulePaths,
  commonRules,
  createCommonPlugins,
  productionOptimization,
  commonResolve,
  commonDevOptions
};
