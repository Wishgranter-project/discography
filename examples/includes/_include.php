<?php

use WishgranterProject\Discography\Discogs\ApiDiscogs;
use WishgranterProject\Discography\Discogs\Source\SourceDiscogs;
use WishgranterProject\Discography\MusicBrainz\ApiMusicBrainz;
use WishgranterProject\Discography\MusicBrainz\Source\SourceMusicBrainz;
use WishgranterProject\Discography\Artist;
use WishgranterProject\Discography\Release;
use AdinanCenci\FileCache\Cache;

//---------------------------------------

require __DIR__ . '/../../vendor/autoload.php';

//---------------------------------------

$cacheDir     = __DIR__ . '/../cache/';
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, true, 755);
}

//---------------------------------------

$cache        = new Cache($cacheDir);

//---------------------------------------

$discogsToken = file_exists('insert-your-discogs-token-here.txt')
    ? file_get_contents('insert-your-discogs-token-here.txt')
    : 'insert-your-token-here';
$discogsApi   = new ApiDiscogs($discogsToken, [], $cache);
$discogs      = new SourceDiscogs($discogsApi);

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
    global $sources;

    $parts = [];
    foreach ($sources as $id => $source) {
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

function get(string $var, ?string $defaultValue = null): mixed
{
    return empty($_GET[$var])
        ? $defaultValue
        : $_GET[$var];
}

function getSourceId(): string
{
    return get('source', 'discogs');
}

function getSource(?string $sourceId = null)
{
    global $sources;

    return $sources[$sourceId ?? getSourceId()] ?? null;
}
