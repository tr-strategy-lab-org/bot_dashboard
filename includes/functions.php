<?php

/**
 * Helper functions
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Format NAV value with configurable decimal places
 *
 * @param float $nav The NAV value
 * @param int $decimals Number of decimal places (default from config)
 * @return string Formatted NAV
 */
function formatNav($nav, $decimals = null) {
    $config = include __DIR__ . '/../config/config.php';
    $decimals = $decimals ?? $config['nav_decimals'];
    return number_format(floatval($nav), $decimals, ',', '.');
}

/**
 * Format timestamp to readable format (DD.MM.YYYY HH:MM:SS)
 * Assumes incoming timestamp is UTC and converts to configured timezone
 *
 * @param string $timestamp ISO timestamp or datetime string (assumed to be UTC)
 * @return string Formatted timestamp in local timezone
 */
function formatTimestamp($timestamp) {
    $config = include __DIR__ . '/../config/config.php';

    try {
        // Create DateTime object treating input as UTC
        $date = new DateTime($timestamp, new DateTimeZone('UTC'));

        // Convert to configured timezone
        $date->setTimeZone(new DateTimeZone($config['timezone']));

        return $date->format('d.m.Y H:i:s');
    } catch (Exception $e) {
        return 'Invalid date';
    }
}

/**
 * Calculate status based on data age
 * Assumes lastUpdate timestamp is UTC
 *
 * @param string $lastUpdate The last update timestamp (assumed to be UTC)
 * @return array ['status' => string, 'indicator' => string, 'minutes_old' => int]
 */
function getDataStatus($lastUpdate) {
    $config = include __DIR__ . '/../config/config.php';

    try {
        // Create DateTime objects in UTC timezone
        $lastTime = new DateTime($lastUpdate, new DateTimeZone('UTC'));
        $now = new DateTime('now', new DateTimeZone('UTC'));

        // Calculate the difference
        $interval = $now->diff($lastTime);
        $minutesOld = (int) $interval->format('%i') + ((int) $interval->format('%h') * 60);

        // Add days to minutes calculation
        $minutesOld += (int) $interval->format('%d') * 24 * 60;

        $thresholds = $config['status_thresholds'];

        if ($minutesOld < $thresholds['success']) {
            return ['status' => 'success', 'indicator' => '🟢', 'minutes_old' => $minutesOld];
        } elseif ($minutesOld < $thresholds['warning']) {
            return ['status' => 'warning', 'indicator' => '🟡', 'minutes_old' => $minutesOld];
        } else {
            return ['status' => 'danger', 'indicator' => '🔴', 'minutes_old' => $minutesOld];
        }
    } catch (Exception $e) {
        return ['status' => 'unknown', 'indicator' => '⚪', 'minutes_old' => -1];
    }
}

/**
 * Log API access or error
 *
 * @param string $type Log type: 'api' or 'error'
 * @param string $message Log message
 * @return bool Success
 */
function logMessage($type, $message) {
    $config = include __DIR__ . '/../config/config.php';

    if (!$config['enable_logging']) {
        return false;
    }

    $logDir = $config['log_directory'];

    // Create logs directory if it doesn't exist
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . $type . '.log';
    $timestamp = (new DateTime())->format('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";

    return file_put_contents($logFile, $logEntry, FILE_APPEND) !== false;
}

/**
 * Get all strategies from database
 *
 * @param PDO $pdo Database connection
 * @return array Array of strategies
 */
function getAllStrategies($pdo) {
    try {
        $stmt = $pdo->prepare('SELECT id, strategy_name, nav, last_update FROM strategies ORDER BY strategy_name ASC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logMessage('error', 'Database query error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get current timestamp in format suitable for database
 *
 * @return string Current timestamp in YYYY-MM-DD HH:MM:SS format
 */
function getCurrentTimestamp() {
    return (new DateTime())->format('Y-m-d H:i:s');
}

/**
 * Safely encode output to prevent XSS
 *
 * @param string $string String to encode
 * @return string HTML-encoded string
 */
function safeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Send JSON response and exit
 *
 * @param int $httpCode HTTP status code
 * @param array $data Response data
 */
function sendJsonResponse($httpCode, $data) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
