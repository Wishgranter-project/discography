<?php

namespace WishgranterProject\Discography;

/**
 * @property-read string $source
 *   The source that originated this object.
 *   See WishgranterProject\Discography\Source\SourceInterface::getId().
 * @property-read string $id
 *   Unique identifier withing the source.
 * @property-read string $title
 *   Title of the album.
 * @property-read string $artist
 *   The artist that released the album.
 * @property-read int $year
 *   The year it was released.
 * @property-read string $thumbnail
 *   An absolute URL to a thumbnail picture.
 * @property-read string[] $tracks
 *   The title of the songs in the album.
 * @property-read bool $single
 *   Traditional album or single.
 * @property-read array $metadata
 *   Metadata about the album. Implementation specific.
 */
interface AlbumInterface
{
    /**
     * Casts down the Album object into a relational array.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Instantiates a new object out of an associative array.
     *
     * @param string[] $array
     *   Associative array.
     *
     * @return WishgranterProject\Discography\AlbumInterface
     *   The resulting object.
     */
    public static function createFromArray(array $array): AlbumInterface;
}
