<?php 
namespace AdinanCenci\Discography;

class Artist 
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
     *   The artist's/band's name.
     */
    protected string $name;

    /**
     * @var string
     *   An URL to a thumbnail picture.
     */
    protected string $thumbnail;

    /**
     * @param string $source
     * @param string $id
     * @param string $name
     * @param string $thumbnail
     */
    public function __construct(
        string $source, 
        string $id, 
        string $name, 
        string $thumbnail = ''
    ) 
    {
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

    public function toArray() : array
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
     * @param string[] $array
     *
     * @return AdinanCenci\Discography\Artist
     */
    public static function createFromArray(array $array) : Artist
    {
        return new self(
            !empty($array['source'])    ? $array['source']    : '',
            !empty($array['id'])        ? $array['id']        : '',
            !empty($array['name'])      ? $array['name']      : '',
            !empty($array['thumbnail']) ? $array['thumbnail'] : ''
        );
    }
}
