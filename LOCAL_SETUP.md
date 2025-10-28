# Local Development Setup Guide

This project has been cleaned up and configured for local development.

## What Was Removed

✅ Deleted GCP-specific files:

-   `cloudbuild.yaml` (Google Cloud Build configuration)
-   `GCP_DEPLOYMENT.md` (GCP deployment documentation)

✅ Cleaned up configuration:

-   Removed Google Cloud Storage (GCS) disk from `config/filesystems.php`
-   Simplified CORS origins in `config/cors.php`
-   Updated `env.example` to local development defaults

## Local Development Requirements

-   **PHP 8.2+**
-   **MySQL** (running on port 3307)
-   **Composer**

## Quick Start (Without Docker)

### Step 1: Copy Environment File

```bash
cp env.example .env
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Generate Application Key

```bash
php artisan key:generate
```

### Step 4: Configure Database

Edit your `.env` file to match your local MySQL setup:

```env
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=archive_playout
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 5: Run Migrations

```bash
php artisan migrate
```

### Step 6: Start the Development Server

You can use either:

```bash
# Option 1: Built-in PHP server
php -S localhost:8000 -t public

# Option 2: Laravel's serve command
php artisan serve
```

The application will be available at: **http://localhost:8000**

## Using Docker (Optional)

If you prefer using Docker:

```bash
# Start all services
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Access application
# http://localhost:8000
```

The `docker-compose.yml` includes:

-   PHP-FPM 8.2
-   Nginx web server
-   MySQL 8.0 (port 3307)
-   Redis (port 6379)

## API Endpoints

The API is available at: `http://localhost:8000/api`

Health check: `http://localhost:8000/api/health`

## Configuration Details

### Database (MySQL)

-   **Host**: 127.0.0.1
-   **Port**: 3307
-   **Database**: archive_playout
-   **Username**: root
-   **Password**: (your local MySQL password)

### Environment Settings

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

## Troubleshooting

### Port Already in Use

If port 8000 is busy, use a different port:

```bash
php -S localhost:8001 -t public
```

Then update `.env`:

```env
APP_URL=http://localhost:8001
```

### Database Connection Issues

Make sure MySQL is running on port 3307:

```bash
# Check if MySQL is running
mysql -h 127.0.0.1 -P 3307 -u root -p

# Or check with netstat (Windows)
netstat -an | findstr 3307

# Or check with netstat (Mac/Linux)
netstat -an | grep 3307
```

### Clear Cache

If you encounter issues, clear the cache:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Create Storage Link

For file uploads to work properly:

```bash
php artisan storage:link
```

## Available Commands

```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Show migration status
php artisan migrate:status

# Run seeders
php artisan db:seed

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Clear all caches
php artisan optimize:clear
```

## Environment Variables Reference

Key variables for local development:

-   `APP_ENV=local` - Development environment
-   `APP_DEBUG=true` - Enable debug mode
-   `APP_URL=http://localhost:8000` - Application URL
-   `CACHE_DRIVER=file` - Use file-based caching
-   `QUEUE_CONNECTION=sync` - Process jobs synchronously
-   `SESSION_DRIVER=file` - Use file-based sessions

## Next Steps

1. ✅ Copy `.env` from `env.example`
2. ✅ Run `php artisan key:generate`
3. ✅ Update database credentials in `.env`
4. ✅ Run `php artisan migrate`
5. ✅ Start server with `php -S localhost:8000 -t public`
6. 🚀 Start developing!

## File Storage

Files are stored locally in:

-   `storage/app` - Private files
-   `storage/app/public` - Public files (accessible via `/storage` URL)

## Frontend Integration

Your frontend should connect to:

```
http://localhost:8000/api
```

Common endpoints:

-   `POST /api/login` - User login
-   `POST /api/register` - User registration
-   `GET /api/contents` - Get all content
-   `POST /api/contents` - Create content
-   `GET /api/health` - Health check
