# Manuelle Test-Checkliste

## API-Tests

### Test 1: Erfolgreicher Daten-Import
- [ ] API-Endpoint mit gültigem API-Key aufrufen
- [ ] Response-Status: 200
- [ ] Response-Body enthält `"status": "success"`

**Command:**
```bash
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_secret_api_key_change_this_in_production",
    "strategy_name": "test_strategy_1",
    "nav": 10250.45678,
    "timestamp": "2025-10-22 14:30:00"
  }'
```

### Test 2: Ablehnung bei ungültigem API-Key
- [ ] API-Endpoint mit falschem API-Key aufrufen
- [ ] Response-Status: 401
- [ ] Response-Body enthält `"status": "error"`

**Command:**
```bash
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "invalid_key",
    "strategy_name": "test_strategy_1",
    "nav": 10250.45678,
    "timestamp": "2025-10-22 14:30:00"
  }'
```

### Test 3: Ablehnung bei fehlenden Parametern
- [ ] API-Endpoint ohne alle erforderlichen Parameter aufrufen
- [ ] Response-Status: 400
- [ ] Response-Body enthält `"status": "error"`

**Command:**
```bash
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_secret_api_key_change_this_in_production",
    "strategy_name": "test_strategy"
  }'
```

### Test 4: Ablehnung bei ungültigen Datentypen
- [ ] NAV mit Non-Numeric-Wert testen
- [ ] Response-Status: 400

### Test 5: UPSERT-Funktionalität
- [ ] Neue Strategie einfügen (INSERT)
- [ ] Gleiche Strategie mit anderem NAV aktualisieren (UPDATE)
- [ ] Verify in Datenbank, dass nur ein Eintrag existiert

## Dashboard-Tests

### Test 6: Anzeige aller Strategien
- [ ] Dashboard öffnen
- [ ] Alle Strategien in der Tabelle sichtbar
- [ ] Keine Fehlermeldungen in Browser-Console

### Test 7: Korrekte Sortierung
- [ ] Strategien sind alphabetisch nach Name sortiert
- [ ] Sortierung ist aufsteigend

### Test 8: Status-Indikatoren
- [ ] 🟢 Grün für Daten < 5 Minuten alt
- [ ] 🟡 Gelb für Daten 5-15 Minuten alt
- [ ] 🔴 Rot für Daten > 15 Minuten alt

**Test durchführen:**
1. Test-Daten mit verschiedenen Zeiten einfügen
2. Überprüfen, ob Status-Indikatoren korrekt angezeigt werden

### Test 9: Auto-Refresh-Funktionalität
- [ ] Dashboard öffnen
- [ ] Neue Daten via API hinzufügen
- [ ] Nach 60 Sekunden sollten neue Daten sichtbar sein
- [ ] Keine Browser-Fehler

### Test 10: Responsive Design
- [ ] Desktop View (≥1024px) - alle Spalten sichtbar
- [ ] Tablet View (768px-1023px) - Layout passt sich an
- [ ] Mobile View (<768px) - Tabelle scrollbar, lesbar

**Testen mit Browser-DevTools (F12):**
- Mobile Simulator verwenden
- Verschiedene Viewports testen

### Test 11: NAV-Formatierung
- [ ] NAV wird mit korrektem Format angezeigt
- [ ] Dezimalstellen entsprechen config (Standard: 4)
- [ ] Beispiel: `10250,4568` (de-Format mit Komma)

## Sicherheitstests

### Test 12: Zugriff ohne Authentifizierung wird blockiert
- [ ] Dashboard öffnen (ohne Basic Auth)
- [ ] Browser sollte ein Login-Dialog anzeigen
- [ ] Nach Eingabe falscher Anmeldedaten: 401 Unauthorized

### Test 13: SQL-Injection-Versuche werden abgewehrt
**Command:**
```bash
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_secret_api_key_change_this_in_production",
    "strategy_name": "test'; DROP TABLE strategies; --",
    "nav": 10250.45678,
    "timestamp": "2025-10-22 14:30:00"
  }'
```
- [ ] Request wird abgewiesen oder Strategy mit Sonderzeichen wird gespeichert (nicht ausgeführt)
- [ ] Datenbank ist intakt, kein DROP ausgeführt

### Test 14: XSS-Versuche werden abgewehrt
**Command:**
```bash
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_secret_api_key_change_this_in_production",
    "strategy_name": "<img src=x onerror=alert(1)>",
    "nav": 10250.45678,
    "timestamp": "2025-10-22 14:30:00"
  }'
```
- [ ] Dashboard öffnen
- [ ] XSS-Code wird als Text angezeigt, nicht ausgeführt
- [ ] Keine JavaScript-Alerts sollten erscheinen

## Datenbank-Tests

### Test 15: Datenbankverbindung
- [ ] Prüfen Sie, dass `data/database.sqlite` existiert
- [ ] Prüfen Sie, ob die Datenbankdatei beschreibbar ist

**Command:**
```bash
ls -la data/database.sqlite
```

### Test 16: Tabellen-Existenz
- [ ] Prüfen Sie, dass `strategies` Tabelle existiert
- [ ] Prüfen Sie, dass Indizes erstellt wurden

**Command (SQLite):**
```bash
sqlite3 data/database.sqlite
sqlite> .tables
sqlite> .indices
sqlite> .quit
```

### Test 17: Test-Daten einfügen
- [ ] Test-Daten via SQL einfügen
- [ ] Daten im Dashboard angezeigt

**Command:**
```bash
sqlite3 data/database.sqlite < sql/test_data.sql
```

## Performance-Tests

### Test 18: Seitenladezeit
- [ ] Browser-DevTools öffnen (F12)
- [ ] "Network" Tab öffnen
- [ ] Dashboard neu laden
- [ ] Gesamtladezeit sollte < 1 Sekunde sein
- [ ] Alle Assets sollten erfolgreich geladen sein

### Test 19: API-Response-Zeit
- [ ] Multiple API-Requests nacheinander senden
- [ ] Response-Zeit sollte < 500ms sein

**Command:**
```bash
time curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"perf_test","nav":10250.45678,"timestamp":"2025-10-22 14:30:00"}'
```

## Logging-Tests

### Test 20: API-Logging
- [ ] API-Requests durchführen
- [ ] `logs/api.log` überprüfen
- [ ] Einträge sollten Strategie-Name und NAV enthalten

**Command:**
```bash
tail -f logs/api.log
```

### Test 21: Error-Logging
- [ ] API mit ungültigem Request testen
- [ ] `logs/error.log` überprüfen (falls vorhanden)

## Zusammenfassung

- [ ] Alle 21 Tests bestanden
- [ ] Keine kritischen Fehler
- [ ] Performance zufriedenstellend
- [ ] Sicherheit gewährleistet
- [ ] Ready for Production
