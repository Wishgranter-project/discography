<?php

namespace WishgranterProject\Discography\Discogs;

use WishgranterProject\Discography\Source\SourceInterface;
use WishgranterProject\Discography\Source\SourceBase;
use WishgranterProject\Discography\Artist;
use WishgranterProject\Discography\Album;

class SourceDiscogs extends SourceBase implements SourceInterface
{
    protected ApiDiscogs $api;

    protected string $id = 'discogs';

    public function __construct(ApiDiscogs $api)
    {
        $this->api = $api;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getAlbum(string $artistName, string $title): ?Album
    {
        $releases = $this->api->searchReleasesByTitleAndArtistsName($artistName, $title, $page = 1, $itensPerPage = 5);
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
            'year'      => isset($r->year) ? $r->year : null,
            'thumbnail' => isset($r->images) ? $r->images[0]->uri : null,
            'single'    => in_array('Single', $formats),
            'tracks'    => $tracks,
        ]);
    }

    /**
     * Gets the id of the artist that more closely matches $artistName
     *
     * @param string $artistName
     *
     * @return string|null The id
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
     * Compares what was typed to the search results.
     *
     * @param string $artistName
     * @param string $toCompare
     *
     * @return bool
     */
    protected function sameName(string $artistName, string $toCompare): bool
    {
        if ($artistName == $toCompare) {
            return true;
        }

        $artistNameNormalized = iconv('UTF-8', 'ASCII//TRANSLIT', $artistName);
        if ($artistNameNormalized == $artistName) {
            return false;
        } elseif ($artistNameNormalized == $toCompare) {
            return true;
        }

        $toCompareNormalized  = iconv('UTF-8', 'ASCII//TRANSLIT', $toCompare);
        if ($toCompareNormalized == $toCompare) {
            return false;
        } elseif ($artistName == $toCompareNormalized) {
            return true;
        }

        return false;
    }
}
