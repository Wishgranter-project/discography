<?php

namespace WishgranterProject\Discography\Source;

use WishgranterProject\Discography\Album;

abstract class SourceBase
{
    protected string $id = 'base';

    public function getId(): string
    {
        return $this->id;
    }
}
