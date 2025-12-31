# Webpack 快速入门

## 快速开始

### 1. 安装依赖

```bash
npm install
```

### 2. 开发模式

```bash
# 构建所有模块
npm run dev

# 构建特定模块
npm run dev:admin
npm run dev:index

# 监听模式（文件变化自动重新构建）
npm run watch
```

### 3. 生产模式

```bash
# 构建所有模块
npm run build

# 构建特定模块
npm run build:admin
npm run build:index
```

## 命令对照表

| Gulp 命令 | Webpack 命令 |
|----------|-------------|
| `gulp` | `npm run dev` |
| `gulp --production` | `npm run build` |
| `gulp watch` | `npm run watch` |

## 模块列表

- admin
- index
- common
- ucenter
- channel
- articles

## 常见问题

### Q: 如何构建所有模块？
```bash
npm run dev
```

### Q: 如何只构建 admin 模块？
```bash
npm run dev:admin
```

### Q: 如何启动监听模式？
```bash
npm run watch
```

### Q: 如何构建生产版本？
```bash
npm run build
```

## 下一步

详细文档请参考 [WEBPACK_MIGRATION.md](./WEBPACK_MIGRATION.md)
