@echo off

setlocal

set "PHP_BIN="
for /f "delims=" %%i in ('where php 2^>nul') do if not defined PHP_BIN set "PHP_BIN=%%i"
if not defined PHP_BIN echo ERROR: PHP not found 1>&2 && exit /b 1

set "SCRIPT_DIR=%~dp0"
"%PHP_BIN%" "%SCRIPT_DIR%bitrix-module-helpers.php" %*

endlocal
