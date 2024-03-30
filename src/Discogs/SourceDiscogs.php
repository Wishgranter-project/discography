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

    protected function getArtistsAlbumsById(string $artistId, string $artistName): array
    {
        $albums             = [];
        $masterIds          = [];
        $titles             = [];
        $page               = 1;
        $undesirableFormats = $this->getUndesirableFormats();
        $undesirableRoles   = $this->getUndesirableRoles();

        do {
            $info = $this->api->getReleasesByArtistId($artistId, $page);

            foreach ($info->releases as $r) {
                $masterId = isset($r->master_id)
                    ? $r->master_id
                    : null;

                $artist = isset($r->artist)
                    ? $r->artist
                    : '';

                $formats = isset($r->format)
                    ? preg_split('/, ?/', $r->format)
                    : [];

                $title = $this->getAlbumTitle($r->title);

                //-------------

                if ($artist == 'Various' && $artistName != 'Various') {
                    continue;
                }

                if (substr_count(strtolower($title), 'remastered')) {
                    continue;
                }

                // Avoid duplicated results.
                if (in_array($title, $titles)) {
                    continue;
                }

                $titles[] = $title;

                if ($formats && array_intersect($undesirableFormats, $formats)) {
                    continue;
                }

                if (isset($r->role) && in_array($r->role, $undesirableRoles)) {
                    continue;
                }

                // Avoid duplicated results.
                if ($masterId && in_array($masterId, $masterIds)) {
                    continue;
                } elseif ($masterId) {
                    $masterIds[] = $masterId;
                }

                $albums[] = Album::createFromArray([
                    'source'    => $this->getId(),
                    'id'        => $r->id,
                    'title'     => $title,
                    'artist'    => $artistName,
                    'year'      => isset($r->year) ? $r->year : null,
                    'thumbnail' => $r->thumb,
                    'single'    => in_array('Single', $formats),
                ]);
            }

            $page++;
        } while (
            $info->pagination &&
            $info->pagination->page < $info->pagination->pages
        );

        // Remove duplicated results.
        foreach ($albums as $k => $a1) {
            foreach ($albums as $k2 => $a2) {
                if ($a1->title == $a2->title) {
                    continue;
                }

                if (substr_count($a1->title, $a2->title)) {
                    unset($albums[$k]);
                    break;
                }
            }
        }

        $albums = array_values($albums);
        Album::sortAlbums($albums);

        return $albums;
    }

    /**
     * Return a list of roles we want to avoid in search results.
     *
     * @return [string]
     */
    protected function getUndesirableRoles(): array
    {
        return [
            'TrackAppearance',
            'UnofficialRelease',
            'Appearance',
        ];
    }

    /**
     * Return a list of release formats we want to avoid in search results.
     *
     * @return [string]
     */
    protected function getUndesirableFormats(): array
    {
        return [
            'Limited Edition',
            'Reissue',
            'Remastered',
            'Unofficial Release',
            'Compilation',
            'Comp',
            'Smplr',
            'Transcription',
        ];
    }

    /**
     * Removes the artist's name prefix from the album's title.
     *
     * @param string $title The album title.
     *
     * @return string The trimmed title.
     */
    protected function getAlbumTitle(string $title): string
    {
        return trim(preg_replace('/^[^\-]+- ?/', '', $title));
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
