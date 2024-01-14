<?php 
namespace AdinanCenci\Discography\Source;

use AdinanCenci\Discography\Api\DiscogsApi;
use AdinanCenci\Discography\Artist;
use AdinanCenci\Discography\Album;

class SourceDiscogs extends SourceBase implements SourceInterface 
{
    protected DiscogsApi $api;

    protected string $id = 'discogs';

    public function __construct(DiscogsApi $api) 
    {
        $this->api = $api;
    }

    /**
     * @inheritDoc
     */
    public function searchForArtistByName(
        string $artistName, 
        int $page = 1, 
        int $itensPerPage = 20
    ) : SearchResults
    {
        $info = $this->api->searchForArtistByName($artistName, $page, $itensPerPage);

        $items = [];
        foreach ($info->results as $r) {
            $items[] = Artist::createFromArray([
                'source'    => 'discogs',
                'id'        => $r->id,
                'name'      => $r->title, 
                'thumbnail' => $r->thumb ?? ''
            ]);
        }

        return new SearchResults(
            $items,
            count($items),
            $info->pagination->page,
            $info->pagination->pages,
            $info->pagination->per_page,
            $info->pagination->items
        );
    }

    /**
     * @inheritDoc
     */
    public function searchForAlbumsByArtistName(
        string $artistName, 
        int $page = 1, 
        int $itensPerPage = 20
    ) : SearchResults
    {
        $info = $this->api->searchForAlbumsByArtistName($artistName, $page, $itensPerPage);

        $items = [];
        foreach ($info->results as $r) {
            $items[] = Album::createFromArray([
                'source'    => 'discogs',
                'id'        => $r->master_id,
                'title'     => $this->title($r->title, $artistName), 
                'thumbnail' => $r->thumb ?? null, 
                'year'      => ((int) !empty($r->year) ? $r->year : 0)
            ]);
        }

        return new SearchResults(
            $items,
            count($items),
            $info->pagination->page,
            $info->pagination->pages,
            $info->pagination->per_page,
            $info->pagination->items
        );
    }

    protected function title($title, $artistName) 
    {
        $title = ltrim($title, $artistName);
        $title = preg_replace('/ *\- */', '', $title);

        return $title;
    }

    public function findAlbumByArtistNameAndTitle(
        string $artistName, 
        string $releaseTitle
    ) : ?Album
    {
        $data    = $this->api->searchForAlbumsByArtistNameAndTitle($artistName, $releaseTitle);
        if (!$data) {
            return null;
        }

        if (!$data->results) {
            return null;
        }

        return $this->getAlbumById($data->results[0]->master_id);
    }

    /**
     * @inheritDoc
     */
    public function getAlbumById(string $releaseId) : ?Album
    {
        $data = $this->api->getAlbumById($releaseId);

        $tracks = [];
        foreach ($data->tracklist as $t) {
            $tracks[] = $t->title;
        }

        $release = Album::createFromArray([
            'source'    => 'discogs',
            'id'        => $releaseId,
            'title'     => $data->title ?? '',
            'year'      => $data->year ?? 0,
            'artist'    => $data->artists[0]->name,
            'thumbnail' => isset($data->images[0]) ? $data->images[0]->uri : null,
            'tracks'    => $tracks
        ]);

        return $release;
    }
}
