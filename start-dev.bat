@echo off
echo Starting Archive Playout Backend Development Server...
echo.
echo Make sure your .env file is configured correctly!
echo.
php -S localhost:8000 -t public
pause

