<?php

// Load environment configuration
require_once __DIR__ . '/environment.php';

// Determine database configuration based on environment
if (isDevelopment()) {
    // Development: SQLite
    $db_config = [
        'type' => getConfigEnv('DB_TYPE_DEV', 'sqlite'),
        'path' => getConfigEnv('DB_PATH_DEV', __DIR__ . '/../data/database.sqlite')
    ];
} else {
    // Production: MySQL
    $db_config = [
        'type' => getConfigEnv('DB_TYPE_PROD', 'mysql'),
        'host' => getConfigEnv('DB_HOST_PROD', 'localhost'),
        'dbname' => getConfigEnv('DB_NAME_PROD', 'hummingbot_dashboard'),
        'username' => getConfigEnv('DB_USER_PROD', 'dashboard_user'),
        'password' => getConfigEnv('DB_PASS_PROD', ''),
        'charset' => getConfigEnv('DB_CHARSET_PROD', 'utf8mb4')
    ];
}

/**
 * Get PDO database connection based on environment
 */
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
