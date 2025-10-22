<?php

/**
 * Authentication functions
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Check API key validity
 *
 * @param string $api_key The API key to validate
 * @param string $config_key The valid API key from config
 * @return bool
 */
function validateApiKey($api_key, $config_key) {
    return hash_equals($config_key, $api_key);
}

/**
 * Validate required parameters
 *
 * @param array $data The data to validate
 * @param array $required_params Required parameter names
 * @return array ['valid' => bool, 'missing' => array]
 */
function validateRequired($data, $required_params) {
    $missing = [];

    foreach ($required_params as $param) {
        if (!isset($data[$param]) || trim($data[$param] === '')) {
            $missing[] = $param;
        }
    }

    return [
        'valid' => count($missing) === 0,
        'missing' => $missing
    ];
}

/**
 * Validate numeric value
 *
 * @param mixed $value The value to validate
 * @param string $field Field name for error message
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateNumeric($value, $field = 'value') {
    if (!is_numeric($value)) {
        return ['valid' => false, 'error' => "$field must be numeric"];
    }
    return ['valid' => true, 'error' => null];
}

/**
 * Validate strategy name format
 *
 * @param string $name The strategy name to validate
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateStrategyName($name) {
    if (empty($name) || strlen($name) > 100) {
        return ['valid' => false, 'error' => 'Strategy name must be between 1 and 100 characters'];
    }

    // Allow alphanumeric, underscore, dash, and space
    if (!preg_match('/^[a-zA-Z0-9_\- ]+$/', $name)) {
        return ['valid' => false, 'error' => 'Strategy name contains invalid characters'];
    }

    return ['valid' => true, 'error' => null];
}

/**
 * Validate datetime format (YYYY-MM-DD HH:MM:SS)
 *
 * @param string $datetime The datetime string to validate
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateDatetime($datetime) {
    $format = 'Y-m-d H:i:s';
    $d = DateTime::createFromFormat($format, $datetime);

    if (!$d || $d->format($format) !== $datetime) {
        return ['valid' => false, 'error' => 'Datetime must be in format YYYY-MM-DD HH:MM:SS'];
    }

    return ['valid' => true, 'error' => null];
}
