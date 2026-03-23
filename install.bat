@echo off

where php >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in PATH.
    exit /b 1
)

where npm >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: npm is not installed or not in PATH.
    exit /b 1
)

echo Installing PoppySeedPets dependencies...

echo.
echo === API (composer install) ===
cd /d %~dp0api && composer install

echo.
echo === SPA (npm install) ===
cd /d %~dp0webapp && npm install

echo.
echo Done!
pause
