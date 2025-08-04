<?php
require '../db.php';

// Get all images, ordered by category
$stmt = $conn->query("SELECT * FROM images ORDER BY category, title ASC");
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group images by category
$categories = [];
foreach ($images as $img) {
    $categories[$img['category']][] = $img;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Shots By Whatsername</title>
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

/* Main Content */
.main-content {
    padding-top: 6rem;
    min-height: 100vh;
}

/* Hero Section */
.gallery-hero {
    padding: 4rem 2rem 2rem;
    text-align: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.hero-content {
    max-width: 800px;
    margin: 0 auto;
}

.page-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.5rem, 6vw, 4rem);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: 1rem;
    letter-spacing: -1px;
}

.page-subtitle {
    font-size: 1.2rem;
    color: var(--text-secondary);
    margin-bottom: 2rem;
    font-weight: 300;
}

/* Filter Navigation */
.filter-nav {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 2rem;
    background: var(--secondary-color);
}

.filter-btn {
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
    text-transform: capitalize;
}

.filter-btn:hover,
.filter-btn.active {
    background: var(--accent-color);
    color: var(--primary-color);
    border-color: var(--accent-color);
}

/* Gallery Grid */
.gallery-container {
    padding: 4rem 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.category-section {
    margin-bottom: 6rem;
}

.category-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: 3rem;
    text-align: center;
    text-transform: capitalize;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 12px;
    background: var(--secondary-color);
    transition: var(--transition);
    cursor: pointer;
    aspect-ratio: 4/5;
}

.gallery-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px var(--shadow-medium);
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.gallery-item:hover img {
    transform: scale(1.1);
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
    padding: 2rem;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

.overlay-title {
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 0.5rem;
}

.overlay-description {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
    text-align: center;
    line-height: 1.5;
}

/* Lightbox Modal */
.lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    z-index: 2000;
    padding: 2rem;
}

.lightbox.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.lightbox-content {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
}

.lightbox-image {
    width: 100%;
    height: auto;
    max-height: 80vh;
    object-fit: contain;
}

.lightbox-info {
    padding: 2rem;
    text-align: center;
}

.lightbox-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.lightbox-description {
    color: var(--text-secondary);
    line-height: 1.6;
}

.lightbox-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.lightbox-close:hover {
    background: rgba(0, 0, 0, 0.8);
}

/* Loading Animation */
.loading {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    transition: opacity 0.5s ease-out;
}

.loading.fade-out {
    opacity: 0;
    pointer-events: none;
}

