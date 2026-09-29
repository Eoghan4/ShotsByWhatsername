<?php
require './db.php';

// Load site content
$contentRows = $conn->query("SELECT content_key, content_value FROM site_content")->fetchAll();
$content = [];
foreach ($contentRows as $row) {
    $content[$row['content_key']] = $row['content_value'];
}

$heroTitle       = htmlspecialchars($content['hero_title']       ?? 'Shots By Whatsername');
$heroSubtitle    = htmlspecialchars($content['hero_subtitle']    ?? 'Photography Portfolio');
$heroDescription = htmlspecialchars($content['hero_description'] ?? 'Welcome to my world in photos! Take a look around!');

// Build featured images (fall back to default pictures if not set)
$defaults = [
    ['src' => './pictures/green.webp',  'alt' => 'Nature Photography',    'label' => 'Nature Photography'],
    ['src' => './pictures/howth.webp',  'alt' => 'Landscape Photography', 'label' => 'Landscape Photography'],
    ['src' => './pictures/water.webp',  'alt' => 'Water Photography',     'label' => 'Water Photography'],
];
$featured = [];
for ($i = 1; $i <= 3; $i++) {
    $imgId = $content['featured_image_' . $i] ?? '';
    if ($imgId) {
        $stmt = $conn->prepare("SELECT url, title, category FROM images WHERE id = ?");
        $stmt->execute([$imgId]);
        $row = $stmt->fetch();
        if ($row) {
            $featured[] = [
                'src'   => htmlspecialchars($row['url']),
                'alt'   => htmlspecialchars($row['title']),
                'label' => htmlspecialchars(ucfirst($row['category']) . ' Photography'),
            ];
            continue;
        }
    }
    $featured[] = $defaults[$i - 1];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shots By Whatsername - Professional Photography Portfolio Ireland</title>
    <meta name="description" content="Professional photography services in Ireland by Shots By Whatsername. Specializing in landscape, nature, and portrait photography. View my gallery of stunning photographs.">
    <meta name="keywords" content="photography, photographer Ireland, portrait photography, landscape photography, nature photography, Irish photographer, professional photography, shotsbywhatsername">
    <meta name="author" content="Shots By Whatsername">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://shotsbywhatsername.ie/">
    <meta property="og:title" content="Shots By Whatsername - Professional Photography Portfolio">
    <meta property="og:description" content="Professional photography services in Ireland. Capturing life's beautiful moments through the lens of creativity and passion.">
    <meta property="og:image" content="https://shotsbywhatsername.ie/pictures/water.webp">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://shotsbywhatsername.ie/">
    <meta property="twitter:title" content="Shots By Whatsername - Professional Photography">
    <meta property="twitter:description" content="Professional photography services in Ireland specializing in landscape, nature, and portrait photography.">
    <meta property="twitter:image" content="https://shotsbywhatsername.ie/pictures/water.webp">

    <link rel="canonical" href="https://shotsbywhatsername.ie/">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="./pictures/other heart.png" type="image/png">
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

html {
    scroll-behavior: smooth;
}

body {
    font-family: 'Inter', sans-serif;
    line-height: 1.6;
    color: var(--text-primary);
    background-color: var(--primary-color);
    overflow-x: hidden;
}

/* Header Navigation */
.header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    z-index: 1000;
    transition: var(--transition);
    border-bottom: 1px solid var(--border-color);
}

.nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    text-decoration: none;
    letter-spacing: -0.5px;
}

.nav-menu {
    display: flex;
    list-style: none;
    gap: 2rem;
}

.nav-link {
    text-decoration: none;
    color: var(--text-primary);
    font-weight: 400;
    font-size: 0.9rem;
    transition: var(--transition);
    position: relative;
}

.nav-link:hover {
    color: var(--text-secondary);
}

.nav-link::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 0;
    height: 1px;
    background-color: var(--text-primary);
    transition: width 0.3s ease;
}

.nav-link:hover::after {
    width: 100%;
}

/* Mobile menu toggle */
.menu-toggle {
    display: none;
    flex-direction: column;
    cursor: pointer;
    padding: 0.5rem;
}

.menu-toggle span {
    width: 25px;
    height: 2px;
    background-color: var(--text-primary);
    margin: 3px 0;
    transition: var(--transition);
}

/* Hero Section */
.hero {
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    position: relative;
    overflow: hidden;
}

.hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('./pictures/water.webp') center/cover;
    opacity: 0.15;
    z-index: 1;
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 800px;
    padding: 0 2rem;
    animation: fadeInUp 1s ease-out;
}

.hero-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(3rem, 8vw, 6rem);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: 1rem;
    letter-spacing: -2px;
    line-height: 1.1;
}

.hero-subtitle {
    font-size: clamp(1.1rem, 2.5vw, 1.5rem);
    color: var(--text-secondary);
    margin-bottom: 2rem;
    font-weight: 300;
}

