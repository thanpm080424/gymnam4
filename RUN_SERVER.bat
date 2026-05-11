@echo off
REM Monkey Gym - Start PHP Development Server
REM Port: 8000

cd /d "%~dp0"

echo.
echo ===============================================
echo   🐒 Monkey Gym - PHP Development Server
echo ===============================================
echo.
echo Starting server on: http://localhost:8000
echo Press Ctrl+C to stop the server
echo.
echo Useful links:
echo   - Home:              http://localhost:8000
echo   - Login:             http://localhost:8000/dang-nhap.php
echo   - Test Config:       http://localhost:8000/public/test-qr.php
echo   - Admin Dashboard:   http://localhost:8000/admin/bang-dieu-khien.php
echo   - Member Dashboard:  http://localhost:8000/member/bang-dieu-khien.php
echo.
echo ===============================================
echo.

php -S localhost:8000 -t public

pause
