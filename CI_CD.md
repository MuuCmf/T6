# MuuCmf T6 CI/CD 配置文档

本文档提供了 MuuCmf T6 项目的完整 CI/CD 配置说明。

## 目录

- [GitHub Actions](#github-actions)
- [GitLab CI](#gitlab-ci)
- [部署脚本](#部署脚本)
- [Docker 配置](#docker-配置)
- [环境变量配置](#环境变量配置)

---

## GitHub Actions

### 配置文件位置
`.github/workflows/ci-cd.yml`

### 工作流程

GitHub Actions 工作流包含以下阶段：

1. **测试阶段 (Test)**
   - PHP 8.0/8.1/8.2 版本测试
   - 单元测试
   - 代码质量检查 (PHPStan, PHP CodeSniffer)
   - 资源构建

2. **安全扫描 (Security Scan)**
   - Composer 依赖审计
   - 安全漏洞检查
   - Snyk 安全扫描

3. **构建阶段 (Build)**
   - Docker 镜像构建
   - 部署包打包

4. **部署阶段 (Deploy)**
   - Staging 环境部署 (develop 分支)
   - Production 环境部署 (main 分支)
   - 自动回滚 (部署失败时)

### 必需的 Secrets

在 GitHub 仓库设置中配置以下 Secrets：

```
DOCKER_USERNAME          # Docker Hub 用户名
DOCKER_PASSWORD          # Docker Hub 密码
STAGING_HOST            # Staging 服务器地址
STAGING_USER            # Staging SSH 用户名
STAGING_SSH_KEY         # Staging SSH 私钥
STAGING_PORT            # Staging SSH 端口 (默认 22)
PRODUCTION_HOST         # Production 服务器地址
PRODUCTION_USER         # Production SSH 用户名
PRODUCTION_SSH_KEY      # Production SSH 私钥
PRODUCTION_PORT        # Production SSH 端口 (默认 22)
DB_USER                # 数据库用户名
DB_PASSWORD            # 数据库密码
SLACK_WEBHOOK          # Slack 通知 Webhook
SNYK_TOKEN            # Snyk 安全扫描 Token
```

### 手动触发部署

```bash
# 推送到 develop 分支触发 Staging 部署
git push origin develop

# 推送到 main 分支触发 Production 部署
git push origin main

# 在 GitHub Actions 页面手动触发工作流
```

---

## GitLab CI

### 配置文件位置
`.gitlab-ci.yml`

### 工作流程

GitLab CI 工作流包含以下阶段：

1. **test** - 测试阶段
2. **security** - 安全扫描
3. **build** - 构建阶段
4. **deploy-staging** - Staging 部署
5. **deploy-production** - Production 部署

### 必需的 CI/CD Variables

在 GitLab 项目设置 > CI/CD > Variables 中配置：

```
# 服务器配置
STAGING_HOST              # Staging 服务器地址
STAGING_SSH_PRIVATE_KEY    # Staging SSH 私钥
PRODUCTION_HOST           # Production 服务器地址
PRODUCTION_SSH_PRIVATE_KEY # Production SSH 私钥

# 数据库配置
DB_USER                   # 数据库用户名
DB_PASSWORD               # 数据库密码

# 通知配置
SLACK_WEBHOOK             # Slack 通知 Webhook

# Docker 配置
CI_REGISTRY               # GitLab Container Registry 地址
CI_REGISTRY_USER          # GitLab Registry 用户名
CI_REGISTRY_PASSWORD      # GitLab Registry 密码
```

### 手动触发部署

```bash
# 推送到 develop 分支
git push origin develop

# 推送到 main 分支
git push origin main

# 在 GitLab CI/CD 页面手动触发部署任务
```

---

## 部署脚本

### 脚本列表

| 脚本 | 功能 |
|------|------|
| `scripts/deploy.sh` | 部署应用 |
| `scripts/rollback.sh` | 回滚到上一个版本 |
| `scripts/backup.sh` | 备份应用和数据库 |
| `scripts/restore.sh` | 恢复备份 |
| `scripts/health-check.sh` | 健康检查 |

### 部署脚本使用

```bash
# 赋予执行权限
chmod +x scripts/*.sh

# 部署到 Staging
./scripts/deploy.sh staging /path/to/package.tar.gz

# 部署到 Production
./scripts/deploy.sh production /path/to/package.tar.gz
```

### 回滚脚本使用

```bash
# 回滚到上一个版本
./scripts/rollback.sh production

# 回滚到指定版本
./scripts/rollback.sh production 20231201_120000
```

### 备份脚本使用

```bash
# 备份 Staging
./scripts/backup.sh staging root password muucmf_staging

# 备份 Production
./scripts/backup.sh production root password muucmf
```

### 恢复脚本使用

```bash
# 恢复备份
./scripts/restore.sh production /path/to/backup_20231201_120000.tar.gz root password muucmf
```

### 健康检查脚本使用

```bash
# 检查 Staging
./scripts/health-check.sh staging

# 检查 Production
./scripts/health-check.sh production
```

---

## Docker 配置

### Docker Compose 文件

| 文件 | 用途 |
|------|------|
| `docker-compose.yml` | 生产环境配置 |
| `docker-compose.dev.yml` | 开发环境配置 |

### 生产环境启动

```bash
# 复制环境变量
cp .env.docker .env

# 修改 .env 中的配置
vim .env

# 启动所有服务
docker-compose up -d

# 启动特定服务
docker-compose up -d app nginx mysql redis

# 查看日志
docker-compose logs -f app

# 停止所有服务
docker-compose down

# 停止并删除数据卷
docker-compose down -v
```

### 开发环境启动

```bash
# 启动开发环境
docker-compose -f docker-compose.dev.yml up -d

# 查看日志
docker-compose -f docker-compose.dev.yml logs -f app

# 访问应用
# http://localhost:8080

# 访问 MailHog (邮件测试)
# http://localhost:8025
```

### Docker 镜像构建

```bash
# 构建应用镜像
docker build -t muucmf-t6:latest .

# 构建并推送
docker build -t registry.example.com/muucmf-t6:${CI_COMMIT_SHA} .
docker push registry.example.com/muucmf-t6:${CI_COMMIT_SHA}
```

### 服务说明

| 服务 | 端口 | 说明 |
|------|------|------|
| app | 9000 | PHP-FPM 应用 |
| worker | 9000 | 队列工作进程 |
| nginx | 80/443 | Web 服务器 |
| mysql | 3306 | 数据库 |
| redis | 6379 | 缓存 |
| phpmyadmin | 8080 | 数据库管理工具 |
| redis-commander | 8081 | Redis 管理工具 |

---

## 环境变量配置

### 应用环境变量

在 `.env` 文件中配置：

```bash
# 应用配置
APP_ENV=production
APP_DEBUG=false

# 数据库配置
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=muucmf
DB_USERNAME=muucmf
DB_PASSWORD=your_password

# Redis 配置
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=

# 队列配置
QUEUE_DRIVER=redis
```

### Docker 环境变量

在 `.env.docker` 文件中配置：

```bash
# Docker 配置
APP_ENV=production
APP_DEBUG=false

# 数据库配置
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=muucmf
DB_USERNAME=muucmf
DB_PASSWORD=change_this_password

# Redis 配置
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=

# Nginx 配置
NGINX_PORT=80
NGINX_SSL_PORT=443

# MySQL 配置
MYSQL_PORT=3306
MYSQL_ROOT_PASSWORD=change_this_root_password

# 工具配置
PHPMYADMIN_PORT=8080
REDIS_COMMANDER_PORT=8081
```

---

## 部署流程

### 自动部署流程

1. 代码推送到 Git 仓库
2. CI/CD 触发构建和测试
3. 测试通过后构建 Docker 镜像
4. 部署到目标环境
5. 健康检查
6. 失败时自动回滚

### 手动部署流程

1. 构建部署包
2. 上传到服务器
3. 执行部署脚本
4. 验证部署结果

---

## 监控和日志

### 查看应用日志

```bash
# Docker 环境
docker-compose logs -f app

# 服务器环境
tail -f /var/www/www.muucmf.cc/runtime/log/$(date +%Y%m%d).log
```

### 查看 Nginx 日志

```bash
# Docker 环境
docker-compose logs -f nginx

# 服务器环境
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### 查看队列日志

```bash
# Docker 环境
docker-compose logs -f worker

# 服务器环境
tail -f /var/log/supervisor/queue-worker.log
```

---

## 故障排查

### 部署失败

1. 检查 CI/CD 日志
2. 验证服务器连接
3. 检查磁盘空间
4. 验证环境变量

### 应用无法访问

1. 检查 Nginx 配置
2. 验证 PHP-FPM 运行状态
3. 检查防火墙规则
4. 查看 Nginx 日志

### 数据库连接失败

1. 检查 MySQL 运行状态
2. 验证数据库凭据
3. 检查网络连接
4. 查看 MySQL 日志

### 队列任务不执行

1. 检查队列工作进程状态
2. 验证 Redis 连接
3. 查看队列日志
4. 重启队列工作进程

---

## 最佳实践

1. **定期备份**: 每天自动备份应用和数据库
2. **监控告警**: 配置监控和告警系统
3. **安全更新**: 定期更新依赖和安全补丁
4. **日志轮转**: 配置日志轮转避免磁盘满
5. **性能优化**: 定期优化数据库和缓存
6. **灾难恢复**: 制定灾难恢复计划并定期演练

---

## 联系支持

如有问题，请联系：
- 官网: https://www.muucmf.cc
- 技术交流群: 633857075
