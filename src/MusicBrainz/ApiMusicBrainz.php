<?php

namespace WishgranterProject\Discography\MusicBrainz;

use AdinanCenci\GenericRestApi\ApiBase;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Client\ClientInterface;

class ApiMusicBrainz extends ApiBase
{
    /**
     * {@inheritdoc}
     */
    protected string $baseUrl = 'https://musicbrainz.org/ws/2/';

    /**
     * {@inheritdoc}
     */
    protected array $options = [
        'timeToLive' => 24 * 60 * 60 * 30, // For how long should GET requests be cached.
    ];

    /**
     * Search for artist/band by name.
     *
     * @param string $artistName
     *   The name of the artist/band.
     * @param int $offset
     *   Results offset.
     * @param int $limit
     *   Max numbero flimits it should return.
     *
     * @return null|\stdClass
     */
    public function searchForArtistByName(
        string $artistName,
        int $offset = 0,
        int $limit = 100
    ): ?\stdClass {
        $query = 'name:"' . $artistName . '"';

        $queryString = http_build_query([
            'query'  => $query,
            'offset' => $offset,
            'limit'  => $limit
        ]);

        $endpoint = 'artist?' . $queryString;
        return $this->getJson($endpoint);
    }

    /**
     * Search release groups by the name of the artist/band.
     *
     * @param string $artistName
     *   The name of the artist/band.
     *
     * @return \stdClass[]
     *   Release groups.
     */
    public function getAllArtistsReleaseGroupsByArtistName(string $artistName): array
    {
        $releaseGroups = [];
        $offset = 0;
        $limit = 100;

        do {
            $info = $this->searchReleaseGroupsByArtistName($artistName, $offset, $limit);
            $count = $info->count;
            $groups = $info->{'release-groups'};
            $releaseGroups = array_merge($releaseGroups, $groups);
            $offset += 100;
        } while (count($releaseGroups) < $count);

        return $releaseGroups;
    }

    /**
     * Search release groups by the id of the artist/band.
     *
     * @param string $artistId
     *   The id of the artist/band.
     *
     * @return \stdClass[]
     *   Release groups.
     */
    public function getAllArtistsReleaseGroupsByArtistId(string $artistId): array
    {
        $releaseGroups = [];
        $offset = 0;
        $limit = 100;

        do {
            $info = $this->searchReleaseGroupsByArtistId($artistId, $offset, $limit);
            $count = $info->count;
            $groups = $info->{'release-groups'};
            $releaseGroups = array_merge($releaseGroups, $groups);
            $offset += 100;
        } while (count($releaseGroups) < $count);

        return $releaseGroups;
    }

     /**
     * Seaarch for releases by the name of the artist/band.
     *
     * @param string $artistName
     *   The name of the artist/band.
     * @param string $title
     *   The title of the release.
     * @param int $offset
     *   Results offset.
     * @param int $limit
     *   Max numbero flimits it should return.
     *
     * @return null|\stdClass
     */
    public function searchReleasesByArtistNameAndTitle(
        string $artistName,
        string $title,
        int $offset = 0,
        int $limit = 100
    ): ?\stdClass {
        $query = 'artistname:"' . $artistName . '" AND release:"' . $title . '"';

        $queryString = http_build_query([
            'query'  => $query,
            'offset' => $offset,
            'limit'  => $limit
        ]);

        $endpoint = 'release?' . $queryString;
        return $this->getJson($endpoint);
    }

    /**
     * Get info on a release by its id.
     *
     * @param string $id
     *   The release id.
     *
     * @return null|\stdClass
     *   Release information
     */
    public function getReleaseById(string $id): ?\stdClass
    {
        $endpoint = 'release/' . $id . '?inc=recordings';
        return $this->getJson($endpoint);
    }

    /**
     * Search release groups by the name of the artist/band.
     *
     * @param string $artistName
     *   The name of the artist/band.
     * @param int $offset
     *   Results offset.
     * @param int $limit
     *   Max numbero flimits it should return.
     *
     * @return null|\stdClass
     *   Release groups.
     */
    public function searchReleaseGroupsByArtistName(
        string $artistName,
        int $offset = 0,
        int $limit = 100
    ): ?\stdClass {
        $query =
        'artistname:"' . $artistName . '" AND ' .
        '(primarytype:"Album" OR primarytype:"Single") AND ' .
        '-secondarytype:"Compilation" AND ' . // No thank you
        '-secondarytype:"Live"'; // Absolutely not.

        $queryString = http_build_query([
            'query'  => $query,
            'offset' => $offset,
            'limit'  => $limit
        ]);

        $endpoint = 'release-group?' . $queryString;
        return $this->getJson($endpoint);
    }

    /**
     * Search release groups by the id of the artist/band.
     *
     * @param string $artistId
     *   The id of the artist/band.
     * @param int $offset
     *   Results offset.
     * @param int $limit
     *   Max numbero flimits it should return.
     *
     * @return null|\stdClass
     *   Release groups.
     */
    public function searchReleaseGroupsByArtistId(
        string $artistId,
        int $offset = 0,
        int $limit = 100
    ): ?\stdClass {
        $query =
        'arid:"' . $artistId . '" AND ' .
        '(primarytype:"Album" OR primarytype:"Single") AND ' .
        '-secondarytype:"Compilation" AND ' .
        '-secondarytype:"Live"';

        $queryString = http_build_query([
            'query'  => $query,
            'offset' => $offset,
            'limit'  => $limit
        ]);

        $endpoint = 'release-group?' . $queryString;
        return $this->getJson($endpoint);
    }

    /**
     * Search for an artist/band by their name.
     *
     * @param string $artistName
     *   The name of the artist/band.
     * @param int $offset
     *   Results offset.
     * @param int $limit
     *   Max numbero flimits it should return.
     *
     * @return null|\stdClass
     *   Artists.
     */
    public function searchArtistsByArtistName(
        string $artistName,
        int $offset = 0,
        int $limit = 100
    ): ?\stdClass {
        $query =
        'artist:"' . $artistName . '"';

        $queryString = http_build_query([
            'query'  => $query,
            'offset' => $offset,
            'limit'  => $limit
        ]);

        $endpoint = 'artist?' . $queryString;
        return $this->getJson($endpoint);
    }

    /**
     * {@inheritdoc}
     */
    protected function createRequest(string $endPoint): RequestInterface
    {
        $request = parent::createRequest($endPoint);
        $request = $request->withHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36');
        $request = $request->withHeader('Accept', 'application/json');

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    protected function generateExceptionMessage(ResponseInterface $response): string
    {
        $body = (string) $response->getBody();

        $json = json_decode($body);

        if (! $json) {
            return $body;
        }

        return $json->error;
    }
}
