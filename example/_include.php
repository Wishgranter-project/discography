<?php

use WishgranterProject\Discography\Api\ApiMusicBrainz;
use WishgranterProject\Discography\Source\SourceMusicBrainz;
use WishgranterProject\Discography\Source\SearchResults;
use WishgranterProject\Discography\Artist;
use WishgranterProject\Discography\Release;
use AdinanCenci\FileCache\Cache;

//---------------------------------------

require '../vendor/autoload.php';

//---------------------------------------

$cacheDir     = __DIR__ . '/cache/';
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, true, 755);
}

//---------------------------------------

$cache          = new Cache($cacheDir);

//---------------------------------------

$musicBrainzApi = new ApiMusicBrainz([], $cache);
$musicBrainz    = new SourceMusicBrainz($musicBrainzApi);

//---------------------------------------

$sources = [
    $musicBrainz->getId() => $musicBrainz
];

function pagination(SearchResults $results) : string
{
    $string =
    '<div class="pagination">' .
        "Displaying {$results->count} results of {$results->total}, page {$results->page} of {$results->pages}
        <ul>";

    $query = $_GET;
    unset($query['page']);
    $query = http_build_query($query);
    $max = 12;

    $max = $results->pages < $max ? $results->pages : $max;

    $start = $results->page - round($max / 2);
    $start = $start <= 0 ? 1 : $start;
    $end   = $start + $max;
    $end   = $end > $results->pages ? $results->pages : $end;

    for ($p = $start; $p < $end; $p++) {
        $string .=
        '<a href="?' . $query . '&page=' . $p . '" class="' . ( $results->page == $p ? 'current' : '')  . '">' . $p . '</a>';
    }

    $string .=
        '</ul>
    </div>';

    return $string;
}


echo '<link href="stylesheet.css" rel="stylesheet">';
