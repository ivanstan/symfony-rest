<?php

namespace Ivanstan\SymfonySupport\Field;

use Doctrine\ORM\Mapping as ORM;

trait UuidField
{
    #[ORM\Column(type: 'guid')]
    #[ORM\GeneratedValue(strategy: 'UUID')]
    protected string $uuid;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }
}
