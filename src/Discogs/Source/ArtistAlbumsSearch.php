<?php

namespace WishgranterProject\Discography\Discogs\Source;

class ArtistAlbumsSearch extends ArtistAlbumsGet
{
    /**
     * {@inheritdoc}
     */
    protected function getPage(int $page): \stdClass
    {
        return $this->api->searchReleasesByArtistsId($this->artistId, $page);
    }

    /**
     * {@inheritdoc}
     */
    protected function filterReleasesData(array $releases): array
    {
        $releases = parent::filterReleasesData($releases);
        $releases = array_filter($releases, [$this, 'filterOutByTitlesMissingArtistName']);
        return $releases;
    }

    /**
     * Filtes based on the lack of the artist/band name.
     *
     * @param \stdClass $release
     *   Release.
     *
     * @return bool
     *   If the $release passed or not the filter.
     */
    protected function filterOutByTitlesMissingArtistName(\stdClass $release): bool
    {
        return (bool) substr_count($release->title, $this->artistName);
    }
}
