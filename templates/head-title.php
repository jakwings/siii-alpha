<?php
$escaped_title = $this->metadata('title', TRUE);

echo <<<"EOT"
<title>{$escaped_title}</title>
EOT;
?>
