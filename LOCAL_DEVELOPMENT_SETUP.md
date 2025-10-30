# Local Development Environment Setup - Mac

This guide walks you through setting up and running the test environment on your Mac.

## Prerequisites Check

Your system has:
- ✓ PHP 8.4.13 installed (excellent - well above minimum 8.0)
- ✓ SQLite support (built into PHP)
- ✓ Project structure ready

## Step 1: Navigate to Project Directory

```bash
cd /Users/matthias/repos/bot_dashboard
```

Verify you're in the right place:
```bash
ls -la
```

You should see:
```
.env                    ← Your configuration
.env.example           ← Template
config/                ← Configuration files
api/                   ← API endpoint
includes/              ← Helper functions
assets/                ← CSS/JS
data/                  ← SQLite database
index.php              ← Dashboard
```

## Step 2: Verify Configuration

Check that your `.env` file has development settings:

```bash
cat .env | grep ENVIRONMENT
```

Should output:
```
ENVIRONMENT=development
```

## Step 3: Start the Development Server

Run this command in your terminal:

```bash
php -S localhost:8000
```

You should see:
```
Development Server (PHP 8.4.13) started at [Thu Oct 30 10:00:00 2025]
Listening on http://localhost:8000
```

**Important**: Keep this terminal window open while developing. To stop the server, press `Ctrl+C`.

## Step 4: Access the Dashboard

Open your browser and visit:

```
http://localhost:8000
```

You should see:
- The Hummingbot Dashboard page
- Title: "Hummingbot Strategy Monitor"
- Status indicators (colored circles)
- A table with columns: Strategy, NAV, Last Update, Status

## Step 5: Add Test Data (Optional)

To see the dashboard working, you can add test data. Open another terminal (keep the first one running) and run:

```bash
cd /Users/matthias/repos/bot_dashboard

# Test the API with sample data
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_secret_api_key_change_this_in_production",
    "strategy_name": "test_strategy_1",
    "nav": 10250.45678,
    "timestamp": "2025-10-30 10:30:00"
  }'
```

Expected response:
```json
{
  "status": "success",
  "message": "Data updated successfully",
  "strategy": "test_strategy_1"
}
```

## Step 6: Verify Dashboard Shows Data

Refresh your browser at `http://localhost:8000`

You should now see:
- Your test strategy in the table
- NAV value: 10.250,4568 (formatted)
- Last update time (converted to your timezone)
- A status indicator (green, yellow, or red based on age)

## Step 7: Add More Test Data (Optional)

Add another strategy:

```bash
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_secret_api_key_change_this_in_production",
    "strategy_name": "test_strategy_2",
    "nav": 5420.12345,
    "timestamp": "2025-10-30 10:25:00"
  }'
```

Then another:

```bash
curl -X POST http://localhost:8000/api/update.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_secret_api_key_change_this_in_production",
    "strategy_name": "test_strategy_3",
    "nav": 8750.99887,
    "timestamp": "2025-10-30 10:10:00"
  }'
```

Refresh the dashboard to see all three strategies.

## Development Workflow

### Terminal 1: Keep Server Running
```bash
cd /Users/matthias/repos/bot_dashboard
php -S localhost:8000
# Keep this open while developing
```

### Terminal 2: Use for Testing/Commands
```bash
# Test API
curl -X POST http://localhost:8000/api/update.php ...

# Check database
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite "SELECT * FROM strategies;"

# View logs
tail -f /Users/matthias/repos/bot_dashboard/logs/api.log
```

## Accessing Different Pages

From `http://localhost:8000/`:

- **Dashboard**: `http://localhost:8000/`
- **API Endpoint**: `http://localhost:8000/api/update.php` (POST only)
- **Info**: `http://localhost:8000/info.php` (shows PHP info)

## Stopping the Server

When you're done developing:

```bash
# In the terminal running the server, press:
Ctrl + C
```

## Troubleshooting

### "Address already in use"
If port 8000 is already in use, use a different port:
```bash
php -S localhost:8001
# Then access at http://localhost:8001
```

### "Database connection failed"
Check that the SQLite file exists:
```bash
ls -la /Users/matthias/repos/bot_dashboard/data/database.sqlite
```

If it doesn't exist, it will be created on first use.

### "API returns 401 Unauthorized"
Make sure you're using the correct API key from your `.env` file:
```bash
cat .env | grep API_KEY
```

### Dashboard shows no data
1. Check that API returned success
2. Refresh the browser (or wait 60 seconds for auto-refresh)
3. Check logs: `tail /Users/matthias/repos/bot_dashboard/logs/api.log`

## Key Files for Development

### Code Files (Edit these)
- `index.php` - Dashboard HTML
- `config/config.php` - Configuration
- `includes/functions.php` - Helper functions
- `api/update.php` - API endpoint
- `assets/css/style.css` - Styling
- `assets/js/dashboard.js` - Auto-refresh JS

### Data Files (Don't edit directly)
- `data/database.sqlite` - SQLite database
- `logs/` - Log files

### Configuration (Don't commit)
- `.env` - Environment variables (local development)

## Database Management

### View all data:
```bash
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite "SELECT * FROM strategies;"
```

### Clear all test data:
```bash
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite "DELETE FROM strategies;"
```

### View table structure:
```bash
sqlite3 /Users/matthias/repos/bot_dashboard/data/database.sqlite ".schema strategies"
```

## Next Steps

1. Make code changes as needed
2. Test in the browser at `http://localhost:8000`
3. Use API to add/update data
4. When ready to deploy: See `CYBERDUCK_DEPLOYMENT_CHECKLIST.md`

## Environment Info

Your development environment:
- **Server**: PHP 8.4.13 built-in server
- **Address**: http://localhost:8000
- **Database**: SQLite at `/data/database.sqlite`
- **Config**: `.env` file (ENVIRONMENT=development)
- **Logs**: `/logs/` directory

All set! Start developing! 🚀
