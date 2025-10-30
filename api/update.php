<?php

/**
 * API Endpoint for updating strategy data
 *
 * POST /api/update.php
 * Content-Type: application/json
 *
 * Request body:
 * {
 *   "api_key": "your_secret_api_key_here",
 *   "strategy_name": "btc_usdt_strategy_1",
 *   "nav": 10250.45678900,
 *   "timestamp": "2025-10-22 14:30:00"
 * }
 */

// Set headers
header('Content-Type: application/json; charset=utf-8');

// Enable error logging but disable output
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Requires
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(405, [
        'status' => 'error',
        'message' => 'Method not allowed. Use POST.'
    ]);
}

// Get configuration
$config = include __DIR__ . '/../config/config.php';

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($input === null) {
    logMessage('api', 'Invalid JSON received');
    sendJsonResponse(400, [
        'status' => 'error',
        'message' => 'Invalid JSON format'
    ]);
}

// Validate required parameters
$required = ['api_key', 'strategy_name', 'nav', 'timestamp'];
$validation = validateRequired($input, $required);

if (!$validation['valid']) {
    logMessage('api', 'Missing parameters: ' . implode(', ', $validation['missing']));
    sendJsonResponse(400, [
        'status' => 'error',
        'message' => 'Missing required parameters: ' . implode(', ', $validation['missing'])
    ]);
}

// Validate API key
if (!validateApiKey($input['api_key'], $config['api_key'])) {
    logMessage('api', 'Invalid API key attempt');
    sendJsonResponse(401, [
        'status' => 'error',
        'message' => 'Invalid API key'
    ]);
}

// Validate strategy name
$nameValidation = validateStrategyName($input['strategy_name']);
if (!$nameValidation['valid']) {
    logMessage('api', 'Invalid strategy name: ' . $nameValidation['error']);
    sendJsonResponse(400, [
        'status' => 'error',
        'message' => $nameValidation['error']
    ]);
}

// Validate NAV
$navValidation = validateNumeric($input['nav'], 'NAV');
if (!$navValidation['valid']) {
    logMessage('api', $navValidation['error']);
    sendJsonResponse(400, [
        'status' => 'error',
        'message' => $navValidation['error']
    ]);
}

// Validate datetime
$datetimeValidation = validateDatetime($input['timestamp']);
if (!$datetimeValidation['valid']) {
    logMessage('api', $datetimeValidation['error']);
    sendJsonResponse(400, [
        'status' => 'error',
        'message' => $datetimeValidation['error']
    ]);
}

try {
    // Get database connection
    $pdo = getPDO();

    // Sanitize inputs
    $strategyName = trim($input['strategy_name']);
    $nav = floatval($input['nav']);
    $timestamp = $input['timestamp'];

    // Store UTC timestamp as-is in the database
    // The incoming timestamp is assumed to be in UTC format
    // Display layer will convert to configured timezone
    $storedTimestamp = $timestamp;

    // UPSERT logic: Try INSERT, if strategy_name exists, UPDATE
    $stmt = $pdo->prepare('
        INSERT INTO strategies (strategy_name, nav, last_update)
        VALUES (:strategy_name, :nav, :timestamp)
        ON CONFLICT(strategy_name) DO UPDATE SET
            nav = :nav,
            last_update = :timestamp
    ');

    // For SQLite compatibility, we use a different approach
    // First check if strategy exists
    $checkStmt = $pdo->prepare('SELECT id FROM strategies WHERE strategy_name = ?');
    $checkStmt->execute([$strategyName]);
    $exists = $checkStmt->fetch();

    if ($exists) {
        // UPDATE existing strategy
        $updateStmt = $pdo->prepare('
            UPDATE strategies
            SET nav = ?, last_update = ?
            WHERE strategy_name = ?
        ');
        $updateStmt->execute([$nav, $storedTimestamp, $strategyName]);
    } else {
        // INSERT new strategy
        $insertStmt = $pdo->prepare('
            INSERT INTO strategies (strategy_name, nav, last_update)
            VALUES (?, ?, ?)
        ');
        $insertStmt->execute([$strategyName, $nav, $storedTimestamp]);
    }

    logMessage('api', "Strategy '{$strategyName}' updated successfully. NAV: {$nav}, Timestamp (UTC): {$timestamp}");

    sendJsonResponse(200, [
        'status' => 'success',
        'message' => 'Data updated successfully',
        'strategy' => $strategyName
    ]);

} catch (Exception $e) {
    logMessage('error', 'Database operation error: ' . $e->getMessage());
    sendJsonResponse(500, [
        'status' => 'error',
        'message' => 'Internal server error'
    ]);
}
