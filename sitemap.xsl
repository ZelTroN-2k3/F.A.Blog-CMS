<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9">
<xsl:output method="html" encoding="UTF-8" indent="yes" />
<xsl:template match="/">
<html>
<head>
    <title>Sitemap XML</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color: #333; margin: 0; padding: 2rem; background: #f8f9fa; }
        h1 { color: #0d6efd; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        p.desc { background: #e9ecef; padding: 10px; border-radius: 5px; border-left: 5px solid #0d6efd; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
        th { background: #0d6efd; color: #fff; text-align: left; padding: 12px; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        tr:hover { background-color: #f1f1f1; }
        a { color: #0d6efd; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Sitemap XML</h1>
    <p class="desc">
        Ce fichier est un plan de site XML généré automatiquement pour les moteurs de recherche (Google, Bing, etc.).
        <br/>Il contient <strong><xsl:value-of select="count(sitemap:urlset/sitemap:url)"/></strong> liens.
    </p>
    <table>
        <tr>
            <th>URL</th>
            <th>Priorité</th>
            <th>Fréquence</th>
            <th>Dernière Modif.</th>
        </tr>
        <xsl:for-each select="sitemap:urlset/sitemap:url">
        <tr>
            <td><a href="{sitemap:loc}" target="_blank"><xsl:value-of select="sitemap:loc"/></a></td>
            <td><xsl:value-of select="sitemap:priority"/></td>
            <td><xsl:value-of select="sitemap:changefreq"/></td>
            <td><xsl:value-of select="sitemap:lastmod"/></td>
        </tr>
        </xsl:for-each>
    </table>
</body>
</html>
</xsl:template>
</xsl:stylesheet>