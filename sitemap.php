<?php
include "core.php";

header('Content-type: application/xml');
echo "<?xml version='1.0' encoding='UTF-8'?>" . "\n";
// --- LIGNE À AJOUTER ICI ---
echo '<?xml-stylesheet type="text/xsl" href="sitemap.xsl"?>' . "\n"; 
// ---------------------------
echo "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>" . "\n";

// 1. Page d'accueil
echo "<url>" . "\n";
echo '  <loc>' . $settings['site_url'] . '/</loc>' . "\n";
echo "  <changefreq>daily</changefreq>" . "\n";
echo "  <priority>1.0</priority>" . "\n";
echo "</url>" . "\n";

// 2. Tous les Articles (Posts)
$posts_query = mysqli_query($connect, "SELECT slug, publish_at FROM posts WHERE active='Yes' AND publish_at <= NOW() ORDER BY publish_at DESC");
while($post = mysqli_fetch_array($posts_query)) {
    // Formatage de la date au format W3C (requis pour <lastmod>)
    $lastmod = date('c', strtotime($post['publish_at']));
    
    echo "<url>" . "\n";
    echo '  <loc>' . $settings['site_url'] . '/post?name=' . htmlspecialchars($post['slug']) . '</loc>' . "\n";
    echo '  <lastmod>' . $lastmod . '</lastmod>' . "\n";
    echo "  <changefreq>weekly</changefreq>" . "\n";
    echo "  <priority>0.9</priority>" . "\n";
    echo "</url>" . "\n";
}

// 3. Toutes les Pages
$pages_query = mysqli_query($connect, "SELECT * FROM `pages`");
while($page = mysqli_fetch_array($pages_query)) {
    echo "<url>" . "\n";
    echo '  <loc>' . $settings['site_url'] . '/page?name=' . htmlspecialchars($page['slug']) . '</loc>' . "\n";
    echo "  <changefreq>monthly</changefreq>" . "\n";
    echo "  <priority>0.7</priority>" . "\n";
    echo "</url>" . "\n";
}

// 4. Toutes les Catégories
$categories = mysqli_query($connect, "SELECT * FROM `categories`");
while($cat = mysqli_fetch_array($categories)) {
    echo "<url>" . "\n";
    echo '  <loc>' . $settings['site_url'] . '/category?name=' . htmlspecialchars($cat['slug']) . '</loc>' . "\n";
    echo "  <changefreq>weekly</changefreq>" . "\n";
    echo "  <priority>0.8</priority>" . "\n";
    echo "</url>" . "\n";
}

// 5. Pages statiques (Galerie, Contact, etc.)
// Nous incluons les autres liens du menu qui ne sont pas des pages ou des articles
$menu_query = mysqli_query($connect, "SELECT * FROM `menu` WHERE path != 'index.php' AND path NOT LIKE 'page?name=%' AND path NOT LIKE 'post?name=%' AND path NOT LIKE 'category?name=%' AND path != 'blog'");
while($link = mysqli_fetch_array($menu_query)) {
	echo "<url>" . "\n";
	echo '  <loc>' . $settings['site_url'] . '/' . htmlspecialchars($link['path']) . '</loc>' . "\n";
	echo "  <changefreq>monthly</changefreq>" . "\n";
	echo "  <priority>0.5</priority>" . "\n";
	echo "</url>" . "\n";
}

echo "</urlset>";
?>