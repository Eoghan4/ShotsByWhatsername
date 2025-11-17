# Quick Server Commands Reference

## SSH Connection
```bash
ssh root@your-server-ip
```

## Service Management
```bash
# Apache
systemctl status apache2
systemctl restart apache2
systemctl reload apache2
systemctl stop apache2
systemctl start apache2

# Nginx (alternative)
systemctl status nginx
systemctl restart nginx

# MySQL/MariaDB
systemctl status mysql
systemctl restart mysql
```

## Log Viewing
```bash
# Apache Error Log
tail -f /var/log/apache2/shotsbywhatsername-error.log

# Apache Access Log
tail -f /var/log/apache2/shotsbywhatsername-access.log

# PHP Error Log
tail -f /var/log/php/error.log

# MySQL Error Log
tail -f /var/log/mysql/error.log

# System Log
tail -f /var/log/syslog
```

## File Operations
```bash
# Navigate to application directory
cd /var/www/shotsbywhatsername

# List files with permissions
ls -la

# Change ownership to web server user
chown -R www-data:www-data /var/www/shotsbywhatsername

# Set directory permissions
find /var/www/shotsbywhatsername -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/shotsbywhatsername -type f -exec chmod 644 {} \;

# Secure config file
chmod 600 /var/www/shotsbywhatsername/config.php
```

## Database Operations
```bash
# Connect to MySQL
mysql -u shotsuser -p shots_by_whatsername

# Backup database
mysqldump -u shotsuser -p shots_by_whatsername > backup_$(date +%Y%m%d).sql

# Restore database
mysql -u shotsuser -p shots_by_whatsername < backup_20250117.sql

# Import initial data
mysql -u shotsuser -p shots_by_whatsername < sample_data.sql
```

## MySQL Queries (from command line)
```bash
# Count images
mysql -u shotsuser -p -e "SELECT COUNT(*) FROM images;" shots_by_whatsername

# List recent uploads
mysql -u shotsuser -p -e "SELECT id, title, category, url FROM images ORDER BY id DESC LIMIT 10;" shots_by_whatsername

# List all categories
mysql -u shotsuser -p -e "SELECT DISTINCT category FROM images;" shots_by_whatsername

# Change admin password (first generate hash)
php -r "echo password_hash('new_password', PASSWORD_DEFAULT);"
# Then update in database
mysql -u shotsuser -p -e "UPDATE users SET password_hash='PASTE_HASH_HERE' WHERE email='eoghanmcgough@gmail.com';" shots_by_whatsername
```

## SSL Certificate Management
```bash
# Install certbot
apt install -y certbot python3-certbot-apache

# Obtain certificate
certbot --apache -d yourdomain.com -d www.yourdomain.com

# Renew certificates (automatic)
certbot renew

# Test renewal
certbot renew --dry-run

# List certificates
certbot certificates
```

## Firewall (UFW)
```bash
# Check status
ufw status

# Enable firewall
ufw enable

# Allow SSH
ufw allow ssh

# Allow HTTP and HTTPS
ufw allow 'Apache Full'

# Deny specific port
ufw deny 8080

# Delete rule
ufw delete allow 8080
```

## Disk Space Management
```bash
# Check disk usage
df -h

# Check directory size
du -sh /var/www/shotsbywhatsername

# Find large files
find / -type f -size +100M -exec ls -lh {} \;

# Clean up old logs
find /var/log -type f -name "*.log" -mtime +30 -delete

# Clean package cache
apt clean
apt autoclean
```

## Monitoring
```bash
# System resources
htop  # or 'top' if htop not installed

# Current connections
netstat -tuln

# Active Apache connections
netstat -an | grep :80 | wc -l

# Memory usage
free -h

# CPU info
lscpu

# Process list
ps aux | grep apache
ps aux | grep mysql
```

## PHP Information
```bash
# PHP version
php -v

# PHP modules
php -m

# PHP configuration
php -i | grep upload
php -i | grep error

# Test PHP file
echo "<?php phpinfo(); ?>" > /var/www/html/info.php
# Visit http://your-domain/info.php then DELETE it
rm /var/www/html/info.php
```

