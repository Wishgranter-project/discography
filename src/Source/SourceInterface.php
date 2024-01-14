<?php 
namespace AdinanCenci\Discography\Source;

use AdinanCenci\Discography\Artist;
use AdinanCenci\Discography\Album;

interface SourceInterface 
{
    public function getId() : string;

    /**
     * Search for artists by their name.
     *
     * @param string $artistName
     * @param int $page
     * @param int $itensPerPage
     * 
     * @return AdinanCenci\Discography\Source\SearchResults
     */
    public function searchForArtistByName(
        string $artistName, 
        int $page = 1, 
        int $itensPerPage = 20
    ) : SearchResults;

    /**
     * Search for albuns by the author's name.
     *
     * @param string $artistName
     * @param int $page
     * @param int $itensPerPage
     * 
     * @return AdinanCenci\Discography\Source\SearchResults
     */
    public function searchForAlbumsByArtistName(
        string $artistName, 
        int $page = 1, 
        int $itensPerPage = 20
    ) : SearchResults;

    /**
     * @param string $id
     * 
     * @return null|Album
     */
    public function getAlbumById(string $id) : ?Album;

    /**
     * @param string $artistName
     * @param string $releaseTitle
     * 
     * @return null|Album
     */
    public function findAlbumByArtistNameAndTitle(
        string $artistName,
        string $releaseTitle
    ) : ?Album;

    /**
     * Get release either by id or by artist name and title.
     * 
     * If it fails to find it by id then tries artist name and title
     * 
     * @param null|string $releaseId
     * @param null|string $artistName
     * @param null|string $releaseTitle
     * 
     * @return null|Album
     */
    public function findAlbum(
        ?string $releaseId = null,
        ?string $artistName = null,
        ?string $releaseTitle = null
    ) : ?Album;
}
