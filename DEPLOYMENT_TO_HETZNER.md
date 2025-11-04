# Deployment zu Hetzner - Working Version

**Diese Version funktioniert! ✓**

Commit: `3227a4d` - Getestet und funktioniert auf Hetzner.

---

## Kurze Zusammenfassung

- ✅ Dashboard zeigt alle Strategien an
- ✅ API akzeptiert neue Daten
- ✅ Timestamps werden korrekt in UTC gespeichert und in lokaler Zeitzone angezeigt
- ✅ Total NAV wird berechnet und angezeigt
- ✅ Auto-Refresh alle 60 Sekunden
- ✅ Logging funktioniert

---

## Deployment - 3 Schritte

### Schritt 1: Clean Up auf Hetzner

Via Cyberduck:

1. Verbinde zu Hetzner
2. Navigiere zu `/home/deinbenutzername/html/`
3. **Lösche ALLES** (außer `.htaccess` wenn du das brauchst)
4. Fertig ✓

### Schritt 2: Upload diese Dateien/Ordner

Drag & Drop in Cyberduck zu `/home/deinbenutzername/html/`:

```
📁 api/
📁 config/
📁 includes/
📁 assets/
📁 data/              (mit database.sqlite!)
📁 logs/              (erstelle neuen leeren Ordner oder lass leer)
📁 sql/               (optional, nur für Referenz)
📄 index.php
📄 .htaccess
📄 README.md          (optional)
```

**Das ist alles was du hochladen musst!**

### Schritt 3: Test

Öffne im Browser:

```
https://dashboard.tr-strategy-lab.com/
```

Sollte funktionieren! 🎉

---

## API Nutzen

```bash
curl -X POST https://dashboard.tr-strategy-lab.com/api/update.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_secret_api_key_change_this_in_production",
    "strategy_name": "btc_trading",
    "nav": 15000.50,
    "timestamp": "2025-10-30 17:30:00"
  }'
```

**Wichtig:**
- `api_key` muss gleich sein wie in `config/config.php` Zeile 5
- `timestamp` muss im Format `YYYY-MM-DD HH:MM:SS` sein
- Die Zeit wird in UTC gespeichert und in Europe/Vienna angezeigt

---

## Konfiguration (auf lokalem Mac)

Wenn du etwas ändern möchtest, editiere `config/config.php`:

```php
return [
    'api_key' => 'your_secret_api_key_change_this_in_production',  // API Key
    'dashboard_title' => 'Hummingbot Strategy Monitor',              // Titel
    'refresh_interval' => 60,                                        // Auto-Refresh Sekunden
    'timezone' => 'Europe/Vienna',                                   // Zeitzone
    'nav_decimals' => 4,                                             // Dezimalstellen für NAV
];
```

Nach Änderungen: `git add config/config.php` → Upload zu Hetzner

---

## Struktur nach Upload

Nach dem Upload sollte auf Hetzner folgende Struktur sein:

```
/home/deinbenutzername/html/
├── index.php
├── .htaccess
├── api/
│   └── update.php
├── config/
│   ├── auth.php
│   ├── config.php
│   └── database.php
├── includes/
│   ├── auth.php
│   └── functions.php
├── assets/
│   ├── css/style.css
│   └── js/dashboard.js
├── data/
│   └── database.sqlite
├── logs/              (wird beim ersten API-Call erstellt)
└── sql/               (optional)
```

---

## Problembehebung

### "Internal Server Error"

1. Teste eine einfache `.html` Datei - funktioniert PHP überhaupt?
2. Kontaktiere Hetzner Support - PHP könnte deaktiviert sein
3. Check Dateiberechtigungen: Dateien sollten `644`, Ordner `755` sein

### Dashboard zeigt "Database connection failed"

1. Überprüfe, dass `data/database.sqlite` hochgeladen wurde
2. Überprüfe Dateigröße - sollte > 20KB sein
3. Überprüfe Berechtigung: `chmod 644 data/database.sqlite`

### API-Key funktioniert nicht

1. Überprüfe: `grep api_key config/config.php`
2. Verwende den EXAKTEN Wert in deinem API-Call

### Timestamps sind falsch

1. Das ist normal - wird als UTC gespeichert, in Europe/Vienna angezeigt
2. Wenn UTC nicht korrekt ist, überprüfe das System des Hummingbot-Senders

---

## Lokale Entwicklung

Zum Entwickeln lokal:

```bash
cd /Users/matthias/repos/bot_dashboard
php -S localhost:8000
```

Öffne: `http://localhost:8000`

---

## Wichtige Dateien

- **`config/config.php`** - Hauptkonfiguration
- **`config/database.php`** - Datenbankverbindung
- **`api/update.php`** - API Endpoint
- **`includes/functions.php`** - Hilfsfunktionen
- **`index.php`** - Dashboard HTML
- **`data/database.sqlite`** - Datenbank (SQLite)

---

## Support

Diese Version wurde ausführlich getestet und funktioniert!

Wenn Probleme auftreten:
1. Überprüfe Dateiberechtigungen
2. Überprüfe `.htaccess`
3. Kontaktiere Hetzner Support für PHP-Probleme

---

**Ready to go!** 🚀
