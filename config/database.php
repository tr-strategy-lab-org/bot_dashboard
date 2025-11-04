<?php

// Entwicklung: SQLite
$db_config = [
    'type' => 'sqlite',
    'path' => __DIR__ . '/../data/database.sqlite'
];

// Produktion: MySQL (auskommentiert)
// $db_config = [
//     'type' => 'mysql',
//     'host' => 'localhost',
//     'dbname' => 'hummingbot_dashboard',
//     'username' => 'db_user',
//     'password' => 'secure_password',
//     'charset' => 'utf8mb4'
// ];

function getPDO() {
    global $db_config;

    if ($db_config['type'] === 'sqlite') {
        // SQLite-Verbindung
        $dsn = 'sqlite:' . $db_config['path'];
        try {
            $pdo = new PDO($dsn);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            die('Database connection failed');
        }
    } elseif ($db_config['type'] === 'mysql') {
        // MySQL-Verbindung
        $dsn = 'mysql:host=' . $db_config['host'] . ';dbname=' . $db_config['dbname'] . ';charset=' . $db_config['charset'];
        try {
            $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            die('Database connection failed');
        }
    }
}
