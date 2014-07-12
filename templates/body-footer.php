<?php
$author = $this->metadata('author', TRUE);
$license = $this->metadata('license');
$license_text = $this->EscapeHtml($license['text']);
$license_link = $this->EscapeHtml($license['link']);
echo <<<"EOT"
<div class="footer">
  <div class="license">
    <a class="text-muted" href="{$license_link}">{$license_text} © 2014 {$author}</a>
  </div>
</div>
EOT;
?>
