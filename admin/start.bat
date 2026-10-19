@echo off
cd /d "%~dp0"
where php >nul 2>nul || (echo PHP is not installed or not in PATH. & pause & exit /b 1)
echo Starting the AchiveX admin. Keep this window open.
start "" cmd /c "timeout /t 4 >nul & start http://127.0.0.1:8000/admin"
php artisan serve
echo.
echo The admin stopped. Take a screenshot of this window and send it.
pause
