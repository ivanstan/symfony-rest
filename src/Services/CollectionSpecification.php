<?php

namespace Ivanstan\SymfonyRest\Services;

class CollectionSpecification
{
    public string $entity;
    public ?string $search = null;
    public ?string $sort = null;
    public ?string $sortDir = null;

    public function __construct()
    {
    }
}
