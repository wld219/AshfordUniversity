# SQL Escaper

[![NPM Version](https://img.shields.io/npm/v/sql-escaper.svg?label=&color=70a1ff&logo=npm&logoColor=white)](https://www.npmjs.com/package/sql-escaper)
[![NPM Downloads](https://img.shields.io/npm/dm/sql-escaper.svg?label=&logo=npm&logoColor=white&color=45aaf2)](https://www.npmjs.com/package/sql-escaper)
[![Coverage](https://img.shields.io/codecov/c/github/mysqljs/sql-escaper?label=&logo=codecov&logoColor=white&color=98cc00)](https://app.codecov.io/gh/mysqljs/sql-escaper)<br />
[![GitHub Workflow Status (Node.js)](https://img.shields.io/github/actions/workflow/status/mysqljs/sql-escaper/ci_node.yml?event=push&label=&branch=main&logo=nodedotjs&logoColor=535c68&color=badc58)](https://github.com/mysqljs/sql-escaper/actions/workflows/ci_node.yml?query=branch%3Amain)
[![GitHub Workflow Status (Bun)](https://img.shields.io/github/actions/workflow/status/mysqljs/sql-escaper/ci_bun.yml?event=push&label=&branch=main&logo=bun&logoColor=ffffff&color=f368e0)](https://github.com/mysqljs/sql-escaper/actions/workflows/ci_bun.yml?query=branch%3Amain)
[![GitHub Workflow Status (Deno)](https://img.shields.io/github/actions/workflow/status/mysqljs/sql-escaper/ci_deno.yml?event=push&label=&branch=main&logo=deno&logoColor=ffffff&color=079992)](https://github.com/mysqljs/sql-escaper/actions/workflows/ci_deno.yml?query=branch%3Amain)

## Motivation

**SQL Escaper** is a rework of [**sqlstring**](https://github.com/mysqljs/sqlstring) (created by [**Douglas Wilson**](https://github.com/dougwilson)) that reads the structure of each query to decide how its values are formatted.

### Rework includes:

- **TypeScript** by default.
- Support for `Uint8Array`, `BigInt`, and `Temporal`.
- Support for both **CJS** and **ESM** exports.
- Up to [**~44% faster**](#performance) than **sqlstring**.
- Distinguishes when a keyword is used as a value.
- Distinguishes when a column has a keyword name.
- Distinguishes between multiple clauses/keywords in the same query.
- Support for the [`NO_BACKSLASH_ESCAPES`](#sql-mode) SQL mode _(**sqlstring** only escapes correctly with it disabled)_.
- Support from **Node.js 12** onward _(**sqlstring** supports **Node.js 0.6**)_, which allows the modern engine optimizations behind the performance gains.

> [!TIP]
>
> **SQL Escaper** has the same API as the original [**sqlstring**](https://github.com/mysqljs/sqlstring) <sup>1</sup>, so it can be used as a drop-in replacement. If it breaks any **sqlstring** usage, please report it as a bug.

> [!IMPORTANT]
>
> 🔐 **SQL Escaper** is intended to fix a potential [**SQL Injection vulnerability**](https://flattsecurity.medium.com/finding-an-unseen-sql-injection-by-bypassing-escape-functions-in-mysqljs-mysql-90b27f6542b4) reported in 2022. When the original [**sqlstring**](https://github.com/mysqljs/sqlstring) is combined with [**mysqljs/mysql**](https://github.com/mysqljs/mysql) or [**MySQL2**](https://github.com/sidorares/node-mysql2), objects passed as values can be expanded into SQL fragments, potentially allowing attackers to manipulate the query structure. See [sidorares/node-mysql2#4051](https://github.com/sidorares/node-mysql2/issues/4051) for details.
>
> <sup>1</sup> Regardless of the `stringifyObjects` value, objects used outside of `SET` or `ON DUPLICATE KEY UPDATE` contexts are stringified as `'[object Object]'` unless you mark them with [**`expand`**](#expand). This is a security measure against that vulnerability and is not considered a breaking change for **sqlstring** usage.

---

## Install

```bash
# Node.js
npm i sql-escaper
```

```bash
# Bun
bun add sql-escaper
```

```bash
# Deno
deno add npm:sql-escaper
```

---

### [MySQL2](https://github.com/sidorares/node-mysql2)

**MySQL2** uses **SQL Escaper** as its default escaping library since version `3.17.0`, so you only need to update it to the latest version:

```bash
npm i mysql2@latest
```

### [mysqljs/mysql](https://github.com/mysqljs/mysql)

You can use an override in your _package.json_:

```json
"dependencies": {
  "mysql": "^2.18.1"
},
"overrides": {
  "sqlstring": "npm:sql-escaper"
}
```

Next, clean the `node_modules` and reinstall the dependencies (`npm i`).

---

## Usage

For _up-to-date_ documentation, always follow the [**README.md**](https://github.com/mysqljs/sql-escaper?tab=readme-ov-file#readme) in the **GitHub** repository.

### Quickstart

```js
import { escape, escapeId, expand, format, raw } from 'sql-escaper';

escape("Hello 'World'", true);
// => "'Hello \\'World\\''"

escapeId('table.column');
// => '`table`.`column`'

format('SELECT * FROM ?? WHERE id = ?', ['users', 42]);
// => 'SELECT * FROM `users` WHERE id = 42'

format('INSERT INTO users SET ?', [{ name: 'foo', email: 'bar@test.com' }]);
// => "INSERT INTO users SET `name` = 'foo', `email` = 'bar@test.com'"

format('SELECT * FROM users WHERE ?', [expand({ id: 42 })]);
// => 'SELECT * FROM users WHERE `id` = 42'

escape(raw('NOW()'), true);
// => 'NOW()'
```

With **CommonJS**, load the same functions with `require`:

```js
const { escape, escapeId, expand, format, raw } = require('sql-escaper');
```

---

## API

### escape

Escapes a value for safe use in SQL queries.

```ts
escape(value: SqlValue, stringifyObjects?: boolean, timezone?: Timezone, sqlMode?: SqlMode): string
```

```js
escape(undefined, true); // 'NULL'
escape(null, true); // 'NULL'
escape(true, true); // 'true'
escape(false, true); // 'false'
escape(5, true); // '5'
escape("Hello 'World", true); // "'Hello \\'World'"
```

#### Dates

Dates are converted to `YYYY-MM-DD HH:mm:ss.sss` format:

```js
escape(new Date(2012, 4, 7, 11, 42, 3, 2), true);
// => "'2012-05-07 11:42:03.002'"
```

Invalid dates return `NULL`:

```js
escape(new Date(NaN), true); // 'NULL'
```

You can specify a timezone:

```js
const date = new Date(Date.UTC(2012, 4, 7, 11, 42, 3, 2));

escape(date, true, 'Z'); // "'2012-05-07 11:42:03.002'"
escape(date, true, '+01'); // "'2012-05-07 12:42:03.002'"
escape(date, true, '-05:00'); // "'2012-05-07 06:42:03.002'"
```

#### Temporal

Among [Temporal](https://tc39.es/proposal-temporal/) values, `Temporal.Instant` and `Temporal.ZonedDateTime` are absolute points in time and honor the `timezone` argument exactly like `Date`, with millisecond precision:

```js
const instant = Temporal.Instant.from('2012-05-07T11:42:03.002Z');

escape(instant, true, 'Z'); // "'2012-05-07 11:42:03.002'"
escape(instant, true, '+0200'); // "'2012-05-07 13:42:03.002'"
```

`Temporal.PlainDateTime`, `Temporal.PlainDate`, and `Temporal.PlainTime` are wall-clock values and are emitted verbatim as `DATETIME` / `DATE` / `TIME` literals, ignoring `timezone`:

```js
escape(Temporal.PlainDate.from('2012-05-07')); // "'2012-05-07'"
escape(Temporal.PlainTime.from('11:42:03')); // "'11:42:03'"
```

#### Buffers

Buffers are converted to hex strings:

```js
escape(Buffer.from([0, 1, 254, 255]), true);
// => "X'0001feff'"
```

#### Objects

When `stringifyObjects` is set to a non-nullish value **(recommended)**, objects are stringified instead of being expanded into `key = value` pairs, unless marked with [`expand`](#expand):

```js
escape({ a: 'b' }, true);
// => "'[object Object]'"
```

Objects with a `toSqlString` method will have that method called:

```js
escape({ toSqlString: () => 'NOW()' }, true);
// => 'NOW()'
```

Without `stringifyObjects`, plain objects are converted to `key = value` pairs **(discouraged)**:

```js
escape({ a: 'b', c: 'd' });
// => "`a` = 'b', `c` = 'd'"
```

Function properties in objects are ignored **(discouraged)**:

```js
escape({ a: 'b', c: () => {} });
// => "`a` = 'b'"
```

> [!CAUTION]
>
> Without `stringifyObjects`, an object reaching `escape` where a value is expected reshapes the query:
>
> ```js
> const userInput = { id: true }; // e.g. JSON.parse('{"id":true}')
>
> /** Unsafe 🔓 (value position) */
> 'DELETE FROM entries WHERE id = ' + escape(userInput);
> // => "DELETE FROM entries WHERE id = `id` = true"
> // `id` = `id` is always true, so every row is deleted ❗️
>
> /** Valid ✅ (SET assignment) */
> 'UPDATE users SET ' + escape({ name: 'foo', role: 'admin' });
> // => "UPDATE users SET `name` = 'foo', `role` = 'admin'"
> ```

> [!TIP]
>
> Instead, `format` expands an object only where it is safe (e.g., a `SET` assignment) or marked with [**`expand`**](#expand), and stringifies it everywhere else, so the same input cannot reshape the query:
>
> ```js
> const userInput = { id: true }; // e.g. JSON.parse('{"id":true}')
>
> /** Unsafe 🔐 (value position) */
> format('DELETE FROM entries WHERE id = ?', [userInput]);
> // => "DELETE FROM entries WHERE id = '[object Object]'"
> // stringified to an inert value
>
> /** Valid ✅ (SET assignment) */
> format('UPDATE users SET ?', [{ name: 'foo', role: 'admin' }]);
> // => "UPDATE users SET `name` = 'foo', `role` = 'admin'"
> // expanded on purpose
> ```

#### Arrays

Arrays are turned into comma-separated lists:

```js
escape([1, 2, 'c'], true);
// => "1, 2, 'c'"
```

Nested arrays are turned into grouped lists (useful for bulk inserts):

```js
escape(
  [
    [1, 2, 3],
    [4, 5, 6],
  ],
  true
);
// => '(1, 2, 3), (4, 5, 6)'
```

#### Sets

Sets are treated like arrays:

```js
escape(new Set([1, 2, 3]), true);
// => '1, 2, 3'
```

---

### escapeId

Escapes an identifier (database, table, or column name).

```ts
escapeId(value: SqlValue, forbidQualified?: boolean): string
```

```js
escapeId('id');
// => '`id`'

escapeId('table.column');
// => '`table`.`column`'

escapeId('i`d');
// => '`i``d`'
```

Qualified identifiers (with `.`) can be forbidden:

```js
escapeId('id1.id2', true);
// => '`id1.id2`'
```

Arrays are turned into comma-separated identifier lists:

```js
escapeId(['a', 'b', 't.c']);
// => '`a`, `b`, `t`.`c`'
```

---

### format

Formats a SQL query by replacing `?` placeholders with escaped values and `??` with escaped identifiers.

```ts
format(sql: string, values?: SqlValue | SqlValue[], stringifyObjects?: boolean, timezone?: Timezone, sqlMode?: SqlMode): string
```

```js
format('SELECT * FROM ?? WHERE id = ?', ['users', 42]);
// => 'SELECT * FROM `users` WHERE id = 42'

format('? and ?', ['a', 'b']);
// => "'a' and 'b'"
```

Triple (or more) question marks are ignored:

```js
format('? or ??? and ?', ['foo', 'bar', 'fizz', 'buzz']);
// => "'foo' or ??? and 'bar'"
```

If no values are provided, the SQL is returned unchanged:

```js
format('SELECT ??');
// => 'SELECT ??'
```

#### Objects in SET clauses

When `stringifyObjects` is falsy, objects used in `SET` or `ON DUPLICATE KEY UPDATE` contexts are expanded into `key = value` pairs:

```js
format('UPDATE users SET ?', [{ name: 'foo', email: 'bar@test.com' }]);
// => "UPDATE users SET `name` = 'foo', `email` = 'bar@test.com'"

format(
  'INSERT INTO users (name, email) VALUES (?, ?) ON DUPLICATE KEY UPDATE ?',
  ['foo', 'bar@test.com', { name: 'foo', email: 'bar@test.com' }]
);
// => "INSERT INTO users (name, email) VALUES ('foo', 'bar@test.com') ON DUPLICATE KEY UPDATE `name` = 'foo', `email` = 'bar@test.com'"
```

When `stringifyObjects` is truthy, objects are stringified unless marked with [`expand`](#expand):

```js
format('UPDATE users SET ?', [{ name: 'foo' }], true);
// => "UPDATE users SET '[object Object]'"
```

#### Maps in SET clauses

Maps are treated like plain objects, preserving insertion order:

```js
format('UPDATE users SET ?', [
  new Map([
    ['name', 'foo'],
    ['email', 'bar@test.com'],
  ]),
]);
// => "UPDATE users SET `name` = 'foo', `email` = 'bar@test.com'"
```

Outside of `SET` or `ON DUPLICATE KEY UPDATE`, a `Map` is stringified as `'[object Map]'` unless marked with [`expand`](#expand):

```js
format('SELECT * FROM users WHERE data = ?', [new Map([['id', 1]])]);
// => "SELECT * FROM users WHERE data = '[object Map]'"
```

#### SQL mode

By default, double-quoted text is read as a string, where a backslash escapes the next character. When the server runs with the [`ANSI_QUOTES`](https://dev.mysql.com/doc/refman/5.7/en/sql-mode.html#sqlmode_ansi_quotes) SQL mode, that reading can skip or misplace the placeholders after a double-quoted identifier that ends in a backslash, so set `ansiQuotes` to have double-quoted text read as an identifier, where a backslash is an ordinary character:

```js
const sqlMode = { ansiQuotes: true };

format('SELECT "dir\\" FROM t WHERE id = ?', [1], false, 'local', sqlMode);
// => 'SELECT "dir\\" FROM t WHERE id = 1'
```

When the server runs with the [`NO_BACKSLASH_ESCAPES`](https://dev.mysql.com/doc/refman/5.7/en/sql-mode.html#sqlmode_no_backslash_escapes) SQL mode, a backslash in a string is an ordinary character, so the default escaping lets a value end its string early. Set `noBackslashEscapes` to escape a quote by doubling it and keep every other character as it is, in `escape` and `format`. Placeholders after a string that ends in a backslash are then found too:

```js
const sqlMode = { noBackslashEscapes: true };

escape("it's C:\\", false, 'local', sqlMode);
// => "'it''s C:\\'"

format("SELECT 'C:\\' AS dir, ? AS name", ["it's"], false, 'local', sqlMode);
// => "SELECT 'C:\\' AS dir, 'it''s' AS name"
```

The option must match the session's SQL mode when the server parses the statement. Set while the mode is off, it lets a value end its string early just as the default does while the mode is on. The server reports the mode in the `SERVER_STATUS_NO_BACKSLASH_ESCAPES` status flag, which can lag behind the session, for example after a stored routine that changes `sql_mode` returns.

Any value other than a boolean throws a `TypeError`, except when no values are provided to `format`.

A value with a `toSqlString` method receives the SQL mode as its argument, to pass on to the `escape` or `format` calls that build its SQL. Text escaped before the outer call, such as a value escaped into a `raw` string, must be given the same mode by hand:

```js
const sqlMode = { noBackslashEscapes: true };
const author = {
  toSqlString: (mode) =>
    format(
      'SELECT id FROM users WHERE name = ?',
      ["it's"],
      false,
      'local',
      mode
    ),
};

format(
  'SELECT * FROM posts WHERE author_id IN (?)',
  [author],
  false,
  'local',
  sqlMode
);
// => "SELECT * FROM posts WHERE author_id IN (SELECT id FROM users WHERE name = 'it''s')"
```

---

### raw

Creates a raw SQL value that will not be escaped.

```ts
raw(sql: string): Raw
```

```js
escape(raw('NOW()'), true);
// => 'NOW()'
```

Inside an expanded object, raw values are preserved **(discouraged)**:

```js
escape({ id: raw('LAST_INSERT_ID()') });
// => '`id` = LAST_INSERT_ID()'
```

Only accepts strings:

```js
raw(42); // throws TypeError
```

---

### expand

Marks an object or a `Map` to be expanded into `key = value` pairs in any position of the statement, regardless of the `stringifyObjects` value.

```ts
expand(value: Record<string, unknown> | Map<string, SqlValue>): Expand
```

```js
format('SELECT ?? FROM ?? WHERE ? AND status IN (?) LIMIT ?', [
  ['id', 'name'],
  'users',
  expand({ role: 'admin' }),
  ['active', 'pending'],
  10,
]);
// => "SELECT `id`, `name` FROM `users` WHERE `role` = 'admin' AND status IN ('active', 'pending') LIMIT 10"

format(
  'UPDATE users SET ? WHERE id = ?',
  [expand({ name: 'foo' }), { id: true }],
  true
);
// => "UPDATE users SET `name` = 'foo' WHERE id = '[object Object]'"

escape(expand({ id: 1 }), true);
// => '`id` = 1'
```

A marked object produces the same text it produces in a `SET` clause, with dates in the timezone given to the call. Only the marked value expands, so other objects in the statement keep the default behavior, and a mark nested inside an array, a `Set`, an object, or a `Map` is stringified like any nested object:

```js
format('SELECT * FROM users WHERE id IN (?)', [[expand({ id: 1 })]]);
// => "SELECT * FROM users WHERE id IN ('[object Object]')"
```

Only accepts an object or a `Map`:

```js
expand('id = 1'); // throws TypeError
expand([{ id: 1 }]); // throws TypeError
expand(raw('NOW()')); // throws TypeError
```

- The mark is the symbol registered as `sql-escaper.expand`, so a value marked by one copy of **SQL Escaper** (for example, your own dependency) expands in another (for example, the one **MySQL2** uses).

> [!WARNING]
>
> The keys of a marked object become column names. Mark only an object whose keys your code controls, never one parsed from user input, such as a request body, or the client chooses which columns the query reads or changes.

---

### TypeScript

You can import the available types:

```ts
import type { Expand, Raw, SqlMode, SqlValue, Timezone } from 'sql-escaper';
```

---

## Performance

Each benchmark formats `10,000` queries using `format` with `100` values, comparing **SQL Escaper** against the original [**sqlstring**](https://github.com/mysqljs/sqlstring) through [**hyperfine**](https://github.com/sharkdp/hyperfine):

|   # | Benchmark                                | sqlstring | SQL Escaper |       Difference |
| --: | ---------------------------------------- | --------: | ----------: | ---------------: |
|   1 | Select 100 values                        |  173.0 ms |    122.0 ms | **1.42x faster** |
|   2 | Insert 100 values                        |  173.4 ms |    126.5 ms | **1.37x faster** |
|   3 | Insert 100 strings requiring escape      |  331.5 ms |    185.1 ms | **1.79x faster** |
|   4 | Insert 100 dates                         |  376.5 ms |    264.0 ms | **1.43x faster** |
|   5 | SET with 100 values                      |  178.7 ms |    140.8 ms | **1.27x faster** |
|   6 | SET with 100 objects                     |  238.4 ms |    158.7 ms | **1.50x faster** |
|   7 | ON DUPLICATE KEY UPDATE with 100 values  |  315.4 ms |    235.6 ms | **1.34x faster** |
|   8 | ON DUPLICATE KEY UPDATE with 100 objects |  369.5 ms |    282.9 ms | **1.31x faster** |
|   9 | Multi-statement SET with 100 objects     |  244.3 ms |    162.2 ms | **1.51x faster** |

- See how the benchmarks are run in the [**benchmark**](https://github.com/mysqljs/sql-escaper/tree/main/benchmark) directory.

> [!NOTE]
>
> Benchmarks ran on [**GitHub Actions**](https://github.com/mysqljs/sql-escaper/blob/main/.github/workflows/ci_benchmark.yml) (`ubuntu-latest`) using **Node.js LTS**.
> Results may vary depending on runner hardware and runtime version.

---

## Caution

> Based on the original [**sqlstring** documentation](https://github.com/mysqljs/sqlstring#readme).

- The escaping methods in this library only work when the [`NO_BACKSLASH_ESCAPES`](https://dev.mysql.com/doc/refman/5.7/en/sql-mode.html#sqlmode_no_backslash_escapes) SQL mode is disabled (which is the default state for **MySQL** servers), unless `noBackslashEscapes` is set in the [SQL mode](#sql-mode) argument.
- This library performs **client-side escaping** to generate SQL strings. The syntax for `format` may look similar to a prepared statement, but it is not one.
- When using `format`, placeholders inside strings (`'...'` and `"..."`), backtick identifiers, and comments (`#`, `/* */`, and `--` followed by whitespace) are left as they are. Executable comments (`/*! */`) and optimizer hints (`/*+ */`) are read as SQL, so their placeholders are replaced.
- When structured user input is provided as the value to escape, validate the shape of the input, as the resulting escaped string may contain more than a single value.
- `NaN` and `Infinity` are left as-is. **MySQL** does not support these values, so trying to insert them triggers an error.
- The string provided to `raw()` will **skip all escaping**, so be careful when passing in unvalidated input.

---

## Security Policy

[![GitHub Workflow Status (with event)](https://img.shields.io/github/actions/workflow/status/mysqljs/sql-escaper/ci_codeql.yml?event=push&label=&branch=main&logo=github&logoColor=white&color=f368e0)](https://github.com/mysqljs/sql-escaper/actions/workflows/ci_codeql.yml?query=branch%3Amain)

Please check the [**SECURITY.md**](https://github.com/mysqljs/sql-escaper/blob/main/SECURITY.md).

---

## Contributing

See the [**Contributing Guide**](https://github.com/mysqljs/sql-escaper/blob/main/CONTRIBUTING.md) and please follow our [**Code of Conduct**](https://github.com/mysqljs/sql-escaper/blob/main/CODE_OF_CONDUCT.md) 🚀

---

## Acknowledgements

- [![Contributors](https://img.shields.io/github/contributors/mysqljs/sql-escaper?label=Contributors)](https://github.com/mysqljs/sql-escaper/graphs/contributors)
- **SQL Escaper** is adapted from [**sqlstring**](https://github.com/mysqljs/sqlstring) ([**MIT**](https://github.com/mysqljs/sqlstring/blob/master/LICENSE)).
- Special thanks to [**Douglas Wilson**](https://github.com/dougwilson) for the original **sqlstring** project and its [**contributors**](https://github.com/mysqljs/sqlstring/graphs/contributors).

---

## License

**SQL Escaper** is under the [**MIT License**](https://github.com/mysqljs/sql-escaper/blob/main/LICENSE).
