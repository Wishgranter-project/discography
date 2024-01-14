<?php 
namespace AdinanCenci\Discography\Api;

use AdinanCenci\GenericRestApi\ApiBase;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Client\ClientInterface;

class LastFmApi extends ApiBase 
{
    /**
     * @inheritDoc
     */
    protected string $baseUrl = 'http://ws.audioscrobbler.com/';

    public function __construct(
        string $apiKey,
        array $options = [],
        ?CacheInterface $cache = null,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
    ) 
    {
        $this->apiKey = $apiKey;
        parent::__construct($options, $cache, $httpClient, $requestFactory);
    }

    public function searchForArtistByName(
        string $artistName, 
        int $page = 1, 
        int $itensPerPage = 20
    ) : \stdClass 
    {
        $endPoint = '2.0/?method=artist.search&artist=' . urlencode($artistName) . '&page=' . $page . '&limit=' . $itensPerPage;
        return $this->getJson($endPoint);
    }

    public function searchForAlbumsByArtistName(
        string $artistName, 
        int $page = 1, 
        int $itensPerPage = 20
    ) : \stdClass
    {
        $endPoint = '2.0/?method=artist.gettopalbums&artist=' . urlencode($artistName) . '&page=' . $page . '&limit=' . $itensPerPage;
        return $this->getJson($endPoint);
    }

    public function getAlbumById(string $id) : ?\stdClass
    {
        $endPoint = '2.0/?method=album.getinfo&mbid='. $id;
        return $this->getJson($endPoint);
    }

    /**
     * @param string $artistName
     * @param string $title
     * 
     * @return \stdClass
     */
    public function findAlbumsByArtistNameAndTitle(
        string $artistName, 
        string $title
    ) : ?\stdClass
    {
        $endPoint = '2.0/?method=album.getinfo&artist='. urlencode($artistName) . '&album=' . urlencode($title);
        return $this->getJson($endPoint);
    }

    /**
     * @inheritDoc
     */
    protected function createRequest(string $endPoint) : RequestInterface
    {
        $request = parent::createRequest($endPoint);
        $uri     = $request->getUri();
        $uri     = $uri->withQuery($uri->getQuery() . '&api_key=' . $this->apiKey . '&format=json');
        $request = $request->withUri($uri);

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
