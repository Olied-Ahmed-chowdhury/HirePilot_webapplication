@echo off
echo ========================================================
echo   HirePilot AI - Automatic MySQL Database Importer
echo ========================================================
echo.

set MYSQL_EXE="C:\xampp\mysql\bin\mysql.exe"

if not exist %MYSQL_EXE% (
    echo [ERROR] MySQL not found at C:\xampp\mysql\bin\mysql.exe
    pause
    exit /b
)

echo [1/3] Creating database & tables from schema.sql...
%MYSQL_EXE% -u root -e "SOURCE %~dp0database/schema.sql;"
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [FAILED] Make sure MySQL is running in your XAMPP Control Panel!
    pause
    exit /b
)

echo [2/3] Seeding demo jobs, admin & user accounts from seed.sql...
%MYSQL_EXE% -u root hirepilot_db -e "SOURCE %~dp0database/seed.sql;"

echo [3/3] Verifying imported tables...
%MYSQL_EXE% -u root -e "SHOW TABLES FROM hirepilot_db;"

echo.
echo ========================================================
echo   SUCCESS! Database 'hirepilot_db' is fully imported!
echo ========================================================
pause
