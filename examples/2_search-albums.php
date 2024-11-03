<?php

include 'includes/_include.php';

//---------------------------------------

$sourceId     = getSourceId();
$source       = getSource($sourceId);
$artistName   = get('artistName', 'Andrew Sisters');
$itemsPerPage = 20;

//---------------------------------------

$albuns = $source->getArtistsAlbums($artistName);
require 'includes/_header.php';
?>

<h1>
    <?php echo 'Albums, results for "' . $artistName . '"'; ?>
</h1>
<h2>
    <?php echo "from $sourceId | See results from " . switchSourceLinks($sourceId); ?>
</h2>
<form>
    <input type="hidden" name="source" value="<?php echo $sourceId;?>" />
    <input type="text" name="artistName" placeholder="artist" value="<?php echo $artistName;?>" />
    <input type="submit" value="Search for artist" />
</form>
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
echo
"<a href=\"1_search-artist.php?artistName=$artistName&source=$sourceId\"><< back</a>";

require 'includes/_footer.php';
