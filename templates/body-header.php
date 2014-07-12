<?php
$root = $this->EscapeHtml(rtrim($this->metadata('path'), '/'));
$title = $this->metadata('title', TRUE);
$subtitle = $this->metadata('subtitle', TRUE);
echo <<<"EOT"
<div class="header">
  <div class="title"><a href="{$root}/">{$title}</a></div>
  <div class="subtitle">{$subtitle}</div>
</div>
EOT;
?>
