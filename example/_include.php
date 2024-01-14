<?php 
use AdinanCenci\Discography\Api\DiscogsApi;
use AdinanCenci\Discography\Api\LastFmApi;
use AdinanCenci\Discography\Source\SourceLastFm;
use AdinanCenci\Discography\Source\SourceDiscogs;
use AdinanCenci\Discography\Source\SearchResults;
use AdinanCenci\Discography\Artist;
use AdinanCenci\Discography\Release;
use AdinanCenci\FileCache\Cache;

//---------------------------------------

require '../vendor/autoload.php';

//---------------------------------------

$cacheDir     = __DIR__ . '/cache/';
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, true, 755);
}

//---------------------------------------

$cache        = new Cache($cacheDir);

//---------------------------------------

$lastFmApiKey = file_exists('insert-yout-lastfm-apikey-here.txt')
                ? file_get_contents('insert-yout-lastfm-apikey-here.txt')
                : '';
$lastFmApi    = new LastFmApi($lastFmApiKey, [], $cache);
$lastFm       = new SourceLastFm($lastFmApi);

//---------------------------------------

$discogsToken = file_exists('insert-yout-discogs-token-here.txt')
                ? file_get_contents('insert-yout-discogs-token-here.txt')
                : '';
$discogsApi   = new DiscogsApi($discogsToken, [], $cache);
$discogs      = new SourceDiscogs($discogsApi);

//---------------------------------------

$sources = [
    $lastFm->getId()  => $lastFm,
    $discogs->getId() => $discogs
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
