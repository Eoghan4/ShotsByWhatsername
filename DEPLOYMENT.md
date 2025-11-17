# Deployment Guide - Hetzner Server

This guide will walk you through deploying the Shots By Whatsername photography portfolio to a Hetzner server.

## Prerequisites

- Hetzner server (VPS or dedicated)
- SSH access to your server
- Domain name (optional but recommended)
- Imgur API Client ID

## Server Requirements

- **OS**: Ubuntu 20.04/22.04 or Debian 11/12
- **PHP**: 7.4 or higher
- **MySQL/MariaDB**: 5.7+ or 10.3+
- **Web Server**: Apache or Nginx
- **PHP Extensions**: 
  - pdo_mysql
  - curl
  - mbstring
  - fileinfo
  - gd (recommended for image processing)

## Step-by-Step Deployment

### 1. Initial Server Setup

```bash
# Connect to your Hetzner server
ssh root@your-server-ip

# Update system packages
apt update && apt upgrade -y

# Install required packages
apt install -y apache2 mysql-server php php-mysql php-curl php-mbstring php-gd php-xml unzip git
```

### 2. Configure MySQL Database

```bash
# Secure MySQL installation
mysql_secure_installation

# Create database and user
mysql -u root -p
```

Execute in MySQL:
```sql
CREATE DATABASE shots_by_whatsername CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'shotsuser'@'localhost' IDENTIFIED BY 'your_secure_password_here';
GRANT ALL PRIVILEGES ON shots_by_whatsername.* TO 'shotsuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Configure Apache

```bash
# Enable required Apache modules
a2enmod rewrite
a2enmod ssl
a2enmod headers

# Create virtual host configuration
nano /etc/apache2/sites-available/shotsbywhatsername.conf
```

Add the following configuration:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    ServerAdmin admin@your-domain.com
    
    DocumentRoot /var/www/shotsbywhatsername
    
    <Directory /var/www/shotsbywhatsername>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    
    # Hide PHP version
    Header unset X-Powered-By
    
    ErrorLog ${APACHE_LOG_DIR}/shotsbywhatsername-error.log
    CustomLog ${APACHE_LOG_DIR}/shotsbywhatsername-access.log combined
</VirtualHost>
```

```bash
# Enable the site
a2ensite shotsbywhatsername.conf

# Disable default site
a2dissite 000-default.conf

# Test configuration
apache2ctl configtest

# Restart Apache
systemctl restart apache2
```

### 4. Deploy Application Files

```bash
# Create web directory
mkdir -p /var/www/shotsbywhatsername

# Option A: Upload via SCP from your local machine
# Run this from your LOCAL machine:
scp -r /path/to/ShotsByWhatsername/* root@your-server-ip:/var/www/shotsbywhatsername/

# Option B: Clone from Git (if you're using a private repo)
cd /var/www/shotsbywhatsername
git clone https://github.com/Eoghan4/ShotsByWhatsername.git .

# Set proper ownership
chown -R www-data:www-data /var/www/shotsbywhatsername

# Set proper permissions
find /var/www/shotsbywhatsername -type d -exec chmod 755 {} \;
find /var/www/shotsbywhatsername -type f -exec chmod 644 {} \;
```

### 5. Configure Application

```bash
cd /var/www/shotsbywhatsername

# Copy example config
cp config.example.php config.php

# Edit configuration
nano config.php
```

Update `config.php` with your production settings:

```php
<?php
// Environment Setting
define('ENVIRONMENT', 'production'); // IMPORTANT: Set to production

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'shots_by_whatsername');
define('DB_USER', 'shotsuser');
define('DB_PASS', 'your_secure_password_here'); // Use the password from Step 2

// Imgur API Configuration
define('IMGUR_CLIENT_ID', 'your_imgur_client_id'); // From Imgur API

// File Upload Settings (keep defaults or adjust)
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Security Settings
define('SESSION_TIMEOUT', 3600); // 1 hour
?>
```

```bash
# Secure the config file
chmod 600 config.php
chown www-data:www-data config.php
```

### 6. Import Database

```bash
# Import the database schema and sample data
mysql -u shotsuser -p shots_by_whatsername < /var/www/shotsbywhatsername/sample_data.sql
```

### 7. Configure PHP (Production Settings)

```bash
# Edit PHP configuration
nano /etc/php/8.1/apache2/php.ini  # Adjust version number as needed
```

Update these settings:
```ini
; Security
expose_php = Off
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; File uploads
file_uploads = On
upload_max_filesize = 10M
post_max_size = 12M
max_file_uploads = 20

; Session security
session.cookie_httponly = 1
session.cookie_secure = 1  ; Only after SSL is configured
session.use_strict_mode = 1
session.cookie_samesite = "Lax"

; Memory and execution
memory_limit = 256M
max_execution_time = 60
max_input_time = 60
```

```bash
# Create PHP log directory
mkdir -p /var/log/php
chown www-data:www-data /var/log/php

# Restart Apache
systemctl restart apache2
```

### 8. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
apt install -y certbot python3-certbot-apache

# Obtain SSL certificate
certbot --apache -d your-domain.com -d www.your-domain.com

