@echo off
SETLOCAL ENABLEDELAYEDEXPANSION

REM Check input argument (dev, build)
IF "%1" == "dev" (
    echo Starting Laravel + React in Development Mode...

    REM Clear Laravel caches
    php artisan cache:clear
    php artisan route:clear
    php artisan config:clear
    php artisan view:clear
    php artisan config:cache
    php artisan route:cache
    REM Start Laravel development server
    start cmd /k "php artisan serve --port=8000"

    REM Wait for the server to start (adjust delay if needed)
    timeout /t 5 /nobreak >nul

    REM Open localhost in Chrome
    start "" "C:\Program Files\Google\Chrome\Application\chrome.exe" http://localhost:8000/

    REM Start React development server (Vite)
    cd resources\js
    npm run dev
    exit /b
)

IF "%1" == "build" (
    echo Building Laravel + React for Production...

    REM Clear Laravel caches and optimize
    php artisan cache:clear
    php artisan route:clear
    php artisan config:clear
    php artisan view:clear
    php artisan config:cache
    php artisan route:cache

    REM Build React (with Vite)
    cd resources\js
    npm run build
    cd ....

    echo Build complete!

    REM Start Laravel production server
    start cmd /k "php artisan serve"

    REM Wait and open localhost
    timeout /t 5 /nobreak >nul
    start "" "C:\Program Files\Google\Chrome\Application\chrome.exe" http://localhost:8000/

    exit /b
)

REM Invalid option
echo Invalid option. Use:
echo     deploy.bat dev   - Start Development Mode
echo     deploy.bat build - Build and Start for Production
exit /b