.loader {
    width: 40px;
    height: 40px;
    border: 3px solid var(--border-color);
    border-top: 3px solid var(--text-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Back to Top Button */
.back-to-top {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 50px;
    height: 50px;
    background: var(--accent-color);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.2rem;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
    z-index: 1000;
}

.back-to-top.visible {
    opacity: 1;
    visibility: visible;
}

.back-to-top:hover {
    background: var(--text-secondary);
    transform: translateY(-3px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .nav {
        padding: 1rem;
    }
    
    .nav-menu {
        position: fixed;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100vh;
        background-color: var(--primary-color);
        flex-direction: column;
        justify-content: center;
        align-items: center;
        transition: var(--transition);
        gap: 3rem;
    }
    
    .nav-menu.active {
        left: 0;
    }
    
    .menu-toggle {
        display: flex;
        z-index: 1001;
    }
    
    .main-content {
        padding-top: 5rem;
    }
    
    .gallery-hero {
        padding: 2rem 1rem;
    }
    
    .filter-nav {
        padding: 1rem;
        gap: 0.5rem;
    }
    
    .filter-btn {
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
    }
    
    .gallery-container {
        padding: 2rem 1rem;
    }
    
    .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .category-section {
        margin-bottom: 3rem;
    }
    
    .lightbox {
        padding: 1rem;
    }
    
    .lightbox-info {
        padding: 1rem;
    }
}

@media (max-width: 480px) {
    .gallery-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-nav {
        flex-direction: column;
        align-items: center;
    }
    
    .filter-btn {
        width: 200px;
        text-align: center;
    }
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
            <a href="../" class="logo">Shots By Whatsername</a>
            <ul class="nav-menu" id="navMenu">
                <li><a href="../" class="nav-link">Home</a></li>
                <li><a href="./" class="nav-link active">Gallery</a></li>
                <li><a href="../about/" class="nav-link">About</a></li>
                <li><a href="../contact/" class="nav-link">Contact</a></li>
            </ul>
            <div class="menu-toggle" id="menuToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Gallery Hero Section -->
        <section class="gallery-hero">
            <div class="hero-content">
                <h1 class="page-title">Photography Gallery</h1>
                <p class="page-subtitle">Explore my collection of captured moments and artistic vision</p>
            </div>
        </section>

        <!-- Filter Navigation -->
        <section class="filter-nav">
            <button class="filter-btn active" data-filter="all">All Categories</button>
            <?php foreach (array_keys($categories) as $category): ?>
                <button class="filter-btn" data-filter="<?= htmlspecialchars($category) ?>">
                    <?= htmlspecialchars(ucfirst($category)) ?>
                </button>
            <?php endforeach; ?>
        </section>

        <!-- Gallery Container -->
        <section class="gallery-container">
            <?php foreach ($categories as $category => $imgs): ?>
                <div class="category-section" data-category="<?= htmlspecialchars($category) ?>">
                    <h2 class="category-title"><?= htmlspecialchars(ucfirst($category)) ?> Photography</h2>
                    <div class="gallery-grid">
                        <?php foreach ($imgs as $img): ?>
                            <div class="gallery-item" onclick="openLightbox('<?= htmlspecialchars($img['url']) ?>', '<?= htmlspecialchars($img['title']) ?>')">
                                <img src="<?= htmlspecialchars($img['url']) ?>" 
                                     alt="<?= htmlspecialchars($img['title']) ?>" 
                                     loading="lazy">
                                <div class="gallery-overlay">
                                    <h3 class="overlay-title"><?= htmlspecialchars($img['title']) ?></h3>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </main>

    <!-- Lightbox Modal -->
    <div class="lightbox" id="lightbox">
        <div class="lightbox-content">
            <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
            <img class="lightbox-image" id="lightboxImage" src="" alt="">
            <div class="lightbox-info">
                <h3 class="lightbox-title" id="lightboxTitle"></h3>
                <p class="lightbox-description" id="lightboxDescription"></p>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" onclick="scrollToTop()">↑</button>

    <script>
        // Loading animation
        window.addEventListener('load', function() {
            const loading = document.getElementById('loading');
            setTimeout(() => {
                loading.classList.add('fade-out');
                setTimeout(() => {
                    loading.style.display = 'none';
                }, 500);
            }, 1000);
        });

        // Mobile menu toggle
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

        // Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                const spans = menuToggle.querySelectorAll('span');
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            });
        });

        // Header background on scroll
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            const backToTop = document.getElementById('backToTop');
            
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
                backToTop.classList.add('visible');
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.boxShadow = 'none';
                backToTop.classList.remove('visible');
            }
        });

        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                const categories = document.querySelectorAll('.category-section');
                
                categories.forEach(category => {
                    if (filter === 'all' || category.getAttribute('data-category') === filter) {
                        category.style.display = 'block';
                        category.style.animation = 'fadeInUp 0.6s ease-out';
                    } else {
                        category.style.display = 'none';
                    }
                });
            });
        });

        // Lightbox functionality
        function openLightbox(url, title) {
            document.getElementById('lightboxImage').src = url;
            document.getElementById('lightboxTitle').textContent = title;
            document.getElementById('lightboxDescription').textContent = '';
            document.getElementById('lightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Close lightbox on click outside
        document.getElementById('lightbox').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });

        // Close lightbox on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
            }
        });

        // Back to top functionality
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.8s ease-out forwards';
                }
            });
        }, observerOptions);

        // Observe gallery items for animation
        document.querySelectorAll('.gallery-item').forEach(el => {
            observer.observe(el);
        });

        // Add fadeInUp animation keyframes
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
