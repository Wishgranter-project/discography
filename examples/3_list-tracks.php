<?php
include 'includes/_include.php';

//---------------------------------------

$sourceId     = getSourceId();
$source       = getSource($sourceId);
$artistName   = get('artistName', '');
$albumTitle   = get('albumTitle', '');

//---------------------------------------

$release = $source->getAlbum($artistName, $albumTitle);
if (!$release) {
    die();
}

require 'includes/_header.php';
?>
<h1>
    <?php echo "$artistName - $albumTitle"; ?>
</h1>
<h2>
    <?php echo "from $sourceId | " . switchSourceLinks($_GET['source'] ?? ''); ?>
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

require 'includes/_footer.php';
