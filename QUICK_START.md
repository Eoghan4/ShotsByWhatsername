# Hetzner Deployment - Quick Start Guide

## Overview
This guide provides the fastest path to deploy Shots By Whatsername to a Hetzner server.

## Prerequisites Checklist
- [ ] Hetzner VPS or dedicated server with root access
- [ ] Domain name with DNS configured (A record pointing to server IP)
- [ ] Imgur API Client ID ([Get it here](https://api.imgur.com/oauth2/addclient))
- [ ] SSH access configured from your local machine

## Deployment Options

### Option 1: Automated Script (Recommended)

#### From Linux/Mac:
```bash
chmod +x deploy.sh
./deploy.sh YOUR_SERVER_IP YOUR_DOMAIN.COM
```

#### From Windows:
```cmd
deploy.bat YOUR_SERVER_IP YOUR_DOMAIN.COM
```

Follow the on-screen prompts. The script will:
- Upload all files
- Configure Apache
- Set permissions
- Guide you through database setup

### Option 2: Manual Deployment

#### 1. Connect to Server
```bash
ssh root@YOUR_SERVER_IP
```

#### 2. Install Required Software
```bash
apt update && apt upgrade -y
apt install -y apache2 mysql-server php php-mysql php-curl php-mbstring php-gd php-xml
```

#### 3. Create Database
```bash
mysql -u root -p
```
```sql
CREATE DATABASE shots_by_whatsername CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'shotsuser'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON shots_by_whatsername.* TO 'shotsuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 4. Upload Files (from your local machine)
```bash
scp -r ./* root@YOUR_SERVER_IP:/var/www/shotsbywhatsername/
```

#### 5. Configure Application
```bash
ssh root@YOUR_SERVER_IP
cd /var/www/shotsbywhatsername
cp config.example.php config.php
nano config.php
```

Update these values in `config.php`:
```php
define('ENVIRONMENT', 'production');
define('DB_USER', 'shotsuser');
define('DB_PASS', 'STRONG_PASSWORD_HERE');
define('IMGUR_CLIENT_ID', 'YOUR_IMGUR_CLIENT_ID');
```

#### 6. Import Database
```bash
mysql -u shotsuser -p shots_by_whatsername < sample_data.sql
```

#### 7. Set Permissions
```bash
chown -R www-data:www-data /var/www/shotsbywhatsername
find /var/www/shotsbywhatsername -type d -exec chmod 755 {} \;
find /var/www/shotsbywhatsername -type f -exec chmod 644 {} \;
chmod 600 /var/www/shotsbywhatsername/config.php
```

#### 8. Configure Apache
```bash
nano /etc/apache2/sites-available/shotsbywhatsername.conf
```

Paste this configuration:
```apache
<VirtualHost *:80>
    ServerName YOUR_DOMAIN.COM
    ServerAlias www.YOUR_DOMAIN.COM
    DocumentRoot /var/www/shotsbywhatsername
    
    <Directory /var/www/shotsbywhatsername>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/shotsbywhatsername-error.log
    CustomLog ${APACHE_LOG_DIR}/shotsbywhatsername-access.log combined
</VirtualHost>
```

Enable and restart:
```bash
a2enmod rewrite headers ssl
a2ensite shotsbywhatsername.conf
a2dissite 000-default.conf
systemctl restart apache2
```

#### 9. Install SSL Certificate
```bash
apt install -y certbot python3-certbot-apache
certbot --apache -d YOUR_DOMAIN.COM -d www.YOUR_DOMAIN.COM
```

#### 10. Update PHP for Production
```bash
nano /etc/php/8.1/apache2/php.ini
```

Set these values:
```ini
display_errors = Off
expose_php = Off
upload_max_filesize = 10M
post_max_size = 12M
session.cookie_secure = 1
```

Restart Apache:
```bash
systemctl restart apache2
```

## Post-Deployment Testing

### 1. Test Basic Functionality
- Visit: `https://YOUR_DOMAIN.COM`
- Check: Homepage loads correctly
- Check: Gallery displays sample images

### 2. Test Admin Functions
- Visit: `https://YOUR_DOMAIN.COM/login/`
- Login with: `eoghanmcgough@gmail.com` / `admin123`
- Try uploading an image

### 3. Run Diagnostic Test
- Visit: `https://YOUR_DOMAIN.COM/test_imgur.php`
- Verify all tests pass (especially Imgur API connection)
- **DELETE the test file after verification:**
  ```bash
  ssh root@YOUR_SERVER_IP
  rm /var/www/shotsbywhatsername/test_imgur.php
  ```

### 4. Security Check
- [ ] Change default admin password
- [ ] Verify HTTPS is working
- [ ] Check that `config.php` is not accessible via browser
- [ ] Verify file uploads work correctly

## Change Default Admin Password

```bash
ssh root@YOUR_SERVER_IP
php -r "echo password_hash('YOUR_NEW_PASSWORD', PASSWORD_DEFAULT);"
# Copy the hash output

mysql -u shotsuser -p shots_by_whatsername
```
```sql
UPDATE users SET password_hash='PASTE_HASH_HERE' WHERE email='eoghanmcgough@gmail.com';
EXIT;
```

## Configure Firewall

```bash
apt install -y ufw
ufw allow ssh
ufw allow 'Apache Full'
ufw enable
```

## Set Up Automatic Backups

Create backup script:
```bash
nano /root/backup-shots.sh
```

Paste:
```bash
#!/bin/bash
BACKUP_DIR="/root/backups"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

mysqldump -u shotsuser -pYOUR_DB_PASSWORD shots_by_whatsername > $BACKUP_DIR/db_$DATE.sql
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/shotsbywhatsername --exclude='pictures'

find $BACKUP_DIR -type f -mtime +7 -delete
```

Make executable and schedule:
```bash
chmod +x /root/backup-shots.sh
crontab -e
```

Add this line (runs daily at 2 AM):
```
0 2 * * * /root/backup-shots.sh >> /var/log/backup-shots.log 2>&1
```

## Troubleshooting

### Site not loading
```bash
systemctl status apache2
tail -f /var/log/apache2/shotsbywhatsername-error.log
```

### Upload fails
```bash
tail -f /var/log/php/error.log
# Check Imgur Client ID in config.php
# Verify cURL is enabled: php -m | grep curl
```

### Database errors
```bash
systemctl status mysql
mysql -u shotsuser -p shots_by_whatsername -e "SELECT 1"
```

### Permission issues
```bash
chown -R www-data:www-data /var/www/shotsbywhatsername
chmod 600 /var/www/shotsbywhatsername/config.php
```

## Quick Reference

### Essential Commands
```bash
# View logs
tail -f /var/log/apache2/shotsbywhatsername-error.log

# Restart Apache
systemctl restart apache2

# Connect to database
mysql -u shotsuser -p shots_by_whatsername

# Check disk space
df -h

# Monitor resources
htop
```

### Important Files
- Application: `/var/www/shotsbywhatsername/`
- Config: `/var/www/shotsbywhatsername/config.php`
- Apache config: `/etc/apache2/sites-available/shotsbywhatsername.conf`
- Apache logs: `/var/log/apache2/shotsbywhatsername-*.log`
- PHP config: `/etc/php/8.1/apache2/php.ini`

## Getting Help

- Full deployment guide: See `DEPLOYMENT.md`
- Server commands: See `SERVER_COMMANDS.md`
- Deployment checklist: See `DEPLOYMENT_CHECKLIST.md`

## Estimated Deployment Time

- Automated script: 15-20 minutes
- Manual deployment: 30-45 minutes
- Including SSL setup: +10 minutes
- Total with testing: ~1 hour

---

**Need help?** Check the full documentation in DEPLOYMENT.md or review server logs for specific errors.

**Ready to deploy?** Run `./deploy.sh YOUR_SERVER_IP YOUR_DOMAIN.COM` to get started!
