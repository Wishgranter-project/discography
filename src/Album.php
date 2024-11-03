<?php

namespace WishgranterProject\Discography;

class Album
{
    /**
     * @var string
     *   The source that originated this object.
     *   See WishgranterProject\Discography\Source\SourceInterface::getId.
     */
    protected string $source;

    /**
     * @var string
     *   Unique identifier withing the source.
     */
    protected string $id;

    /**
     * @var string
     *   Title of the album.
     */
    protected string $title;

    /**
     * @var string
     *   The artist that released the album.
     */
    protected string $artist;

    /**
     * @var int $year
     *   The year it was released.
     */
    protected int $year;

    /**
     * @var string
     *   An absolute URL to a thumbnail picture.
     */
    protected string $thumbnail;

    /**
     * @var string[]
     *   The title of the songs in the album.
     */
    protected array $tracks = [];

    /**
     * @var bool
     *   Traditional album or single.
     */
    protected bool $single = false;

    /**
     * @param string $source
     *   The source that originated this object.
     * @param string $id
     *   Unique identifier withing the source.
     * @param string $title
     *   Title of the album.
     * @param string $artist
     *   The artist that released the album.
     * @param int $year $year
     *   The year it was released.
     * @param string $thumbnail
     *   An absolute URL to a thumbnail picture.
     * @param string[] $tracks
     *   The title of the songs in the album.
     * @var bool $single
     *   Traditional album or single.
     */
    public function __construct(
        string $source,
        string $id,
        string $title,
        string $artist = '',
        int $year = 0,
        string $thumbnail = '',
        array $tracks = [],
        bool $single = false
    ) {
        $this->source    = $source;
        $this->id        = $id;
        $this->title     = $title;
        $this->artist    = $artist;
        $this->year      = (int) $year;
        $this->thumbnail = $thumbnail;
        $this->tracks    = $tracks;
        $this->single    = $single;
    }

    public function __get($var)
    {
        if ($var == 'tracks') {
            return $this->tracks;
        }

        return !empty($this->{$var})
            ? $this->{$var}
            : null;
    }

    public function __isset($var)
    {
        return !empty($this->{$var});
    }

    /**
     * Casts down the Album object into a relational array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [];

        if (!empty($this->source)) {
            $array['source'] = $this->source;
        }

        if (!empty($this->id)) {
            $array['id'] = $this->id;
        }

        if (!empty($this->title)) {
            $array['title'] = $this->title;
        }

        if (!empty($this->artist)) {
            $array['artist'] = $this->artist;
        }

        if (!empty($this->year)) {
            $array['year'] = $this->year;
        }

        if (!empty($this->thumbnail)) {
            $array['thumbnail'] = $this->thumbnail;
        }

        if (!empty($this->tracks)) {
            $array['tracks'] = $this->tracks;
        }

        $array['single'] = $this->single;

        return $array;
    }

    /**
     * Creates a new ofject out of an associative array.
     *
     * @param string[] $array
     *   Associative array.
     *
     * @return WishgranterProject\Discography\Album
     *   The resulting object.
     */
    public static function createFromArray(array $array): Album
    {
        return new self(
            !empty($array['source'])    ? $array['source']    : '',
            !empty($array['id'])        ? $array['id']        : '',
            !empty($array['title'])     ? $array['title']     : '',
            !empty($array['artist'])    ? $array['artist']    : '',
            !empty($array['year'])      ? $array['year']      : 0,
            !empty($array['thumbnail']) ? $array['thumbnail'] : '',
            !empty($array['tracks'])    ? $array['tracks']    : [],
            !empty($array['single'])    ? $array['single']    : false
        );
    }

    /**
     * Custom method to order albums.
     *
     * Puts single at the end and orders everything alphabetically.
     *
     * @param WishgranterProject\Discography\Album[] $albums
     *   Array of albums.
     */
    public static function sortAlbums(array &$albums): void
    {
        usort($albums, function ($a, $b) {
            $aFirst = -1;
            $bFirst = 1;

            if ($a->single && !$b->single) {
                return $bFirst;
            } elseif (!$a->single && $b->single) {
                return $aFirst;
            }

            return strcmp($a->title, $b->title) <= -1
                ? $aFirst
                : $bFirst;
        });
    }
}
