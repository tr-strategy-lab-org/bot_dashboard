<?php

/**
 * Environment Configuration Loader
 *
 * Loads environment variables from .env file and provides
 * environment-specific configuration for development and production
 */

// Load .env file if it exists
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos($line, '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }

            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

/**
 * Get environment variable with fallback
 */
function getConfigEnv($key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

/**
 * Get current environment
 */
function getCurrentEnvironment() {
    return getEnv('ENVIRONMENT', 'development');
}

/**
 * Check if running in development mode
 */
function isDevelopment() {
    return getCurrentEnvironment() === 'development';
}

/**
 * Check if running in production mode
 */
function isProduction() {
    return getCurrentEnvironment() === 'production';
}
