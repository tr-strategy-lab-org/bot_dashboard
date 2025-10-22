<?php

return [
    // API-Konfiguration
    'api_key' => 'your_secret_api_key_change_this_in_production',

    // Dashboard-Konfiguration
    'dashboard_title' => 'Hummingbot Strategy Monitor',
    'refresh_interval' => 60, // Sekunden

    // Zeitkonfiguration
    'timezone' => 'Europe/Vienna',

    // Alter-Schwellwerte für Status-Indikatoren (Minuten)
    'status_thresholds' => [
        'success' => 5,   // Grün: < 5 Minuten
        'warning' => 15   // Gelb: 5-15 Minuten, Rot: > 15 Minuten
    ],

    // Logging
    'enable_logging' => true,
    'log_directory' => __DIR__ . '/../logs/',

    // NAV-Formatierung
    'nav_decimals' => 4  // Anzahl Dezimalstellen für NAV-Anzeige
];
