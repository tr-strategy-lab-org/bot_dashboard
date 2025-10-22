# Quick Start Guide

## 🚀 Schneller Einstieg

### Lokale Entwicklung (ohne PHP Installation)

Wenn Sie PHP lokal noch nicht haben, können Sie die Datenbank bereits initialisiert im Repository vorfinden.

### 1. Projekt klonen und in das Verzeichnis gehen

```bash
cd /Users/matthias/repos/bot_dashboard
```

### 2. Datenbank ist bereits initialisiert

Die SQLite-Datenbank wurde bereits erstellt:
```bash
ls -la data/database.sqlite
```

### 3. Datenbank-Tabellen überprüfen

```bash
sqlite3 data/database.sqlite ".schema strategies"
```

### 4. Test-Daten einfügen (optional)

```bash
sqlite3 data/database.sqlite < sql/test_data.sql
```

Daten überprüfen:
```bash
sqlite3 data/database.sqlite "SELECT * FROM strategies;"
```

## 📋 Projektstruktur

```
bot_dashboard/
├── index.php                      # Dashboard-Hauptseite
├── api/update.php                # API für Daten-Import
├── config/
│   ├── config.php               # Konfiguration
│   └── database.php             # DB-Konfiguration
├── includes/
│   ├── auth.php                 # Validierung
│   └── functions.php            # Helper-Funktionen
├── assets/
│   ├── css/style.css           # Styling
│   └── js/dashboard.js         # Auto-Refresh
├── sql/
│   ├── sqlite_setup.sql        # SQLite-Schema
│   ├── mysql_setup.sql         # MySQL-Schema
│   └── test_data.sql           # Test-Daten
├── data/database.sqlite        # SQLite-DB
└── tests/                       # Test-Dateien
```

## 🔧 Konfiguration

### API-Key ändern

**WICHTIG:** Vor Produktivstart ändern!

Bearbeiten Sie `config/config.php`:
```php
'api_key' => 'your_very_secure_random_api_key_at_least_32_chars'
```

### Zeitzone anpassen (optional)

In `config/config.php`:
```php
'timezone' => 'Europe/Berlin',  // oder andere Zeitzone
```

### NAV-Dezimalstellen (optional)

In `config/config.php`:
```php
'nav_decimals' => 4  // Anzahl der Dezimalstellen
```

## 🧪 API Testen

### Mit cURL (einfaches Test)

```bash
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_secret_api_key_change_this_in_production",
    "strategy_name": "btc_usdt_test",
    "nav": 10250.45678,
    "timestamp": "2025-10-22 14:30:00"
  }'
```

**Erwartete Response:**
```json
{
  "status": "success",
  "message": "Data updated successfully",
  "strategy": "btc_usdt_test"
}
```

### Mit Python

```python
import requests
from datetime import datetime

url = "http://localhost:8000/api/update.php"
data = {
    "api_key": "your_secret_api_key_change_this_in_production",
    "strategy_name": "eth_usdt_test",
    "nav": 5420.12345,
    "timestamp": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
}

response = requests.post(url, json=data)
print(response.json())
```

## 📊 Dashboard Features

- 🟢 **Grün**: Daten < 5 Min alt
- 🟡 **Gelb**: Daten 5-15 Min alt
- 🔴 **Rot**: Daten > 15 Min alt

Dashboard aktualisiert sich automatisch alle 60 Sekunden.

## 🔒 Sicherheit

### Für Produktion notwendig:

1. **API-Key ändern** (stark und zufällig, min. 32 Zeichen)
2. **Passwort für HTTP Basic Auth setzen**
3. **HTTPS aktivieren** (Let's Encrypt)
4. **Datenbank-Passwörter ändern** (wenn MySQL)
5. **Logs regelmäßig überprüfen**

## 📝 Datenbanktypen

### Entwicklung (SQLite)
- Datei-basiert, keine Server notwendig
- Datei: `data/database.sqlite`

### Produktion (MySQL)
- In `config/database.php` MySQL-Einstellungen aktivieren
- Datenbank mit `sql/mysql_setup.sql` initialisieren

## 🐛 Troubleshooting

### "Database connection failed"
```bash
# Überprüfen ob Datei existiert
ls -la data/database.sqlite

# Überprüfen ob Ordner schreibbar ist
chmod 755 data/
chmod 755 logs/
```

### "Invalid API key"
- API-Key in Request mit config.php vergleichen
- Auf Whitespace prüfen

### Dashboard zeigt keine Daten
- Prüfen ob Daten in DB existieren:
  ```bash
  sqlite3 data/database.sqlite "SELECT COUNT(*) FROM strategies;"
  ```
- Browser-Cache leeren (Ctrl+Shift+Del)

## 📚 Weitere Dokumentation

- Ausführliche Dokumentation: `README.md`
- Manuelle Test-Checkliste: `tests/test_manual.md`
- Projekt-Anforderungen: `hummingbot-dashboard-projektbeschreibung.md`

## 🎯 Nächste Schritte

1. **Test-Daten einfügen**: `sqlite3 data/database.sqlite < sql/test_data.sql`
2. **API testen**: siehe API Testen oben
3. **Dashboard ansehen**: `http://localhost:8000`
4. **Logs überprüfen**: `tail -f logs/api.log`

Viel Erfolg! 🚀
