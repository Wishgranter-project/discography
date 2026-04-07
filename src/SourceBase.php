<?php

namespace WishgranterProject\Discography;

abstract class SourceBase
{
    protected string $id = 'base';

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->id;
    }
}
