<?php

use WishgranterProject\Discography\Discogs\ApiDiscogs;
use WishgranterProject\Discography\Discogs\SourceDiscogs;
use WishgranterProject\Discography\MusicBrainz\ApiMusicBrainz;
use WishgranterProject\Discography\MusicBrainz\SourceMusicBrainz;
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

$discogsApi     = new ApiDiscogs('insert-your-token-here', [], $cache);
$discogs        = new SourceDiscogs($discogsApi);

//---------------------------------------

$musicBrainzApi = new ApiMusicBrainz([], $cache);
$musicBrainz    = new SourceMusicBrainz($musicBrainzApi);

//---------------------------------------

$sources = [
    $discogs->getId()     => $discogs,
    $musicBrainz->getId() => $musicBrainz,
];

function switchSourceLinks(string $notIt): string
{
    $parts = [];
    foreach ($GLOBALS['sources'] as $id => $source) {
        if ($id == $notIt) {
            continue;
        }
        $parts[] = switchSourceLink($id);
    }

    return implode(' / ', $parts);
}

function switchSourceLink(string $sourceId)
{
    $vars = $_GET;
    $vars['source'] = $sourceId;
    $query = http_build_query($vars);

    return 
    '<a href="?' . $query . '" title="see results from ' . $sourceId . ' ">' . $sourceId . '</a>';
}
