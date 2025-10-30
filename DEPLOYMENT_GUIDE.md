# Deployment Guide: Development vs. Production

This guide explains how to manage separate development and production environments for the Hummingbot Dashboard.

## Overview

- **Development Environment**: Local machine (localhost:8000) with SQLite database
- **Production Environment**: Web host with MySQL database
- **Configuration**: Environment-based using `.env` files

---

## Environment Configuration

### Files Involved

1. `.env` - Local development configuration (NOT committed to Git)
2. `.env.example` - Template for reference (committed to Git)
3. `config/environment.php` - Environment loader
4. `config/config.php` - Main configuration (uses environment variables)
5. `config/database.php` - Database configuration (uses environment variables)

### How It Works

The application automatically detects the environment based on the `ENVIRONMENT` variable in `.env`:
- If `ENVIRONMENT=development` → Uses SQLite from `/data/database.sqlite`
- If `ENVIRONMENT=production` → Uses MySQL with credentials from `.env`

---

## Local Development Setup

### 1. Create `.env` for Development

Your `.env` file (development environment):

```env
ENVIRONMENT=development

API_KEY=your_secret_api_key_change_this_in_production
DASHBOARD_TITLE=Hummingbot Strategy Monitor
REFRESH_INTERVAL=60
TIMEZONE=Europe/Vienna

DB_TYPE_DEV=sqlite
DB_PATH_DEV=/Users/matthias/repos/bot_dashboard/data/database.sqlite

ENABLE_LOGGING=true
NAV_DECIMALS=4
```

### 2. Run Development Server

```bash
# Navigate to project directory
cd /Users/matthias/repos/bot_dashboard

# Start PHP built-in server
php -S localhost:8000
```

Access the dashboard at: `http://localhost:8000`

### 3. Database

Development uses SQLite:
- Database file: `/data/database.sqlite`
- No credentials needed
- Persists locally on your machine

### 4. Test Data

You can safely add test data in development without affecting production.

---

## Production Deployment

### 1. Prepare Server-Side `.env`

Before uploading files, your webhost administrator should create a `.env` file on the server with production settings:

```env
ENVIRONMENT=production

API_KEY=your_strong_secret_api_key_here
DASHBOARD_TITLE=Hummingbot Strategy Monitor
REFRESH_INTERVAL=60
TIMEZONE=Europe/Vienna

DB_TYPE_PROD=mysql
DB_HOST_PROD=your_webhost_domain_or_ip
DB_NAME_PROD=hummingbot_dashboard
DB_USER_PROD=dashboard_user
DB_PASS_PROD=your_strong_database_password
DB_CHARSET_PROD=utf8mb4

ENABLE_LOGGING=true
NAV_DECIMALS=4
```

**Important**: The `.env` file on the webhost will NOT be committed to Git and will NOT be overwritten during deployment.

### 2. Database Setup (One-Time)

On the webhost, create the MySQL database:

```sql
-- Create database
CREATE DATABASE hummingbot_dashboard
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'dashboard_user'@'localhost'
IDENTIFIED BY 'your_strong_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON hummingbot_dashboard.*
TO 'dashboard_user'@'localhost';

FLUSH PRIVILEGES;

-- Create table
CREATE TABLE IF NOT EXISTS strategies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    strategy_name VARCHAR(100) UNIQUE NOT NULL,
    nav DECIMAL(20,8) NOT NULL,
    last_update DATETIME NOT NULL,
    INDEX idx_strategy_name (strategy_name),
    INDEX idx_last_update (last_update)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. Deploy Files Using Cyberduck

#### Which Files to Upload

**Upload these files to your webhost:**

```
api/update.php
config/config.php
config/database.php
config/environment.php          ← NEW FILE
includes/auth.php
includes/functions.php
assets/
index.php
.htaccess
sql/
```

**Do NOT upload these files** (they stay local):
- `.env` (stays on webhost, not overwritten)
- `data/database.sqlite` (SQLite, not needed on production)
- `tests/`
- `CLAUDE.md`
- `.git/`

#### Steps Using Cyberduck

1. Open Cyberduck
2. Connect to your webhost
3. Navigate to your web root directory
4. For each file/directory above:
   - Right-click → "Upload"
   - Or drag and drop
   - Confirm overwrite if needed

5. Verify the `.env` file exists on the server (created by webhost admin)
6. Test the dashboard: Visit your domain

#### Important Notes

- `.env` is in `.gitignore` and will NOT sync
- On the webhost, the `.env` file stays in place (not deleted/updated)
- Each environment has its own `.env` with different settings
- The code automatically reads from the appropriate `.env` based on location

### 4. Verify Production Setup

1. Visit your production domain
2. Check that MySQL data is displayed
3. Verify API is working: Send a test request to `/api/update.php`
4. Check that logs are being written to `/logs/`

---

## File Comparison

### Development Machine (localhost)

```
.env                          ← ENVIRONMENT=development
config/
  environment.php
  config.php                  ← Reads from .env
  database.php                ← Uses SQLite from .env
data/database.sqlite          ← Local test database
logs/                         ← Local logs
```

### Production Webhost

```
.env                          ← ENVIRONMENT=production (NEVER uploaded)
config/
  environment.php
  config.php                  ← Reads from .env
  database.php                ← Uses MySQL from .env
logs/                         ← Server logs
(No SQLite database)
```

---

## Workflow Summary

### For Development

1. Make code changes locally
2. Test with SQLite database
3. Add/modify test data
4. Commit code changes to Git (`.env` is ignored)

### For Production Deployment

1. Code changes are ready and tested
2. Use Cyberduck to upload code files ONLY
3. Do NOT upload `.env` or database files
4. Production `.env` remains on server (created once)
5. Production data remains in MySQL

---

## Troubleshooting

### "Database connection failed" on production

**Check:**
1. `.env` file exists on webhost
2. `ENVIRONMENT=production` in `.env`
3. MySQL credentials are correct
4. Database and user exist
5. MySQL server is running

### "Using SQLite in production" warning

**Check:**
1. `.env` on webhost has `ENVIRONMENT=production`
2. File was actually uploaded to webhost
3. Refresh browser cache

### Development data appearing on production

**This shouldn't happen because:**
- Development uses SQLite (local file)
- Production uses MySQL (remote server)
- They never share data

---

## Security Best Practices

1. **Never commit `.env`** - It's in `.gitignore` for a reason
2. **Strong API_KEY** in production - Use something like: `openssl rand -hex 32`
3. **Strong DB password** - At least 16 characters, mix of types
4. **HTTPS only** in production - Configure on your webhost
5. **Protect `.env` file** - Set proper file permissions (640)
6. **Rotate credentials periodically** - Change API_KEY and DB password regularly

---

## Quick Reference

| Aspect | Development | Production |
|--------|-------------|-----------|
| Location | localhost:8000 | your_domain.com |
| Database | SQLite | MySQL |
| Config | `.env` (local) | `.env` (server) |
| Database File | `/data/database.sqlite` | MySQL on server |
| API Key | Default (test) | Strong key (required) |
| Test Data | Allowed | NO test data |
| Logs | Local `/logs/` | Server `/logs/` |

---

## Questions?

Refer to the main README.md for general setup instructions.
