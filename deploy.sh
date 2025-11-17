#!/bin/bash

#################################################################################
# Automated Deployment Script for Shots By Whatsername
# 
# This script automates the deployment process to a Hetzner server
# Run from your LOCAL machine, not on the server
#
# Usage: ./deploy.sh [server-ip] [domain-name]
# Example: ./deploy.sh 123.45.67.89 yourdomain.com
#################################################################################

set -e  # Exit on error

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SERVER_IP=${1:-""}
DOMAIN_NAME=${2:-""}
SERVER_USER="root"
REMOTE_DIR="/var/www/shotsbywhatsername"
LOCAL_DIR="."

# Functions
print_header() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

# Check if required parameters are provided
if [ -z "$SERVER_IP" ] || [ -z "$DOMAIN_NAME" ]; then
    print_error "Missing required parameters"
    echo ""
    echo "Usage: $0 [server-ip] [domain-name]"
    echo "Example: $0 123.45.67.89 yourdomain.com"
    exit 1
fi

# Verify SSH connection
print_header "Step 1: Verifying SSH Connection"
if ssh -o ConnectTimeout=5 -o BatchMode=yes "$SERVER_USER@$SERVER_IP" exit 2>/dev/null; then
    print_success "SSH connection successful"
else
    print_error "Cannot connect to server via SSH"
    print_info "Make sure you can SSH to the server: ssh $SERVER_USER@$SERVER_IP"
    exit 1
fi

# Check if config.php exists locally
print_header "Step 2: Checking Configuration Files"
if [ ! -f "config.php" ]; then
    print_warning "config.php not found locally"
    print_info "Creating from config.example.php..."
    cp config.example.php config.php
    print_warning "Please edit config.php with your production settings before continuing"
    read -p "Press Enter when ready to continue..."
fi
print_success "Configuration files ready"

# Create remote directory structure
print_header "Step 3: Preparing Remote Server"
ssh "$SERVER_USER@$SERVER_IP" << 'ENDSSH'
    # Create directory
    mkdir -p /var/www/shotsbywhatsername
    
    # Install required packages if not present
    if ! command -v php &> /dev/null; then
        echo "Installing PHP and dependencies..."
        apt update
        apt install -y apache2 mysql-server php php-mysql php-curl php-mbstring php-gd php-xml
    fi
ENDSSH
print_success "Remote server prepared"

# Upload application files
print_header "Step 4: Uploading Application Files"
print_info "Uploading files to $SERVER_IP:$REMOTE_DIR..."

# Create temporary exclude file
cat > /tmp/rsync-exclude << EOF
.git/
.gitignore
.vscode/
node_modules/
*.log
*.bak
*.backup
.DS_Store
Thumbs.db
test_imgur.php
deploy.sh
DEPLOYMENT.md
EOF

# Use rsync for efficient file transfer
rsync -avz --progress \
    --exclude-from=/tmp/rsync-exclude \
    "$LOCAL_DIR/" \
    "$SERVER_USER@$SERVER_IP:$REMOTE_DIR/"

# Clean up
rm /tmp/rsync-exclude

print_success "Files uploaded successfully"

# Set permissions
print_header "Step 5: Setting File Permissions"
ssh "$SERVER_USER@$SERVER_IP" << ENDSSH
    cd $REMOTE_DIR
    
    # Set ownership
    chown -R www-data:www-data $REMOTE_DIR
    
    # Set directory permissions
    find $REMOTE_DIR -type d -exec chmod 755 {} \;
    
    # Set file permissions
    find $REMOTE_DIR -type f -exec chmod 644 {} \;
    
    # Secure config file
    chmod 600 config.php
    
    # Make scripts executable
    [ -f backup-shots.sh ] && chmod +x backup-shots.sh
ENDSSH
print_success "Permissions set correctly"

# Configure Apache virtual host
print_header "Step 6: Configuring Apache Virtual Host"
ssh "$SERVER_USER@$SERVER_IP" << ENDSSH
    # Create Apache configuration
    cat > /etc/apache2/sites-available/shotsbywhatsername.conf << 'EOF'
