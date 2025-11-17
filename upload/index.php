<?php
require '../db.php';

// Configure session before starting
if (ENVIRONMENT === 'production') {
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
}

session_start();

if (empty($_SESSION['logged_in'])) {
    header('Location: ../login');
    exit;
}

// Get Imgur Client-ID from configuration
$clientId = IMGUR_CLIENT_ID;

/**
 * Validates uploaded image file
 * 
 * @param array $file The $_FILES array element
 * @return array Returns ['valid' => bool, 'error' => string]
 */
function validateImageFile($file) {
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        $error = $errorMessages[$file['error']] ?? 'Unknown upload error';
        return ['valid' => false, 'error' => $error];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['valid' => false, 'error' => 'File size exceeds ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB limit'];
    }

    // Check file mime type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['valid' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP allowed'];
    }

    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['valid' => false, 'error' => 'Invalid file extension'];
    }

    // Verify it's actually an image
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['valid' => false, 'error' => 'File is not a valid image'];
    }

    return ['valid' => true, 'error' => null];
}

/**
 * Uploads image to Imgur
 * 
 * @param string $imagePath Path to the image file
 * @param string $clientId Imgur Client ID
 * @return array Returns ['success' => bool, 'url' => string|null, 'error' => string|null]
 */
function uploadToImgur($imagePath, $clientId) {
    try {
        $imageData = base64_encode(file_get_contents($imagePath));

        $headers = [
            "Authorization: Client-ID $clientId"
        ];

        $postFields = [
            'image' => $imageData,
            'type' => 'base64'
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 second timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 second connection timeout
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['success' => false, 'url' => null, 'error' => 'cURL error: ' . $curlError];
        }

        $json = json_decode($response, true);

        if ($json === null) {
            return ['success' => false, 'url' => null, 'error' => 'Invalid JSON response from Imgur'];
        }

        if ($httpCode === 200 && isset($json['data']['link'])) {
            return ['success' => true, 'url' => $json['data']['link'], 'error' => null];
        } else {
            $errorMsg = $json['data']['error'] ?? 'Unknown Imgur API error';
            return ['success' => false, 'url' => null, 'error' => 'Imgur API error: ' . $errorMsg];
        }
    } catch (Exception $e) {
        return ['success' => false, 'url' => null, 'error' => 'Exception: ' . $e->getMessage()];
    }
}

$message = '';
$messageType = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log session and post tokens
    error_log("POST csrf_token: " . ($_POST['csrf_token'] ?? 'NOT SET'));
    error_log("SESSION csrf_token: " . ($_SESSION['csrf_token'] ?? 'NOT SET'));
    error_log("Session ID: " . session_id());

    // Verify CSRF token
    if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid security token. Please try again.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');

        // Validate inputs
        if (empty($title)) {
            $message = "Please provide an image title.";
        } elseif (empty($category)) {
            $message = "Please provide a category.";
        } elseif (strlen($title) > 255) {
            $message = "Title is too long (max 255 characters).";
        } elseif (strlen($category) > 100) {
            $message = "Category is too long (max 100 characters).";
        } elseif (!isset($_FILES['image'])) {
            $message = "Please select an image to upload.";
        } else {
            // Validate the uploaded file
            $validation = validateImageFile($_FILES['image']);
            
            if (!$validation['valid']) {
                $message = $validation['error'];
            } else {
                $tmpFile = $_FILES['image']['tmp_name'];

                // Upload to Imgur
                $uploadResult = uploadToImgur($tmpFile, $clientId);

                if ($uploadResult['success']) {
                    // Save to database
                    try {
                        $stmt = $conn->prepare("INSERT INTO images (title, category, url) VALUES (?, ?, ?)");
                        $success = $stmt->execute([$title, $category, $uploadResult['url']]);
                        
                        if ($success) {
                            $message = "Upload successful! Image has been added to the gallery.";
                            $messageType = 'success';
                            
                            // Clear form by regenerating CSRF token
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        } else {
                            $message = "Database error: Failed to save image information.";
                        }
                    } catch (PDOException $e) {
                        $message = "Database error: " . $e->getMessage();
                        error_log("Database error in upload: " . $e->getMessage());
                    }
                } else {
                    $message = $uploadResult['error'];
                }
            }
        }
    }
}

