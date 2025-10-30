# Setup Instructions for Webhost Administrator

This document is for the webhost administrator to set up the production environment.

## What Needs to Be Done (One-Time Setup)

### 1. Create MySQL Database

Connect to the webhost MySQL and run these commands:

```sql
-- Create database
CREATE DATABASE hummingbot_dashboard
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with secure password
CREATE USER 'dashboard_user'@'localhost'
IDENTIFIED BY 'YOUR_STRONG_PASSWORD_HERE';

-- Grant permissions (only what's needed)
GRANT SELECT, INSERT, UPDATE ON hummingbot_dashboard.*
TO 'dashboard_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Create table structure
CREATE TABLE IF NOT EXISTS strategies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    strategy_name VARCHAR(100) UNIQUE NOT NULL,
    nav DECIMAL(20,8) NOT NULL,
    last_update DATETIME NOT NULL,
    INDEX idx_strategy_name (strategy_name),
    INDEX idx_last_update (last_update)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Note**: Replace `YOUR_STRONG_PASSWORD_HERE` with an actual strong password!

### 2. Create `.env` Configuration File

Create a file named `.env` in the web root directory with these contents:

```env
ENVIRONMENT=production

API_KEY=CHANGE_THIS_TO_A_STRONG_API_KEY
DASHBOARD_TITLE=Hummingbot Strategy Monitor
REFRESH_INTERVAL=60
TIMEZONE=Europe/Vienna

DB_TYPE_PROD=mysql
DB_HOST_PROD=localhost
DB_NAME_PROD=hummingbot_dashboard
DB_USER_PROD=dashboard_user
DB_PASS_PROD=YOUR_STRONG_PASSWORD_HERE
DB_CHARSET_PROD=utf8mb4

ENABLE_LOGGING=true
NAV_DECIMALS=4
```

**Important**:
- Replace `YOUR_STRONG_PASSWORD_HERE` with the password from step 1
- Replace `CHANGE_THIS_TO_A_STRONG_API_KEY` with a random strong key (example: `openssl rand -hex 32`)
- Set proper file permissions: `chmod 640 .env`

### 3. Create Logs Directory

Create a directory named `logs` in the web root:

```bash
mkdir logs
chmod 755 logs
```

### 4. Verify File Permissions

```bash
# Web root should be readable
chmod 755 .

# Config files should be readable
chmod 644 config/*.php

# Logs directory should be writable
chmod 755 logs
```

### 5. Verify PHP Configuration

Make sure the webhost has:
- PHP 8.0 or higher
- PDO extension (usually included)
- MySQL extension or MySQLi extension

---

## What The Developer Will Do

The developer (Matthias) will upload code files using Cyberduck:

1. Update configuration files (`config/`)
2. Update API and application files
3. Update frontend assets

The developer will NOT:
- Upload `.env` file (you created this)
- Upload `data/database.sqlite` (SQLite database)
- Delete or modify your `.env`

---

## Testing the Setup

After everything is in place, verify with these tests:

### 1. Test Dashboard Access
```
Visit: https://your_domain.com/
Should display the dashboard (may be empty initially)
```

### 2. Test Database Connection
```bash
# SSH into server and test MySQL connection
mysql -u dashboard_user -p hummingbot_dashboard
# Should connect successfully

# Check table exists
SHOW TABLES;
# Should show: strategies

# View table structure
DESCRIBE strategies;
```

### 3. Test File Permissions
```bash
# Check .env file is readable but not world-accessible
ls -la .env
# Should show: -rw-r----- (640) or -rw-r--r-- (644)

# Check logs directory is writable
ls -la logs/
# Should show: drwxr-xr-x (755)
```

### 4. Check Logs
```bash
# After a dashboard visit, check logs
cat logs/api.log
# Should contain entries (or empty if no API calls yet)
```

---

## Important Notes

⚠️ **Do NOT**
- Commit the `.env` file to version control
- Change the `.env` file unless specifically instructed
- Move the `logs` directory or change its permissions
- Modify `config/` files manually (let the developer handle this)

✓ **DO**
- Keep `.env` file secure (proper permissions)
- Back up the `.env` file (contains credentials)
- Monitor the `logs/` directory for any errors
- Test after initial setup

---

## Support

If there are any issues:

1. Check that MySQL database and user were created correctly
2. Verify `.env` file exists in web root
3. Check file permissions are correct
4. Look in `logs/` for error messages
5. Verify PHP has MySQL support

---

## Quick Setup Summary

For the admin who just wants the commands:

```bash
# Create directory
mkdir logs && chmod 755 logs

# Create .env file with the content provided above
# Set permissions
chmod 640 .env

# MySQL commands (as root or admin user)
mysql -u root -p < setup.sql  # After running SQL commands above
```

---

## After Setup

- Dashboard will be accessible at: `https://your_domain.com/`
- API endpoint at: `https://your_domain.com/api/update.php`
- Logs stored in: `/logs/` directory
- Database is ready for the developer's data

The developer can now upload code files via Cyberduck and test the API connection.
