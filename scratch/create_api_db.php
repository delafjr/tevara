<?php
// ponytail: one-off scratch script, delete after use
try {
    $p = new PDO('mysql:host=127.0.0.1', 'root', '');
    $p->exec('CREATE DATABASE IF NOT EXISTS tevara_api_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo 'DB CREATED' . PHP_EOL;
} catch (Exception $e) {
    echo 'FAIL: ' . $e->getMessage() . PHP_EOL;
}
