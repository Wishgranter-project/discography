<?php

namespace WishgranterProject\Discography\Discogs\Source;

use WishgranterProject\Discography\Discogs\ApiDiscogs;
use WishgranterProject\Discography\Source\SourceInterface;
use WishgranterProject\Discography\Source\SourceBase;
use WishgranterProject\Discography\Artist;
use WishgranterProject\Discography\Album;

class SourceDiscogs extends SourceBase implements SourceInterface
{
    /**
     * @var WishgranterProject\Discography\Discogs\ApiDiscogs
     *   The discogs api.
     */
    protected ApiDiscogs $api;

    /**
     * @var string
     *   This source's id.
     */
    protected string $id = 'discogs';

    /**
     * @param WishgranterProject\Discography\Discogs\ApiDiscogs $api
     *   The discogs api.
     */
    public function __construct(ApiDiscogs $api)
    {
        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     */
    public function searchForArtist(string $artistName): array
    {
        $info = $this->api->searchForArtistByName($artistName, 1, 25);

        $items = [];
        foreach ($info->results as $a) {
            $items[] = Artist::createFromArray([
                'source'    => $this->getId(),
                'id'        => $a->id,
                'name'      => $a->title,
                'thumbnail' => $a->thumb,
            ]);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function getArtistsAlbums(string $artistName): array
    {
        $artistId = $this->getArtistIdByName($artistName);

        if (!$artistId) {
            return [];
        }

        return $this->getArtistsAlbumsById($artistId, $artistName);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlbum(string $artistName, string $title): ?Album
    {
        $releases = $this->api->searchReleasesByTitleAndArtistsName($artistName, $title, $page = 1, $itemsPerPage = 5);
        if (!$releases->results) {
            return null;
        }

        $first    = $releases->results[0];
        $masterId = $first->master_id ?: null;
        $id       = $first->id ?: null;

        $r = $masterId
            ? $this->api->getMasterRelease($masterId)
            : $this->api->getRelease($id);

        $formats = isset($r->format)
            ? preg_split('/, ?/', $r->format)
            : [];

        $tracks = [];
        foreach ($r->tracklist as $t) {
            $tracks[] = $t->title;
        }

        return Album::createFromArray([
            'source'    => $this->getId(),
            'id'        => $r->id,
            'title'     => $r->title,
            'artist'    => $artistName,
            'year'      => $r->year ?? null,
            'thumbnail' => isset($r->images) ? $r->images[0]->uri : null,
            'single'    => in_array('Single', $formats),
            'tracks'    => $tracks,
        ]);
    }

    /**
     * Retrieves the id of the artist/band that more closely matches $artistName.
     *
     * @param string $artistName
     *   The name of the artist/band.
     *
     * @return string|null
     *   The id.
     */
    protected function getArtistIdByName(string $artistName): ?string
    {
        $results = $this->searchForArtist($artistName);
        if (!$results) {
            return null;
        }

        foreach ($results as $artist) {
            if ($this->sameName($artist->name, $artistName)) {
                return $artist->id;
            }
        }

        // Settles for the first result.
        return $results[0]->id;
    }

    /**
     * @todo Will need to abstract this into classes.
     */
    protected function getArtistsAlbumsById(string $artistId, string $artistName): array
    {
        $getter = new ArtistAlbumsSearch($this, $this->api, $artistId, $artistName);
        $albums = $getter->getAlbums();

        if ($albums) {
            return $albums;
        }

        $getter = new ArtistAlbumsGet($this, $this->api, $artistId, $artistName);
        return $getter->getAlbums();
    }

    /**
     * Compares the name of the search term with the search results.
     *
     * @param string $artistName
     *   Name from the search results.
     * @param string $toCompare
     *   Name we are searching for.
     *
     * @return bool
     *   If it matches or not.
     */
    protected function sameName(string $artistName, string $toCompare): bool
    {
        if ($artistName == $toCompare) {
            return true;
        }

        $artistNameNormalized = iconv('UTF-8', 'ASCII//TRANSLIT', $artistName);
        if ($artistNameNormalized == $artistName) {
            return false;
        }

        if ($artistNameNormalized == $toCompare) {
            return true;
        }

        $toCompareNormalized  = iconv('UTF-8', 'ASCII//TRANSLIT', $toCompare);
        if ($toCompareNormalized == $toCompare) {
            return false;
        }

        if ($artistName == $toCompareNormalized) {
            return true;
        }

        return false;
    }
}
