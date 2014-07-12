<ul class="nav nav-pills">
<?php
$events = $data;
$categories = array();
foreach ($events as $event) {
    $categories[] = $event['category'];
}
$categories = array_unique($categories, SORT_LOCALE_STRING);
echo '<li class="active"><a data-toggle="pill" href="#"><span class="glyphicon glyphicon-list"></span></a></li>';
foreach ($categories as $category) {
    $name = $this->EscapeHtml($category);
    echo '<li><a data-toggle="pill" href="#">' . $name . '</a></li>';
}
?>
</ul>
