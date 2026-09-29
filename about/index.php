<?php
require '../db.php';

$contentRows = $conn->query("SELECT content_key, content_value FROM site_content")->fetchAll();
$content = [];
foreach ($contentRows as $row) {
    $content[$row['content_key']] = $row['content_value'];
}

$aboutPhoto = htmlspecialchars($content['about_photo'] ?? '../pictures/water.webp');
$aboutBio   = $content['about_bio'] ?? '<p>Photography has always been a passion of mine - one I inherited from my father. As a child, I was fascinated by my uncle\'s darkroom, where he developed his own photographs. That early curiosity inspired me to take an opportunity I was presented with to develop my own photos as a teenager, and I loved every moment of the process.</p><p>In the year 2000, after undergoing spinal cord surgery, it became too painful for me to carry around my camera and lenses. For a long time, I thought my days behind the lens were over. But with the incredible advances in mobile phone cameras, I found a new way to rekindle that passion.</p><p>After joining Instagram and sharing my work, I was delighted to receive encouragement and requests from people who wanted to purchase my photos. Eventually, after selling my work for some time, my son kindly offered to build this website to make the process easier - which brings you here today.</p><p>I hope you enjoy browsing through my gallery as much as I\'ve enjoyed capturing each moment.</p><p>Thank you for visiting,</p><p>Dominica</p>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Shots By Whatsername</title>
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

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background-color: var(--primary-color);
            overflow-x: hidden;
        }

        .header {
            position: fixed; top: 0; left: 0; right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            transition: var(--transition);
            border-bottom: 1px solid var(--border-color);
        }

        .nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1rem 2rem; max-width: 1400px; margin: 0 auto;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem; font-weight: 600;
            color: var(--text-primary); text-decoration: none; letter-spacing: -0.5px;
        }

        .nav-menu { display: flex; list-style: none; gap: 2rem; }

        .nav-link {
            text-decoration: none; color: var(--text-primary);
            font-weight: 400; font-size: 0.9rem;
            transition: var(--transition); position: relative;
        }
        .nav-link:hover { color: var(--text-secondary); }
        .nav-link.active { color: var(--accent-color); }
        .nav-link::after {
            content: ''; position: absolute; bottom: -5px; left: 0;
            width: 0; height: 1px; background-color: var(--text-primary);
            transition: width 0.3s ease;
        }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }

        .menu-toggle { display: none; flex-direction: column; cursor: pointer; padding: 0.5rem; }
        .menu-toggle span { width: 25px; height: 2px; background-color: var(--text-primary); margin: 3px 0; transition: var(--transition); }

        .main-content { padding-top: 6rem; min-height: 100vh; }

        .about-hero {
            padding: 4rem 2rem 2rem; text-align: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .hero-content { max-width: 800px; margin: 0 auto; }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 400; color: var(--text-primary);
            margin-bottom: 1rem; letter-spacing: -1px;
        }

        .page-subtitle { font-size: 1.2rem; color: var(--text-secondary); margin-bottom: 2rem; font-weight: 300; }

        .about-content { padding: 6rem 2rem; max-width: 1200px; margin: 0 auto; }

        .about-grid {
            display: grid; grid-template-columns: 1fr 1.5fr;
            gap: 6rem; align-items: center; margin-bottom: 6rem;
        }

        .about-image { position: relative; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 40px var(--shadow-light); }
        .about-image img { width: 100%; height: auto; display: block; aspect-ratio: 4/5; object-fit: cover; }

        .about-text { padding: 0 2rem; }
        .about-text h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem; font-weight: 400;
            color: var(--text-primary); margin-bottom: 2rem; letter-spacing: -1px;
        }
        .about-text p { font-size: 1.1rem; line-height: 1.8; color: var(--text-secondary); margin-bottom: 1.5rem; }
        .about-text p:last-child { margin-bottom: 0; }

        .footer {
            background: var(--secondary-color); color: var(--text-secondary);
            padding: 2rem; text-align: center;
            border-top: 1px solid var(--border-color);
        }

        @media (max-width: 768px) {
            .nav { padding: 1rem; }
            .nav-menu {
                position: fixed; top: 0; left: -100%; width: 100%; height: 100vh;
                background-color: var(--primary-color); flex-direction: column;
                justify-content: center; align-items: center;
                transition: var(--transition); gap: 3rem;
            }
            .nav-menu.active { left: 0; }
            .menu-toggle { display: flex; z-index: 1001; }
            .main-content { padding-top: 5rem; }
            .about-hero { padding: 2rem 1rem; }
            .about-content { padding: 3rem 1rem; }
            .about-grid { grid-template-columns: 1fr; gap: 3rem; text-align: center; }
            .about-text { padding: 0; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeInUp 0.8s ease-out; }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <a href="../" class="logo">Shots By Whatsername</a>
            <ul class="nav-menu" id="navMenu">
                <li><a href="../" class="nav-link">Home</a></li>
                <li><a href="../gallery/" class="nav-link">Gallery</a></li>
                <li><a href="./" class="nav-link active">About</a></li>
                <li><a href="../contact/" class="nav-link">Contact</a></li>
            </ul>
            <div class="menu-toggle" id="menuToggle">
                <span></span><span></span><span></span>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <section class="about-hero">
            <div class="hero-content">
                <h1 class="page-title">About Me</h1>
                <p class="page-subtitle">Passionate photographer capturing life's beautiful moments</p>
            </div>
        </section>

        <section class="about-content">
            <div class="about-grid">
                <div class="about-image">
                    <img src="<?= $aboutPhoto ?>" alt="Photographer at work" loading="lazy">
                </div>
                <div class="about-text">
                    <h2>My Story</h2>
                    <?= $aboutBio ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; 2026 Shots By Whatsername. All rights reserved.</p>
    </footer>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const navMenu = document.getElementById('navMenu');

        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            const spans = menuToggle.querySelectorAll('span');
            if (navMenu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });

        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                const spans = menuToggle.querySelectorAll('span');
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            });
        });

        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.boxShadow = 'none';
            }
        });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('fade-in');
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        document.querySelectorAll('.about-text, .about-image').forEach(el => observer.observe(el));
    </script>
</body>
</html>