// Generate CSRF token for the form
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Image - Shots By Whatsername</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../pictures/other heart.png" type="image/png">
    <style>
        :root {
            --primary-color: #ffffff;
            --secondary-color: #f8f9fa;
            --accent-color: #000000;
            --text-primary: #000000;
            --text-secondary: #6c757d;
            --border-color: #e9ecef;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --shadow-medium: rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            min-height: 100vh;
        }

        .nav-buttons {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 1rem;
        }

        .nav-button {
            padding: 0.75rem 1.5rem;
            background: transparent;
            border: 2px solid var(--border-color);
            color: var(--text-secondary);
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }

        .nav-button:hover {
            background: var(--accent-color);
            color: var(--primary-color);
            border-color: var(--accent-color);
        }

        .upload-container {
            max-width: 600px;
            margin: 6rem auto 2rem;
            background: var(--primary-color);
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 20px 40px var(--shadow-light);
            border: 1px solid var(--border-color);
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--text-primary);
            text-align: center;
            margin-bottom: 2rem;
            letter-spacing: -0.5px;
        }

        .user-info {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: var(--secondary-color);
            border-radius: 8px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        input[type="text"],
        select,
        input[type="file"] {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            background: var(--primary-color);
            color: var(--text-primary);
            transition: var(--transition);
        }

        input[type="text"]:focus,
        select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }

        input[type="file"] {
            padding: 0.75rem;
            cursor: pointer;
        }

        input[type="file"]::-webkit-file-upload-button {
            background: var(--secondary-color);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.5rem 1rem;
            margin-right: 1rem;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        input[type="file"]::-webkit-file-upload-button:hover {
            background: var(--accent-color);
            color: var(--primary-color);
        }

        .form-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        button[type="submit"] {
            flex: 1;
            background: var(--accent-color);
            color: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 1rem;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        button[type="submit"]:hover {
            background: var(--text-secondary);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px var(--shadow-medium);
        }

        .logout-btn {
            background: transparent;
            border: 2px solid var(--border-color);
            color: var(--text-secondary);
            border-radius: 8px;
            padding: 1rem;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .logout-btn:hover {
            border-color: #dc3545;
            color: #dc3545;
        }

        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 500;
        }

        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 500;
        }

        .category-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .category-option {
            padding: 0.75rem;
            background: var(--secondary-color);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .category-option:hover,
        .category-option.selected {
            background: var(--accent-color);
            color: var(--primary-color);
            border-color: var(--accent-color);
        }

        @media (max-width: 768px) {
            .nav-buttons {
                position: relative;
                top: 1rem;
                right: auto;
                justify-content: center;
                margin-bottom: 2rem;
            }

            .upload-container {
                margin: 2rem auto;
                padding: 2rem;
            }

            .form-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="nav-buttons">
        <a href="../" class="nav-button">Home</a>
        <a href="../gallery/" class="nav-button">Gallery</a>
    </div>
    
    <div class="upload-container">
        <h1 class="page-title">Upload New Image</h1>
        
        <div class="user-info">
            Logged in as: <?= htmlspecialchars($_SESSION['email']) ?>
        </div>
        
        <?php if ($message): ?>
            <div class="<?= $messageType === 'success' ? 'success-message' : 'error-message' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data" id="uploadForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="MAX_FILE_SIZE" value="<?= MAX_FILE_SIZE ?>">
            
            <div class="form-group">
                <label for="title">Image Title</label>
                <input type="text" id="title" name="title" required placeholder="Enter a descriptive title for your image" maxlength="255">
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" required placeholder="e.g., portrait, landscape, nature, wedding" maxlength="100">
                <small style="color: var(--text-secondary); margin-top: 0.5rem; display: block;">
                    Enter a category to help organize your images (e.g., portrait, landscape, nature, wedding, event, etc.)
                </small>
            </div>
            
            <div class="form-group">
                <label for="image">Select Image</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required>
                <small style="color: var(--text-secondary); margin-top: 0.5rem; display: block;">
                    Accepted formats: JPG, PNG, GIF, WEBP. Max size: <?= MAX_FILE_SIZE / 1024 / 1024 ?>MB. Image will be uploaded to Imgur for hosting.
                </small>
            </div>
            
            <div class="form-buttons">
                <button type="submit">Upload Image</button>
                <a href="../logout/" class="logout-btn">Logout</a>
            </div>
        </form>
    </div>

    <script>
        // File size constant from PHP
        const MAX_FILE_SIZE = <?= MAX_FILE_SIZE ?>;
        const MAX_FILE_SIZE_MB = <?= MAX_FILE_SIZE / 1024 / 1024 ?>;
        
        // Allowed file types
        const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        // Add file change interactivity with validation
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const label = document.querySelector('label[for="image"]');
            
            if (file) {
                // Validate file type
                if (!ALLOWED_TYPES.includes(file.type)) {
                    alert('Invalid file type. Please select a JPG, PNG, GIF, or WEBP image.');
                    this.value = '';
                    label.textContent = 'Select Image';
                    return;
                }
                
                // Validate file size
                if (file.size > MAX_FILE_SIZE) {
                    alert(`File size (${(file.size / 1024 / 1024).toFixed(2)}MB) exceeds the maximum allowed size of ${MAX_FILE_SIZE_MB}MB.`);
                    this.value = '';
                    label.textContent = 'Select Image';
                    return;
                }
                
                // Update label with filename and size
                const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
                label.textContent = `Selected: ${file.name} (${fileSizeMB}MB)`;
            } else {
                label.textContent = 'Select Image';
            }
        });
        
        // Form validation and submission
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const category = document.getElementById('category').value.trim();
            const image = document.getElementById('image').files[0];
            
            // Basic validation
            if (!title || !category || !image) {
                e.preventDefault();
                alert('Please fill in all fields and select an image.');
                return;
            }
            
            // Validate title length
            if (title.length > 255) {
                e.preventDefault();
                alert('Title is too long (maximum 255 characters).');
                return;
            }
            
            // Validate category length
            if (category.length > 100) {
                e.preventDefault();
                alert('Category is too long (maximum 100 characters).');
                return;
            }
            
            // Validate file type again
            if (!ALLOWED_TYPES.includes(image.type)) {
                e.preventDefault();
                alert('Invalid file type. Please select a JPG, PNG, GIF, or WEBP image.');
                return;
            }
            
            // Validate file size again
            if (image.size > MAX_FILE_SIZE) {
                e.preventDefault();
                alert(`File size exceeds the maximum allowed size of ${MAX_FILE_SIZE_MB}MB.`);
                return;
            }
            
            // Show loading state
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.textContent = 'Uploading to Imgur...';
            submitBtn.disabled = true;
            submitBtn.style.cursor = 'not-allowed';
            
            // Disable all form inputs during upload
            document.querySelectorAll('input, button').forEach(el => {
                el.disabled = true;
            });
        });
        
        // Auto-dismiss success messages after 5 seconds
        window.addEventListener('load', function() {
            const successMessage = document.querySelector('.success-message');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.transition = 'opacity 0.5s';
                    successMessage.style.opacity = '0';
                    setTimeout(function() {
                        successMessage.style.display = 'none';
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>
