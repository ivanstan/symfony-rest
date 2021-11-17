<?php

namespace Ivanstan\SymfonySupport\Field;

use Doctrine\ORM\Mapping as ORM;
use Ivanstan\SymfonySupport\Services\DateTimeService;

trait CreatedAtTrait
{
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTime $createdAt = null;

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = DateTimeService::getCurrentUTC();
    }
}

