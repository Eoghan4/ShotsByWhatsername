<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: ../login');
    exit;
}
?>

<?php
require '../db.php';

// Your Imgur Client-ID
$clientId = 'd9f5286d17b0a33';

function uploadToImgur($imagePath, $clientId) {
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

    $response = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($response, true);

    if ($json && isset($json['data']['link'])) {
        return $json['data']['link'];
    }
    return false;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if (!empty($title) && isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $tmpFile = $_FILES['image']['tmp_name'];

        $imgurUrl = uploadToImgur($tmpFile, $clientId);

        if ($imgurUrl) {
            // Save to DB
            $stmt = $conn->prepare("INSERT INTO images (title, category, url) VALUES (?, ?, ?)");
            $stmt->execute([$title, $category, $imgurUrl]);
            $message = "Upload successful!";
        } else {
            $message = "Imgur upload failed.";
        }
    } else {
        $message = "Please provide a title and select an image.";
    }
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
            <div class="<?= strpos($message, 'successful') !== false ? 'success-message' : 'error-message' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Image Title</label>
                <input type="text" id="title" name="title" required placeholder="Enter a descriptive title for your image">
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" required placeholder="e.g., portrait, landscape, nature, wedding">
                <small style="color: var(--text-secondary); margin-top: 0.5rem; display: block;">
                    Enter a category to help organize your images (e.g., portrait, landscape, nature, wedding, event, etc.)
                </small>
            </div>
            
            <div class="form-group">
                <label for="image">Select Image</label>
                <input type="file" id="image" name="image" accept="image/*" required>
                <small style="color: var(--text-secondary); margin-top: 0.5rem; display: block;">
                    Accepted formats: JPG, PNG, GIF, WEBP. Image will be uploaded to Imgur for hosting.
                </small>
            </div>
            
            <div class="form-buttons">
                <button type="submit">Upload Image</button>
                <a href="../logout/" class="logout-btn">Logout</a>
            </div>
        </form>
    </div>

    <script>
        // Add some interactivity
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const label = document.querySelector('label[for="image"]');
                label.textContent = `Selected: ${file.name}`;
            }
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const category = document.getElementById('category').value;
            const image = document.getElementById('image').files[0];
            
            if (!title || !category || !image) {
                e.preventDefault();
                alert('Please fill in all fields and select an image.');
                return;
            }
            
            // Show loading state
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.textContent = 'Uploading...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
