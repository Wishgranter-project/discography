<?php

namespace WishgranterProject\Discography\Discogs;

use AdinanCenci\GenericRestApi\ApiBase;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Client\ClientInterface;

class ApiDiscogs extends ApiBase
{
    /**
     * @var string $token
     */
    protected string $token;

    /**
     * {@inheritdoc}
     */
    protected string $baseUrl = 'https://api.discogs.com/';

    /**
     * {@inheritdoc}
     */
    protected array $options = [
        'timeToLive' => 24 * 60 * 60 * 30, // For how long should GET requests be cached.
    ];

    /**
     * @param string $token
     *   Api token.
     * @param array $options
     *   Implementation specific.
     * @param null|Psr\SimpleCache\CacheInterface $cache
     *   Cache, opcional.
     * @param null|Psr\Http\Client\ClientInterface $httpClient
     *   Optional, the class will use a generic library if not informed.
     * @param Psr\Http\Message\RequestFactoryInterface|null $requestFactory
     *   Optional, the class will use a generic library if not informed.
     */
    public function __construct(
        string $token,
        array $options = [],
        ?CacheInterface $cache = null,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
    ) {
        parent::__construct($options, $cache, $httpClient, $requestFactory);
        $this->token = $token;
    }

    /**
     * Search for artists/bands by their name.
     *
     * @param string $artistName
     *   The name of the artist/band.
     * @param int $page
     *   The page of results to retrieve.
     * @param int $itemsPerPage
     *   How many results there should be per page.
     *
     * @return \stdClass
     *   Search results.
     */
    public function searchForArtistByName(string $artistName, int $page = 1, int $itemsPerPage = 100)
    {
        $artistName = strtolower($artistName);
        return $this->search([
            'page'     => $page,
            'per_page' => $itemsPerPage,
            'type'     => 'artist',
            'query'    => $artistName,
        ]);
    }

    /**
     * Search for releases by their authoring artist/band name.
     *
     * @param string $artistName
     *   The name of the artist/band.
     * @param int $page
     *   The page of results to retrieve.
     * @param int $itemsPerPage
     *   How many results there should be per page.
     *
     * @return \stdClass
     *   Search results.
     */
    public function searchReleasesByArtistsName(string $artistName, int $page = 1, int $itemsPerPage = 100)
    {
        $artistName = strtolower($artistName);
        return $this->search([
            'page'     => $page,
            'per_page' => $itemsPerPage,
            'type'     => 'release',
            'artist'   => $artistName,
        ]);
    }

    /**
     * Search for releases by the authoring artist/band id.
     *
     * @param string $artistId
     *   The id of the artist/band.
     * @param int $page
     *   The page of results to retrieve.
     * @param int $itemsPerPage
     *   How many results there should be per page.
     *
     * @return \stdClass
     *   Search results.
     */
    public function searchReleasesByArtistsId(string $artistId, int $page = 1, int $itemsPerPage = 100)
    {
        return $this->search([
            'page'     => $page,
            'per_page' => $itemsPerPage,
            'type'     => 'release',
            'query'    => $artistId,
        ]);
    }

    /**
     * Search for releases by title and the name of the authoring artist/band.
     *
     * @param string $artistName
     *   The name of the artist/band.
     * @param string $title
     *   The title of the album.
     * @param int $page
     *   The page of results to retrieve.
     * @param int $itemsPerPage
     *   How many results there should be per page.
     *
     * @return \stdClass
     *   Search results.
     */
    public function searchReleasesByTitleAndArtistsName(string $artistName, string $title, int $page = 1, int $itemsPerPage = 100)
    {
        $artistName = strtolower($artistName);
        $title = strtolower($title);

        return $this->search([
            'page'     => $page,
            'per_page' => $itemsPerPage,
            'type'     => 'release',
            'artist'   => $artistName,
            'title'    => $title,
        ]);
    }

    /**
     * Search for tracks for the specified album.
     *
     * @param string $artistName
     *   The name of the artist/band.
     * @param string $title
     *   The title of the album.
     * @param int $page
     *   The page of results to retrieve.
     * @param int $itemsPerPage
     *   How many results there should be per page.
     *
     * @return \stdClass
     *   Search results.
     */
    public function getTracksFromAlbum(string $artistName, string $title, int $page = 1, int $itemsPerPage = 100)
    {
        $artistName = strtolower($artistName);
        $title      = strtolower($title);

        return $this->search([
            'page'          => $page,
            'per_page'      => $itemsPerPage,
            'type'          => 'track',
            'artist'        => $artistName,
            'release_title' => $title
        ]);
    }

    /**
     * Search for releases by the specified artist id.
     *
     * @param string $artistId
     *   The id of the artist/band.
     * @param int $page
     *   The page of results to retrieve.
     * @param int $itemsPerPage
     *   How many results there should be per page.
     *
     * @return \stdClass
     *   Search results.
     */
    public function getReleasesByArtistId(string $artistId, int $page = 1, int $itemsPerPage = 100)
    {
        return $this->getJson('artists/' . $artistId . '/releases?page=' . $page . '&per_page=' . $itemsPerPage);
    }

    /**
     * Generic method to search.
     *
     * @param array $data
     *   To be added to the query string.
     *
     * @return \stdClass
     *   The results.
     */
    public function search(array $data)
    {
        $query = http_build_query($data);
        $url = 'database/search?' . $query;

        return $this->getJson($url);
    }

    /**
     * Retrieves data for the specified master id.
     *
     * @param string $masterId
     *   The master release id.
     *
     * @return \stdClass
     *   Data regarding the master release.
     */
    public function getMasterRelease(string $masterId)
    {
        return $this->getJson('masters/' . $masterId);
    }

    /**
     * Retrieves data for the specified master id.
     *
     * @param string $id
     *   The release id.
     *
     * @return \stdClass
     *   Data regarding the release.
     */
    public function getRelease(string $id)
    {
        return $this->getJson('releases/' . $id);
    }

    /**
     * {@inheritdoc}
     */
    protected function createRequest(string $endPoint): RequestInterface
    {
        $request = parent::createRequest($endPoint . ( substr_count($endPoint, '?') ? '&' : '?' ) . 'token=' . $this->token);
        //$request = $request->withHeader('Authorization', 'Discogs ' . $this->token);
        $request = $request->withHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36');
        $request = $request->withHeader('Accept', 'application/json');

        return $request;
    }
}
