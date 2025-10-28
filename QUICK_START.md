# Quick Start Guide

## Your Setup (Without Docker)

You're running the application with PHP's built-in server:

```bash
php -S localhost:8000 -t public
```

## To Get Started:

### 1. Copy Environment File (if not done)

```bash
cp env.example .env
```

### 2. Generate Application Key

```bash
php artisan key:generate
```

### 3. Make Sure MySQL is Running on Port 3307

Update `.env` if your MySQL has different credentials:

```env
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=archive_playout
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Start the Server

```bash
php -S localhost:8000 -t public
```

Or use the convenience scripts:

-   Windows: Double-click `start-dev.bat`
-   Mac/Linux: `./start-dev.sh`

## That's It! 🎉

Your API will be available at: **http://localhost:8000**

### Key Endpoints:

-   Health Check: http://localhost:8000/api/health
-   Login: POST http://localhost:8000/api/login
-   Register: POST http://localhost:8000/api/register

## Configuration Summary

✅ **Database**: MySQL on port 3307
✅ **Server**: PHP built-in server (localhost:8000)
✅ **Cache**: File-based
✅ **Queue**: Sync (immediate processing)
✅ **Storage**: Local file system

## Troubleshooting

If the server doesn't start, check:

1. Is MySQL running? → Make sure MySQL is running on port 3307
2. Port in use? → Try `php -S localhost:8001 -t public` (then update APP_URL in .env)
3. Database connection issues? → Check MySQL credentials in `.env`

For more details, see [LOCAL_SETUP.md](LOCAL_SETUP.md)
