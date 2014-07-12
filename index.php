<?php
require './utils/config.php';

$root = $blog->EscapeHtml(rtrim($blog->metadata('path'), '/'));
$is_index = ($blog->slug() === '');
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <?php $blog->Load('head-title') ?>

<?php echo <<<"EOT"
    <link rel="stylesheet" href="{$root}/data/css/bootstrap.min.css">
    <link rel="stylesheet" href="{$root}/data/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="{$root}/data/css/style.css">
EOT;
?>

  </head>

  <body>
    <div class="blog">
      <?php $blog->Load('body-header') ?>

      <?php $is_index and $blog->Load('index/body-timelines') ?>
      <?php !$is_index and $blog->Load('article/body-main') ?>

      <?php $blog->Load('body-footer') ?>
    </div>

<?php echo <<<"EOT"
    <script src="{$root}/data/js/jquery-2.1.1.min.js"></script>
    <script src="{$root}/data/js/bootstrap.min.js"></script>
    <script src="{$root}/data/js/miscellaneous.js"></script>
EOT;
?>

  </body>
</html>