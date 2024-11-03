<?php
include 'includes/_include.php';

//---------------------------------------

$sourceId     = getSourceId();
$source       = getSource($sourceId);
$artistName   = get('artistName', '');
$albumTitle   = get('albumTitle', '');

//---------------------------------------

$album = $source->getAlbum($artistName, $albumTitle);

require 'includes/_header.php';
?>
<h1>
    <?php echo 'Tracks of "' . $albumTitle . '" by ' . $artistName; ?>
</h1>
<h2>
    <?php echo "from $sourceId | See results from " . switchSourceLinks($_GET['source'] ?? ''); ?>
</h2>
<?php
if (!$album) {
    echo '<p>-- Nothing found --</p>';
}
?>
<ul>
    <?php
    if ($album) {
        foreach ($album->tracks as $t) {
            echo "<li>$t</t>";
        }
    }
    ?>
</ul>

<?php
echo
"<a href=\"2_search-albums.php?artistName=$artistName&source=$sourceId\"><< back</a>";

require 'includes/_footer.php';
