
# How to Update the Test Database

This guide shows you all the ways to add, update, and manage test data in your development environment.

## Overview

You have **3 main ways** to update the database:

1. **API Endpoint** (Recommended for testing) - Most realistic
2. **SQLite CLI** - Direct database manipulation
3. **Python Script** - Batch operations (advanced)

---

## Method 1: Using the API Endpoint (Recommended)

This is the most realistic way since it tests your actual API.

### Prerequisites

- Development server running: `php -S localhost:8000`
- Terminal window for API calls

### Add a New Strategy

```bash
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_secret_api_key_change_this_in_production",
    "strategy_name": "btc_trading_bot",
    "nav": 15000.50,
    "timestamp": "2025-10-30 10:30:00"
  }'
```

**Expected response:**
```json
{
  "status": "success",
  "message": "Data updated successfully",
  "strategy": "btc_trading_bot"
}
```

### Update an Existing Strategy

Use the same command with the same `strategy_name` but different `nav` and `timestamp`:

```bash
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_secret_api_key_change_this_in_production",
    "strategy_name": "btc_trading_bot",
    "nav": 16200.75,
    "timestamp": "2025-10-30 11:15:00"
  }'
```

The API will automatically **UPDATE** the existing strategy (UPSERT logic).

### Add Multiple Strategies at Once

Open Terminal 2 and run these commands one by one:

```bash
# Strategy 1
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"eth_arbitrage","nav":5420.12,"timestamp":"2025-10-30 10:45:00"}'

# Strategy 2
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"bnb_grid_trading","nav":8750.99,"timestamp":"2025-10-30 11:00:00"}'

# Strategy 3
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"ada_market_maker","nav":3200.45,"timestamp":"2025-10-30 11:20:00"}'
```

### Test Error Cases

#### Test with wrong API key:
```bash
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"wrong_api_key","strategy_name":"test","nav":1000,"timestamp":"2025-10-30 10:00:00"}'
```

Expected response:
```json
{
  "status": "error",
  "message": "Invalid API key"
}
```

#### Test with missing parameter:
```bash
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"test","nav":1000}'
```

Expected response:
```json
{
  "status": "error",
  "message": "Missing required parameters: timestamp"
}
```

### View Data After API Call

After each API call, refresh the browser at `http://localhost:8000` to see the updated data.

Or check the database directly:
```bash
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  "SELECT strategy_name, nav, last_update FROM strategies ORDER BY strategy_name;"
```

---

## Method 2: Using SQLite CLI (Direct Database)

Use this method for quick testing or bulk operations.

### Prerequisites

- SQLite command-line tool (usually comes with macOS)
- Terminal window

### View Current Data

```bash
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite "SELECT * FROM strategies;"
```

Output example:
```
1|UniswapArbitrum|531.645995986|2025-10-28 11:18:34
2|btc_trading_bot|15000.50|2025-10-30 10:30:00
3|eth_arbitrage|5420.12|2025-10-30 10:45:00
```

### View Formatted Data (Easier to Read)

```bash
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  "SELECT strategy_name, nav, last_update FROM strategies ORDER BY strategy_name;"
```

Output:
```
UniswapArbitrum|531.645995986|2025-10-28 11:18:34
btc_trading_bot|15000.50|2025-10-30 10:30:00
eth_arbitrage|5420.12|2025-10-30 10:45:00
```

### Count Strategies

```bash
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  "SELECT COUNT(*) as total FROM strategies;"
```

Output:
```
10
```

### Add Data Directly (SQLite)

**Important**: When using SQLite directly, timestamps should be in UTC format (like from API).

```bash
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite << EOF
INSERT INTO strategies (strategy_name, nav, last_update) VALUES
('direct_sqlite_test', 5000.00, '2025-10-30 10:00:00');
EOF
```

### Update Data Directly (SQLite)

```bash
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  "UPDATE strategies SET nav = 5500.00, last_update = '2025-10-30 11:30:00' WHERE strategy_name = 'direct_sqlite_test';"
```

### Delete One Strategy

```bash
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  "DELETE FROM strategies WHERE strategy_name = 'btc_trading_bot';"
```

### Delete ALL Strategies (Warning!)

```bash
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  "DELETE FROM strategies;"
```

### View Table Structure

```bash
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  ".schema strategies"
```

Output:
```
CREATE TABLE strategies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    strategy_name VARCHAR(100) UNIQUE NOT NULL,
    nav DECIMAL(20,8) NOT NULL,
    last_update DATETIME NOT NULL
);
CREATE INDEX idx_strategy_name ON strategies(strategy_name);
CREATE INDEX idx_last_update ON strategies(last_update);
```

### Interactive SQLite Shell

For multiple commands at once:

```bash
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite

# Then at the sqlite> prompt:
SELECT * FROM strategies;
INSERT INTO strategies (strategy_name, nav, last_update) VALUES ('test', 1000, '2025-10-30 10:00:00');
SELECT COUNT(*) FROM strategies;
.exit
```

---

## Method 3: Batch Update Script (Python)

Create a Python script to add test data in bulk.

### Create Script

Save this as `/Users/matthias/repos/bot_dashboard/load_test_data.py`:

