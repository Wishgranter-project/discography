<?php

namespace WishgranterProject\Discography;

class Artist
{
    /**
     * @var string
     *   The source that originated this object.
     *   See WishgranterProject\Discography\Source\SourceInterface::getId().
     */
    protected string $source;

    /**
     * @var string
     *   Unique identifier withing the source.
     */
    protected string $id;

    /**
     * @var string
     *   The name of the artist or band.
     */
    protected string $name;

    /**
     * @var string
     *   An URL to a thumbnail picture.
     */
    protected string $thumbnail;

    /**
     * @param string $source
     *   The source that originated this object.
     *   See WishgranterProject\Discography\Source\SourceInterface::getId().
     * @param string $id
     *   Unique identifier withing the source.
     * @param string $name
     *   The name of the artist or band.
     * @param string $thumbnail
     *   An URL to a thumbnail picture.
     */
    public function __construct(
        string $source,
        string $id,
        string $name,
        string $thumbnail = ''
    ) {
        $this->source    = $source;
        $this->id        = $id;
        $this->name      = $name;
        $this->thumbnail = $thumbnail;
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
     * Casts down the Artist object into a relational array.
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

        if (!empty($this->name)) {
            $array['name'] = $this->name;
        }

        if (!empty($this->thumbnail)) {
            $array['thumbnail'] = $this->thumbnail;
        }

        return $array;
    }

    /**
     * Creates a new ofject out of an associative array.
     *
     * @param string[] $array
     *   Associative array.
     *
     * @return WishgranterProject\Discography\Artist
     *   The resulting object.
     */
    public static function createFromArray(array $array): Artist
    {
        return new self(
            !empty($array['source'])    ? $array['source']    : '',
            !empty($array['id'])        ? $array['id']        : '',
            !empty($array['name'])      ? $array['name']      : '',
            !empty($array['thumbnail']) ? $array['thumbnail'] : ''
        );
    }
}
