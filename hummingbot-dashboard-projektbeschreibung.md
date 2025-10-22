# Hummingbot Monitoring Dashboard - MVP Projektbeschreibung

## Übersicht

Einfaches Web-Dashboard zur Überwachung mehrerer automatischer Hummingbot-Trading-Strategien mit Fokus auf Stabilität, Sicherheit und einfache Wartbarkeit.

---

## Technologie-Stack

### Backend
- **PHP 8.x** - Serverseitige Logik
- **SQLite** (Entwicklung) / **MySQL/MariaDB** (Produktion) - Datenbank
- **PDO** - Datenbank-Abstraktion für einfachen DB-Wechsel

### Frontend
- **HTML5** - Struktur
- **CSS3** - Styling (minimal)
- **JavaScript (Vanilla)** - Auto-Refresh
- Optional: **Bootstrap 5** - Grundlegendes, sauberes Design

### Sicherheit
- **HTTP Basic Auth** oder einfaches PHP-Login
- **API-Key-Authentifizierung** für Daten-Import
- **Prepared Statements (PDO)** gegen SQL-Injection
- **HTTPS** (Let's Encrypt in Produktion)

---

## Projektziel

Entwicklung eines minimalistischen Dashboards, das:
- Den aktuellen NAV (Net Asset Value) jeder Strategie anzeigt
- Den Zeitstempel des letzten Updates darstellt
- Einfach erweiterbar ist für zukünftige Features
- Lokal mit SQLite entwickelt werden kann
- Ohne große Anpassungen auf MySQL migriert werden kann

---

## Funktionale Anforderungen (MVP)

### 1. Datenbankstruktur

**Tabelle: strategies**

| Feld | Typ | Eigenschaften |
|------|-----|---------------|
| id | INTEGER/INT | Primary Key, Auto Increment |
| strategy_name | VARCHAR(100) | UNIQUE, NOT NULL |
| nav | DECIMAL(20,8) | NOT NULL |
| last_update | TIMESTAMP/DATETIME | NOT NULL |

**Indizes:**
- Primary Key auf `id`
- Unique Index auf `strategy_name`
- Index auf `last_update` für Performance

### 2. API-Endpoint für Daten-Import

**Endpoint:** `/api/update.php`

**Methode:** POST

**Content-Type:** `application/json`

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

**Error Responses:**

400 Bad Request:
```json
{
  "status": "error",
  "message": "Missing required parameters"
}
```

401 Unauthorized:
```json
{
  "status": "error",
  "message": "Invalid API key"
}
```

**Funktionalität:**
- Validierung aller Eingabeparameter
- API-Key-Prüfung
- UPSERT-Logik:
  - UPDATE wenn strategy_name bereits existiert
  - INSERT wenn strategy_name neu ist
- Fehlerbehandlung und Logging

### 3. Dashboard-Ansicht

**URL:** `/index.php` oder `/`

**Darstellung:**
- Tabelle mit drei Spalten:
  - **Strategy** - Name der Strategie
  - **NAV** - Aktueller Net Asset Value (formatiert mit 2-8 Dezimalstellen)
  - **Last Update** - Zeitstempel im Format DD.MM.YYYY HH:MM:SS

**Features:**
- Sortierung nach Strategy-Namen (alphabetisch aufsteigend)
- Automatischer Refresh alle 60 Sekunden
- Visuelle Status-Indikatoren:
  - 🟢 Grün: Daten < 5 Minuten alt
  - 🟡 Gelb: Daten 5-15 Minuten alt
  - 🔴 Rot: Daten > 15 Minuten alt
- Responsive Design (funktioniert auf Desktop und Mobile)
- Anzeige der Gesamtanzahl aktiver Strategien
- Anzeige des Dashboard-Update-Zeitpunkts

### 4. Sicherheit

**Dashboard-Zugriff:**
- HTTP Basic Authentication über `.htaccess` (einfachste Lösung)
- Alternative: Einfaches PHP-Login mit Session-Management

**API-Sicherheit:**
- API-Key-Authentifizierung für alle Write-Operationen
- Rate Limiting (optional für MVP, empfohlen für Produktion)
- Logging aller API-Zugriffe

**Allgemeine Security-Maßnahmen:**
- Prepared Statements für alle DB-Queries
- Input-Validierung und Sanitization
- Error-Handling ohne Preisgabe sensibler Informationen
- HTTPS in Produktion (Let's Encrypt)
- Keine Versionskontrolldateien (.git) im Web-Root

---

## Projektstruktur

```
/var/www/dashboard/
│
├── index.php                   # Dashboard-Hauptseite
│
├── api/
│   └── update.php             # API-Endpoint für Daten-Import
│
├── config/
│   ├── database.php           # DB-Verbindungs-Konfiguration (PDO)
│   └── config.php             # Allgemeine Konfiguration (API-Keys, etc.)
│
├── includes/
│   ├── auth.php               # Authentifizierungs-Funktionen
│   └── functions.php          # Hilfsfunktionen (Formatierung, etc.)
│
├── assets/
│   ├── css/
│   │   └── style.css          # Minimales Styling
│   └── js/
│       └── dashboard.js       # Auto-Refresh-Funktionalität
│
├── data/
│   └── database.sqlite        # SQLite-Datenbank (Entwicklung)
│   └── .gitignore             # DB nicht in Git committen
│
├── logs/
│   ├── api.log                # API-Zugriffs-Log
│   └── error.log              # Fehler-Log
│
├── sql/
│   ├── sqlite_setup.sql       # SQLite-Setup-Script
│   └── mysql_setup.sql        # MySQL-Setup-Script
│
├── .htaccess                  # Apache-Konfiguration & Auth
├── .htpasswd                  # Passwort-Datei für Basic Auth
├── .gitignore                 # Git-Ignore-Datei
└── README.md                  # Projekt-Dokumentation

```

---

## Datenbankdesign

### SQLite (Entwicklung)

```sql
CREATE TABLE IF NOT EXISTS strategies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    strategy_name VARCHAR(100) UNIQUE NOT NULL,
    nav DECIMAL(20,8) NOT NULL,
    last_update DATETIME NOT NULL
);

CREATE INDEX idx_strategy_name ON strategies(strategy_name);
CREATE INDEX idx_last_update ON strategies(last_update);
```

### MySQL (Produktion)

```sql
CREATE TABLE IF NOT EXISTS strategies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    strategy_name VARCHAR(100) UNIQUE NOT NULL,
    nav DECIMAL(20,8) NOT NULL,
    last_update TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_strategy_name (strategy_name),
    INDEX idx_last_update (last_update)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Migrations-Strategie

**PDO-Konfiguration für einfachen DB-Wechsel:**

```php
// config/database.php

// Entwicklung: SQLite
$db_config = [
    'type' => 'sqlite',
    'path' => __DIR__ . '/../data/database.sqlite'
];

// Produktion: MySQL (auskommentiert)
// $db_config = [
//     'type' => 'mysql',
//     'host' => 'localhost',
//     'dbname' => 'hummingbot_dashboard',
//     'username' => 'db_user',
//     'password' => 'secure_password',
//     'charset' => 'utf8mb4'
// ];
```

---

## Schnittstellen-Spezifikation

### Hummingbot → Dashboard

**Von Hummingbot-Seite zu implementieren:**
wird extern entwickelt, hier nicht relevant

---

## Konfiguration

### Umgebungsvariablen / Konfigurationsdatei

**config/config.php:**

```php
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
```

### .htaccess für Basic Authentication

```apache
AuthType Basic
AuthName "Hummingbot Dashboard"
AuthUserFile /var/www/dashboard/.htpasswd
Require valid-user

# API-Endpoint von Basic Auth ausnehmen
<Files "api/update.php">
    Satisfy Any
    Allow from all
</Files>
```

**Erstellen der .htpasswd-Datei:**

```bash
htpasswd -c /var/www/dashboard/.htpasswd admin
```

---

## Entwicklungsphasen

### Phase 1: MVP 

**Sprint 1 - Setup & Grundstruktur:**
- Projektstruktur aufsetzen
- SQLite-Datenbank erstellen
- Basis-Konfiguration implementieren

**Sprint 2 - API-Entwicklung:**
- API-Endpoint für Daten-Import entwickeln
- API-Key-Authentifizierung implementieren
- Validierung und Fehlerbehandlung
- Logging-Funktionalität

**Sprint 3 - Dashboard-Entwicklung:**
- Dashboard-Ansicht erstellen
- Tabellen-Darstellung implementieren
- Status-Indikatoren (Farbcodierung)
- Auto-Refresh-Funktionalität

**Sprint 4 - Sicherheit & Testing:**
- HTTP Basic Authentication einrichten
- Sicherheitsüberprüfung durchführen
- Manuelle Tests aller Funktionen
- Dokumentation vervollständigen

**Deliverables:**
- Funktionierendes Dashboard mit SQLite
- API-Endpoint für Daten-Import
- Basis-Authentifizierung
- Dokumentation

### Phase 2: Erweiterungen (Zukünftig)

**Mögliche Features:**
- Historische Daten-Anzeige (Charts)
- Detailansicht pro Strategie
- Performance-Metriken (ROI, Sharpe Ratio, etc.)
- Alert-System (E-Mail/Telegram bei Ausfällen)
- Export-Funktionen (CSV, JSON)
- Multi-Timeframe-Ansichten (1h, 24h, 7d, 30d)
- Strategie-Vergleiche
- Dashboard-Customization (User Preferences)

---

## Testing-Strategie

### Manuelle Tests

**API-Tests:**
1. Erfolgreicher Daten-Import mit gültigem API-Key
2. Ablehnung bei ungültigem API-Key
3. Ablehnung bei fehlenden Parametern
4. Ablehnung bei ungültigen Datentypen
5. UPSERT-Funktionalität (neuer Eintrag vs. Update)

**Dashboard-Tests:**
1. Anzeige aller Strategien
2. Korrekte Sortierung
3. Status-Indikatoren (verschiedene Zeitpunkte simulieren)
4. Auto-Refresh-Funktionalität
5. Responsive Design (Desktop, Tablet, Mobile)

**Sicherheitstests:**
1. Zugriff ohne Authentifizierung wird blockiert
2. SQL-Injection-Versuche werden abgewehrt
3. XSS-Versuche werden abgewehrt

### Test-Daten

**SQL-Script für Test-Daten:**

```sql
INSERT INTO strategies (strategy_name, nav, last_update) VALUES
('btc_usdt_market_making', 10250.45678, datetime('now', '-2 minutes')),
('eth_usdt_arbitrage', 5420.12345, datetime('now', '-7 minutes')),
('bnb_btc_grid_trading', 8750.99887, datetime('now', '-20 minutes'));
```

---

## Migration von SQLite zu MySQL

### Schritt-für-Schritt-Anleitung

**1. MySQL-Datenbank vorbereiten:**
```sql
CREATE DATABASE hummingbot_dashboard 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'dashboard_user'@'localhost' 
IDENTIFIED BY 'secure_password_here';

GRANT ALL PRIVILEGES ON hummingbot_dashboard.* 
TO 'dashboard_user'@'localhost';

FLUSH PRIVILEGES;
```

**2. MySQL-Schema erstellen:**
```bash
mysql -u dashboard_user -p hummingbot_dashboard < sql/mysql_setup.sql
```

**3. Daten migrieren (optional):**
```bash
# SQLite-Daten exportieren
sqlite3 data/database.sqlite .dump > sqlite_dump.sql

# Manuell nach MySQL importieren (Syntax anpassen)
# oder: neuer Start mit leerer DB
```

**4. Konfiguration anpassen:**

In `config/database.php` von SQLite auf MySQL umschalten:
```php
// SQLite auskommentieren
// $db_config = [
//     'type' => 'sqlite',
//     'path' => __DIR__ . '/../data/database.sqlite'
// ];

// MySQL aktivieren
$db_config = [
    'type' => 'mysql',
    'host' => 'localhost',
    'dbname' => 'hummingbot_dashboard',
    'username' => 'dashboard_user',
    'password' => 'secure_password_here',
    'charset' => 'utf8mb4'
];
```

**5. Testen:**
- Alle Funktionen testen
- Performance überprüfen
- Logs auf Fehler prüfen

---

## Deployment-Checkliste

### Entwicklungsumgebung (Lokal)

- [ ] PHP 8.0+ installiert
- [ ] SQLite3-Extension aktiviert
- [ ] Verwende php inbuild server php -S locahost:8000
- [ ] Projektstruktur erstellt
- [ ] Datenbank initialisiert
- [ ] API-Key konfiguriert
- [ ] Basic Auth eingerichtet
- [ ] Test-Daten eingefügt

### Produktionsumgebung (Server)

- [ ] Webserver mit PHP 8.0+ und MySQL
- [ ] HTTPS-Zertifikat (Let's Encrypt) installiert
- [ ] MySQL-Datenbank erstellt
- [ ] Datenbank-User mit minimalen Rechten erstellt
- [ ] Produktions-API-Key generiert (stark, zufällig)
- [ ] Produktions-Passwörter für Basic Auth gesetzt
- [ ] Dateiberechtigungen korrekt gesetzt (logs/, data/ schreibbar)
- [ ] Logs-Verzeichnis außerhalb Web-Root oder geschützt
- [ ] .git-Verzeichnis nicht im Web-Root
- [ ] Error-Reporting in Produktion deaktiviert
- [ ] Backup-Strategie implementiert
- [ ] Monitoring eingerichtet (optional)

---

## Sicherheits-Best-Practices

### Checkliste

1. **Authentifizierung:**
   - Starke Passwörter verwenden
   - API-Keys regelmäßig rotieren
   - Session-Timeouts konfigurieren

2. **Datenbank:**
   - Prepared Statements für alle Queries
   - Minimale DB-User-Rechte (kein DROP, ALTER)
   - Regelmäßige Backups

3. **Input-Validierung:**
   - Whitelist-basierte Validierung
   - Typ-Checks für alle Eingaben
   - Längen-Limitierungen

4. **Output-Encoding:**
   - HTML-Entities escapen
   - JSON korrekt kodieren

5. **Server-Konfiguration:**
   - PHP-Version-Disclosure deaktivieren
   - Directory Listing deaktivieren
   - Unnötige PHP-Extensions deaktivieren
   - Sicherheits-Header setzen (X-Frame-Options, etc.)

6. **Logging:**
   - Keine sensitiven Daten loggen
   - Logs regelmäßig rotieren
   - Anomalien monitoren

---

