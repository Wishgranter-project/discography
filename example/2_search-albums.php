<?php 
include '_include.php';

$sourceId     = empty($_GET['source'])     ? array_keys($sources)[0] : $_GET['source'];
$artistName   = empty($_GET['artistName']) ? 'Andrew Sisters'        : $_GET['artistName'];
$page         = empty($_GET['page'])       ? 1                       : $_GET['page'];
$itensPerPage = 20;

$source       = $sources[$sourceId];

$results = $source->searchForAlbumsByArtistName($artistName, $page);
require '_header.php';
?>

<h1>
    <?php echo "$sourceId: albums of $artistName"; ?>
</h1>
<div class="grid albums">
    <?php
    foreach ($results->items as $release) {
        echo 
        "<a href=\"3_list-tracks.php?source={$release->source}&releaseId={$release->id}&artistName=" . ( $release->artist ?: $artistName ) . "&releaseTitle={$release->title}\" class=\"cell release\">
            <span class=\"thumbnail\" style=\"background-image: url({$release->thumbnail})\"></span>
            <h3>{$release->title}</h3>
        </a>";
    }
    ?>
</div>
<?php 
echo pagination($results);
require '_footer.php';
