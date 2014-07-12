<div class="tab-content">
<?php
$timelines = $this->timelines();
foreach ($timelines as $index => $timeline) {
    $id = 'timeline-' . ($index + 1);
    if ($index === 0) {
        echo '<div class="timeline tab-pane active" id="'. $id . '">';
    } else {
        echo '<div class="timeline tab-pane" id="'. $id . '">';
    }
    $this->Load('index/body-timeline', $timeline['events']);
    echo '</div>';
}
?>
</div>
