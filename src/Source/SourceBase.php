<?php 
namespace AdinanCenci\Discography\Source;

use AdinanCenci\Discography\Album;

abstract class SourceBase
{
    protected string $apiKey;

    /**
     * @inheritDoc
     */
    public function getId() : string 
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function findAlbum(?string $releaseId = null, ?string $artistName = null, ?string $releaseTitle = null) : ?Album
    {
        $release = null;

        if (!empty($releaseId)) {
            $release = $this->getAlbumById($releaseId);
        }

        if (!$release && !empty($releaseTitle)) {
            $release = $this->findAlbumByArtistNameAndTitle($artistName, $releaseTitle);
        }

        return $release
            ? $release
            : null;
    }

}
