/**
 * Webpack 模块配置文件
 *
 * 这个文件用于管理所有需要独立构建的模块配置
 * 自动扫描 app 目录下的模块，加载存在 webpack.config.js 的模块配置
 *
 * 当添加新的需要独立配置的模块时：
 * 1. 在 app/{模块名}/ 目录下创建 webpack.config.js
 * 2. 在 webpack.config.js 的 modules 数组中添加模块名
 * 3. 在 package.json 中添加构建命令（可选）
 *
 * 无需手动在此文件中注册！
 */

const fs = require('fs');
const path = require('path');

/**
 * 自动扫描 app 目录下所有模块
 * 检测哪些模块有独立的 webpack.config.js 配置文件
 */
function discoverModuleConfigs() {
  const appDir = path.resolve(__dirname, 'app');
  const configs = {};

  // 检查 app 目录是否存在
  if (!fs.existsSync(appDir)) {
    console.warn('app 目录不存在，跳过模块配置扫描');
    return configs;
  }

  // 读取 app 目录下的所有子目录
  const modules = fs.readdirSync(appDir, { withFileTypes: true })
    .filter(dirent => dirent.isDirectory())
    .map(dirent => dirent.name);

  // 遍历所有模块，检查是否有 webpack.config.js
  modules.forEach(moduleName => {
    const webpackConfigPath = path.join(appDir, moduleName, 'webpack.config.js');

    if (fs.existsSync(webpackConfigPath)) {
      try {
        // 动态加载模块的 webpack 配置
        configs[moduleName] = require(webpackConfigPath);
        console.log(`✓ 已加载模块配置: ${moduleName}`);
      } catch (error) {
        console.warn(`⚠ 加载模块配置失败: ${moduleName}`, error.message);
      }
    }
  });

  if (Object.keys(configs).length === 0) {
    console.log('未发现任何独立模块配置');
  }

  return configs;
}

/**
 * 独立模块配置映射表
 * key: 模块名称
 * value: 配置函数，接收 isProduction 参数，返回 webpack 配置对象
 *
 * 注意：这个对象会自动生成，无需手动添加！
 */
const moduleConfigs = discoverModuleConfigs();

/**
 * 获取模块的独立配置
 * @param {string} moduleName - 模块名称
 * @param {boolean} isProduction - 是否为生产环境
 * @returns {Object|null} webpack 配置对象，如果模块不存在则返回 null
 */
function getModuleConfig(moduleName, isProduction) {
  if (moduleConfigs[moduleName]) {
    return moduleConfigs[moduleName](isProduction);
  }
  return null;
}

/**
 * 检查模块是否有独立配置
 * @param {string} moduleName - 模块名称
 * @returns {boolean}
 */
function hasCustomConfig(moduleName) {
  return !!moduleConfigs[moduleName];
}

/**
 * 获取所有有独立配置的模块名称列表
 * @returns {Array<string>}
 */
function getCustomModules() {
  return Object.keys(moduleConfigs);
}

/**
 * 自动扫描 app 目录下所有模块
 * 返回所有模块名称（无论是否有独立配置）
 * @returns {Array<string>}
 */
function getAllModules() {
  const appDir = path.resolve(__dirname, 'app');

  if (!fs.existsSync(appDir)) {
    console.warn('app 目录不存在');
    return [];
  }

  return fs.readdirSync(appDir, { withFileTypes: true })
    .filter(dirent => dirent.isDirectory())
    .map(dirent => dirent.name);
}

module.exports = {
  moduleConfigs,
  getModuleConfig,
  hasCustomConfig,
  getCustomModules,
  getAllModules
};
