<?php
require '../db.php';

if (ENVIRONMENT === 'production') {
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
}

session_start();

if (empty($_SESSION['logged_in'])) {
    header('Location: ../login/');
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load all images
$images = $conn->query("SELECT * FROM images ORDER BY category, title ASC")->fetchAll();

// Load site content
$contentRows = $conn->query("SELECT content_key, content_value FROM site_content")->fetchAll();
$content = [];
foreach ($contentRows as $row) {
    $content[$row['content_key']] = $row['content_value'];
}

// Helper to get content value with fallback
function c($content, $key, $default = '') {
    return htmlspecialchars($content[$key] ?? $default);
}

// Build image options for featured image selects
$imageOptions = '<option value="">— Use default picture —</option>';
foreach ($images as $img) {
    $imageOptions .= '<option value="' . $img['id'] . '">' . htmlspecialchars($img['title']) . ' (' . htmlspecialchars($img['category']) . ')</option>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Shots By Whatsername</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ffffff;
            --secondary: #f8f9fa;
            --accent: #000000;
            --text: #000000;
            --muted: #6c757d;
            --border: #e9ecef;
            --shadow: rgba(0,0,0,0.1);
            --danger: #dc3545;
            --success: #28a745;
            --transition: all 0.2s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--secondary); color: var(--text); min-height: 100vh; }

        /* Top bar */
        .topbar {
            background: var(--accent);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar h1 { font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 500; }
        .topbar-right { display: flex; gap: 1rem; align-items: center; font-size: 0.9rem; opacity: 0.8; }
        .topbar a { color: white; text-decoration: none; opacity: 0.8; transition: var(--transition); }
        .topbar a:hover { opacity: 1; }

        /* Tabs */
        .tabs { background: var(--primary); border-bottom: 1px solid var(--border); padding: 0 2rem; display: flex; gap: 0; }
        .tab-btn {
            padding: 1rem 1.5rem;
            border: none;
            background: none;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--muted);
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: var(--transition);
        }
        .tab-btn:hover { color: var(--text); }
        .tab-btn.active { color: var(--text); border-bottom-color: var(--accent); }

        /* Content */
        .tab-content { display: none; padding: 2rem; max-width: 1200px; margin: 0 auto; }
        .tab-content.active { display: block; }

        /* Cards */
        .card {
            background: var(--primary);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 12px var(--shadow);
            margin-bottom: 2rem;
        }
        .card h2 { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 500; margin-bottom: 1.5rem; }

        /* Form elements */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.9rem; font-weight: 500; margin-bottom: 0.4rem; }
        .form-group input[type="text"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
            background: var(--primary);
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus { outline: none; border-color: var(--accent); }
        .form-group textarea { min-height: 200px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }

        /* Buttons */
        .btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 6px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { background: #333; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-danger:hover { background: #b02030; }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.8rem; }

        /* Gallery table */
        .gallery-table { width: 100%; border-collapse: collapse; }
        .gallery-table th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--muted);
            border-bottom: 2px solid var(--border);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .gallery-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .gallery-table tr:last-child td { border-bottom: none; }
        .gallery-table tr:hover td { background: var(--secondary); }

        .thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            display: block;
        }
        .inline-input {
            border: 1px solid transparent;
            border-radius: 6px;
            padding: 0.4rem 0.6rem;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            width: 100%;
            background: transparent;
            transition: var(--transition);
        }
        .inline-input:hover { border-color: var(--border); background: var(--secondary); }
        .inline-input:focus { outline: none; border-color: var(--accent); background: var(--primary); }

        .actions-cell { display: flex; gap: 0.5rem; align-items: center; }

        /* Toast */
        #toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            color: white;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 9999;
            pointer-events: none;
        }
        #toast.show { opacity: 1; transform: translateY(0); }
        #toast.success { background: var(--success); }
        #toast.error { background: var(--danger); }

        /* About photo preview */
        #about-photo-preview {
            width: 120px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 0.75rem;
            display: block;
            border: 2px solid var(--border);
        }

        /* Featured image preview row */
        .featured-preview {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }
        .featured-preview img {
            width: 100%;
            aspect-ratio: 4/5;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid var(--border);
        }

        @media (max-width: 768px) {
            .topbar { padding: 1rem; }
            .tab-content { padding: 1rem; }
            .form-row { grid-template-columns: 1fr; }
            .featured-preview { grid-template-columns: 1fr; }
            .gallery-table th:nth-child(3),
            .gallery-table td:nth-child(3) { display: none; }
        }
    </style>
</head>
<body>

