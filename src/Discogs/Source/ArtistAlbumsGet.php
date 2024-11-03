<?php

namespace WishgranterProject\Discography\Discogs\Source;

use WishgranterProject\Discography\Discogs\ApiDiscogs;
use WishgranterProject\Discography\Source\SourceInterface;
use WishgranterProject\Discography\Source\SourceBase;
use WishgranterProject\Discography\Artist;
use WishgranterProject\Discography\Album;

/**
 * Returns albuns by artist id.
 */
class ArtistAlbumsGet
{
    /**
     * @var WishgranterProject\Discography\Discogs\Source\SourceDiscogs
     *   The main class.
     *   We will only use it to instantiate album objects.
     */
    protected SourceDiscogs $source;

    /**
     * @var WishgranterProject\Discography\Discogs\ApiDiscogs
     *   The discogs api.
     */
    protected ApiDiscogs $api;

    /**
     * @var string
     *   The id of the artist within Discogs.
     */
    protected string $artistId;

    /**
     * @var string
     *   The name of the artist/band.]
     *   We will only use it to instantiate album objects.
     */
    protected string $artistName;

    /**
     * @var string[]
     *   Array of titles from processed releases.
     *   We keep track to avoid duplicated results.
     */
    protected array $titles = [];

    /**
     * @var string[]
     *   Array of master ids from processed releases.
     *   We keep track to avoid duplicated results.
     */
    protected array $masterIds = [];

    /**
     * @param WishgranterProject\Discography\Discogs\Source\SourceDiscogs $source
     *   The main class.
     *   We will only use it to instantiate album objects.
     * @param WishgranterProject\Discography\Discogs\ApiDiscogs $api
     *   The discogs api.
     * @param string $artistId
     *   The id of the artist within Discogs.
     * @param string $artistName
     *   The name of the artist/band.
     *   We will only use it to instantiate album objects.
     */
    public function __construct(SourceDiscogs $source, ApiDiscogs $api, string $artistId, string $artistName)
    {
        $this->source     = $source;
        $this->api        = $api;
        $this->artistId   = $artistId;
        $this->artistName = $artistName;
    }

    /**
     * Retrieve albums.
     */
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

    /**
     * Retrieves the specified page of results.
     *
     * @param int $page
     *   The page of results.
     *
     * @return \stdClass
     *   Data from the API.
     */
    protected function getPage(int $page): \stdClass
    {
        return $this->api->getReleasesByArtistId($this->artistId, $page);
    }

    /**
     * Filters out garbage.
     *
     * @param \stdClass[] $releases
     *   Release data from the API.
     *
     * @return \stdClass[]
     *   Filtered array.
     */
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
     * Filtes based on the artist/band name.
     *
     * @param \stdClass $release
     *   Release.
     *
     * @return bool
     *   If the $release passed or not the filter.
     */
    protected function filterOutByArtistName(\stdClass $release): bool
    {
        // No compilations.
        if ($release->artist == 'Various' && $this->artistName == 'Various') {
            return true;
        }

        if ($release->artist == 'Various') {
            return false;
        }

        return true;
    }

    /**
     * Filtes based on the release title.
     *
     * @param \stdClass $release
     *   Release.
     *
     * @return bool
     *   If the $release passed or not the filter.
     */
    protected function filterOutByTitle(\stdClass $release): bool
    {
        $albumTitle = strtolower($release->albumTitle);

        // No, thank you.
        if (substr_count($albumTitle, 'remastered')) {
            return false;
        }

        // Absolutely not !!
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

    /**
     * Filtes based on the release format.
     *
     * @param \stdClass $release
     *   Release.
     *
     * @return bool
     *   If the $release passed or not the filter.
     */
    protected function filterOutByFormats(\stdClass $release): bool
    {
        if (!$release->albumFormats) {
            return true;
        }

        $undesirableFormats = [
            // 'Limited Edition', // this excluded really cool albums such as Foundations of Burden.
            // 'Reissue',         // this excluded pallbearer's demo album.
            'Remastered',         // Basic duplicated content
            'Unofficial Release', // waste of time
            'Compilation',        // I am searching for a specific artist's content...
            'Comp',               // Same as above
            'Smplr',
            'Transcription',
        ];

        return !array_intersect($undesirableFormats, $release->albumFormats);
    }

    /**
     * Filtes based on the role in the release.
     *
     * @param \stdClass $release
     *   Release.
     *
     * @return bool
     *   If the $release passed or not the filter.
     */
    protected function filterOutByRoles(\stdClass $release): bool
    {
        if (!$release->role) {
            return true;
        }

        // No compilations, we want the artist's work.
        $undesirableRoles = [
            'TrackAppearance',
            'UnofficialRelease',
            'Appearance',
        ];

        return !in_array($release->role, $undesirableRoles);
    }

    /**
     * Filtes out repeated titles.
     *
     * @param \stdClass $release
     *   Release.
     *
     * @return bool
     *   If the $release passed or not the filter.
     */
    protected function filterOutRepeatedTitles(\stdClass $release): bool
    {
        if (in_array($release->albumTitle, $this->titles)) {
            return false;
        }

        $this->titles[] = $release->albumTitle;
        return true;
    }

    /**
     * Filtes out repeated master ids.
     *
     * @param \stdClass $release
     *   Release.
     *
     * @return bool
     *   If the $release passed or not the filter.
     */
    protected function filterOutRepeatedMasterIds(\stdClass $release): bool
    {
        if ($release->master_id && in_array($release->master_id, $this->masterIds)) {
            return false;
        }

        if ($release->master_id) {
            $this->masterIds[] = $release->master_id;
        }

        return true;
    }

    /**
     * Just normalizing data for sanity sake.
     *
     * @param \stdClass[] $releases
     *   Array of releases.
     */
    protected function deformReleasesData(array $releases): void
    {
        foreach ($releases as $k => $r) {
            $r->albumTitle = $this->getAlbumTitle($r->title);
            $r->master_id  = $r->master_id ?? null;
            $r->artist     = $r->artist    ?? null;
            $r->role       = $r->role      ?? null;
            $r->year       = $r->year      ?? null;

            $formats = [];
            if (isset($r->format)) {
                $formats = $r->format;
            } elseif (isset($r->formats)) {
                $formats = $r->formats;
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
     * @param string $albumTitle
     *   The album title.
     *
     * @return string
     *   The trimmed title.
     */
    protected function getAlbumTitle(string $albumTitle): string
    {
        return trim(preg_replace('/^[^\-]+- ?/', '', $albumTitle));
    }
}
