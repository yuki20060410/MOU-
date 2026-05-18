<?php
$envPath = file_exists('/var/www/.env')
    ? '/var/www/.env'
    : __DIR__ . '/../../.env';
$env = parse_ini_file($envPath);

$pdo = new PDO(
    'mysql:host=' . $env['DB_HOST'] . ';dbname=' . $env['DB_NAME'] . ';charset=utf8',
    $env['DB_USER'],
    $env['DB_PASS'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);