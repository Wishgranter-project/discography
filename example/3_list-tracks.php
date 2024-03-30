<?php
include '_include.php';

$sourceId   = empty($_GET['source'])     ? array_keys($sources)[0] : $_GET['source'];
$artistName = empty($_GET['artistName']) ? ''                      : $_GET['artistName'];
$albumTitle = empty($_GET['albumTitle']) ? ''                      : $_GET['albumTitle'];

$source     = $sources[$sourceId];

$release = $source->getAlbum($artistName, $albumTitle);
if (!$release) {
    die();
}

require '_header.php';
?>
<h1>
    <?php echo "$artistName - $albumTitle"; ?>
</h1>
<h2>
    <?php echo "$sourceId | " . switchSourceLinks($_GET['source'] ?? ''); ?>
</h2>
<ul>
    <?php 
    foreach ($release->tracks as $t) {
        echo "<li>$t</t>";
    }
    ?>
</ul>

<?php
echo
"<a href=\"2_search-albums.php?artistName=$artistName&source=$sourceId\"><< back</a>";

require '_footer.php';
