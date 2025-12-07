<?php
include "core.php";

header("Content-type: text/xml");

// On génère l'en-tête via PHP pour éviter le conflit "short_open_tag"
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?xml-stylesheet type="text/xsl" href="sitemap.xsl"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    <url>
        <loc><?php echo $settings['site_url']; ?></loc>
        <priority>1.00</priority>
    </url>
    <url>
        <loc><?php echo $settings['site_url']; ?>/blog</loc>
        <priority>0.90</priority>
    </url>
    <url>
        <loc><?php echo $settings['site_url']; ?>/projects</loc>
        <priority>0.90</priority>
    </url>
    <url>
        <loc><?php echo $settings['site_url']; ?>/contact</loc>
        <priority>0.50</priority>
    </url>

    <?php
    $sql = mysqli_query($connect, "SELECT slug FROM pages WHERE active='Yes'");
    while ($row = mysqli_fetch_assoc($sql)) {
    ?>
    <url>
        <loc><?php echo $settings['site_url']; ?>/page?name=<?php echo $row['slug']; ?></loc>
        <priority>0.60</priority>
    </url>
    <?php } ?>

    <?php
    $sql = mysqli_query($connect, "SELECT slug, created_at FROM posts WHERE active='Yes' AND publish_at <= NOW()");
    while ($row = mysqli_fetch_assoc($sql)) {
        $date = date('Y-m-d', strtotime($row['created_at']));
    ?>
    <url>
        <loc><?php echo $settings['site_url']; ?>/post?name=<?php echo $row['slug']; ?></loc>
        <lastmod><?php echo $date; ?></lastmod>
        <priority>0.80</priority>
    </url>
    <?php } ?>

    <?php
    $sql = mysqli_query($connect, "SELECT slug FROM categories");
    while ($row = mysqli_fetch_assoc($sql)) {
    ?>
    <url>
        <loc><?php echo $settings['site_url']; ?>/category?name=<?php echo $row['slug']; ?></loc>
        <priority>0.70</priority>
    </url>
    <?php } ?>

    <?php
    $sql = mysqli_query($connect, "SELECT slug, created_at FROM projects WHERE active='Yes'");
    while ($row = mysqli_fetch_assoc($sql)) {
        $date = date('Y-m-d', strtotime($row['created_at']));
    ?>
    <url>
        <loc><?php echo $settings['site_url']; ?>/project?name=<?php echo $row['slug']; ?></loc>
        <lastmod><?php echo $date; ?></lastmod>
        <priority>0.85</priority>
    </url>
    <?php } ?>

    <?php
    $sql = mysqli_query($connect, "SELECT slug FROM project_categories");
    while ($row = mysqli_fetch_assoc($sql)) {
    ?>
    <url>
        <loc><?php echo $settings['site_url']; ?>/projects?category=<?php echo $row['slug']; ?></loc>
        <priority>0.70</priority>
    </url>
    <?php } ?>

</urlset>