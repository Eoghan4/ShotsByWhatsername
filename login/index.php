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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($email && $pass) {
        $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($pass, $row['password_hash'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email'] = $email;
            header('Location: ../upload/');
            exit;
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Please enter email and password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Shots By Whatsername</title>
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

        .login-container {
            max-width: 400px;
            margin: 10vh auto;
            background: var(--primary-color);
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 20px 40px var(--shadow-light);
            border: 1px solid var(--border-color);
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 600;
            color: var(--text-primary);
            text-align: center;
            margin-bottom: 2rem;
            letter-spacing: -0.5px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        input[type="email"],
        input[type="password"] {
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

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }

        button[type="submit"] {
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
            margin-top: 1rem;
        }

        button[type="submit"]:hover {
            background: var(--text-secondary);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px var(--shadow-medium);
        }

        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }

        .back-link {
            text-align: center;
            margin-top: 2rem;
        }

        .back-link a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .back-link a:hover {
            color: var(--accent-color);
        }

        @media (max-width: 768px) {
            .nav-buttons {
                position: relative;
                top: 1rem;
                right: auto;
                justify-content: center;
                margin-bottom: 2rem;
            }

            .login-container {
                margin: 2rem auto;
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="nav-buttons">
        <a href="../" class="nav-button">Home</a>
        <a href="../gallery/" class="nav-button">Gallery</a>
    </div>
    
    <div class="login-container">
        <h1 class="page-title">Admin Login</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <div class="back-link">
            <a href="../">← Back to Home</a>
        </div>
    </div>
</body>
</html>
