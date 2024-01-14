<?php 
include '_include.php';

$sourceId     = empty($_GET['source'])       ? array_keys($sources)[0] : $_GET['source'];
$releaseId    = empty($_GET['releaseId'])    ? ''                      : $_GET['releaseId'];
$artistName   = empty($_GET['artistName'])   ? ''                      : $_GET['artistName'];
$releaseTitle = empty($_GET['releaseTitle']) ? ''                      : $_GET['releaseTitle'];

$source       = $sources[$sourceId];

$release = $source->findAlbum($releaseId, $artistName, $releaseTitle);
if (!$release) {
    die();
}

require '_header.php';
?>
<ul>
    <?php 
    foreach ($release->tracks as $t) {
        echo "<li>$t</t>";
    }
    ?>
</ul>
<?php 
require '_footer.php';
