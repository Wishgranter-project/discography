<?php 
namespace AdinanCenci\Discography;

class Album 
{
    /**
     * @var string
     *   The source of discography that originated this object.
     *   See AdinanCenci\Discography\Source\SourceInterface::getId.
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
    protected array $tracks;

    public function __construct(
        string $source, 
        string $id, 
        string $title, 
        string $artist = '', 
        int $year = 0, 
        string $thumbnail = '', 
        array $tracks = []
    ) 
    {
        $this->source    = $source;
        $this->id        = $id;
        $this->title     = $title;
        $this->artist    = $artist;
        $this->year      = (int) $year;
        $this->thumbnail = $thumbnail;
        $this->tracks    = $tracks;
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
     */
    public function toArray() : array
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

        return $array;
    }

    /**
     * @param string[] $array
     *
     * @return AdinanCenci\Discography\Album
     */
    public static function createFromArray(array $array) : Album
    {
        return new self(
            !empty($array['source'])    ? $array['source']    : '',
            !empty($array['id'])        ? $array['id']        : '',
            !empty($array['title'])     ? $array['title']     : '',
            !empty($array['artist'])    ? $array['artist']    : '',
            !empty($array['year'])      ? $array['year']      : 0,
            !empty($array['thumbnail']) ? $array['thumbnail'] : '',
            !empty($array['tracks'])    ? $array['tracks']    : []
        );
    }
}
