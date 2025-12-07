<?php
// 1. Démarrer la mémoire tampon
ob_start();

include "core.php";

// 2. EFFACER tout ce qui a été généré avant (espaces, lignes vides venant des includes)
ob_end_clean();

// 3. Définir le header
header("Content-Type: application/xml; charset=UTF-8");

// 4. Générer le XML (Avec des sauts de ligne \n pour la propreté)
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?xml-stylesheet type="text/xsl" href="rss.xsl"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title><?php echo htmlspecialchars($settings['sitename']); ?></title>
    <link><?php echo htmlspecialchars($settings['site_url']); ?></link>
    <description><?php echo htmlspecialchars($settings['description']); ?></description>
    <atom:link href="<?php echo htmlspecialchars($settings['site_url']); ?>/rss.php" rel="self" type="application/rss+xml" />
    <language>en-us</language>
    
    <?php
    // REQUÊTE MIXTE (Articles + Projets) triés par date
    $query = "
        (SELECT 'post' as type, title, slug, content as description, image, created_at, id 
         FROM posts 
         WHERE active='Yes' AND publish_at <= NOW())
        UNION
        (SELECT 'project' as type, title, slug, pitch as description, image, created_at, id 
         FROM projects 
         WHERE active='Yes')
        ORDER BY created_at DESC 
        LIMIT 20
    ";
    
    $result = mysqli_query($connect, $query);

    while($row = mysqli_fetch_assoc($result)) {
        
        $link = ($row['type'] == 'post') 
                ? $settings['site_url'] . '/post?name=' . $row['slug'] 
                : $settings['site_url'] . '/project?name=' . $row['slug'];
        
        $desc = strip_tags(html_entity_decode($row['description']));
        $desc = substr($desc, 0, 300) . '...';
        
        // Image
        $img_tag = '';
        if (!empty($row['image'])) {
            $clean_img = str_replace('../', '', $row['image']);
            if (file_exists($clean_img)) {
                $img_url = $settings['site_url'] . '/' . $clean_img;
                $img_tag = '<enclosure url="' . $img_url . '" type="image/jpeg" />';
            }
        }
    ?>
    <item>
        <title><?php echo htmlspecialchars($row['title']); ?></title>
        <link><?php echo $link; ?></link>
        <guid><?php echo $link; ?></guid>
        <pubDate><?php echo date(DATE_RSS, strtotime($row['created_at'])); ?></pubDate>
        <description><![CDATA[<?php echo $desc; ?>]]></description>
        <?php echo $img_tag; ?>
    </item>
    <?php } ?>
    
</channel>
</rss>