# Enable auto-renewal
certbot renew --dry-run
```

Certbot will automatically:
- Obtain an SSL certificate
- Configure Apache for HTTPS
- Set up automatic renewal

After SSL is configured, update `php.ini`:
```bash
nano /etc/php/8.1/apache2/php.ini
```
Set: `session.cookie_secure = 1`

```bash
systemctl restart apache2
```

### 9. Create .htaccess for Security

Create `/var/www/shotsbywhatsername/.htaccess`:

```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Protect sensitive files
<FilesMatch "^(config\.php|\.gitignore|\.htaccess|README\.md|DEPLOYMENT\.md)$">
    Require all denied
</FilesMatch>

# Prevent directory browsing
Options -Indexes

# Protect against XSS
<IfModule mod_headers.c>
    Header set X-XSS-Protection "1; mode=block"
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# PHP security
php_flag display_errors Off
php_flag log_errors On
```

### 10. Configure Firewall

```bash
# Install UFW if not already installed
apt install -y ufw

# Configure firewall rules
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Apache Full'

# Enable firewall
ufw enable
ufw status
```

### 11. Post-Deployment Tasks

#### Change Default Admin Password

```bash
# Generate new password hash
php -r "echo password_hash('your_new_secure_password', PASSWORD_DEFAULT) . PHP_EOL;"

# Update in database
mysql -u shotsuser -p shots_by_whatsername
```

```sql
UPDATE users SET password_hash = 'paste_hash_here' WHERE email = 'eoghanmcgough@gmail.com';
EXIT;
```

#### Set Up Automated Backups

Create backup script `/root/backup-shots.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/root/backups"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u shotsuser -pyour_secure_password_here shots_by_whatsername > $BACKUP_DIR/db_$DATE.sql

# Backup files (excluding pictures if they're on Imgur)
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/shotsbywhatsername --exclude='pictures'

# Keep only last 7 days of backups
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
chmod +x /root/backup-shots.sh

# Add to crontab (daily at 2 AM)
crontab -e
```

Add:
```
0 2 * * * /root/backup-shots.sh >> /var/log/backup-shots.log 2>&1
```

### 12. Monitoring and Logging

```bash
# Install log rotation for application logs
nano /etc/logrotate.d/shotsbywhatsername
```

Add:
```
/var/log/php/error.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### 13. Final Checks

```bash
# Test the site
curl -I https://your-domain.com

# Check PHP errors
tail -f /var/log/php/error.log

# Check Apache errors
tail -f /var/log/apache2/shotsbywhatsername-error.log

# Test upload functionality
# Navigate to https://your-domain.com/test_imgur.php

# Remove test file after verification
rm /var/www/shotsbywhatsername/test_imgur.php
```

## Security Checklist

- [ ] `ENVIRONMENT` set to `'production'` in config.php
- [ ] Database user with strong password (not root)
- [ ] config.php has 600 permissions
- [ ] SSL certificate installed and working
- [ ] `session.cookie_secure = 1` enabled
- [ ] Default admin password changed
- [ ] Firewall configured and enabled
- [ ] `display_errors = Off` in php.ini
- [ ] .htaccess protects sensitive files
- [ ] Automated backups configured
- [ ] test_imgur.php removed from production

## Nginx Alternative Configuration

If you prefer Nginx over Apache:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;

    root /var/www/shotsbywhatsername;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Hide Nginx version
    server_tokens off;

    # File upload size
    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /(config\.php|\.git|\.htaccess|README\.md|DEPLOYMENT\.md) {
        deny all;
        return 404;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
        return 404;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|webp)$ {
        expires 7d;
        add_header Cache-Control "public, immutable";
    }

    access_log /var/log/nginx/shotsbywhatsername-access.log;
    error_log /var/log/nginx/shotsbywhatsername-error.log;
}
```

## Troubleshooting

### Database Connection Issues
```bash
# Check MySQL is running
systemctl status mysql

# Test connection
mysql -u shotsuser -p shots_by_whatsername -e "SELECT 1"
```

### Permission Issues
```bash
# Reset permissions
chown -R www-data:www-data /var/www/shotsbywhatsername
find /var/www/shotsbywhatsername -type d -exec chmod 755 {} \;
find /var/www/shotsbywhatsername -type f -exec chmod 644 {} \;
chmod 600 /var/www/shotsbywhatsername/config.php
```

### Upload Issues
```bash
# Check PHP upload settings
php -i | grep upload

# Check PHP error log
tail -f /var/log/php/error.log
```

### Imgur API Issues
```bash
# Test API connectivity
curl -H "Authorization: Client-ID your_client_id" https://api.imgur.com/3/credits
```

## Performance Optimization

### Enable PHP OPcache
```bash
nano /etc/php/8.1/apache2/php.ini
```

Add/enable:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

### Enable Apache Compression
```bash
a2enmod deflate
systemctl restart apache2
```

## Maintenance

### Regular Updates
```bash
# Update system packages monthly
apt update && apt upgrade -y

# Update SSL certificates (automatic via certbot)
certbot renew

# Check for security updates
apt list --upgradable | grep security
```

### Monitor Disk Space
```bash
# Check disk usage
df -h

# Check database size
mysql -u shotsuser -p -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.tables WHERE table_schema = 'shots_by_whatsername' GROUP BY table_schema;"
```

## Support

For issues or questions:
- Check logs: `/var/log/apache2/` or `/var/log/nginx/`
- PHP errors: `/var/log/php/error.log`
- Application errors: Check error messages in browser (dev environment only)

---

**Last Updated**: November 17, 2025
