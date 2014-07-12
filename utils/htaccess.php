<?php
$tmp_base = $this->metadata('path');

$tmp_htaccess = <<<"EOT"
Options FollowSymLinks SymLinksIfOwnerMatch
Order Deny,Allow

AddDefaultCharset UTF-8
<IfModule mod_php5.c>
  php_value magic_quotes_gpc off
  php_value default_charset utf-8
</IfModule>

<FilesMatch "^\.">
    Deny from all
</FilesMatch>

#<IfModule mod_dir.c>
    DirectoryIndex index.php
    DirectorySlash Off
#</IfModule>

<IfModule mod_headers.c>
    Header always set X-Frame-Options SAMEORIGIN
</IfModule>

#<IfModule mod_expires.c>
#  ExpiresActive On
#  ExpiresDefault "access plus 12 hours"
#  ExpiresByType "text/html" "access plus 60 minutes"
#  ExpiresByType "text/css" "access plus 7 days"
#  ExpiresByType "application/rss+xml" "access plus 30 minutes"
#  ExpiresByType "application/javascript" "access plus 7 days"
#  ExpiresByType "application/x-font-woff" "access plus 30 days"
#  ExpiresByType "image/png" "access plus 7 days"
#  ExpiresByType "image/jpg" "access plus 7 days"
#  ExpiresByType "image/gif" "access plus 7 days"
#  ExpiresByType "image/bmp" "access plus 7 days"
#</IfModule>

#<IfModule mod_rewrite.c>
    RewriteEngine on
    # blog path
    RewriteBase {$tmp_base}/
    RewriteCond %{REQUEST_URI} \.html$
    RewriteRule ^(.*)\.html$ index.php?view=$1 [NS,L]

    # no hotlinking
    #RewriteCond %{REQUEST_URI}  ^/data/
    #RewriteCond %{HTTP_REFERER} !^$
    #RewriteCond %{HTTP_REFERER} !^https?://([^.]+\.)*example\.com\.?/ [NC]
    #RewriteRule \.(png|gif|jpe?g|bmp)$ errors/hotlink.gif? [R,L,NC]
#</IfModule>
EOT;

file_put_contents('./.htaccess', $tmp_htaccess, LOCK_EX);
?>
