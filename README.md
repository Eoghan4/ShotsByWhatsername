# Shots By Whatsername - Photography Portfolio

A photography portfolio website with image gallery and admin upload functionality using Imgur API for image hosting.

## Features

- Dynamic image gallery with category filtering
- Secure admin login system
- Image upload to Imgur with automatic database integration
- Responsive design for mobile and desktop
- Lightbox image viewing

## Setup Instructions

### 1. Database Setup

1. Import the `sample_data.sql` file into your MySQL database:
   ```bash
   mysql -u root -p < sample_data.sql
   ```
   Or use phpMyAdmin to import the SQL file.

2. This will create:
   - Database: `shots_by_whatsername`
   - Tables: `users`, `images`
   - Default admin user: `eoghanmcgough@gmail.com` / password: `admin123`

### 2. Configuration

1. Copy `config.example.php` to `config.php`:
   ```bash
   copy config.example.php config.php
   ```

2. Edit `config.php` and update the following:
   - Database credentials (if different from defaults)
   - **Imgur Client ID** (see below for setup)

### 3. Get Imgur API Credentials

The application uses Imgur API to host uploaded images. To get your Client ID:

1. Go to https://api.imgur.com/oauth2/addclient
2. Register for a free API account
3. Fill in the application details:
   - **Application name**: Shots By Whatsername (or your choice)
   - **Authorization type**: Select "OAuth 2 authorization without a callback URL"
   - **Email**: Your email address
   - **Description**: Photography portfolio image hosting
4. Submit the form
5. Copy your **Client ID** (not the Client Secret)
6. Paste the Client ID into `config.php` as the value for `IMGUR_CLIENT_ID`

### 4. File Permissions

Ensure the web server has write permissions for:
- Session storage directory
- Any cache/temporary directories

### 5. Security Recommendations

**Before deploying to production:**

1. Change `ENVIRONMENT` to `'production'` in `config.php`
2. Update the default admin password in the database
3. Ensure `config.php` is NOT committed to version control (already in `.gitignore`)
4. Set appropriate file permissions (644 for files, 755 for directories)
5. Enable HTTPS on your web server
6. Configure secure session settings in PHP

## Usage

### Admin Upload

1. Navigate to `/login/` and log in with admin credentials
2. Go to `/upload/` to access the upload form
3. Fill in:
   - **Title**: Descriptive name for the image
   - **Category**: Group images by type (portrait, landscape, nature, etc.)
   - **Image**: Select a JPG, PNG, GIF, or WEBP file (max 10MB)
4. Click "Upload Image"
5. The image will be uploaded to Imgur and the URL saved to your database

### Gallery

- Visit `/gallery/` to view all uploaded images
- Filter by category using the buttons at the top
- Click any image to view in lightbox mode

## File Upload Specifications

- **Max file size**: 10MB (configurable in `config.php`)
- **Allowed formats**: JPG, JPEG, PNG, GIF, WEBP
- **Hosting**: Images are uploaded to Imgur (not stored locally)
- **Validation**: Both client-side and server-side validation

## Security Features

- CSRF token protection on forms
- File type validation (MIME type and extension)
- File size limits
- SQL injection protection (PDO prepared statements)
- XSS protection (htmlspecialchars on output)
- Session-based authentication
- Password hashing (bcrypt)

## Deployment

### Production Deployment to Hetzner

For complete deployment instructions to a Hetzner server, see:
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Comprehensive deployment guide
- **[DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)** - Pre/post-deployment checklist
- **[SERVER_COMMANDS.md](SERVER_COMMANDS.md)** - Quick reference for server commands

Quick deployment using the automated script:
```bash
chmod +x deploy.sh
./deploy.sh your-server-ip your-domain.com
```

## Troubleshooting

### Upload fails with "Imgur upload failed"

1. Verify your Imgur Client ID is correct in `config.php`
2. Check that cURL is enabled in PHP (`php -m | grep curl`)
3. Ensure your server can make outbound HTTPS requests
4. Check PHP error logs for specific error messages

### Database connection error

1. Verify MySQL is running
2. Check database credentials in `config.php`
3. Ensure the database exists (run `sample_data.sql`)
4. Check MySQL user permissions

### File upload errors

1. Check PHP `upload_max_filesize` and `post_max_size` settings
2. Verify file permissions on temp directory
3. Ensure file meets size/type requirements
4. Check browser console for client-side validation errors

## Technologies Used

- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL/MariaDB
- **Image Hosting**: Imgur API
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Fonts**: Google Fonts (Inter, Playfair Display)
- **Deployment**: Apache/Nginx, Let's Encrypt SSL

## License

Personal project - All rights reserved.
