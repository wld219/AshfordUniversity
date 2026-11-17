#!/usr/bin/env node
/**
 * Ashford University Portal - Environment Setup Verification
 * Run with: node verify-setup.js
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const PROJECT_ROOT = process.cwd();
const RESULTS = {
  passed: 0,
  failed: 0,
  warnings: 0,
  checks: [],
};

function check(name, condition, detail = '') {
  if (condition) {
    RESULTS.passed++;
    RESULTS.checks.push({ status: 'PASS', name, detail });
    console.log(`  ✓ ${name}${detail ? ` - ${detail}` : ''}`);
  } else {
    RESULTS.failed++;
    RESULTS.checks.push({ status: 'FAIL', name, detail });
    console.log(`  ✗ ${name}${detail ? ` - ${detail}` : ''}`);
  }
}

function warning(name, detail = '') {
  RESULTS.warnings++;
  RESULTS.checks.push({ status: 'WARN', name, detail });
  console.log(`  ⚠ ${name}${detail ? ` - ${detail}` : ''}`);
}

function tryCommand(cmd) {
  try {
    return execSync(cmd, { stdio: 'pipe', encoding: 'utf8' }).trim();
  } catch (e) {
    return null;
  }
}

function fileExists(relativePath) {
  return fs.existsSync(path.join(PROJECT_ROOT, relativePath));
}

console.log('╔═════════════════════════════════════════════════════════════════╗');
console.log('║  Ashford University Portal - Environment Setup Verification    ║');
console.log('╚═════════════════════════════════════════════════════════════════╝\n');

// ── Node.js ──
console.log('Node.js:');
const nodeVersion = tryCommand('node --version');
check('Node.js installed', !!nodeVersion, nodeVersion || 'not found');
const npmVersion = tryCommand('npm --version');
check('npm installed', !!npmVersion, npmVersion || 'not found');

// ── PHP ──
console.log('\nPHP:');
const phpVersion = tryCommand('php --version');
check('PHP installed', !!phpVersion, phpVersion || 'not found');
if (phpVersion) {
  const pdoMysql = tryCommand('php -m | grep pdo_mysql');
  check('pdo_mysql extension', !!pdoMysql, pdoMysql || 'missing');
}

// ── MySQL ──
console.log('\nMySQL:');
const mysqlVersion = tryCommand('mysql --version');
check('MySQL client installed', !!mysqlVersion, mysqlVersion || 'not found');
const mysqldVersion = tryCommand('mysqld --version');
check('MySQL server installed', !!mysqldVersion, mysqldVersion || 'not found');

// ── Project Files ──
console.log('\nProject Files:');
const criticalFiles = [
  'config/database.php',
  'config/auth.php',
  'includes/env.php',
  'includes/session.php',
  'includes/auth.php',
  'includes/functions.php',
  'includes/helpers.php',
  'includes/layout.php',
  'database/schema.sql',
  'lib/PHPMailer/src/PHPMailer.php',
  'index.php',
  'login.php',
  'logout.php',
  'register.php',
  'install.php',
  'router.php',
  '.env',
];

criticalFiles.forEach(f => {
  check(`File exists: ${f}`, fileExists(f));
});

// ── Portal Directories ──
console.log('\nPortal Directories:');
const portals = ['admin', 'student', 'faculty', 'statstaff', 'public'];
portals.forEach(p => {
  check(`Directory exists: ${p}/`, fs.existsSync(path.join(PROJECT_ROOT, p)));
});

// ── Playwright ──
console.log('\nPlaywright:');
const playwrightVersion = tryCommand('npx playwright --version');
check('Playwright installed', !!playwrightVersion, playwrightVersion || 'not found');

// ── .env Configuration ──
console.log('\nEnvironment Configuration:');
if (fileExists('.env')) {
  const envContent = fs.readFileSync(path.join(PROJECT_ROOT, '.env'), 'utf8');
  const requiredEnvKeys = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'APP_URL', 'SESSION_NAME'];
  requiredEnvKeys.forEach(key => {
    check(`.env has ${key}`, envContent.includes(`${key}=`));
  });
} else {
  check('.env file exists', false);
}

// ── Summary ──
console.log('\n════════════════════════════════════════════════════════════════');
console.log(`Results: ${RESULTS.passed} passed, ${RESULTS.failed} failed, ${RESULTS.warnings} warnings`);
console.log('════════════════════════════════════════════════════════════════');

if (RESULTS.failed > 0) {
  console.log('\n⚠ Some checks failed. Resolve the issues above before running the portal.');
  process.exit(1);
} else {
  console.log('\n✓ All checks passed. The environment is ready.');
  console.log('\nNext steps:');
  console.log('  1. Start PHP server:  php -S localhost:8000 -t . router.php');
  console.log('  2. Open browser:      http://localhost:8000');
  console.log('  3. Run tests:         npm test');
  process.exit(0);
}