<VirtualHost *:80>
    ServerName $DOMAIN_NAME
    ServerAlias www.$DOMAIN_NAME
    ServerAdmin admin@$DOMAIN_NAME
    
    DocumentRoot $REMOTE_DIR
    
    <Directory $REMOTE_DIR>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header unset X-Powered-By
    
    ErrorLog \${APACHE_LOG_DIR}/shotsbywhatsername-error.log
    CustomLog \${APACHE_LOG_DIR}/shotsbywhatsername-access.log combined
</VirtualHost>
EOF

    # Enable required Apache modules
    a2enmod rewrite headers ssl
    
    # Enable the site
    a2ensite shotsbywhatsername.conf
    
    # Test configuration
    apache2ctl configtest
    
    # Restart Apache
    systemctl restart apache2
ENDSSH
print_success "Apache configured and restarted"

# Database setup prompt
print_header "Step 7: Database Configuration"
print_warning "Database setup requires manual intervention"
print_info "Run these commands on the server:"
echo ""
echo "  ssh $SERVER_USER@$SERVER_IP"
echo "  mysql -u root -p"
echo ""
echo "Then execute:"
echo "  CREATE DATABASE shots_by_whatsername CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "  CREATE USER 'shotsuser'@'localhost' IDENTIFIED BY 'your_secure_password';"
echo "  GRANT ALL PRIVILEGES ON shots_by_whatsername.* TO 'shotsuser'@'localhost';"
echo "  FLUSH PRIVILEGES;"
echo "  EXIT;"
echo ""
echo "  mysql -u shotsuser -p shots_by_whatsername < $REMOTE_DIR/sample_data.sql"
echo ""
read -p "Press Enter after completing database setup..."
print_success "Database setup completed"

# SSL Certificate setup
print_header "Step 8: SSL Certificate (Optional but Recommended)"
print_info "To install SSL certificate with Let's Encrypt:"
echo ""
echo "  ssh $SERVER_USER@$SERVER_IP"
echo "  apt install -y certbot python3-certbot-apache"
echo "  certbot --apache -d $DOMAIN_NAME -d www.$DOMAIN_NAME"
echo ""
read -p "Would you like to install SSL now? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    ssh "$SERVER_USER@$SERVER_IP" << ENDSSH
        apt install -y certbot python3-certbot-apache
        certbot --apache -d $DOMAIN_NAME -d www.$DOMAIN_NAME --non-interactive --agree-tos --email admin@$DOMAIN_NAME || true
ENDSSH
    print_success "SSL certificate installation attempted"
else
    print_warning "Skipping SSL installation. You can do this later manually."
fi

# Final checks
print_header "Step 9: Final Checks"
ssh "$SERVER_USER@$SERVER_IP" << ENDSSH
    # Check if Apache is running
    systemctl is-active --quiet apache2 && echo "✓ Apache is running" || echo "✗ Apache is not running"
    
    # Check if MySQL is running
    systemctl is-active --quiet mysql && echo "✓ MySQL is running" || echo "✗ MySQL is not running"
    
    # Check if site is accessible
    curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" http://localhost || true
ENDSSH

# Deployment summary
print_header "Deployment Complete!"
print_success "Application deployed successfully to $SERVER_IP"
echo ""
print_info "Next Steps:"
echo "  1. Update config.php on server with production credentials"
echo "  2. Change default admin password"
echo "  3. Test the site: http://$DOMAIN_NAME"
echo "  4. Run diagnostic test: http://$DOMAIN_NAME/test_imgur.php"
echo "  5. Remove test file after verification"
echo ""
print_warning "Important Reminders:"
echo "  • Set ENVIRONMENT='production' in config.php"
echo "  • Configure Imgur API Client ID in config.php"
echo "  • Set up automated backups"
echo "  • Configure firewall (UFW)"
echo "  • Monitor logs at /var/log/apache2/"
echo ""
print_info "For detailed instructions, see DEPLOYMENT.md"
echo ""

# Ask if user wants to open SSH connection
read -p "Would you like to connect to the server now? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_info "Connecting to server..."
    ssh "$SERVER_USER@$SERVER_IP"
fi

exit 0
