# Environment Setup Summary

## What Was Created

A complete development and production environment separation system for the Hummingbot Dashboard.

### New Files

1. **`.env`** - Your local development configuration (NOT in Git)
2. **`.env.example`** - Template for reference (in Git)
3. **`config/environment.php`** - Environment loader and helper functions
4. **`DEPLOYMENT_GUIDE.md`** - Complete deployment instructions

### Updated Files

1. **`config/config.php`** - Now uses environment variables
2. **`config/database.php`** - Now supports dev (SQLite) and production (MySQL) automatically

---

## Quick Start

### Local Development

1. Your `.env` file is already created with:
   - `ENVIRONMENT=development`
   - SQLite database configuration
   - API key and other settings

2. Run the development server:
   ```bash
   cd /Users/matthias/repos/bot_dashboard
   php -S localhost:8000
   ```

3. Access at: `http://localhost:8000`

That's it! Development works exactly as before, but now with proper environment separation.

---

## Production Deployment

### For Your Webhost Administrator

Before uploading files, they need to create a `.env` file on the server:

```env
ENVIRONMENT=production

API_KEY=strong_secret_key_here
DASHBOARD_TITLE=Hummingbot Strategy Monitor
REFRESH_INTERVAL=60
TIMEZONE=Europe/Vienna

DB_TYPE_PROD=mysql
DB_HOST_PROD=your_webhost
DB_NAME_PROD=hummingbot_dashboard
DB_USER_PROD=dashboard_user
DB_PASS_PROD=strong_password_here
DB_CHARSET_PROD=utf8mb4

ENABLE_LOGGING=true
NAV_DECIMALS=4
```

### Files to Upload via Cyberduck

Upload ONLY these files/folders:
```
api/update.php
config/config.php
config/database.php
config/environment.php          ← NEW
includes/auth.php
includes/functions.php
assets/
index.php
.htaccess
sql/
```

**Do NOT upload:**
- `.env` (stays on server, never overwritten)
- `data/database.sqlite` (not needed on production)
- `tests/`, `.git/`, `CLAUDE.md`, etc.

---

## How It Works

### Development Environment
```
Your Machine (localhost:8000)
  └── .env (ENVIRONMENT=development)
      └── Uses: /data/database.sqlite (SQLite)
```

### Production Environment
```
Webhost (your_domain.com)
  └── .env (ENVIRONMENT=production) [stays on server]
      └── Uses: MySQL database
```

The application automatically detects which database to use based on the `.env` file.

---

## Key Benefits

✅ **No test data in production** - SQLite stays local, MySQL is separate
✅ **Easy deployment** - Just upload code files via Cyberduck
✅ **Secure credentials** - `.env` never committed to Git
✅ **Environment-aware** - Code automatically adapts to dev or production
✅ **No hardcoded paths** - All configuration in `.env`
✅ **Backward compatible** - Existing code still works

---

## File Structure

```
/your/project/
├── .env                         ← LOCAL development config (ignored by Git)
├── .env.example                 ← TEMPLATE for reference
├── config/
│   ├── environment.php          ← NEW: loads .env
│   ├── config.php              ← Updated: uses env vars
│   └── database.php            ← Updated: dev/prod aware
├── api/
├── includes/
├── data/
│   └── database.sqlite         ← LOCAL only
└── logs/                       ← LOCAL only
```

---

## Checklist for Production

- [ ] Webhost admin creates `.env` on server with production settings
- [ ] Database and user created on webhost MySQL
- [ ] Upload code files via Cyberduck (NOT `.env` or `data/` folder)
- [ ] Test dashboard at your domain
- [ ] Verify API is working with test request
- [ ] Check logs are being written

---

## Troubleshooting

**Q: "Database connection failed" when accessing production**
- A: Check that `.env` exists on server with correct MySQL credentials

**Q: Development still using SQLite?**
- A: That's correct! It should. Check that `.env` has `ENVIRONMENT=development`

**Q: ".env file not found" error?**
- A: Application will use defaults, but production needs the file. Check file permissions.

---

## Next Steps

Refer to **`DEPLOYMENT_GUIDE.md`** for detailed step-by-step deployment instructions.
