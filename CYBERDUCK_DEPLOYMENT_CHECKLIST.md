# Cyberduck Deployment Checklist

Quick reference for deploying to your webhost using Cyberduck.

## Pre-Deployment Setup (One-Time on Webhost)

Before you deploy any files, your webhost admin must:

1. Create MySQL database and user
2. Create `.env` file with production settings (see `DEPLOYMENT_GUIDE.md`)
3. Create `/logs/` directory with proper permissions
4. Verify paths and permissions

## Files to Upload

Use Cyberduck to upload these files/folders. **Overwrite existing files.**

### Configuration Files ✓
- `config/config.php` - Updated
- `config/database.php` - Updated
- `config/environment.php` - NEW
- `config/auth.php` - If changed
- `.htaccess` - If changed

### API Endpoint ✓
- `api/update.php` - Updated

### Application Files ✓
- `index.php` - Main dashboard
- `includes/` - ALL files
  - `includes/auth.php`
  - `includes/functions.php`

### Frontend Assets ✓
- `assets/css/` - ALL files
- `assets/js/` - ALL files

### Database Setup (One-Time) ✓
- `sql/mysql_setup.sql` - Run once to create table structure

### Documentation ✓
- Optional: `README.md`, `DEPLOYMENT_GUIDE.md` (for reference)

## Files to NOT Upload

**Do NOT upload these** (they stay local or on server):

- ❌ `.env` - Stays on server, don't overwrite
- ❌ `data/database.sqlite` - SQLite database (not needed on production)
- ❌ `logs/` - Server logs stay on server
- ❌ `tests/` - Test files
- ❌ `.git/` - Version control
- ❌ `.idea/` - IDE settings
- ❌ `CLAUDE.md` - Developer notes
- ❌ `.env.example` - Template only

## Step-by-Step Deployment

### 1. Connect with Cyberduck
- Open Cyberduck
- Click "Open Connection"
- Select your webhost
- Navigate to web root directory (usually `public_html/` or similar)

### 2. Create Folders (If Not Exist)
```
/config/
/includes/
/assets/css/
/assets/js/
/api/
/sql/
/logs/           ← Create this with proper permissions
```

### 3. Upload Configuration
1. Right-click → Upload
2. Select `config/config.php`
3. Select `config/database.php`
4. Select `config/environment.php` (NEW)
5. Select `.htaccess` (if modified)

### 4. Upload Code Files
1. Upload all files from `includes/`
2. Upload all files from `api/`
3. Upload `index.php`

### 5. Upload Assets
1. Right-click → Upload
2. Select `assets/` folder
3. Confirm overwrite if needed

### 6. Verify Structure
On your webhost, you should now have:
```
your_domain.com/
├── config/
│   ├── config.php ✓
│   ├── database.php ✓
│   ├── environment.php ✓ (NEW)
│   └── auth.php
├── includes/
│   ├── auth.php
│   └── functions.php
├── api/
│   └── update.php
├── assets/
│   ├── css/
│   └── js/
├── index.php
├── .htaccess
├── .env ← Already there, don't touch
└── logs/ ← Directory exists
```

### 7. Test Deployment
1. Visit `https://your_domain.com/`
2. Should display dashboard with MySQL data
3. Try API: POST to `https://your_domain.com/api/update.php`
4. Check logs in `/logs/`

## Troubleshooting

| Issue | Check |
|-------|-------|
| "Database connection failed" | `.env` exists on server, MySQL credentials correct |
| Still seeing SQLite data | `.env` has `ENVIRONMENT=production` |
| File permission errors | Check `/logs/` directory permissions (755 or 775) |
| 404 errors | Verify files uploaded to correct directory |
| Blank page | Check PHP error logs on server |

## Quick Summary

✅ Do this:
- Upload code files
- Update `config/` files including new `environment.php`
- Verify `.env` exists on server

❌ Don't do this:
- Upload `.env` (it stays on server)
- Upload `data/database.sqlite` (SQLite, not needed)
- Delete any folders on server
- Touch the `.env` file

## Need Help?

See `DEPLOYMENT_GUIDE.md` for detailed instructions and explanations.
