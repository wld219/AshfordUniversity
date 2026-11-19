const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');

function loadEnv() {
  const envPath = path.resolve(process.cwd(), '.env');
  if (!fs.existsSync(envPath)) return {};
  return fs
    .readFileSync(envPath, 'utf8')
    .split('\n')
    .map((l) => l.trim())
    .filter((l) => l && !l.startsWith('#'))
    .reduce((acc, l) => {
      const [k, ...v] = l.split('=');
      acc[k.trim()] = v.join('=').trim();
      return acc;
    }, {});
}

const env = loadEnv();
const config = {
  host: env.DB_HOST || '127.0.0.1',
  port: Number(env.DB_PORT) || 3306,
  user: env.DB_USER || 'root',
  password: env.DB_PASS || '',
  database: env.DB_NAME || 'ashford_university',
};

const MIN_ROWS = {
  User: 1985,
  Login: 1985,
  Department: 10,
  Course: 240,
  Semester: 14,
  Major: 12,
  Minor: 12,
  Building: 8,
  Faculty: 350,
  Course_Section: 2500,
  Enrollment: 7000,
  Attendance: 57000,
};

let failures = 0;
let checks = 0;

function check(name, ok, detail = '') {
  checks++;
  if (ok) {
    console.log(`  PASS  ${name}${detail ? ` — ${detail}` : ''}`);
  } else {
    failures++;
    console.log(`  FAIL  ${name}${detail ? ` — ${detail}` : ''}`);
  }
}

async function main() {
  const conn = await mysql.createConnection(config);
  try {
    console.log(
      `\nConnecting to ${config.host}:${config.port}/${config.database} as ${config.user}...\n`
    );

    const [tables] = await conn.query(
      "SELECT COUNT(*) AS n FROM information_schema.tables WHERE table_schema = ?",
      [config.database]
    );
    const tableCount = tables[0].n;
    check('Total tables present', tableCount >= 60, `${tableCount} tables (expected >= 60)`);

    const required = [
      'User', 'Login', 'Department', 'Course', 'Semester', 'Course_Section',
      'Enrollment', 'Faculty', 'Student', 'Admin', 'Major', 'Minor',
      'Building', 'Room', 'Attendance', 'Master_Schedule', 'Stat_Staff',
    ];
    for (const t of required) {
      const [[row]] = await conn.query(
        "SELECT COUNT(*) AS n FROM information_schema.tables WHERE table_schema = ? AND table_name = ?",
        [config.database, t]
      );
      check(`Table exists: ${t}`, row.n === 1);
    }

    for (const [table, min] of Object.entries(MIN_ROWS)) {
      const [[row]] = await conn.query(`SELECT COUNT(*) AS n FROM \`${table}\``);
      check(`Row count: ${table} >= ${min}`, row.n >= min, `${row.n} rows`);
    }

    const fkChecks = [
      ['Enrollment', 'CRN', 'Course_Section', 'CRN'],
      ['Enrollment', 'student_ID', 'Student', 'student_ID'],
      ['Course_Section', 'course_ID', 'Course', 'course_ID'],
      ['Advisor', 'faculty_ID', 'Faculty', 'faculty_ID'],
      ['Advisor', 'student_ID', 'Student', 'student_ID'],
      ['Admin', 'user_ID', 'User', 'user_ID'],
    ];
    for (const [child, childCol, parent, parentCol] of fkChecks) {
      const [[row]] = await conn.query(
        `SELECT COUNT(*) AS n FROM \`${child}\` c
         LEFT JOIN \`${parent}\` p ON c.\`${childCol}\` = p.\`${parentCol}\`
         WHERE p.\`${parentCol}\` IS NULL`
      );
      check(
        `No orphans: ${child}.${childCol} -> ${parent}.${parentCol}`,
        row.n === 0,
        `${row.n} orphaned rows`
      );
    }

    const accounts = [
      ['arianaaugustin@ashford.edu', 'Admin'],
      ['kevinbaptiste200001@ashford.edu', 'Faculty'],
      ['ericjones400001@ashford.edu', 'StatStaff'],
    ];
    for (const [email, role] of accounts) {
      const [[row]] = await conn.query(
        `SELECT u.user_ID FROM Login l
         JOIN User u ON u.user_ID = l.user_ID
         WHERE l.user_Email = ? AND u.user_Type = ?
         LIMIT 1`,
        [email, role]
      );
      check(`Account present: ${email} (${role})`, !!row.user_ID);
    }

    const [views] = await conn.query(
      "SELECT COUNT(*) AS n FROM information_schema.views WHERE table_schema = ?",
      [config.database]
    );
    check('Views defined', views[0].n >= 1, `${views[0].n} views`);

    console.log(`\n${'='.repeat(60)}`);
    console.log(`Results: ${checks - failures}/${checks} passed, ${failures} failed`);
    console.log(`${'='.repeat(60)}\n`);
    process.exit(failures > 0 ? 1 : 0);
  } finally {
    await conn.end();
  }
}

main().catch((err) => {
  console.error('FATAL:', err.message);
  process.exit(1);
});
