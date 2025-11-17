# Pre-Deployment Checklist

## Before Uploading to Server

### Local Configuration
- [ ] Copy `config.example.php` to `config.php`
- [ ] Update database credentials in `config.php`
- [ ] Set Imgur API Client ID in `config.php`
- [ ] Set `ENVIRONMENT` to `'production'` in `config.php`
- [ ] Test application locally with production config
- [ ] Run all functionality tests (login, upload, gallery)
- [ ] Remove or secure `test_imgur.php`

### Code Review
- [ ] Check for debug statements or console.logs
- [ ] Verify all error messages are user-friendly (no stack traces)
- [ ] Ensure no hardcoded credentials anywhere
- [ ] Review all SQL queries use prepared statements
- [ ] Check XSS protection on all user inputs
- [ ] Verify file upload validation is complete

### Files to Upload
- [ ] All PHP files
- [ ] All HTML files
- [ ] CSS and JavaScript files
- [ ] Images in `/pictures/` directory
- [ ] `sample_data.sql`
- [ ] `.htaccess`
- [ ] `config.php` (configured for production)

### Files NOT to Upload
- [ ] `.git/` directory
- [ ] `.vscode/` or `.idea/` directories
- [ ] `test_imgur.php` (or remove after testing)
- [ ] `deploy.sh` (optional)
- [ ] `.env` files
- [ ] `README.md` and `DEPLOYMENT.md` (optional, but safe)

---

## Server Setup

### Initial Server Access
- [ ] SSH access configured
- [ ] Root or sudo user access available
- [ ] Server IP address noted
- [ ] Domain name configured (DNS A record pointing to server)

### Software Installation
- [ ] Apache or Nginx installed
- [ ] MySQL/MariaDB installed
- [ ] PHP 7.4+ installed
- [ ] PHP extensions: pdo_mysql, curl, mbstring, gd, fileinfo

### Database Setup
- [ ] MySQL root password set
- [ ] Database `shots_by_whatsername` created
- [ ] Database user `shotsuser` created with secure password
- [ ] User permissions granted on database
- [ ] `sample_data.sql` imported
- [ ] Default admin password changed

### Web Server Configuration
- [ ] Virtual host/server block configured
- [ ] DocumentRoot pointing to application directory
- [ ] `.htaccess` support enabled (Apache: AllowOverride All)
- [ ] Rewrite module enabled
- [ ] Headers module enabled

### File Permissions
- [ ] Web directory owned by www-data:www-data (or appropriate user)
- [ ] Directories: 755 permissions
- [ ] Files: 644 permissions
- [ ] `config.php`: 600 permissions
- [ ] No world-writable directories

---

## Security Configuration

### SSL/TLS
- [ ] SSL certificate installed (Let's Encrypt recommended)
- [ ] HTTPS redirect configured
- [ ] HTTP Strict Transport Security (HSTS) enabled
- [ ] `session.cookie_secure = 1` in php.ini

### PHP Security
- [ ] `display_errors = Off` in php.ini
- [ ] `expose_php = Off` in php.ini
- [ ] `log_errors = On` in php.ini
- [ ] Error log path configured
- [ ] `open_basedir` restriction set (optional)
- [ ] `disable_functions` configured for dangerous functions (optional)

### Application Security
- [ ] Default admin credentials changed
- [ ] CSRF tokens implemented and working
- [ ] Session timeout configured
- [ ] File upload restrictions in place
- [ ] SQL injection protection verified
- [ ] XSS protection verified

### Firewall
- [ ] UFW or iptables configured
- [ ] Only ports 22 (SSH), 80 (HTTP), 443 (HTTPS) open
- [ ] SSH port changed from 22 (optional but recommended)
- [ ] Fail2ban installed and configured (optional)

---

## Post-Deployment Testing

### Functionality Tests
- [ ] Homepage loads correctly
- [ ] Gallery page displays images
- [ ] About and Contact pages load
- [ ] Login functionality works
- [ ] Admin can access upload page
- [ ] Image upload to Imgur works
- [ ] Images appear in gallery after upload
- [ ] Category filtering works
- [ ] Lightbox works for images
- [ ] Logout works correctly

### Security Tests
- [ ] HTTPS is enforced (no HTTP access)
- [ ] `config.php` not accessible via browser
- [ ] `.htaccess` not accessible via browser
- [ ] Directory listing disabled
- [ ] SQL injection attempts blocked
- [ ] File upload restrictions working
- [ ] Session security working

### Performance Tests
- [ ] Page load times acceptable (<3 seconds)
- [ ] Images loading properly
- [ ] No console errors in browser
- [ ] Mobile responsive design working
- [ ] Imgur API rate limits sufficient

---

## Monitoring & Maintenance

### Logging
- [ ] PHP error log location noted
- [ ] Apache/Nginx error log location noted
- [ ] Log rotation configured
- [ ] Monitoring tool installed (optional)

### Backups
- [ ] Database backup script created
- [ ] File backup script created
- [ ] Backup schedule configured (daily recommended)
- [ ] Backup restoration tested
- [ ] Off-site backup storage configured (optional)

### Monitoring
- [ ] Uptime monitoring configured (optional)
- [ ] Disk space monitoring
- [ ] SSL certificate expiry monitoring
- [ ] Error rate monitoring (optional)

### Documentation
- [ ] Server credentials stored securely
- [ ] Database credentials documented
- [ ] Imgur API credentials documented
- [ ] Admin account credentials documented
- [ ] Backup locations documented
- [ ] Emergency contacts documented

---

## Optional Enhancements

### Performance
- [ ] PHP OPcache enabled
- [ ] Gzip compression enabled
- [ ] CDN configured for static assets (optional)
- [ ] Database query optimization
- [ ] Caching headers configured

### Advanced Security
- [ ] Web Application Firewall (WAF) configured
- [ ] Rate limiting on login attempts
- [ ] Two-factor authentication (2FA) for admin
- [ ] Regular security audits scheduled
- [ ] Automated security updates

### Monitoring
- [ ] Google Analytics or similar
- [ ] Error tracking service (Sentry, etc.)
- [ ] Server monitoring (Netdata, etc.)
- [ ] Log aggregation service

---

## Rollback Plan

### If Deployment Fails
- [ ] Previous version backup available
- [ ] Database rollback script ready
- [ ] Quick restoration procedure documented
- [ ] Communication plan for downtime

---

## Sign-Off

- [ ] All tests passed
- [ ] Client/stakeholder approval received
- [ ] Documentation completed
- [ ] Team notified of deployment

**Deployed By:** ___________________

**Date:** ___________________

**Server IP:** ___________________

**Domain:** ___________________

**Notes:**
_______________________________________________________________
_______________________________________________________________
_______________________________________________________________