## Apache Configuration
```bash
# Test configuration
apache2ctl configtest

# List enabled sites
ls -l /etc/apache2/sites-enabled/

# Enable site
a2ensite shotsbywhatsername.conf

# Disable site
a2dissite shotsbywhatsername.conf

# Enable module
a2enmod rewrite
a2enmod headers
a2enmod ssl

# Disable module
a2dismod status
```

## Backup Commands
```bash
# Full site backup
tar -czf backup_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/shotsbywhatsername

# Database backup
mysqldump -u shotsuser -p shots_by_whatsername | gzip > db_backup_$(date +%Y%m%d_%H%M%S).sql.gz

# Combined backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p /root/backups
mysqldump -u shotsuser -p'PASSWORD' shots_by_whatsername > /root/backups/db_$DATE.sql
tar -czf /root/backups/files_$DATE.tar.gz /var/www/shotsbywhatsername
echo "Backup completed: $DATE"
```

## Testing & Debugging
```bash
# Test Imgur API connection
curl -H "Authorization: Client-ID your_client_id" https://api.imgur.com/3/credits

# Check PHP syntax
php -l /var/www/shotsbywhatsername/upload/index.php

# Test database connection
mysql -u shotsuser -p -e "SELECT 1;" shots_by_whatsername

# Check file permissions
ls -la /var/www/shotsbywhatsername/config.php

# Test site accessibility
curl -I http://localhost
curl -I https://yourdomain.com

# Check Apache configuration
apache2ctl -S
```

## Security Commands
```bash
# Check for failed login attempts
grep "Failed password" /var/log/auth.log | tail -20

# Check for suspicious activity
grep "404" /var/log/apache2/shotsbywhatsername-access.log | tail -20

# List active sessions
who

# Check listening ports
netstat -tuln | grep LISTEN

# Update system packages
apt update && apt upgrade -y

# Install fail2ban (optional)
apt install -y fail2ban
systemctl enable fail2ban
systemctl start fail2ban
```

## Emergency Procedures
```bash
# Stop Apache (if under attack)
systemctl stop apache2

# Block IP address
ufw deny from 123.45.67.89

# Restore from backup
tar -xzf backup_20250117.tar.gz -C /
mysql -u shotsuser -p shots_by_whatsername < backup_20250117.sql

# Clear PHP OPcache (if enabled)
systemctl restart apache2

# Reset file permissions after upload
chown -R www-data:www-data /var/www/shotsbywhatsername
find /var/www/shotsbywhatsername -type d -exec chmod 755 {} \;
find /var/www/shotsbywhatsername -type f -exec chmod 644 {} \;
chmod 600 /var/www/shotsbywhatsername/config.php
```

## Quick Troubleshooting
```bash
# Website not loading
systemctl status apache2
tail -f /var/log/apache2/error.log

# Database connection errors
systemctl status mysql
mysql -u shotsuser -p shots_by_whatsername

# Upload not working
tail -f /var/log/php/error.log
ls -la /var/www/shotsbywhatsername/config.php
php -i | grep upload_max_filesize

# SSL issues
certbot certificates
tail -f /var/log/letsencrypt/letsencrypt.log

# Permission denied errors
ls -la /var/www/shotsbywhatsername
chown -R www-data:www-data /var/www/shotsbywhatsername
```

## Useful Shortcuts
```bash
# Create alias for quick navigation
echo "alias cdweb='cd /var/www/shotsbywhatsername'" >> ~/.bashrc
source ~/.bashrc

# Quick log check
alias weberr='tail -f /var/log/apache2/shotsbywhatsername-error.log'
alias phperr='tail -f /var/log/php/error.log'

# Quick restart
alias restartweb='systemctl restart apache2 && echo "Apache restarted"'
```

---

**Save this file for quick reference during deployment and maintenance!**
