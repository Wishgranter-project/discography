<?php

namespace WishgranterProject\Discography;

class Artist implements ArtistInterface
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
     * The name of the artist or band.
     *
     * @var string
     */
    protected string $name;

    /**
     * An URL to a thumbnail picture.
     *
     * @var string
     */
    protected string $thumbnail;

    /**
     * @var array
     *
     * Metadata about the artist. Implementation specific.
     */
    protected array $metadata;

    /**
     * Constructor.
     *
     * @param string $source
     *   The source that originated this object.
     *   See WishgranterProject\Discography\Source\SourceInterface::getId().
     * @param string $id
     *   Unique identifier withing the source.
     * @param string $name
     *   The name of the artist or band.
     * @param string $thumbnail
     *   An URL to a thumbnail picture.
     * @param array $metadata
     *   Metadata about the artist. Implementation specific.
     */
    public function __construct(
        string $source,
        string $id,
        string $name,
        string $thumbnail = '',
        array $metadata = [],
    ) {
        $this->source    = $source;
        $this->id        = $id;
        $this->name      = $name;
        $this->thumbnail = $thumbnail;
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

        if (!empty($this->name)) {
            $array['name'] = $this->name;
        }

        if (!empty($this->thumbnail)) {
            $array['thumbnail'] = $this->thumbnail;
        }

        if (!empty($this->metadata)) {
            $array['metadata'] = $this->metadata;
        }

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromArray(array $array): ArtistInterface
    {
        return new self(
            !empty($array['source'])    ? $array['source']    : '',
            !empty($array['id'])        ? $array['id']        : '',
            !empty($array['name'])      ? $array['name']      : '',
            !empty($array['thumbnail']) ? $array['thumbnail'] : '',
            !empty($array['metadata'])  ? $array['metadata']     : [],
        );
    }
}
