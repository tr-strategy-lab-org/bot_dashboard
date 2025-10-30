# Development Quick Commands

Keep this file bookmarked for quick reference while developing.

## Starting & Stopping

```bash
# Start development server (run this FIRST)
cd /Users/matthias/repos/bot_dashboard
php -S localhost:8000

# Stop the server
Ctrl + C
```

## Accessing the Application

```
Dashboard:    http://localhost:8000
API Endpoint: http://localhost:8000/api/update.php (POST only)
```

## Testing the API

```bash
# Add a test strategy
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"test_strategy","nav":10000.00,"timestamp":"2025-10-30 10:30:00"}'

# Update existing strategy
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"test_strategy","nav":15000.00,"timestamp":"2025-10-30 11:00:00"}'
```

## Database Commands (SQLite)

```bash
# View all strategies
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  "SELECT strategy_name, nav, last_update FROM strategies ORDER BY strategy_name;"

# View specific strategy
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  "SELECT * FROM strategies WHERE strategy_name='test_strategy';"

# Count strategies
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  "SELECT COUNT(*) FROM strategies;"

# Delete one strategy
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  "DELETE FROM strategies WHERE strategy_name='test_strategy';"

# Delete ALL strategies (warning!)
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  "DELETE FROM strategies;"

# View table structure
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  ".schema strategies"
```

## Logging & Debugging

```bash
# View API logs (real-time)
tail -f /Users/matthias/repos/bot_dashboard/logs/api.log

# View last 20 API log entries
tail -20 /Users/matthias/repos/bot_dashboard/logs/api.log

# View error logs
tail -f /Users/matthias/repos/bot_dashboard/logs/error.log

# Search logs for specific strategy
grep "strategy_name" /Users/matthias/repos/bot_dashboard/logs/api.log
```

## Configuration

```bash
# View current environment
cat /Users/matthias/repos/bot_dashboard/.env | grep ENVIRONMENT

# View API key
cat /Users/matthias/repos/bot_dashboard/.env | grep API_KEY

# View all configuration
cat /Users/matthias/repos/bot_dashboard/.env
```

## File Management

```bash
# View project structure
ls -la /Users/matthias/repos/bot_dashboard/

# View data directory
ls -la /Users/matthias/repos/bot_dashboard/data/

# View logs directory
ls -la /Users/matthias/repos/bot_dashboard/logs/

# Check database file size
ls -lh /Users/matthias/repos/bot_dashboard/data/database.sqlite
```

## Code Files (Edit These)

```
/config/config.php            → Main configuration
/config/database.php          → Database setup
/config/environment.php       → Environment loader
/includes/functions.php       → Helper functions
/includes/auth.php            → Authentication
/api/update.php               → API endpoint
/index.php                    → Dashboard view
/assets/css/style.css         → Styling
/assets/js/dashboard.js       → Auto-refresh script
```

## Common Scenarios

### Scenario: Development & Testing
```bash
# Terminal 1: Start server
php -S localhost:8000

# Terminal 2: Add test data
curl -X POST http://localhost:8000/api/update.php ...

# Terminal 3: Monitor logs
tail -f logs/api.log
```

### Scenario: Clear and Restart
```bash
# Stop server
Ctrl + C

# Clear database
sqlite3 data/database.sqlite "DELETE FROM strategies;"

# Restart server
php -S localhost:8000
```

### Scenario: Check Everything Works
```bash
# 1. Start server
php -S localhost:8000

# 2. Test dashboard loads
curl http://localhost:8000 | head -20

# 3. Test API works
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"test","nav":1000,"timestamp":"2025-10-30 10:00:00"}'

# 4. Check database
sqlite3 data/database.sqlite "SELECT * FROM strategies WHERE strategy_name='test';"

# 5. View logs
tail -5 logs/api.log
```

## Ports

- **8000** (primary) - `php -S localhost:8000`
- **8001** (alternate) - `php -S localhost:8001` (if 8000 is busy)
- **8080** (alternate) - `php -S localhost:8080`

## Tips

- Keep Terminal 1 running the server (don't close it)
- Use Terminal 2 for test commands
- Use Terminal 3 for monitoring logs
- Browser auto-refreshes every 60 seconds
- Data persists in SQLite until you delete it
- Logs are helpful for debugging

## Environment Info

```bash
# PHP version
php -v

# PHP modules
php -m | grep -i pdo

# SQLite version
sqlite3 --version

# Timezone (from .env)
cat .env | grep TIMEZONE
```

---

**Keep this file open in your editor while developing!**
