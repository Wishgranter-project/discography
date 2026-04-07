<?php

namespace WishgranterProject\Discography;

/**
 * @property-read array $source
 *   The source that originated this object.
 *   See WishgranterProject\Discography\Source\SourceInterface::getId().
 * @property-read array $id
 *   Unique identifier withing the source.
 * @property-read array $name
 *   The name of the artist or band.
 * @property-read array $thumbnail
 *   An URL to a thumbnail picture.
 * @property-read array $metadata
 *   Metadata about the artist. Implementation specific.
 */
interface ArtistInterface
{
    /**
     * Casts down the Artist object into a relational array.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Creates a new object out of an associative array.
     *
     * @param string[] $array
     *   Associative array.
     *
     * @return WishgranterProject\Discography\ArtistInterface
     *   The resulting object.
     */
    public static function createFromArray(array $array): ArtistInterface;
}
