# Gulp 到 Webpack 迁移指南

## 概述

本文档说明如何将 MuuCmf T6 项目从 Gulp 构建系统迁移到 Webpack。

## 迁移原因

- **更好的性能**: Webpack 提供更快的构建速度和更好的缓存机制
- **更强大的模块化**: 支持 ES6 模块、动态导入等现代特性
- **更好的开发体验**: 热模块替换（HMR）、source map 等
- **更灵活的配置**: 更细粒度的控制和插件系统
- **社区支持**: 更活跃的社区和更丰富的插件生态

## 项目结构

```
MuuCmf-T6/
├── app/
│   ├── admin/
│   │   └── _src/
│   │       ├── js/
│   │       ├── css/
│   │       ├── images/
│   │       └── lib/
│   ├── index/
│   │   └── _src/
│   │       ├── js/
│   │       ├── css/
│   │       ├── images/
│   │       └── lib/
│   ├── common/
│   ├── ucenter/
│   ├── channel/
│   └── articles/
├── public/static/
│   ├── admin/
│   ├── index/
│   ├── common/
│   ├── ucenter/
│   ├── channel/
│   └── articles/
├── webpack.config.js
├── .babelrc
└── package.json
```

## 迁移步骤

### 1. 安装依赖

```bash
npm install
```

### 2. 删除旧的 Gulp 配置（可选）

```bash
# 删除各模块的 gulpfile.js
rm app/admin/gulpfile.js
rm app/index/gulpfile.js
rm app/common/gulpfile.js
rm app/ucenter/gulpfile.js
rm app/channel/gulpfile.js
rm app/articles/gulpfile.js
```

### 3. 调整源代码结构

#### Admin 模块特殊处理

Admin 模块需要创建入口文件：

**app/admin/_src/js/adminlte/index.js**
```javascript
import './adminlte.js';
```

**app/admin/_src/js/main/index.js**
```javascript
import './main.js';
```

**app/admin/_src/css/main.scss**
```scss
@import './main.scss';
```

#### 其他模块

其他模块的 JS 文件会被自动扫描并创建入口点。

### 4. 使用 Webpack 命令

#### 开发模式

```bash
# 构建所有模块（开发模式）
npm run dev

# 构建特定模块（开发模式）
npm run dev:admin
npm run dev:index
npm run dev:common
npm run dev:ucenter
npm run dev:channel
npm run dev:articles

# 监听模式
npm run watch
```

#### 生产模式

```bash
# 构建所有模块（生产模式）
npm run build

# 构建特定模块（生产模式）
npm run build:admin
npm run build:index
npm run build:common
npm run build:ucenter
npm run build:channel
npm run build:articles
```

#### 使用构建脚本

```bash
# 构建所有模块
node scripts/build.js all

# 构建特定模块（开发模式）
node scripts/build.js dev admin

# 构建特定模块（生产模式）
node scripts/build.js build admin

# 监听模式
node scripts/build.js watch
```

## 功能对比

### Gulp 功能 → Webpack 对应功能

| Gulp 功能 | Webpack 实现 |
|----------|-------------|
| `gulp-sass` | `sass-loader` |
| `gulp-babel` | `babel-loader` |
| `gulp-uglify` | `TerserPlugin` |
| `gulp-minify-css` | `CssMinimizerPlugin` |
| `gulp-concat` | Webpack 自动处理 |
| `gulp-contrib-copy` | `CopyPlugin` |
| `gulp-watch` | Webpack watch 模式 |
| `browser-sync` | 需要单独配置 |

## 配置说明

### Webpack 配置文件

`webpack.config.js` 是主配置文件，支持：

- **多模块构建**: 自动处理 admin、index、common、ucenter、channel、articles 模块
- **环境切换**: 通过 `--mode` 参数切换开发/生产模式
- **模块选择**: 通过 `--env module=<module>` 选择特定模块
- **自动扫描**: 自动扫描 JS 和 SCSS 文件创建入口点

### Babel 配置

`.babelrc` 配置文件定义了 Babel 转译规则：

- 使用 `@babel/preset-env` 预设
- 支持现代浏览器（>1%, last 2 versions, not dead）
- 自动按需引入 polyfill

## 输出文件说明

### 开发模式

- JS 文件: `public/static/<module>/js/<name>.min.js`
- CSS 文件: `public/static/<module>/css/<name>.min.css`
- 图片: `public/static/<module>/images/`
- 第三方库: `public/static/<module>/lib/`

### 生产模式

- 所有文件都会被压缩和优化
- JS 文件会移除 console.log
- CSS 文件会被压缩
- 代码会被拆分（vendor chunks）

## 常见问题

### 1. 如何添加新的模块？

在 `webpack.config.js` 中的 `modules` 数组添加新模块名称：

```javascript
const modules = ['admin', 'index', 'common', 'ucenter', 'channel', 'articles', 'your-module'];
```

### 2. 如何自定义 Webpack 配置？

修改 `webpack.config.js` 中的 `createConfig` 函数。

### 3. 如何启用 HMR（热模块替换）？

需要配置 webpack-dev-server，在 `webpack.config.js` 中添加：

```javascript
const webpack = require('webpack');

module.exports = {
  // ... 其他配置
  devServer: {
    hot: true,
    port: 8080,
    proxy: {
      '/': 'http://localhost:80'
    }
  }
};
```

### 4. 如何处理图片资源？

Webpack 会自动处理图片资源，支持以下格式：
- PNG
- JPEG
- GIF
- SVG
- BMP
- WebP

### 5. 如何添加 CSS 预处理器？

Webpack 已支持 SCSS，如需添加其他预处理器：

```javascript
module: {
  rules: [
    {
      test: /\.less$/,
      use: [
        MiniCssExtractPlugin.loader,
        'css-loader',
        'less-loader'
      ]
    }
  ]
}
```

## 性能优化

### 1. 构建速度优化

- 使用 `cache-loader` 或 `thread-loader` 加速构建
- 配置 `resolve.extensions` 减少查找时间
- 使用 DLL 预编译第三方库

### 2. 产物优化

- 代码分割（Code Splitting）
- Tree Shaking
- 压缩和混淆
- 资源压缩（Gzip）

### 3. 开发体验优化

- Source Map
- HMR（热模块替换）
- 快速刷新（Fast Refresh）

## 迁移检查清单

- [ ] 安装新的依赖包
- [ ] 删除旧的 Gulp 配置文件（可选）
- [ ] 调整源代码结构（特别是 admin 模块）
- [ ] 测试开发模式构建
- [ ] 测试生产模式构建
- [ ] 验证输出文件路径正确
- [ ] 测试监听模式
- [ ] 更新 CI/CD 配置（如有）
- [ ] 更新团队文档

## 回滚方案

如果迁移过程中遇到问题，可以：

1. 保留原有的 Gulp 配置文件
2. 继续使用 `gulp` 命令进行构建
3. 逐步迁移各个模块

## 参考资料

- [Webpack 官方文档](https://webpack.js.org/)
- [Babel 官方文档](https://babeljs.io/)
- [Sass 官方文档](https://sass-lang.com/)
- [从 Gulp 迁移到 Webpack](https://webpack.js.org/guides/migrating/)

## 支持

如有问题，请联系开发团队或提交 Issue。
