@echo off
title Score Tracker - Local Server
echo ========================================================
echo   Starting Score Tracker on http://localhost:8000
echo ========================================================
echo.

:: Open browser automatically
start "" "http://localhost:8000"

:: Start Laravel Server
php artisan serve --port=8000
pause
