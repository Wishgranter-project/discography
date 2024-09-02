<?php

namespace WishgranterProject\Discography\Source\Discogs;

class ArtistAlbumsSearch extends ArtistAlbumsGet
{
    protected function getPage(int $page): \stdClass
    {
        return $this->api->searchReleasesByArtistsId($this->artistId, $page);
    }

    protected function filterReleasesData(array $releases): array
    {
        $releases = parent::filterReleasesData($releases);
        $releases = array_filter($releases, [$this, 'filterOutByTitlesMissingArtistName']);
        return $releases;
    }

    protected function filterOutByTitlesMissingArtistName(\stdClass $r): bool
    {
        return (bool) substr_count($r->title, $this->artistName);
    }
}
