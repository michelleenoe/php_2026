<?php

require_once __DIR__ . '/private/env.php';
loadEnvFile(__DIR__ . '/.env');

global $_db;

$dbHost = getenv('DB_HOST');
$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $_db = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $ex) {
    error_log("DB CONNECTION FAILED: " . $ex->getMessage());
    throw $ex;
}