.hero-description {
    font-size: 1.1rem;
    color: var(--text-secondary);
    margin-bottom: 3rem;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.8;
}

.cta-button {
    display: inline-block;
    padding: 1rem 2.5rem;
    background-color: var(--text-primary);
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    border-radius: 50px;
    transition: var(--transition);
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.cta-button:hover {
    background-color: var(--text-secondary);
    transform: translateY(-2px);
    box-shadow: 0 10px 25px var(--shadow-medium);
}

/* Scroll indicator */
.scroll-indicator {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    width: 24px;
    height: 40px;
    border: 2px solid var(--text-secondary);
    border-radius: 20px;
    opacity: 0.7;
    cursor: pointer;
}

.scroll-indicator::before {
    content: '';
    position: absolute;
    top: 6px;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 8px;
    background-color: var(--text-secondary);
    border-radius: 2px;
    animation: scroll 1.5s infinite;
}

/* Featured Work Section */
.featured-work {
    padding: 8rem 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.section-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.5rem, 5vw, 4rem);
    text-align: center;
    margin-bottom: 4rem;
    font-weight: 400;
}

.work-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 4rem;
}

.work-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    aspect-ratio: 4/5;
    background: var(--secondary-color);
    transition: var(--transition);
    cursor: pointer;
}

.work-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px var(--shadow-medium);
}

.work-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.work-item:hover img {
    transform: scale(1.1);
}

.work-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
}

.work-item:hover .work-overlay {
    opacity: 1;
}

.work-overlay h3 {
    color: white;
    font-size: 1.5rem;
    font-weight: 500;
    text-align: center;
}

/* Footer */
.footer {
    background: var(--accent-color);
    color: white;
    padding: 4rem 2rem 2rem;
    text-align: center;
}

.footer p {
    margin-bottom: 2rem;
    opacity: 0.8;
}

/* Animations */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}

@keyframes scroll {
    0%   { opacity: 0; transform: translateX(-50%) translateY(0); }
    50%  { opacity: 1; }
    100% { opacity: 0; transform: translateX(-50%) translateY(10px); }
}

/* Responsive Design */
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
    .hero { padding: 2rem 1rem; }
    .hero-content { padding: 0 1rem; }
    .hero-description { font-size: 1rem; margin-bottom: 2rem; }
    .cta-button { padding: 0.875rem 2rem; font-size: 0.9rem; }
    .featured-work { padding: 4rem 1rem; }
    .work-grid { grid-template-columns: 1fr; gap: 1.5rem; }
}

@media (max-width: 480px) {
    .nav { padding: 0.75rem 1rem; }
    .logo { font-size: 1.25rem; }
}

/* Loading animation */
.loading {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background-color: var(--primary-color);
    display: flex; align-items: center; justify-content: center;
    z-index: 9999; transition: opacity 0.5s ease-out;
}
.loading.fade-out { opacity: 0; pointer-events: none; }
.loader {
    width: 40px; height: 40px;
    border: 3px solid var(--border-color);
    border-top: 3px solid var(--text-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0%   { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading" id="loading">
        <div class="loader"></div>
    </div>

    <!-- Header Navigation -->
    <header class="header">
        <nav class="nav">
            <a href="#" class="logo">Shots By Whatsername</a>
            <ul class="nav-menu" id="navMenu">
                <li><a href="./gallery/" class="nav-link">Gallery</a></li>
                <li><a href="./about/" class="nav-link">About</a></li>
                <li><a href="./contact/" class="nav-link">Contact</a></li>
            </ul>
            <div class="menu-toggle" id="menuToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title"><?= $heroTitle ?></h1>
            <p class="hero-subtitle"><?= $heroSubtitle ?></p>
            <p class="hero-description"><?= $heroDescription ?></p>
            <a href="./gallery/" class="cta-button">View My Work</a>
        </div>
        <div class="scroll-indicator" onclick="scrollToNext()"></div>
    </section>

    <!-- Featured Work Section -->
    <section class="featured-work" id="featured">
        <h2 class="section-title">Featured Work</h2>
        <div class="work-grid">
            <?php foreach ($featured as $f): ?>
            <div class="work-item">
                <img src="<?= $f['src'] ?>" alt="<?= $f['alt'] ?>" loading="lazy">
                <div class="work-overlay">
                    <h3><?= $f['label'] ?></h3>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center;">
            <a href="./gallery/" class="cta-button">View Full Gallery</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 Shots By Whatsername. All rights reserved.</p>
    </footer>

    <script>
        window.addEventListener('load', function() {
            const loading = document.getElementById('loading');
            setTimeout(() => {
                loading.classList.add('fade-out');
                setTimeout(() => { loading.style.display = 'none'; }, 500);
            }, 1000);
        });

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

        function scrollToNext() {
            document.getElementById('featured').scrollIntoView({ behavior: 'smooth' });
        }

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
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.8s ease-out forwards';
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        document.querySelectorAll('.work-item').forEach(el => observer.observe(el));
    </script>
</body>
</html>
