<?php 
namespace AdinanCenci\Discography\Api;

use AdinanCenci\GenericRestApi\ApiBase;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Client\ClientInterface;

class DiscogsApi extends ApiBase 
{
    protected string $token;

    /**
     * @inheritDoc
     */
    protected string $baseUrl = 'https://api.discogs.com/';

    /**
     * @param string $token
     *   Discogs API token.
     * @param array $options
     *   Implementation specific.
     * @param null|Psr\SimpleCache\CacheInterface $cache
     *   Cache, opcional.
     * @param null|Psr\Http\Client\ClientInterface $httpClient
     *   Optional, the class will use a generic library if not informed.
     * @param null|Psr\Http\Message\RequestFactoryInterface $requestFactory
     *   Optional, the class will use a generic library if not informed.
     */
    public function __construct(
        string $token,
        array $options = [],
        ?CacheInterface $cache = null,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
    ) 
    {
        parent::__construct($options, $cache, $httpClient, $requestFactory);
        $this->token = $token;
    }

    public function searchForArtistByName(
        string $artistName, 
        int $page = 1, 
        int $itensPerPage = 20
    ) : ?\stdClass
    {
        $endpoint = 'database/search?type=artist&title=' . $artistName . '&page=' . $page . '&per_page=' . $itensPerPage;
        return $this->getJson($endpoint);
    }

    public function getAlbumById(string $releaseId) : ?\stdClass
    {
        $endpoint = 'masters/' . $releaseId;
        return $this->getJson($endpoint);
    }

    public function searchForAlbumsByArtistName(string $artistName, $page = 1, $itensPerPage = 20) : ?\stdClass // X
    {
        $endpoint = 'database/search?type=master&artist=' . $artistName . '&page=' . $page . '&per_page=' . $itensPerPage;
        return $this->getJson($endpoint);
    }

    public function searchForAlbumsByArtistNameAndTitle(string $artistName, string $title, $page = 1, $itensPerPage = 20) : ?\stdClass 
    {
        $endpoint = 'database/search?type=master&artist=' . $artistName . '&title=' . $title . '&page=' . $page . '&per_page=' . $itensPerPage;
        return $this->getJson($endpoint);
    }

    /**
     * @inheritDoc
     */
    protected function createRequest(string $endPoint) : RequestInterface
    {
        $request = parent::createRequest($endPoint);
        $request = $request->withAddedHeader('Authorization', 'Discogs token=' . $this->token);
        $request = $request->withAddedHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36');

        return $request;
    }

    protected function generateExceptionMessage(ResponseInterface $response) : string
    {
        $body = (string) $response->getBody();

        $json = json_decode($body);

        if (! $json) {
            return $body;
        }

        return $json->message;
    }
}
