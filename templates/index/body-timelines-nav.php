<ul class="nav nav-tabs" role="tablist">
<?php
$timelines = $this->timelines();
foreach ($timelines as $index => $timeline) {
    $id = 'timeline-' . ($index + 1);
    $name = $this->EscapeHtml($timeline['name']);
    if ($index === 0) {
        echo '<li class="active">';
    } else {
        echo '<li>';
    }
    echo '<a role="tab" data-toggle="tab" href="#'. $id . '">' . $name . '</a>';
    echo '</li>';
}
?>
</ul>
