@echo off
set "WEB_ROOT=%~dp0"
if exist "%WEB_ROOT%php\php.exe" (
    "%WEB_ROOT%php\php.exe" -S localhost:8000 -t "%WEB_ROOT%my_website"
) else (
    where php >nul 2>&1
    if errorlevel 1 (
        echo PHP was not found. Install PHP 8.0 or newer and add it to PATH.
        pause
        exit /b 1
    )
    php -S localhost:8000 -t "%WEB_ROOT%my_website"
)
pause
