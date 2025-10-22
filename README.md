# Hummingbot Strategy Monitor Dashboard

Ein minimalistisches Web-Dashboard zur Überwachung mehrerer automatischer Hummingbot-Trading-Strategien.

## Features

- 🎯 Überwachung des aktuellen NAV (Net Asset Value) jeder Strategie
- 📊 Zeitstempel des letzten Updates mit Farbcodierung
- 🟢 Status-Indikatoren basierend auf Datenalter
- 🔄 Automatischer Refresh alle 60 Sekunden
- 📱 Responsive Design für Desktop und Mobile
- 🔐 API-Key-Authentifizierung für Datenimport
- 🔒 HTTP Basic Authentication für Dashboard-Zugriff
- 💾 SQLite (Entwicklung) / MySQL (Produktion)
- 📝 Detailliertes Logging

## Status-Indikatoren

- 🟢 **Grün**: Daten < 5 Minuten alt
- 🟡 **Gelb**: Daten 5-15 Minuten alt
- 🔴 **Rot**: Daten > 15 Minuten alt

## Projektstruktur

```
/var/www/dashboard/
├── index.php                      # Dashboard-Hauptseite
├── api/
│   └── update.php                # API-Endpoint für Daten-Import
├── config/
│   ├── config.php                # Allgemeine Konfiguration
│   └── database.php              # DB-Konfiguration (PDO)
├── includes/
│   ├── auth.php                  # Authentifizierung & Validierung
│   └── functions.php             # Hilfsfunktionen
├── assets/
│   ├── css/
│   │   └── style.css            # Dashboard-Styling
│   └── js/
│       └── dashboard.js         # Auto-Refresh-Funktionalität
├── data/
│   └── database.sqlite          # SQLite-Datenbank
├── logs/
│   ├── api.log                  # API-Zugriffs-Log
│   └── error.log                # Fehler-Log
├── sql/
│   ├── sqlite_setup.sql         # SQLite-Schema
│   ├── mysql_setup.sql          # MySQL-Schema
│   ├── init_db.php              # Datenbank-Initialisierung
│   └── test_data.sql            # Test-Daten
├── tests/
│   ├── ApiTest.php              # PHP Unit Tests
│   └── test_api.sh              # Bash API Tests
├── .htaccess                     # Apache-Konfiguration & Auth
├── .htpasswd                     # Passwort-Datei (wird erstellt)
├── .gitignore                    # Git-Ignore-Datei
└── README.md                     # Diese Datei
```

## Installation

### Voraussetzungen

- PHP 8.0+
- SQLite3 (oder MySQL 5.7+)
- Apache/Nginx mit PHP-Support
- Bash (für Tests)

### Lokale Entwicklung

1. **Projekt klonen:**
   ```bash
   git clone <repository> hummingbot-dashboard
   cd hummingbot-dashboard
   ```

2. **Datenbank initialisieren:**
   ```bash
   sqlite3 data/database.sqlite < sql/sqlite_setup.sql
   ```

3. **PHP-Built-in-Server starten:**
   ```bash
   php -S localhost:8000
   ```

4. **Dashboard öffnen:**
   ```
   http://localhost:8000
   ```

### Produktions-Deployment

#### 1. MySQL-Datenbank vorbereiten:

```sql
CREATE DATABASE hummingbot_dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dashboard_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON hummingbot_dashboard.* TO 'dashboard_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 2. MySQL-Schema erstellen:

```bash
mysql -u dashboard_user -p hummingbot_dashboard < sql/mysql_setup.sql
```

#### 3. Konfiguration anpassen:

Bearbeiten Sie `config/database.php` und aktivieren Sie die MySQL-Einstellungen:

```php
$db_config = [
    'type' => 'mysql',
    'host' => 'localhost',
    'dbname' => 'hummingbot_dashboard',
    'username' => 'dashboard_user',
    'password' => 'secure_password_here',
    'charset' => 'utf8mb4'
];
```

#### 4. Sicherheit einrichten:

**HTTP Basic Authentication erstellen:**
```bash
htpasswd -c /var/www/dashboard/.htpasswd admin
```

**Dateiberechtigungen setzen:**
```bash
chmod 755 /var/www/dashboard
chmod 755 /var/www/dashboard/{config,includes,assets,api,sql,tests}
chmod 644 /var/www/dashboard/{.htaccess,index.php}
chmod 644 /var/www/dashboard/api/update.php
chmod 755 /var/www/dashboard/logs
chmod 755 /var/www/dashboard/data
```

#### 5. API-Key ändern:

Bearbeiten Sie `config/config.php` und setzen Sie einen starken API-Key:

```php
'api_key' => 'your_very_secure_and_random_api_key_here_at_least_32_chars'
```

## Konfiguration

### config/config.php

```php
return [
    'api_key' => 'your_secret_api_key_change_this_in_production',
    'dashboard_title' => 'Hummingbot Strategy Monitor',
    'refresh_interval' => 60,              // Sekunden
    'timezone' => 'Europe/Vienna',
    'status_thresholds' => [
        'success' => 5,                    // Grün: < 5 Minuten
        'warning' => 15                    // Gelb: 5-15 Minuten
    ],
    'enable_logging' => true,
    'log_directory' => __DIR__ . '/../logs/',
    'nav_decimals' => 4                    // Dezimalstellen für NAV
];
```

## API-Referenz

### POST /api/update.php

Endpoint zum Aktualisieren von Strategiedaten.

**Request Body:**
```json
{
  "api_key": "your_secret_api_key_here",
  "strategy_name": "btc_usdt_strategy_1",
  "nav": 10250.45678900,
  "timestamp": "2025-10-22 14:30:00"
}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Data updated successfully",
  "strategy": "btc_usdt_strategy_1"
}
```

**Error Response (400/401/500):**
```json
{
  "status": "error",
  "message": "Error description"
}
```

**HTTP Status Codes:**
- `200`: Erfolgreiche Aktualisierung
- `400`: Ungültige Parameter
- `401`: Ungültiger API-Key
- `405`: Ungültige HTTP-Methode
- `500`: Interner Fehler

### Beispiel-Request mit cURL:

```bash
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_secret_api_key_here",
    "strategy_name": "btc_usdt_market_making",
    "nav": 10250.45678,
    "timestamp": "2025-10-22 14:30:00"
  }'
