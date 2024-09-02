<?php

namespace WishgranterProject\Discography\Api;

use AdinanCenci\GenericRestApi\ApiBase;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Client\ClientInterface;

class ApiMusicBrainz extends ApiBase
{
    /**
     * @inheritDoc
     */
    protected string $baseUrl = 'https://musicbrainz.org/ws/2/';

    /**
     * @inheritDoc
     */
    protected array $options = [
        'timeToLive' => 24 * 60 * 60 * 30, // For how long should GET requests be cached.
    ];

    /**
     * @param string $artistName
     * @param int $offset
     * @param int $limit
     *
     * @return null|\stdClass
     */
    public function searchForArtistByName(
        string $artistName,
        int $offset = 0,
        int $limit = 100
    ): ?\stdClass {
        $query = 'name:"' . $artistName . '"';

        $endpoint = 'artist?query=' . $query . '&offset=' . $offset . '&limit=' . $limit;
        return $this->getJson($endpoint);
    }

    /**
     * @param string $artistName
     *
     * @return array
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
     * @param string $artistName
     * @param string $title
     * @param int $offset
     * @param int $limit
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

        $endpoint = 'release?query=' . $query . '&offset=' . $offset . '&limit=' . $limit;
        return $this->getJson($endpoint);
    }

    /**
     * @param string $id
     *
     * @return null|\stdClass
     */
    public function getReleaseById(string $id): ?\stdClass
    {
        $endpoint = 'release/' . $id . '?inc=recordings';
        return $this->getJson($endpoint);
    }

    /**
     * @param string $artistName
     * @param int $offset
     * @param int $limit
     *
     * @return null|\stdClass
     */
    public function searchReleaseGroupsByArtistName(
        string $artistName,
        int $offset = 0,
        int $limit = 100
    ): ?\stdClass {
        $query =
        'artistname:"' . $artistName . '" AND ' .
        '(primarytype:"Album" OR primarytype:"Single") AND ' .
        '-secondarytype:"Compilation" AND ' .
        '-secondarytype:"Live"';

        $endpoint = 'release-group?query=' . $query . '&offset=' . $offset . '&limit=' . $limit;
        return $this->getJson($endpoint);
    }

    /**
     * @param string $artistId
     * @param int $offset
     * @param int $limit
     *
     * @return null|\stdClass
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

        $endpoint = 'release-group?query=' . $query . '&offset=' . $offset . '&limit=' . $limit;
        return $this->getJson($endpoint);
    }

    /**
     * @param string $artistName
     * @param int $offset
     * @param int $limit
     *
     * @return null|\stdClass
     */
    public function searchArtistsByArtistName(
        string $artistName,
        int $offset = 0,
        int $limit = 100
    ): ?\stdClass {
        $query =
        'artist:"' . $artistName . '"';

        $endpoint = 'artist?query=' . $query . '&offset=' . $offset . '&limit=' . $limit;
        return $this->getJson($endpoint);
    }

    /**
     * @inheritDoc
     */
    protected function createRequest(string $endPoint): RequestInterface
    {
        $request = parent::createRequest($endPoint);
        $request = $request->withHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36');
        $request = $request->withHeader('Accept', 'application/json');

        return $request;
    }

    /**
     * @inheritDoc
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
