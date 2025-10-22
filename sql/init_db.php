<?php

/**
 * Database initialization script
 * Creates SQLite database and tables
 * Usage: php sql/init_db.php
 */

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    // Read SQLite setup script
    $sql = file_get_contents(__DIR__ . '/sqlite_setup.sql');

    // Execute setup script
    $pdo->exec($sql);

    echo "Database initialized successfully!\n";
    echo "Database location: " . __DIR__ . '/../data/database.sqlite' . "\n";

} catch (Exception $e) {
    echo "Error initializing database: " . $e->getMessage() . "\n";
    exit(1);
}
