<?php 
namespace AdinanCenci\Discography\Source;

use AdinanCenci\Discography\Api\LastFmApi;
use AdinanCenci\Discography\Artist;
use AdinanCenci\Discography\Album;

class SourceLastFm extends SourceBase implements SourceInterface 
{
    protected LastFmApi $api;

    /**
     * @inheritDoc
     */
    protected string $id = 'lastfm';

    /**
     * @param AdinanCenci\Discography\Api\LastFmApi
     */
    public function __construct(LastFmApi $api) 
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
        $info         = $this->api->searchForArtistByName($artistName);

        $offset       = $info->results->{'opensearch:startIndex'};
        $itensPerPage = $info->results->{'opensearch:itemsPerPage'};
        $total        = $info->results->{'opensearch:totalResults'};

        $pages        = round($total / $itensPerPage);
        $pages        += $total > $itensPerPage * $pages ? 1 : 0;
        $page         = $offset > 0 ? round($offset / $itensPerPage) : 1;

        $items = [];
        foreach ($info->results->artistmatches->artist as $r) {
            $items[] = Artist::createFromArray([
                'source'    => 'lastfm',
                'id'        => !empty($r->mbid) ? $r->mbid  : '',
                'name'      => $r->name, 
                'thumbnail' => $r->image ? $r->image[0]->{'#text'} : ''
            ]);
        }

        return new SearchResults(
            $items,
            count($items),
            $page,
            $pages,
            $itensPerPage,
            $total
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

        if (!isset($info->topalbums->album)) {
            throw new NotFound("No albuns for $artistName found");
        }

        $items = [];
        foreach ($info->topalbums->album as $album) {
            if ($album->name == '(null)') {
                continue;
            }

            $items[] = Album::createFromArray([
                'source'    => 'lastfm',
                'id'        => isset($album->mbid) ? $album->mbid : '',
                'title'     => $album->name, 
                'artist'    => $album->artist->name, 
                'thumbnail' => $album->image ? $album->image[2]->{'#text'} : null, 
                'year'      => 0
            ]);
        }

        $attrs = $info->topalbums->{'@attr'};

        return new SearchResults(
            $items,
            count($items),
            $attrs->page,
            $attrs->totalPages,
            $attrs->perPage,
            $attrs->total
        );
    }

    /**
     * @inheritDoc
     */
    public function getAlbumById(string $releaseId) : ?Album
    {
        $data    = $this->api->getAlbumById($releaseId);
        if (!$data) {
            return null;
        }

        $release = $this->buildRelease($data);
        return $release;
    }

    /**
     * @inheritDoc
     */
    public function findAlbumByArtistNameAndTitle(
        string $artistName, 
        string $releaseTitle
    ) : ?Album
    {
        $data    = $this->api->findAlbumsByArtistNameAndTitle($artistName, $releaseTitle);
        if (!$data) {
            return null;
        }

        $release = $this->buildRelease($data);

        return $release;
    }

    protected function buildRelease(\stdClass $data) : ?Album
    {
        if (!isset($data->album->tracks->track)) {
            return null;
        }

        $albumTracks = $data->album->tracks->track;

        $albumTracks = is_array($albumTracks)
            ? $albumTracks
            : [ $albumTracks ];

        $tracks = [];
        foreach ($albumTracks as $t) {
            $tracks[] = $t->name;
        }

        $release = Album::createFromArray([
            'source'    => 'lastfm',
            'id'        => !empty($releaseId->mbid) ? $releaseId->mbid : '',
            'title'     => $data->album->name ?? '',
            'artist'    => $data->album->artist,
            'thumbnail' => $data->album->image ? $data->album->image[2]->{'#text'} : null,
            'tracks'    => $tracks
        ]);

        return $release;
    }

}
