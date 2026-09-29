<?php
require '../db.php';

if (ENVIRONMENT === 'production') {
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
}

session_start();

header('Content-Type: application/json');

if (empty($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorised']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {

    case 'update_image':
        $id       = (int)($_POST['id'] ?? 0);
        $title    = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');

        if (!$id || !$title || !$category) {
            echo json_encode(['success' => false, 'error' => 'Missing fields']);
            exit;
        }
        if (strlen($title) > 255 || strlen($category) > 100) {
            echo json_encode(['success' => false, 'error' => 'Field too long']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE images SET title = ?, category = ? WHERE id = ?");
        $stmt->execute([$title, $category, $id]);
        echo json_encode(['success' => true]);
        break;

    case 'delete_image':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Missing id']);
            exit;
        }

        $stmt = $conn->prepare("SELECT url FROM images WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            echo json_encode(['success' => false, 'error' => 'Image not found']);
            exit;
        }

        // Delete physical file if it's in uploads/
        $url = $row['url'];
        if (strpos($url, '/uploads/') === 0) {
            $filename = basename($url);
            $filepath = UPLOAD_DIR . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }

        $stmt = $conn->prepare("DELETE FROM images WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    case 'update_content':
        $key   = trim($_POST['key'] ?? '');
        $value = $_POST['value'] ?? '';

        $allowed_keys = [
            'hero_title', 'hero_subtitle', 'hero_description',
            'featured_image_1', 'featured_image_2', 'featured_image_3',
            'about_bio', 'about_photo'
        ];

        if (!in_array($key, $allowed_keys)) {
            echo json_encode(['success' => false, 'error' => 'Invalid key']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO site_content (content_key, content_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE content_value = ?");
        $stmt->execute([$key, $value, $value]);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
