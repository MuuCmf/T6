const { execSync } = require('child_process');
const path = require('path');

const modules = ['admin', 'index', 'common', 'ucenter', 'channel', 'articles'];

function runCommand(command, cwd) {
  try {
    execSync(command, {
      cwd: cwd || process.cwd(),
      stdio: 'inherit'
    });
  } catch (error) {
    console.error(`Command failed: ${command}`);
    process.exit(1);
  }
}

function buildAll(mode = 'development') {
  console.log(`Building all modules in ${mode} mode...`);
  modules.forEach(module => {
    console.log(`\nBuilding ${module}...`);
    runCommand(`npm run ${mode}:${module}`);
  });
  console.log('\nAll modules built successfully!');
}

function buildModule(module, mode = 'development') {
  if (!modules.includes(module)) {
    console.error(`Invalid module: ${module}`);
    console.log(`Available modules: ${modules.join(', ')}`);
    process.exit(1);
  }
  console.log(`Building ${module} in ${mode} mode...`);
  runCommand(`npm run ${mode}:${module}`);
}

const args = process.argv.slice(2);
const command = args[0];
const module = args[1];
const mode = args.includes('--prod') ? 'production' : 'development';

switch (command) {
  case 'all':
    buildAll(mode);
    break;
  case 'dev':
  case 'build':
    if (module) {
      buildModule(module, command === 'build' ? 'production' : 'development');
    } else {
      console.log('Please specify a module');
      console.log(`Available modules: ${modules.join(', ')}`);
    }
    break;
  case 'watch':
    console.log('Starting webpack watch mode...');
    runCommand('npm run watch');
    break;
  default:
    console.log('Usage:');
    console.log('  node scripts/build.js all [--prod]          - Build all modules');
    console.log('  node scripts/build.js dev <module>           - Build specific module in dev mode');
    console.log('  node scripts/build.js build <module> [--prod] - Build specific module in prod mode');
    console.log('  node scripts/build.js watch                 - Start watch mode');
    console.log(`\nAvailable modules: ${modules.join(', ')}`);
}
