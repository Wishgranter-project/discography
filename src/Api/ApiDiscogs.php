<?php

namespace WishgranterProject\Discography\Api;

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
     * @inheritDoc
     */
    protected string $baseUrl = 'https://api.discogs.com/';

    /**
     * @inheritDoc
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

    public function searchForArtistByName(string $artistName, int $page = 1, int $itensPerPage = 100)
    {
        $artistName = strtolower($artistName);
        return $this->search([
            'page'     => $page,
            'per_page' => $itensPerPage,
            'type'     => 'artist',
            'query'    => $artistName,
        ]);
    }


    public function searchReleasesByArtistsName(string $artistName, int $page = 1, int $itensPerPage = 100)
    {
        $artistName = strtolower($artistName);
        return $this->search([
            'page'     => $page,
            'per_page' => $itensPerPage,
            'type'     => 'release',
            'artist'   => $artistName,
        ]);
    }

    public function searchReleasesByArtistsId(string $artistId, int $page = 1, int $itensPerPage = 100)
    {
        return $this->search([
            'page'     => $page,
            'per_page' => $itensPerPage,
            'type'     => 'release',
            'query'    => $artistId,
        ]);
    }

    public function searchReleasesByTitleAndArtistsName(string $artistName, string $title, int $page = 1, int $itensPerPage = 100)
    {
        $artistName = strtolower($artistName);
        $title = strtolower($title);

        return $this->search([
            'page'     => $page,
            'per_page' => $itensPerPage,
            'type'     => 'release',
            'artist'   => $artistName,
            'title'    => $title,
        ]);
    }

    public function getTracksFromAlbum(string $artistName, string $title, int $page = 1, int $itensPerPage = 100)
    {
        $artistName = strtolower($artistName);
        $title = strtolower($title);
        return $this->getJson('database/search?page=' . $page . '&per_page=' . $itensPerPage . '&type=track&artist=' . $artistName . '&release_title=' . $title);
    }

    public function getReleasesByArtistId(string $artistId, int $page = 1, int $itensPerPage = 100)
    {
        return $this->getJson('artists/' . $artistId . '/releases?page=' . $page . '&per_page=' . $itensPerPage);
    }

    public function search(array $data)
    {
        $query = http_build_query($data);
        $url = 'database/search?' . $query;

        return $this->getJson($url);
    }

    public function getMasterRelease(string $masterId)
    {
        return $this->getJson('masters/' . $masterId);
    }

    public function getRelease(string $id)
    {
        return $this->getJson('releases/' . $id);
    }

    /**
     * @inheritDoc
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
