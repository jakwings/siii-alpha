<div class="list-group">
<?php
$events = $data;
foreach ($events as $event) {
    $category = $this->EscapeHtml($event['category']);
    $content = $this->ParseMarkdown($event['content']);
    $has_slug = ($event['slug'] !== '');
    if ($has_slug) {
        echo '<a class="event list-group-item" data-category="' . $category
             . '" href="' . $this->EscapeHtml($event['slug']) . '.html"><div>';
    } else {
        echo '<div class="event list-group-item" data-category="' . $category
             . '" href="#">';
    }
    echo   '<div class="list-group-item-heading">';
    if ($has_slug) {
        echo '<span class="glyphicon glyphicon-pencil"></span>';
    } else {
        echo '<span class="glyphicon glyphicon-bullhorn"></span>';
    }
    echo     ' ';
    echo     '<b>' . $category . '</b>';
    echo     '<span class="date pull-right" data-toggle="tooltip" title="'
             . strftime('%F %H:%M:%S%z', $event['date']) . '">'
             . strftime('%F', $event['date']) . '</span>';
    echo   '</div>';
    echo   '<div class="list-group-item-text">';
    echo      '<div style="white-space: pre-wrap">' . $content . '</div>';
    echo   '</div>';
    if ($has_slug) {
        echo '</div></a>';
    } else {
        echo '</div>';
    }
}
?>
</div>
