<?php
include "core.php";

$query = mysqli_query($connect, "SELECT * FROM posts WHERE active='Yes' AND publish_at <= NOW() ORDER BY id DESC LIMIT 20");
 
header( "Content-type: text/xml");
 
echo '<?xml version=\'1.0\' encoding=\'UTF-8\'?>
<?xml-stylesheet type="text/xsl" href="rss.xsl"?>
<rss version=\'2.0\'>
	<channel>
		<title>' . htmlspecialchars($settings['sitename']) . ' | RSS</title>
		<link>' . $settings['site_url'] . '/blog</link>
		<description>RSS Feed</description>
		<language>en-us</language>';
 
while($post = mysqli_fetch_array($query)){
	$title       = htmlspecialchars($post["title"]);
	$link        = $settings['site_url'] . '/post?name=' . $post["slug"];
	$description = short_text(strip_tags(html_entity_decode($post['content'])), 100);
	// MODIFICATION : Utilisation de created_at et formatage RSS (RFC 2822)
	$pubDate     = date('r', strtotime($post["created_at"]));
	$guid        = $post["id"];
	
	echo "
	<item>
		<title>$title</title>
		<link>$link</link>
		<pubDate>$pubDate</pubDate>
		<guid isPermaLink=\"false\">$guid</guid>
	</item>";
 }
 echo "</channel></rss>";
?>