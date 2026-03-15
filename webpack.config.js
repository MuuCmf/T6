const path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const { getModulePaths, commonRules, createCommonPlugins, productionOptimization, commonResolve, commonDevOptions } = require('./webpack.common');

// 引入模块配置管理
const { hasCustomConfig, getModuleConfig, getAllModules } = require('./webpack.modules.config');

// 自动获取所有模块（无需手动维护）
const modules = getAllModules();

/**
 * 标准模块只有一个入口: index.js
 */
function createStandardConfig(module, isProduction = false) {
  const { srcPath, distPath } = getModulePaths(module);

  const entry = {
    'admin': path.join(srcPath, 'index.js')
  };

  const baseConfig = {
    mode: isProduction ? 'production' : 'development',
    entry,
    output: {
      path: distPath,
      filename: 'js/main.min.js',
      clean: false
    },
    module: {
      rules: commonRules
    },
    plugins: [
      new CleanWebpackPlugin({
        cleanOnceBeforeBuildPatterns: ['**/*', '!lib/**', '!images/**', '!diy/**', '!assets/**', '!index.html'],
        verbose: false
      }),
      ...createCommonPlugins(module)
    ],
    resolve: {
      ...commonResolve,
      alias: {
        '@': srcPath
      }
    },
    devtool: isProduction ? false : 'eval-source-map',
    watch: !isProduction,
    ...commonDevOptions
  };

  if (isProduction) {
    baseConfig.optimization = productionOptimization;
  }

  return baseConfig;
}

module.exports = (env, argv) => {
  const isProduction = argv.mode === 'production';
  const targetModule = env.module;

  if (targetModule) {
    // 检查模块是否存在
    if (!modules.includes(targetModule)) {
      console.warn(`\n⚠ 警告: 模块 '${targetModule}' 不存在，可用模块: ${modules.join(', ')}`);
      process.exit(1);
    }
    // 检查是否有独立配置
    if (hasCustomConfig(targetModule)) {
      return getModuleConfig(targetModule, isProduction);
    }
    // 使用标准配置的模块
    return createStandardConfig(targetModule, isProduction);
  }

  // 构建所有模块
  console.log(`\n准备构建 ${modules.length} 个模块: ${modules.join(', ')}\n`);
  const configs = modules.map(module => {
    // 检查是否有独立配置
    if (hasCustomConfig(module)) {
      return getModuleConfig(module, isProduction);
    }
    return createStandardConfig(module, isProduction);
  });
  return configs;
};
