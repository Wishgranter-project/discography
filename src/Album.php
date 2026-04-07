<?php

namespace WishgranterProject\Discography;

class Album implements AlbumInterface
{
    /**
     * The source that originated this object.
     * See WishgranterProject\Discography\Source\SourceInterface::getId().
     *
     * @var string
     */
    protected string $source;

    /**
     * Unique identifier withing the source.
     *
     * @var string
     */
    protected string $id;

    /**
     * Title of the album.
     *
     * @var string
     */
    protected string $title;

    /**
     * The artist that released the album.
     *
     * @var string
     */
    protected string $artist;

    /**
     * The year it was released.
     *
     * @var int $year
     */
    protected int $year;

    /**
     * An absolute URL to a thumbnail picture.
     *
     * @var string
     */
    protected string $thumbnail;

    /**
     * The title of the songs in the album.
     *
     * @var string[]
     */
    protected array $tracks = [];

    /**
     * Traditional album or single.
     *
     * @var bool
     */
    protected bool $single = false;

    /**
     * Metadata about the album. Implementation specific.
     *
     * @var array
     */
    protected array $metadata;

    /**
     * Constructor.
     *
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
     * @param array $metadata
     *   Metadata about the album. Implementation specific.
     */
    public function __construct(
        string $source,
        string $id,
        string $title,
        string $artist = '',
        int $year = 0,
        string $thumbnail = '',
        array $tracks = [],
        bool $single = false,
        array $metadata = [],
    ) {
        $this->source    = $source;
        $this->id        = $id;
        $this->title     = $title;
        $this->artist    = $artist;
        $this->year      = (int) $year;
        $this->thumbnail = $thumbnail;
        $this->tracks    = $tracks;
        $this->single    = $single;
        $this->metadata  = $metadata;
    }

    public function __get($var)
    {
        return !empty($this->{$var})
            ? $this->{$var}
            : null;
    }

    public function __isset($var)
    {
        return !empty($this->{$var});
    }

    /**
     * {@inheritdoc}
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

        if (!empty($this->metadata)) {
            $array['metadata'] = $this->metadata;
        }

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromArray(array $array): AlbumInterface
    {
        return new self(
            !empty($array['source'])    ? $array['source']    : '',
            !empty($array['id'])        ? $array['id']        : '',
            !empty($array['title'])     ? $array['title']     : '',
            !empty($array['artist'])    ? $array['artist']    : '',
            !empty($array['year'])      ? $array['year']      : 0,
            !empty($array['thumbnail']) ? $array['thumbnail'] : '',
            !empty($array['tracks'])    ? $array['tracks']    : [],
            !empty($array['single'])    ? $array['single']    : false,
            !empty($array['metadata'])  ? $array['metadata']  : [],
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
