<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" encoding="UTF-8" indent="yes" />
<xsl:template match="/">
<html>
<head>
    <title><xsl:value-of select="/rss/channel/title"/> - Flux RSS</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f6f9; color: #333; margin: 0; padding: 20px; 
        }
        .container { max-width: 800px; margin: 0 auto; }
        
        header { 
            background: #fff; padding: 30px; border-radius: 12px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; border-left: 5px solid #ffc107;
        }
        header h1 { margin: 0 0 10px 0; color: #2c3e50; font-size: 1.8rem; }
        header p { margin: 0; color: #666; }
        
        .item { 
            background: #fff; padding: 25px; margin-bottom: 25px; 
            border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
            transition: transform 0.2s; border: 1px solid #eee;
        }
        .item:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        
        .item h2 { margin-top: 0; margin-bottom: 10px; font-size: 1.4rem; }
        .item h2 a { color: #0d6efd; text-decoration: none; }
        .item h2 a:hover { text-decoration: underline; }
        
        .meta { color: #888; font-size: 0.85rem; margin-bottom: 15px; display: flex; align-items: center; }
        .badge { background: #e9ecef; color: #495057; padding: 3px 8px; border-radius: 4px; margin-right: 10px; font-weight: bold; }
        
        .content-flex { display: flex; gap: 20px; }
        .image-box { flex: 0 0 150px; }
        .image-box img { width: 150px; height: 100px; object-fit: cover; border-radius: 6px; border: 1px solid #eee; }
        .text-box { flex: 1; }
        
        .desc { line-height: 1.6; color: #555; }
        .btn { 
            display: inline-block; margin-top: 15px; padding: 8px 20px; 
            background-color: #0d6efd; color: white; text-decoration: none; 
            border-radius: 30px; font-size: 0.9rem; font-weight: 500;
        }
        .btn:hover { background-color: #0b5ed7; }
        
        @media (max-width: 600px) {
            .content-flex { flex-direction: column; }
            .image-box { flex: 0 0 auto; width: 100%; }
            .image-box img { width: 100%; height: auto; max-height: 200px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><xsl:value-of select="/rss/channel/title"/></h1>
            <p><xsl:value-of select="/rss/channel/description"/></p>
            <p style="margin-top:10px; font-size:0.9rem; color:#999;">
                CThis is an RSS feed. Subscribe with a feed reader to receive updates.
            </p>
        </header>
        
        <xsl:for-each select="/rss/channel/item">
        <div class="item">
            <div class="content-flex">
                <xsl:if test="enclosure">
                    <div class="image-box">
                        <img src="{enclosure/@url}" alt="Image" />
                    </div>
                </xsl:if>
                
                <div class="text-box">
                    <h2><a href="{link}" target="_blank"><xsl:value-of select="title"/></a></h2>
                    <div class="meta">
                        <span class="badge">POST</span>
                        <xsl:value-of select="pubDate"/>
                    </div>
                    <div class="desc"><xsl:value-of select="description" disable-output-escaping="yes"/></div>
                    <a href="{link}" target="_blank" class="btn">Read more</a>
                </div>
            </div>
        </div>
        </xsl:for-each>
    </div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>