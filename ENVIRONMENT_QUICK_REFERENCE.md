# Environment Quick Reference

## Your Setup Now Has

### Development (Your Machine)
```
localhost:8000
    ↓
.env (ENVIRONMENT=development)
    ↓
SQLite: /data/database.sqlite
```

### Production (Your Webhost)
```
your_domain.com
    ↓
.env (ENVIRONMENT=production) [stays on server]
    ↓
MySQL: database on server
```

---

## How to Use

### Development

```bash
# 1. Open terminal
cd /Users/matthias/repos/bot_dashboard

# 2. Start server
php -S localhost:8000

# 3. Open browser
http://localhost:8000

# 4. That's it! Development works with your local SQLite database
```

### Production

```
1. Your webhost admin creates .env file on server
2. You upload code files via Cyberduck (using CYBERDUCK_DEPLOYMENT_CHECKLIST.md)
3. Application automatically reads production .env
4. Production uses MySQL database
```

---

## Environment Files

| File | Location | Purpose | In Git? |
|------|----------|---------|---------|
| `.env` | Your machine | Your development config | ❌ NO (in .gitignore) |
| `.env` | Webhost server | Production config | ❌ NO (never uploaded) |
| `.env.example` | Repo root | Template/reference | ✓ YES |
| `config/environment.php` | Repo | Config loader | ✓ YES |
| `config/config.php` | Repo | Main config (uses .env) | ✓ YES |
| `config/database.php` | Repo | DB config (uses .env) | ✓ YES |

---

## What Each Environment Uses

### Development Environment
```
Database: SQLite
Location: /data/database.sqlite
Config:   .env (your machine)
API Key:  Default (test key)
Data:     Test data OK
```

### Production Environment
```
Database: MySQL
Location: On webhost server
Config:   .env (on server)
API Key:  Strong production key
Data:     Real data only
```

---

## Configuration Priority

The application reads settings in this order:

1. **`.env` file** (if exists) - Highest priority
2. **Hardcoded defaults** - If .env not found

So if `.env` exists, those values are used. Otherwise, defaults apply.

---

## Why This Works

- Development uses SQLite (simple, no setup)
- Production uses MySQL (robust, scalable)
- Code is identical for both
- Configuration is environment-specific
- Credentials are never in code (only in .env)
- `.env` is never committed to Git

---

## Deployment Reminder

When you upload to production via Cyberduck:

✓ Upload these:
```
config/config.php
config/database.php
config/environment.php
api/update.php
includes/*
assets/*
index.php
.htaccess
```

❌ Do NOT upload:
```
.env (stays on server)
data/database.sqlite (SQLite)
logs/ (server logs)
tests/
.git/
```

---

## Environment Detection

The code automatically detects which environment you're in:

```php
// In code (example):
if (isDevelopment()) {
    // Use SQLite, verbose logging, etc.
}

if (isProduction()) {
    // Use MySQL, security mode, etc.
}
```

---

## Troubleshooting Quick Reference

| Problem | Cause | Solution |
|---------|-------|----------|
| Using SQLite on production | `.env` not on server | Create `.env` on webhost |
| Using MySQL locally | Wrong `ENVIRONMENT` value | Check `.env` says `development` |
| Config not loading | `.env` in wrong location | Place in project root |
| Database connection fails | Wrong credentials | Update `.env` values |

---

## Summary

You now have:
- ✓ Separate development and production environments
- ✓ Easy local development (just run php -S localhost:8000)
- ✓ Safe production deployment (no test data leaks)
- ✓ Secure configuration (credentials in .env, not in code)
- ✓ Git-friendly (sensitive files never committed)

Start developing!