<div class="topbar">
    <h1>Admin Dashboard</h1>
    <div class="topbar-right">
        <span><?= htmlspecialchars($_SESSION['email']) ?></span>
        <a href="../upload/">Upload</a>
        <a href="../gallery/">Gallery</a>
        <a href="../logout/">Logout</a>
    </div>
</div>

<div class="tabs">
    <button class="tab-btn active" data-tab="gallery">Gallery</button>
    <button class="tab-btn" data-tab="home">Home Page</button>
    <button class="tab-btn" data-tab="about">About Page</button>
</div>

<!-- ===== GALLERY TAB ===== -->
<div class="tab-content active" id="tab-gallery">
    <div class="card">
        <h2>Gallery Images</h2>
        <?php if (empty($images)): ?>
            <p style="color: var(--muted);">No images uploaded yet.</p>
        <?php else: ?>
        <table class="gallery-table">
            <thead>
                <tr>
                    <th style="width:80px">Photo</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th style="width:120px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($images as $img): ?>
                <tr data-id="<?= $img['id'] ?>">
                    <td><img src="<?= htmlspecialchars($img['url']) ?>" class="thumb" alt=""></td>
                    <td><input type="text" class="inline-input img-title" value="<?= htmlspecialchars($img['title']) ?>" maxlength="255"></td>
                    <td><input type="text" class="inline-input img-category" value="<?= htmlspecialchars($img['category']) ?>" maxlength="100"></td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn btn-primary btn-sm save-image-btn">Save</button>
                            <button class="btn btn-danger btn-sm delete-image-btn">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- ===== HOME TAB ===== -->
<div class="tab-content" id="tab-home">
    <div class="card">
        <h2>Hero Text</h2>
        <div class="form-group">
            <label>Title</label>
            <input type="text" id="hero_title" value="<?= c($content, 'hero_title', 'Shots By Whatsername') ?>" maxlength="100">
        </div>
        <div class="form-group">
            <label>Subtitle</label>
            <input type="text" id="hero_subtitle" value="<?= c($content, 'hero_subtitle', 'Photography Portfolio') ?>" maxlength="150">
        </div>
        <div class="form-group">
            <label>Description</label>
            <input type="text" id="hero_description" value="<?= c($content, 'hero_description', 'Welcome to my world in photos! Take a look around!') ?>" maxlength="300">
        </div>
        <button class="btn btn-primary" id="save-hero-btn">Save Hero Text</button>
    </div>

    <div class="card">
        <h2>Featured Images</h2>
        <p style="color:var(--muted); margin-bottom:1.5rem; font-size:0.9rem;">Choose which images appear in the Featured Work section on the home page. Leave blank to use the default photos.</p>
        <div class="form-row">
            <div class="form-group">
                <label>Featured Image 1</label>
                <select id="featured_image_1">
                    <?= $imageOptions ?>
                </select>
            </div>
            <div class="form-group">
                <label>Featured Image 2</label>
                <select id="featured_image_2">
                    <?= $imageOptions ?>
                </select>
            </div>
            <div class="form-group">
                <label>Featured Image 3</label>
                <select id="featured_image_3">
                    <?= $imageOptions ?>
                </select>
            </div>
        </div>
        <div class="featured-preview">
            <img id="fp1" src="" alt="">
            <img id="fp2" src="" alt="">
            <img id="fp3" src="" alt="">
        </div>
        <button class="btn btn-primary" id="save-featured-btn" style="margin-top:1.5rem">Save Featured Images</button>
    </div>
</div>

<!-- ===== ABOUT TAB ===== -->
<div class="tab-content" id="tab-about">
    <div class="card">
        <h2>About Photo</h2>
        <div class="form-group">
            <label>Photo URL (or path like /uploads/filename.jpg)</label>
            <input type="text" id="about_photo" value="<?= c($content, 'about_photo', '../pictures/water.webp') ?>">
        </div>
        <img id="about-photo-preview" src="<?= c($content, 'about_photo', '../pictures/water.webp') ?>" alt="About photo preview">
        <button class="btn btn-primary" id="save-about-photo-btn" style="margin-top:1rem">Save Photo</button>
    </div>

    <div class="card">
        <h2>Bio Text</h2>
        <p style="color:var(--muted); margin-bottom:1rem; font-size:0.9rem;">You can use basic HTML like &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;. Each paragraph should be wrapped in &lt;p&gt;...&lt;/p&gt;</p>
        <div class="form-group">
            <textarea id="about_bio"><?= htmlspecialchars($content['about_bio'] ?? '') ?></textarea>
        </div>
        <button class="btn btn-primary" id="save-about-bio-btn">Save Bio</button>
    </div>
</div>

<div id="toast"></div>

