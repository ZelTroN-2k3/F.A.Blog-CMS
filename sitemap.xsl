<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9">
<xsl:output method="html" encoding="UTF-8" indent="yes" />
<xsl:template match="/">
<html>
<head>
    <title>XML Sitemap - Site Map</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #333; margin: 0; padding: 30px; background: #f8f9fa; 
        }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        h1 { color: #0d6efd; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-top: 0; }
        p.desc { background: #e7f1ff; padding: 15px; border-radius: 5px; color: #0c5460; border-left: 5px solid #0d6efd; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f1f1f1; color: #555; text-align: left; padding: 15px; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
        td { padding: 12px 15px; border-bottom: 1px solid #eee; font-size: 0.95rem; }
        tr:hover { background-color: #f8f9fa; }
        
        a { color: #212529; text-decoration: none; font-weight: 500; }
        a:hover { color: #0d6efd; text-decoration: underline; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; }
        .high { background-color: #d1e7dd; color: #0f5132; } /* Vert pour priorité haute */
        .med { background-color: #fff3cd; color: #664d03; }  /* Jaune pour moyen */
        .low { background-color: #f8d7da; color: #842029; }  /* Rouge pour bas */
    </style>
</head>
<body>
    <div class="container">
        <h1>XML Sitemap</h1>
        <p class="desc">
            This XML file helps search engines (Google, Bing) index your content.<br/>
            It currently contains <strong><xsl:value-of select="count(sitemap:urlset/sitemap:url)"/></strong> pages (Posts, Projects, Pages).
        </p>
        <table>
            <thead>
                <tr>
                    <th width="60%">Page URL</th>
                    <th width="15%">Priority</th>
                    <th width="25%">Last Modified</th>
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="sitemap:urlset/sitemap:url">
                <tr>
                    <td><a href="{sitemap:loc}" target="_blank"><xsl:value-of select="sitemap:loc"/></a></td>
                    <td>
                        <xsl:choose>
                            <xsl:when test="sitemap:priority &gt;= 0.8"><span class="badge high"><xsl:value-of select="sitemap:priority"/></span></xsl:when>
                            <xsl:when test="sitemap:priority &gt;= 0.5"><span class="badge med"><xsl:value-of select="sitemap:priority"/></span></xsl:when>
                            <xsl:otherwise><span class="badge low"><xsl:value-of select="sitemap:priority"/></span></xsl:otherwise>
                        </xsl:choose>
                    </td>
                    <td><xsl:value-of select="sitemap:lastmod"/></td>
                </tr>
                </xsl:for-each>
            </tbody>
        </table>
    </div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>