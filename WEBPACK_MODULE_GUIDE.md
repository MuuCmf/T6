# Webpack 模块配置指南

## 目录结构

```
project-root/
├── webpack.common.js              # 通用配置（规则、插件、优化等）
├── webpack.modules.config.js      # 模块配置注册表
├── webpack.config.js              # 主 webpack 配置入口
└── app/
    ├── articles/
    │   ├── webpack.config.js      # Articles 模块独立配置
    │   └── _src/                  # Articles 模块源码
    ├── [新模块]/
    │   ├── webpack.config.js      # 新模块独立配置（可选）
    │   └── _src/
    └── [其他模块]/
        └── _src/
```

## 添加新的独立模块

### 步骤 1: 创建模块配置文件

在 `app/{模块名}/` 目录下创建 `webpack.config.js` 文件，例如 `app/shop/webpack.config.js`:

```javascript
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const path = require('path');

/**
 * Shop 模块独立配置
 *
 * @param {boolean} isProduction - 是否为生产环境
 * @returns {Object} webpack 配置对象
 */
function createShopConfig(isProduction = false) {
  const srcPath = path.resolve(__dirname, '_src');
  const distPath = path.resolve(__dirname, '../../public/static/shop');

  const entry = {
    // 定义你的入口文件
    'admin': path.join(srcPath, 'index.js'),
    // 可以添加更多入口
    // 'pc': path.join(srcPath, 'pc/index.js'),
  };

  const MiniCssExtractPlugin = require('mini-css-extract-plugin');
  const CopyPlugin = require('copy-webpack-plugin');

  const baseConfig = {
    mode: isProduction ? 'production' : 'development',
    entry,
    output: {
      path: distPath,
      filename: 'js/main.min.js',  // 或自定义文件名规则
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
        cleanOnceBeforeBuildPatterns: ['**/*', '!lib/**', '!images/**', '!diy/**'],
        verbose: false
      }),
      new MiniCssExtractPlugin({
        filename: 'css/main.min.css'
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
    baseConfig.optimization = productionOptimization;
  }

  return baseConfig;
}

module.exports = createShopConfig;
```

### 步骤 2: 添加构建脚本（可选）

在 `package.json` 中添加构建命令:

```json
{
  "scripts": {
    "dev:shop": "webpack --mode development --env module=shop",
    "build:shop": "webpack --mode production --env module=shop"
  }
}
```

**重要说明**：
- 不需要在 `webpack.modules.config.js` 中手动注册模块
- 系统会自动扫描 `app/` 目录，加载所有包含 `webpack.config.js` 的模块
- 运行构建时会自动发现并加载模块配置

## 配置说明

每个模块的独立配置文件需要：

1. 定义一个函数，接收 `isProduction` 参数
2. 在函数内部定义 `srcPath` 和 `distPath`
3. 返回完整的 webpack 配置对象

路径说明：
- `srcPath`: 模块的源码目录，通常是 `path.resolve(__dirname, '_src')`
- `distPath`: 模块的输出目录，通常是 `path.resolve(__dirname, '../../public/{模块名}')`

完整的配置示例可以参考 `app/articles/webpack.config.js`。

## 标准模块 vs 独立模块

### 标准模块

适用于大多数简单模块，只有一个入口文件 `index.js`，无需独立配置:

- admin
- index
- common
- ucenter
- channel

### 独立模块

适用于需要特殊构建配置的模块，需要在模块目录下创建 `webpack.config.js`:

- articles（多入口、特殊目录结构）
- 未来的复杂模块（shop、forum 等）

独立模块配置文件应放置在 `app/{模块名}/webpack.config.js`，这样可以：
- 配置与模块代码在一起，更易于维护
- 每个模块独立管理自己的构建配置
- 便于模块分发和复用

## 使用示例

### 构建单个模块

```bash
# 开发模式
npm run dev:articles

# 生产模式
npm run build:articles
```

### 构建所有模块

```bash
# 开发模式
npm run dev

# 生产模式
npm run build
```

## 注意事项

1. 独立配置函数必须接收 `isProduction` 参数
2. 配置函数必须返回完整的 webpack 配置对象
3. 确保模块名与目录名称一致
4. 模块的源码必须放在 `app/{module}/_src/` 目录下
5. 输出目录为 `public/static/{module}/`
6. 无需手动维护模块列表，系统自动发现 app/ 目录下的所有模块