<script>
const CSRF = <?= json_encode($_SESSION['csrf_token']) ?>;

// Image data for featured preview
const imageMap = {
    <?php foreach ($images as $img): ?>
    <?= $img['id'] ?>: { url: <?= json_encode($img['url']) ?>, title: <?= json_encode($img['title']) ?> },
    <?php endforeach; ?>
};

// Set featured select values from saved content
const featuredSaved = {
    featured_image_1: <?= json_encode($content['featured_image_1'] ?? '') ?>,
    featured_image_2: <?= json_encode($content['featured_image_2'] ?? '') ?>,
    featured_image_3: <?= json_encode($content['featured_image_3'] ?? '') ?>,
};
['featured_image_1','featured_image_2','featured_image_3'].forEach(key => {
    const sel = document.getElementById(key);
    if (featuredSaved[key]) sel.value = featuredSaved[key];
});
updateFeaturedPreviews();

// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});

// Toast helper
function toast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'show ' + type;
    setTimeout(() => { t.className = ''; }, 3000);
}

// Generic action poster
async function postAction(data) {
    data.csrf_token = CSRF;
    const body = new URLSearchParams(data);
    const res = await fetch('actions.php', { method: 'POST', body });
    return res.json();
}

// --- Gallery: save image ---
document.querySelectorAll('.save-image-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const row = btn.closest('tr');
        const id = row.dataset.id;
        const title = row.querySelector('.img-title').value.trim();
        const category = row.querySelector('.img-category').value.trim();
        if (!title || !category) { toast('Title and category are required', 'error'); return; }
        btn.disabled = true;
        const result = await postAction({ action: 'update_image', id, title, category });
        btn.disabled = false;
        if (result.success) toast('Image updated');
        else toast(result.error || 'Error', 'error');
    });
});

// --- Gallery: delete image ---
document.querySelectorAll('.delete-image-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('Delete this image? This cannot be undone.')) return;
        const row = btn.closest('tr');
        const id = row.dataset.id;
        btn.disabled = true;
        const result = await postAction({ action: 'delete_image', id });
        if (result.success) {
            row.remove();
            toast('Image deleted');
        } else {
            btn.disabled = false;
            toast(result.error || 'Error', 'error');
        }
    });
});

// --- Home: save hero ---
document.getElementById('save-hero-btn').addEventListener('click', async () => {
    const fields = ['hero_title', 'hero_subtitle', 'hero_description'];
    for (const key of fields) {
        const val = document.getElementById(key).value;
        const result = await postAction({ action: 'update_content', key, value: val });
        if (!result.success) { toast(result.error || 'Error saving ' + key, 'error'); return; }
    }
    toast('Hero text saved');
});

// --- Home: save featured images ---
document.getElementById('save-featured-btn').addEventListener('click', async () => {
    const keys = ['featured_image_1', 'featured_image_2', 'featured_image_3'];
    for (const key of keys) {
        const val = document.getElementById(key).value;
        const result = await postAction({ action: 'update_content', key, value: val });
        if (!result.success) { toast(result.error || 'Error saving ' + key, 'error'); return; }
    }
    toast('Featured images saved');
    updateFeaturedPreviews();
});

// --- Featured image live preview ---
['featured_image_1','featured_image_2','featured_image_3'].forEach((key, i) => {
    document.getElementById(key).addEventListener('change', updateFeaturedPreviews);
});

function updateFeaturedPreviews() {
    const defaults = ['./pictures/green.webp', './pictures/howth.webp', './pictures/water.webp'];
    ['featured_image_1','featured_image_2','featured_image_3'].forEach((key, i) => {
        const sel = document.getElementById(key);
        const img = document.getElementById('fp' + (i+1));
        const val = sel.value;
        img.src = val && imageMap[val] ? imageMap[val].url : '../' + defaults[i];
        img.style.display = 'block';
    });
}

// --- About: photo preview ---
document.getElementById('about_photo').addEventListener('input', function() {
    document.getElementById('about-photo-preview').src = this.value;
});

// --- About: save photo ---
document.getElementById('save-about-photo-btn').addEventListener('click', async () => {
    const value = document.getElementById('about_photo').value;
    const result = await postAction({ action: 'update_content', key: 'about_photo', value });
    if (result.success) toast('Photo saved');
    else toast(result.error || 'Error', 'error');
});

// --- About: save bio ---
document.getElementById('save-about-bio-btn').addEventListener('click', async () => {
    const value = document.getElementById('about_bio').value;
    const result = await postAction({ action: 'update_content', key: 'about_bio', value });
    if (result.success) toast('Bio saved');
    else toast(result.error || 'Error', 'error');
});
</script>
</body>
</html>
