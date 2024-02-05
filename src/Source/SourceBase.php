<?php 
namespace AdinanCenci\Discography\Source;

use AdinanCenci\Discography\Album;

abstract class SourceBase
{
    protected string $id = 'base';

    /**
     * @inheritDoc
     */
    public function getId() : string 
    {
        return $this->id;
    }
}
