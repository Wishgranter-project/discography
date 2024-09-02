<?php

namespace WishgranterProject\Discography\Source\MusicBrainz;

use WishgranterProject\Discography\Api\ApiMusicBrainz;
use WishgranterProject\Discography\Source\SourceInterface;
use WishgranterProject\Discography\Source\SourceBase;
use WishgranterProject\Discography\Artist;
use WishgranterProject\Discography\Album;

class SourceMusicBrainz extends SourceBase implements SourceInterface
{
    protected ApiMusicBrainz $api;

    protected string $id = 'music_brainz';

    public function __construct(ApiMusicBrainz $api)
    {
        $this->api = $api;
    }

    /**
     * @inheritDoc
     */
    public function searchForArtist(string $artistName): array
    {
        $info = $this->api->searchForArtistByName($artistName, 0, 25);

        $artists = [];

        foreach ($info->artists as $a) {
            $artists[] = Artist::createFromArray([
                'source'    => $this->getId(),
                'id'        => $a->id,
                'name'      => $a->name,
                'thumbnail' => null,
            ]);
        }

        return $artists;
    }

    /**
     * @inheritDoc
     */
    public function getArtistsAlbums(string $artistName): array
    {
        $albums = [];
        $releaseGroups = $this->api->getAllArtistsReleaseGroups($artistName);
        $titles = [];

        foreach ($releaseGroups as $g) {
            if (in_array($g->title, $titles)) {
                continue;
            }

            if (count($g->{'artist-credit'}) > 1) {
                continue;
            }

            $albums[] = Album::createFromArray([
                'source'    => $this->getId(),
                'id'        => $g->id,
                'title'     => $g->title,
                'artist'    => $g?->{'artist-credit'}[0]?->name ?? '',
                'thumbnail' => "http://coverartarchive.org/release-group/{$g->id}/front-250.jpg",
                'year'      => $g->{'first-release-data'} ?? 0,
                'single'    => $g->{'primary-type'} == 'Single'
            ]);

            $titles[] = $g->title;
        }

        Album::sortAlbums($albums);

        return $albums;
    }

    /**
     * @inheritDoc
     */
    public function getAlbum(string $artistName, string $title): ?Album
    {
        $releases = $this->api->searchReleasesByArtistNameAndTitle($artistName, $title, 0, 1);

        if (empty($releases->releases)) {
            return null;
        }

        $firstResult = $releases->releases[0];

        $details = $this->api->getReleaseById($firstResult->id);
        $tracks = [];

        foreach ($details->media[0]->tracks as $t) {
            $tracks[] = $t->title;
        }

        return Album::createFromArray([
            'source'    => $this->getId(),
            'id'        => $firstResult->id, // release id, not the release group
            'title'     => $firstResult->title ?? '',
            'year'      => isset($firstResult->date) ? $this->getYear($firstResult->date) : 0,
            'artist'    => $artistName,
         // 'thumbnail' => isset($data->images[0]) ? $data->images[0]->uri : null,
            'tracks'    => $tracks
        ]);
    }

    protected function getYear(string $date): int
    {
        $unixTimeStamp = strtotime($date);
        return (int) date('Y');
    }
}