```

### Beispiel-Request mit Python:

```python
import requests
import json
from datetime import datetime

url = "http://localhost:8000/api/update.php"
headers = {"Content-Type": "application/json"}

data = {
    "api_key": "your_secret_api_key_here",
    "strategy_name": "btc_usdt_market_making",
    "nav": 10250.45678,
    "timestamp": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
}

response = requests.post(url, headers=headers, json=data)
print(response.json())
```

## Tests

### API-Tests mit cURL/Bash:

```bash
bash tests/test_api.sh
```

### PHP Unit Tests:

```bash
php tests/ApiTest.php
```

## Datenbank

### SQLite (Entwicklung)

**Datenbank initialisieren:**
```bash
sqlite3 data/database.sqlite < sql/sqlite_setup.sql
```

**Test-Daten einfügen:**
```bash
sqlite3 data/database.sqlite < sql/test_data.sql
```

**Datenbank anschauen:**
```bash
sqlite3 data/database.sqlite
sqlite> SELECT * FROM strategies;
sqlite> .quit
```

### MySQL (Produktion)

**Datenbank initialisieren:**
```bash
mysql -u dashboard_user -p hummingbot_dashboard < sql/mysql_setup.sql
```

**Test-Daten einfügen (mit SQL-Anpassung):**
```bash
mysql -u dashboard_user -p hummingbot_dashboard <<EOF
INSERT INTO strategies (strategy_name, nav, last_update) VALUES
('btc_usdt_market_making', 10250.45678, NOW()),
('eth_usdt_arbitrage', 5420.12345, NOW()),
('bnb_btc_grid_trading', 8750.99887, NOW());
EOF
```

### Tabelle: strategies

| Spalte | Typ | Beschreibung |
|--------|-----|-------------|
| id | INTEGER/INT | Primary Key |
| strategy_name | VARCHAR(100) | Eindeutiger Name der Strategie |
| nav | DECIMAL(20,8) | Net Asset Value |
| last_update | DATETIME | Zeitstempel der letzten Aktualisierung |

## Sicherheit

### Checkliste

- [x] Prepared Statements für alle DB-Queries
- [x] Input-Validierung und Sanitization
- [x] API-Key-Authentifizierung
- [x] HTTP Basic Authentication
- [x] Error-Handling ohne Preisgabe sensibler Informationen
- [x] XSS-Protection (HTML Entity Encoding)
- [ ] HTTPS in Produktion (Let's Encrypt)
- [ ] Rate Limiting (optional)
- [ ] CORS-Header (wenn APIs extern genutzt werden)

### Best Practices

1. **API-Key regelmäßig ändern**
2. **Starke Passwörter für Basic Auth verwenden**
3. **Logs regelmäßig überprüfen auf Anomalien**
4. **HTTPS in Produktion aktivieren**
5. **Regelmäßige Backups der Datenbank**
6. **PHP Error Display in Produktion deaktivieren**

## Troubleshooting

### Datenbank-Fehler

**Problem:** "Database connection failed"
- Prüfen Sie, ob die Datenbank initialisiert wurde
- Prüfen Sie die Dateiberechtigungen für `data/` und `logs/`

### API-Key Fehler

**Problem:** "Invalid API key"
- Prüfen Sie, ob der API-Key in `config/config.php` korrekt ist
- Prüfen Sie, ob der Key in der Request identisch ist

### Auto-Refresh funktioniert nicht

**Problem:** Dashboard wird nicht aktualisiert
- Öffnen Sie die Browser-Console (F12) und prüfen Sie auf Fehler
- Prüfen Sie, ob der Webserver läuft
- Prüfen Sie die Netzwerkverbindung

## Logs

### API-Log (`logs/api.log`)

```
[2025-10-22 14:30:45] Strategy 'btc_usdt_market_making' updated successfully. NAV: 10250.45678, Timestamp: 2025-10-22 14:30:00
[2025-10-22 14:35:10] Invalid API key attempt
[2025-10-22 14:40:22] Missing parameters: nav, timestamp
```

### Error-Log (`logs/error.log`)

```
[2025-10-22 14:30:45] Database query error: database disk image is malformed
```

## Performance

### Optimierungen

- Indizes auf `strategy_name` und `last_update` für schnelle Queries
- Prepared Statements verhindern SQL-Injection
- Auto-Refresh nur bei Sichtbarkeit der Seite
- Minimales CSS/JS - keine jQuery oder andere große Frameworks

## Erweiterungen (Phase 2)

Zukünftige Features:

- [ ] Historische Daten-Anzeige (Charts mit Chart.js)
- [ ] Detailansicht pro Strategie
- [ ] Performance-Metriken (ROI, Sharpe Ratio)
- [ ] Alert-System (E-Mail/Telegram)
- [ ] Export-Funktionen (CSV, JSON)
- [ ] Multi-Timeframe-Ansichten
- [ ] Strategie-Vergleiche
- [ ] Dashboard-Customization

## Lizenz

MIT

## Support

Für Probleme oder Vorschläge bitte ein Issue auf GitHub öffnen.

## Author

Matthias
