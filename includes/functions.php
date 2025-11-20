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
 * Converts UTC timestamps from bot to Vienna time (Europe/Vienna)
 *
 * @param string $timestamp ISO timestamp or datetime string (assumed to be UTC from bot)
 * @return string Formatted timestamp in Vienna time
 */
function formatTimestamp($timestamp) {
    try {
        $config = include __DIR__ . '/../config/config.php';

        // Create DateTime object assuming the input is in UTC
        $date = new DateTime($timestamp, new DateTimeZone('UTC'));

        // Convert to configured timezone (Vienna)
        $date->setTimezone(new DateTimeZone($config['timezone']));

        return $date->format('d.m.Y H:i:s');
    } catch (Exception $e) {
        return 'Invalid date';
    }
}

/**
 * Calculate status based on data age
 * Properly handles UTC timestamps from bot and compares with Vienna time
 * Status is sticky: once it reaches red (danger), it stays red
 *
 * @param string $lastUpdate The last update timestamp (assumed to be UTC from bot)
 * @return array ['status' => string, 'indicator' => string, 'time_diff' => string]
 */
function getDataStatus($lastUpdate) {
    $config = include __DIR__ . '/../config/config.php';

    try {
        // Create DateTime object from UTC timestamp (from bot)
        $lastTime = new DateTime($lastUpdate, new DateTimeZone('UTC'));

        // Create current time in Vienna timezone
        $now = new DateTime('now', new DateTimeZone($config['timezone']));

        // Convert last update to Vienna timezone for proper comparison
        $lastTime->setTimezone(new DateTimeZone($config['timezone']));

        // Calculate difference
        $interval = $now->diff($lastTime);
        // Include days in the calculation: days * 1440 minutes + hours * 60 + minutes
        $minutesOld = (int) $interval->format('%d') * 1440 + (int) $interval->format('%h') * 60 + (int) $interval->format('%i');

        // Format time difference with appropriate unit
        $days = (int) $interval->format('%d');
        $hours = (int) $interval->format('%h');
        $minutes = (int) $interval->format('%i');

        if ($days > 0) {
            $timeDiff = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($hours > 0) {
            $timeDiff = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } else {
            $timeDiff = $minutes . ' min ago';
        }

        $thresholds = $config['status_thresholds'];

        // Sticky status: once danger threshold is reached, always show danger
        if ($minutesOld >= $thresholds['warning']) {
            return ['status' => 'danger', 'indicator' => '🔴', 'time_diff' => $timeDiff];
        } elseif ($minutesOld >= $thresholds['success']) {
            return ['status' => 'warning', 'indicator' => '🟡', 'time_diff' => $timeDiff];
        } else {
            return ['status' => 'success', 'indicator' => '🟢', 'time_diff' => $timeDiff];
        }
    } catch (Exception $e) {
        return ['status' => 'unknown', 'indicator' => '⚪', 'time_diff' => 'N/A'];
    }
}

/**
 * Format elapsed time in human-readable format
 * Converts UTC timestamps from bot to configured timezone
 *
 * @param string $timestamp ISO timestamp or datetime string (assumed to be UTC from bot)
 * @return string Formatted elapsed time (e.g., "5 minutes ago", "2 hours ago")
 */
function formatElapsedTime($timestamp) {
    try {
        $config = include __DIR__ . '/../config/config.php';

        // Create DateTime object assuming the input is in UTC
        $date = new DateTime($timestamp, new DateTimeZone('UTC'));

        // Convert to configured timezone
        $date->setTimezone(new DateTimeZone($config['timezone']));

        // Create current time in configured timezone
        $now = new DateTime('now', new DateTimeZone($config['timezone']));

        // Calculate difference
        $interval = $now->diff($date);

        // Format elapsed time
        if ($interval->y > 0) {
            return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
        } elseif ($interval->m > 0) {
            return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
        } elseif ($interval->d > 0) {
            return $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            return 'just now';
        }
    } catch (Exception $e) {
        return 'N/A';
    }
}

/**
 * Get trade time difference in the same format as data status
 * Shows days/hours/minutes appropriately
 *
 * @param string $lastTrade The last trade timestamp (assumed to be UTC from bot)
 * @return string Formatted time difference (e.g., "5 min ago", "2 hours ago", "1 day ago")
 */
function getTradeTimeDiff($lastTrade) {
    try {
        $config = include __DIR__ . '/../config/config.php';

        // Create DateTime object from UTC timestamp (from bot)
        $tradeTime = new DateTime($lastTrade, new DateTimeZone('UTC'));

        // Create current time in configured timezone
        $now = new DateTime('now', new DateTimeZone($config['timezone']));

        // Convert trade time to configured timezone for proper comparison
        $tradeTime->setTimezone(new DateTimeZone($config['timezone']));

        // Calculate difference
        $interval = $now->diff($tradeTime);

        // Format time difference with appropriate unit
        $days = (int) $interval->format('%d');
        $hours = (int) $interval->format('%h');
        $minutes = (int) $interval->format('%i');

        if ($days > 0) {
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($hours > 0) {
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } else {
            return $minutes . ' min ago';
        }
    } catch (Exception $e) {
        return 'N/A';
    }
}

/**
 * Calculate trade status based on last trade age
 * Uses same thresholds as data status for consistent color coding
 * Status is sticky: once it reaches red (danger), it stays red
 *
 * @param string $lastTrade The last trade timestamp (assumed to be UTC from bot)
 * @return array ['status' => string, 'indicator' => string]
 */
function getTradeStatus($lastTrade) {
    $config = include __DIR__ . '/../config/config.php';

    try {
        // Create DateTime object from UTC timestamp (from bot)
        $tradeTime = new DateTime($lastTrade, new DateTimeZone('UTC'));

        // Create current time in configured timezone
        $now = new DateTime('now', new DateTimeZone($config['timezone']));

        // Convert trade time to configured timezone for proper comparison
        $tradeTime->setTimezone(new DateTimeZone($config['timezone']));

        // Calculate difference
        $interval = $now->diff($tradeTime);
        // Include days in the calculation: days * 1440 minutes + hours * 60 + minutes
        $minutesOld = (int) $interval->format('%d') * 1440 + (int) $interval->format('%h') * 60 + (int) $interval->format('%i');

        $thresholds = $config['status_thresholds'];

        // Sticky status: once danger threshold is reached, always show danger
        if ($minutesOld >= $thresholds['warning']) {
            return ['status' => 'danger', 'indicator' => '🔴'];
        } elseif ($minutesOld >= $thresholds['success']) {
            return ['status' => 'warning', 'indicator' => '🟡'];
        } else {
            return ['status' => 'success', 'indicator' => '🟢'];
        }
    } catch (Exception $e) {
        return ['status' => 'unknown', 'indicator' => '⚪'];
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
        $stmt = $pdo->prepare('SELECT id, strategy_name, nav, nav_btc, system_token, fee_currency_balance, fee_currency_balance_usd, last_trade, last_update FROM strategies ORDER BY strategy_name ASC');
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
