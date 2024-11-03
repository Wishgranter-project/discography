<?php

namespace WishgranterProject\Discography\Source;

use WishgranterProject\Discography\Artist;
use WishgranterProject\Discography\Album;
use WishgranterProject\Discography\Helper\SearchResults;

interface SourceInterface
{
    /**
     * A unique string identifying this source.
     */
    public function getId(): string;

    /**
     * Search for artists/bands by their name.
     *
     * @param string $artistName
     *   The name of the artist/band.
     *
     * @return [WishgranterProject\Discography\Artist]
     *   An array of with matching artists.
     */
    public function searchForArtist(string $artistName): array;

    /**
     * Return the albums for a given artist.
     *
     * It must exclude compilations, colaborations etc.
     * Only albuns and singles from the specified artist.
     *
     * @param string $artistName
     *   The name of the artist/band.
     *
     * @return [WishgranterProject\Discography\Album]
     *   An array with matching albums.
     */
    public function getArtistsAlbums(string $artistName): array;

    /**
     * Return the specified album.
     *
     * The album must include tracks ( unless it is a single, ofcourse ).
     *
     * @param string $artistName
     *   The name of the artist/band.
     * @param string $title
     *   The title of the album/single.
     *
     * @return null|Album
     *   The matching album/single.
     */
    public function getAlbum(string $artistName, string $title): ?Album;
}
