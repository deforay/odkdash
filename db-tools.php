<?php

/**
 * db-tools configuration — read by vendor/bin/db-tools.
 *
 * Pulls DB credentials from the same Laminas autoload files the app uses
 * (config/autoload/global.php + local.php), so db-tools always targets the
 * exact database the running app talks to. No .env, no duplicated config.
 */

declare(strict_types=1);

$autoloadDir = __DIR__ . '/config/autoload';

// Merge global + local the way Laminas would (later files win). global.php
// is the gitignored per-environment file; global.dist.php is the template.
// Either may be present at any given time on a freshly-cloned repo.
$merged = [];
$globals = glob($autoloadDir . '/{,*.}global.php', GLOB_BRACE) ?: [];
$locals  = glob($autoloadDir . '/{,*.}local.php', GLOB_BRACE) ?: [];
foreach (array_merge($globals, $locals) as $file) {
    $config = require $file;
    if (is_array($config)) {
        $merged = array_replace_recursive($merged, $config);
    }
}

// Extract host + dbname from the DSN string (mysql:dbname=foo;host=bar).
$dsn = (string) ($merged['db']['dsn'] ?? '');
$host = 'localhost';
$database = '';
if (preg_match('/host=([^;]+)/', $dsn, $m)) {
    $host = $m[1];
}
if (preg_match('/dbname=([^;]+)/', $dsn, $m)) {
    $database = $m[1];
}

// Port may be in DSN or default to 3306.
$port = 3306;
if (preg_match('/port=(\d+)/', $dsn, $m)) {
    $port = (int) $m[1];
}

// Credentials live in local.php under the long-form keys this repo has
// historically used. Allow both naming styles for safety.
$dbConfig  = $merged['db'] ?? [];
$user      = $dbConfig['username']      ?? $dbConfig['user']     ?? 'root';
$password  = $dbConfig['password']      ?? '';
$dbNameAlt = $dbConfig['data-base-name'] ?? null;
$hostAlt   = $dbConfig['data-base-host'] ?? null;
if ($database === '' && $dbNameAlt !== null) {
    $database = (string) $dbNameAlt;
}
if ($hostAlt !== null) {
    $host = (string) $hostAlt;
}

$backupDir = __DIR__ . '/var/backups/db';
if (!is_dir($backupDir)) {
    @mkdir($backupDir, 0755, true);
}

return [
    'odkdash' => [
        'host'       => $host,
        'port'       => $port,
        'database'   => $database,
        'user'       => $user,
        'password'   => $password,
        'output_dir' => $backupDir,
        'retention'  => 7,
    ],
];
