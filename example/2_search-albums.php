<?php

include '_include.php';

$sourceId     = empty($_GET['source'])     ? array_keys($sources)[0] : $_GET['source'];
$artistName   = empty($_GET['artistName']) ? 'Andrew Sisters'        : $_GET['artistName'];
$itensPerPage = 20;

$source       = $sources[$sourceId];

$albuns = $source->getArtistsAlbums($artistName);
require '_header.php';
?>

<h1>
    <?php echo "$sourceId: albums of $artistName"; ?>
</h1>
<div class="grid albums">
    <?php
    foreach ($albuns as $album) {
        if ($album->single) {
            echo
            "<div class=\"cell release\" title=\"{$album->id}\">
                <span class=\"thumbnail\" style=\"background-image: url({$album->thumbnail})\"></span>
                <h3>{$album->title}</h3>
            </div>";
        } else {
            echo
            "<a href=\"3_list-tracks.php?source={$album->source}&artistName=" . ( $album->artist ?: $artistName ) . "&albumTitle={$album->title}\" class=\"cell release\" title=\"{$album->id}\">
                <span class=\"thumbnail\" style=\"background-image: url({$album->thumbnail})\"></span>
                <h3>{$album->title}</h3>
            </a>";
        }
    }
    ?>
</div>
<?php

require '_footer.php';
