@echo off
REM ================================================================================
REM Automated Deployment Script for Shots By Whatsername (Windows)
REM 
REM This script automates the deployment process to a Hetzner server from Windows
REM Requires: PuTTY (plink.exe, pscp.exe) or OpenSSH for Windows
REM
REM Usage: deploy.bat [server-ip] [domain-name]
REM Example: deploy.bat 123.45.67.89 yourdomain.com
REM ================================================================================

setlocal enabledelayedexpansion

REM Check if parameters are provided
if "%~1"=="" (
    echo Error: Missing server IP address
    echo.
    echo Usage: %~nx0 [server-ip] [domain-name]
    echo Example: %~nx0 123.45.67.89 yourdomain.com
    exit /b 1
)

if "%~2"=="" (
    echo Error: Missing domain name
    echo.
    echo Usage: %~nx0 [server-ip] [domain-name]
    echo Example: %~nx0 123.45.67.89 yourdomain.com
    exit /b 1
)

set SERVER_IP=%~1
set DOMAIN_NAME=%~2
set SERVER_USER=root
set REMOTE_DIR=/var/www/shotsbywhatsername

echo ========================================
echo Shots By Whatsername - Deployment
echo ========================================
echo.
echo Server IP: %SERVER_IP%
echo Domain: %DOMAIN_NAME%
echo Remote Directory: %REMOTE_DIR%
echo.

REM Check for SSH client
where ssh >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo Error: SSH client not found
    echo Please install OpenSSH for Windows or PuTTY
    echo.
    echo To install OpenSSH:
    echo   Settings ^> Apps ^> Optional Features ^> Add OpenSSH Client
    exit /b 1
)

REM Check for SCP
where scp >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo Error: SCP not found
    echo Please install OpenSSH for Windows
    exit /b 1
)

echo [Step 1] Testing SSH connection...
ssh -o ConnectTimeout=5 -o BatchMode=yes %SERVER_USER%@%SERVER_IP% exit 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo Error: Cannot connect to server via SSH
    echo Please ensure you can SSH to: %SERVER_USER%@%SERVER_IP%
    echo You may need to accept the host key first: ssh %SERVER_USER%@%SERVER_IP%
    pause
    exit /b 1
)
echo [OK] SSH connection successful
echo.

echo [Step 2] Checking configuration files...
if not exist "config.php" (
    echo Warning: config.php not found
    echo Creating from config.example.php...
    copy config.example.php config.php >nul
    echo.
    echo IMPORTANT: Please edit config.php with your production settings
    echo Press any key when ready to continue...
    pause >nul
)
echo [OK] Configuration files ready
echo.

echo [Step 3] Creating remote directory...
ssh %SERVER_USER%@%SERVER_IP% "mkdir -p %REMOTE_DIR%"
if %ERRORLEVEL% NEQ 0 (
    echo Error: Failed to create remote directory
    exit /b 1
)
echo [OK] Remote directory created
echo.

echo [Step 4] Uploading files...
echo This may take a few minutes...

REM Create temporary exclude list
(
echo .git
echo .gitignore
echo .vscode
echo node_modules
echo *.log
echo *.bak
echo test_imgur.php
echo deploy.sh
echo deploy.bat
) > %TEMP%\deploy_exclude.txt

REM Upload files using SCP (recursive)
REM Note: This is a simplified version. For better performance, use rsync with WSL
echo Uploading application files...
scp -r * %SERVER_USER%@%SERVER_IP%:%REMOTE_DIR%/ 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo Warning: Some files may not have uploaded correctly
    echo Please verify manually
)

del %TEMP%\deploy_exclude.txt
echo [OK] Files uploaded
echo.

echo [Step 5] Setting file permissions...
ssh %SERVER_USER%@%SERVER_IP% "cd %REMOTE_DIR% && chown -R www-data:www-data . && find . -type d -exec chmod 755 {} \; && find . -type f -exec chmod 644 {} \; && chmod 600 config.php"
if %ERRORLEVEL% EQU 0 (
    echo [OK] Permissions set
) else (
    echo Warning: Could not set all permissions
)
echo.

echo [Step 6] Configuring Apache...
ssh %SERVER_USER%@%SERVER_IP% "cat > /etc/apache2/sites-available/shotsbywhatsername.conf << 'EOF'
<VirtualHost *:80>
    ServerName %DOMAIN_NAME%
    ServerAlias www.%DOMAIN_NAME%
    ServerAdmin admin@%DOMAIN_NAME%
    
    DocumentRoot %REMOTE_DIR%
    
    <Directory %REMOTE_DIR%>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    Header always set X-Content-Type-Options \"nosniff\"
    Header always set X-Frame-Options \"SAMEORIGIN\"
    Header always set X-XSS-Protection \"1; mode=block\"
    Header unset X-Powered-By
    
    ErrorLog ${APACHE_LOG_DIR}/shotsbywhatsername-error.log
    CustomLog ${APACHE_LOG_DIR}/shotsbywhatsername-access.log combined
</VirtualHost>
EOF
"
ssh %SERVER_USER%@%SERVER_IP% "a2enmod rewrite headers ssl && a2ensite shotsbywhatsername.conf && systemctl restart apache2"
if %ERRORLEVEL% EQU 0 (
    echo [OK] Apache configured and restarted
) else (
    echo Warning: Apache configuration may need manual adjustment
)
echo.

echo ========================================
echo Deployment Script Completed
echo ========================================
echo.
echo IMPORTANT: Complete these manual steps:
echo.
echo 1. Configure the database:
echo    ssh %SERVER_USER%@%SERVER_IP%
echo    mysql -u root -p
echo    CREATE DATABASE shots_by_whatsername;
echo    CREATE USER 'shotsuser'@'localhost' IDENTIFIED BY 'password';
echo    GRANT ALL PRIVILEGES ON shots_by_whatsername.* TO 'shotsuser'@'localhost';
echo    FLUSH PRIVILEGES;
echo    EXIT;
echo    mysql -u shotsuser -p shots_by_whatsername ^< %REMOTE_DIR%/sample_data.sql
echo.
echo 2. Update config.php on server with correct credentials
echo.
echo 3. Install SSL certificate:
echo    ssh %SERVER_USER%@%SERVER_IP%
echo    apt install -y certbot python3-certbot-apache
echo    certbot --apache -d %DOMAIN_NAME% -d www.%DOMAIN_NAME%
echo.
echo 4. Test the site: http://%DOMAIN_NAME%
echo.
echo 5. Run diagnostics: http://%DOMAIN_NAME%/test_imgur.php
echo.
echo 6. Remove test file: rm %REMOTE_DIR%/test_imgur.php
echo.
echo For detailed instructions, see DEPLOYMENT.md
echo.

set /p CONNECT="Would you like to connect to the server now? (Y/N): "
if /i "%CONNECT%"=="Y" (
    ssh %SERVER_USER%@%SERVER_IP%
)

endlocal
