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
     * Search for artists by their name.
     *
     * @param string $artistName
     *
     * @return [WishgranterProject\Discography\Artist]
     */
    public function searchForArtist(string $artistName): array;

    /**
     * Return the albums for a given artist.
     *
     * It must exclude compilations, colaborations etc.
     * Only albuns and singles from the specified artist.
     *
     * @param string $artistName
     *
     * @return Album[]
     */
    public function getArtistsAlbums(string $artistName): array;

    /**
     * Return the specified album.
     *
     * The album bust include tracks ( unless it is a single, ofcourse ).
     *
     * @param string $artistName
     * @param string $title
     *
     * @return null|Album
     */
    public function getAlbum(string $artistName, string $title): ?Album;
}
