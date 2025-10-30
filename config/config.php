<?php

// Load environment configuration
require_once __DIR__ . '/environment.php';

return [
    // Environment
    'environment' => getCurrentEnvironment(),
    'is_development' => isDevelopment(),
    'is_production' => isProduction(),

    // API-Konfiguration
    'api_key' => getConfigEnv('API_KEY', 'your_secret_api_key_change_this_in_production'),

    // Dashboard-Konfiguration
    'dashboard_title' => getConfigEnv('DASHBOARD_TITLE', 'Hummingbot Strategy Monitor'),
    'refresh_interval' => (int) getConfigEnv('REFRESH_INTERVAL', 60), // Sekunden

    // Zeitkonfiguration
    'timezone' => getConfigEnv('TIMEZONE', 'Europe/Vienna'),

    // Alter-Schwellwerte für Status-Indikatoren (Minuten)
    'status_thresholds' => [
        'success' => 5,   // Grün: < 5 Minuten
        'warning' => 15   // Gelb: 5-15 Minuten, Rot: > 15 Minuten
    ],

    // Logging
    'enable_logging' => getConfigEnv('ENABLE_LOGGING', 'true') === 'true',
    'log_directory' => __DIR__ . '/../logs/',

    // NAV-Formatierung
    'nav_decimals' => (int) getConfigEnv('NAV_DECIMALS', 4)  // Anzahl Dezimalstellen für NAV-Anzeige
];
