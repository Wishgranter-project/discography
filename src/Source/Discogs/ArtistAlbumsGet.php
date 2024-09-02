<?php

namespace WishgranterProject\Discography\Source\Discogs;

use WishgranterProject\Discography\Api\ApiDiscogs;
use WishgranterProject\Discography\Source\SourceInterface;
use WishgranterProject\Discography\Source\SourceBase;
use WishgranterProject\Discography\Artist;
use WishgranterProject\Discography\Album;

/**
 * Returns albuns by artist id.
 */
class ArtistAlbumsGet
{
    protected ApiDiscogs $api;

    protected SourceDiscogs $source;

    protected string $artistId;

    protected string $artistName;

    protected array $titles = [];

    protected array $masterIds = [];

    public function __construct(SourceDiscogs $source, ApiDiscogs $api, string $artistId, string $artistName)
    {
        $this->source     = $source;
        $this->api        = $api;
        $this->artistId   = $artistId;
        $this->artistName = $artistName;
    }

    protected function getPage(int $page): \stdClass
    {
        return $this->api->getReleasesByArtistId($this->artistId, $page);
    }

    public function getAlbums(): array
    {
        $albums             = [];
        $page               = 1;
        $pageLimit          = 2;

        do {
            $info = $this->getPage($page);

            $releases = isset($info->releases)
                ? $info->releases
                : $info->results;

            $initialCount = count($releases);

            $this->deformReleasesData($releases);
            $releases = $this->filterReleasesData($releases);

            foreach ($releases as $k => $r) {
                $albums[] = Album::createFromArray([
                    'source'    => $this->source->getId(),
                    'id'        => $r->id,
                    'title'     => $r->albumTitle,
                    'artist'    => $this->artistName,
                    'year'      => $r->year,
                    'thumbnail' => $r->thumb,
                    'single'    => in_array('Single', $r->albumFormats),
                ]);
            }

            $page++;
        } while (
            $initialCount &&
            $info->pagination &&
            $info->pagination->page < $info->pagination->pages &&
            $info->pagination->page < $pageLimit
        );

        $albums = array_values($albums);
        Album::sortAlbums($albums);

        return $albums;
    }

    protected function filterReleasesData(array $releases): array
    {
        $releases = array_filter($releases, [$this, 'filterOutByArtistName']);
        $releases = array_filter($releases, [$this, 'filterOutByTitle']);
        $releases = array_filter($releases, [$this, 'filterOutByFormats']);
        $releases = array_filter($releases, [$this, 'filterOutByRoles']);
        $releases = array_filter($releases, [$this, 'filterOutRepeatedTitles']);
        $releases = array_filter($releases, [$this, 'filterOutRepeatedMasterIds']);

        return $releases;
    }

    /**
     * 
     */
    protected function filterOutByArtistName(\stdClass $r): bool
    {
        // No compilations.
        if ($r->artist == 'Various' && $this->artistName == 'Various') {
            return true;
        } elseif ($r->artist == 'Various') {
            return false;
        }

        return true;
    }

    protected function filterOutByTitle(\stdClass $r): bool
    {
        $albumTitle = strtolower($r->albumTitle);

        // No, thank you.
        if (substr_count($albumTitle, 'remastered')) {
            return false;
        }

        // Absolutely not.
        if (substr_count($albumTitle, '(live)')) {
            return false;
        }

        $live_word_count  = substr_count($albumTitle, 'live');

        if (!$live_word_count) {
            return true;
        }

        // Exceptions.
        $lives = preg_match('/lives$/', $albumTitle);
        $alive = preg_match('/alive$/', $albumTitle);

        return $lives || $alive;
    }

    protected function filterOutByFormats(\stdClass $r): bool
    {
        if (!$r->albumFormats) {
            return true;
        }

        $undesirableFormats = [
            // 'Limited Edition', // this excluded really cool albums such as Foundations of Burden.
            // 'Reissue', // this excluded pallbearer's demo album.
            'Remastered',
            'Unofficial Release',
            'Compilation',
            'Comp',
            'Smplr',
            'Transcription',
        ];

        return !array_intersect($undesirableFormats, $r->albumFormats);
    }

    protected function filterOutByRoles(\stdClass $r): bool
    {
        if (!$r->role) {
            return true;
        }

        // No compilations, we want the artist's work.
        $undesirableRoles = [
            'TrackAppearance',
            'UnofficialRelease',
            'Appearance',
        ];

        return !in_array($r->role, $undesirableRoles);
    }

    protected function filterOutRepeatedTitles(\stdClass $r): bool
    {
        if (in_array($r->albumTitle, $this->titles)) {
            return false;
        }

        $this->titles[] = $r->albumTitle;
        return true;
    }

    protected function filterOutRepeatedMasterIds(\stdClass $r): bool
    {
        if ($r->master_id && in_array($r->master_id, $this->masterIds)) {
            return false;
        }

        if ($r->master_id) {
            $this->masterIds[] = $r->master_id;
        }

        return true;
    }

    /**
     * Just normalizing data for sanity sake.
     */
    protected function deformReleasesData(array $releases): void
    {
        foreach ($releases as $k => $r) {
            $r->albumTitle = $this->getAlbumTitle($r->title);
            $r->master_id  = isset($r->master_id) ? $r->master_id : null;
            $r->artist     = isset($r->artist)    ? $r->artist    : null;
            $r->role       = isset($r->role)      ? $r->role      : null;
            $r->year       = isset($r->year)      ? $r->year      : null;

            if (isset($r->format)) {
                $formats = $r->format;
            } elseif (isset($r->formats)) {
                $formats = $r->formats;
            } else {
                $formats = [];
            }

            if (is_string($formats)) {
                $formats = preg_split('/, ?/', $formats);
            } elseif (is_array($formats) && $formats && is_object($formats[0])) {
                foreach ($formats as $k => $f) {
                    $formats[$k] = $f->name;
                }
            }

            $r->albumFormats = $formats;
        }
    }

    /**
     * Discogs prefixes the album's title with the artist's name, let's remove it.
     *
     * @param string $albumTitle The album title.
     *
     * @return string The trimmed title.
     */
    protected function getAlbumTitle(string $albumTitle): string
    {
        return trim(preg_replace('/^[^\-]+- ?/', '', $albumTitle));
    }
}
