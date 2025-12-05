<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:atom="http://www.w3.org/2005/Atom">
<xsl:output method="html" encoding="UTF-8" indent="yes" />
<xsl:template match="/">
<html>
<head>
    <title><xsl:value-of select="/rss/channel/title"/> - Flux RSS</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; max-width: 900px; margin: 2rem auto; background: #fdfdfd; padding: 20px; }
        header { background: #ffc107; padding: 20px; border-radius: 8px; margin-bottom: 20px; color: #333; }
        h1 { margin: 0; }
        .item { background: #fff; border: 1px solid #ddd; padding: 20px; margin-bottom: 15px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .item h2 { margin-top: 0; font-size: 1.5rem; }
        .item h2 a { color: #333; text-decoration: none; }
        .item h2 a:hover { color: #0d6efd; }
        .meta { color: #777; font-size: 0.9rem; margin-bottom: 10px; }
        .desc { line-height: 1.6; }
        .btn { display: inline-block; background: #0d6efd; color: #fff; padding: 8px 15px; text-decoration: none; border-radius: 4px; margin-top: 10px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <header>
        <h1><i class="fas fa-rss"></i> <xsl:value-of select="/rss/channel/title"/></h1>
        <p><xsl:value-of select="/rss/channel/description"/></p>
    </header>
    
    <xsl:for-each select="/rss/channel/item">
    <div class="item">
        <h2><a href="{link}"><xsl:value-of select="title"/></a></h2>
        <div class="meta">Publié le : <xsl:value-of select="pubDate"/></div>
        <div class="desc"><xsl:value-of select="description" disable-output-escaping="yes"/></div>
        <a href="{link}" class="btn">Lire l'article complet →</a>
    </div>
    </xsl:for-each>
</body>
</html>
</xsl:template>
</xsl:stylesheet>