```python
#!/usr/bin/env python3
import sqlite3
import json
from datetime import datetime, timedelta

DB_PATH = '/Users/matthias/repos/bot_dashboard/data/database.sqlite'

# Test data
test_strategies = [
    {'name': 'btc_market_making', 'nav': 10250.45678, 'timestamp': '2025-10-30 10:00:00'},
    {'name': 'eth_arbitrage', 'nav': 5420.12345, 'timestamp': '2025-10-30 10:05:00'},
    {'name': 'bnb_grid_trading', 'nav': 8750.99887, 'timestamp': '2025-10-30 10:10:00'},
    {'name': 'ada_momentum', 'nav': 3100.55555, 'timestamp': '2025-10-30 10:15:00'},
    {'name': 'sol_scalping', 'nav': 4200.77777, 'timestamp': '2025-10-30 10:20:00'},
]

def load_test_data():
    try:
        conn = sqlite3.connect(DB_PATH)
        cursor = conn.cursor()

        for strategy in test_strategies:
            cursor.execute('''
                INSERT OR REPLACE INTO strategies (strategy_name, nav, last_update)
                VALUES (?, ?, ?)
            ''', (strategy['name'], strategy['nav'], strategy['timestamp']))

        conn.commit()
        print(f'✓ Loaded {len(test_strategies)} test strategies')

        # Show what was loaded
        cursor.execute('SELECT COUNT(*) FROM strategies')
        total = cursor.fetchone()[0]
        print(f'✓ Total strategies in database: {total}')

        conn.close()
    except Exception as e:
        print(f'✗ Error: {e}')

if __name__ == '__main__':
    load_test_data()
```

### Run the Script

```bash
python3 /Users/matthias/repos/bot_dashboard/load_test_data.py
```

Expected output:
```
✓ Loaded 5 test strategies
✓ Total strategies in database: 15
```

---

## Practical Workflows

### Workflow 1: Add Test Data and Verify

```bash
# Terminal 1: Make sure server is running
php -S localhost:8000

# Terminal 2: Add test data via API
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"test_workflow","nav":9999.99,"timestamp":"2025-10-30 11:30:00"}'

# Check database
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  "SELECT * FROM strategies WHERE strategy_name = 'test_workflow';"

# Check dashboard
curl http://localhost:8000 | grep -A 5 "test_workflow"

# Check logs
tail -5 /Users/matthias/repos/bot_dashboard/logs/api.log
```

### Workflow 2: Update Multiple Strategies

```bash
# Update strategy 1
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"btc_trading","nav":11000.00,"timestamp":"2025-10-30 12:00:00"}'

# Update strategy 2
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"eth_trading","nav":6000.00,"timestamp":"2025-10-30 12:00:00"}'

# View all updated data
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  "SELECT strategy_name, nav, last_update FROM strategies ORDER BY strategy_name;"
```

### Workflow 3: Clear and Reload Fresh Data

```bash
# Delete all data
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite "DELETE FROM strategies;"

# Verify it's empty
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite "SELECT COUNT(*) FROM strategies;"

# Reload test data using Python script
python3 /Users/matthias/repos/bot_dashboard/load_test_data.py

# Verify new data
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite \
  "SELECT COUNT(*) FROM strategies;"
```

---

## Real-World Example: Complete Testing Flow

Let's do a complete example from start to finish:

### Step 1: Start Server (Terminal 1)
```bash
cd /Users/matthias/repos/bot_dashboard
php -S localhost:8000
```

### Step 2: Clear Old Data (Terminal 2)
```bash
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite "DELETE FROM strategies;"
```

### Step 3: Add Three Test Strategies via API
```bash
# Strategy 1 - Bitcoin Trading
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"btc_market_maker","nav":15000.50,"timestamp":"2025-10-30 11:00:00"}'

# Strategy 2 - Ethereum Arbitrage
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"eth_arbitrage","nav":7500.25,"timestamp":"2025-10-30 11:05:00"}'

# Strategy 3 - Altcoin Grid
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"alt_grid_trade","nav":4200.75,"timestamp":"2025-10-30 11:10:00"}'
```

### Step 4: Verify in Dashboard
Open browser and visit: `http://localhost:8000`

You should see:
- 3 strategies listed
- Total NAV: 26,700.50
- Timestamps converted to your timezone
- Status indicators (color coded)

### Step 5: Update One Strategy
```bash
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_secret_api_key_change_this_in_production","strategy_name":"btc_market_maker","nav":16500.00,"timestamp":"2025-10-30 11:30:00"}'
```

### Step 6: Refresh Dashboard
The BTC strategy should now show:
- NAV: 16.500,0000
- Last Update: 30.10.2025 12:30:00

### Step 7: Check Logs
```bash
tail -10 /Users/matthias/repos/bot_dashboard/logs/api.log
```

You should see entries for all three API calls.

---

## Troubleshooting

### API Returns "Invalid API Key"
Make sure you're using the correct API key from your `.env`:
```bash
cat /Users/matthias/repos/bot_dashboard/.env | grep API_KEY
```

Copy the key and use it in your curl command.

### Dashboard Shows Old Data
1. Refresh the browser (hard refresh: `Cmd + Shift + R`)
2. Or wait 60 seconds for auto-refresh
3. Check that API returned success (not error)

### SQLite Command Not Found
Install SQLite:
```bash
brew install sqlite
```

### "Database Locked" Error
The database might be in use. Make sure no other process is accessing it:
```bash
# This should show nothing if not locked
lsof /Users/matthias/repos/bot_dashboard/data/database.sqlite
```

### Database File Doesn't Exist
It will be created automatically on first use. Check:
```bash
ls -la /Users/matthias/repos/bot_dashboard/data/
```

---

## Summary

| Method | Best For | Speed | Accuracy |
|--------|----------|-------|----------|
| API Endpoint | Testing realistic scenarios | Medium | High |
| SQLite CLI | Quick manual updates | Fast | High |
| Python Script | Batch loading test data | Very Fast | High |

**Recommendation**: Use the **API Endpoint** for most testing since it's the most realistic and tests your actual code path.

---

## Next Steps

Now you can:
1. Add test data
2. Verify it appears in the dashboard
3. Test the API
4. Modify the code
5. See changes reflected immediately

Happy testing! 🚀
