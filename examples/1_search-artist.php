<?php 
use AdinanCenci\GenericRestApi\Exception\UserError;
use AdinanCenci\GenericRestApi\Exception\ServerError;

include 'includes/_include.php';

//---------------------------------------

$sourceId     = getSourceId();
$source       = getSource($sourceId);
$artistName   = get('artistName', 'The Andrew Sisters');

//---------------------------------------

try {
    $results  = $source->searchForArtist($artistName);
} catch (UserError $e) {
    echo $e->getMessage();
    die();
}

require 'includes/_header.php';
?>

<h1>
    <?php echo "Artists"; ?>
</h1>
<h2>
    <?php echo "from $sourceId | " . switchSourceLinks($_GET['source'] ?? ''); ?>
</h2>
<form>
    <input type="hidden" name="source" value="<?php echo $sourceId;?>" />
    <input type="text" name="artistName" placeholder="artist" value="<?php echo $artistName;?>" />
    <input type="submit" value="Search for artist" />
</form>
<div class="grid artists">
    <?php
    foreach ($results as $artist) {
        echo
        "<a href=\"2_search-albums.php?source={$artist->source}&artistName={$artist->name}\" class=\"cell artist\" title=\"{$artist->id}\">
            <span class=\"thumbnail\" style=\"background-image: url({$artist->thumbnail})\"></span>
            <h3>{$artist->name}</h3>
        </a>";
    }
    ?>
</div>

<?php
require 'includes/_footer.php';
