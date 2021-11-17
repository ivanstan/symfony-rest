<?php

namespace Ivanstan\SymfonySupport\Field;

use Doctrine\ORM\Mapping as ORM;
use Ivanstan\SymfonySupport\Services\DateTimeService;

trait UpdatedAtTrait
{
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTime $updatedAt = null;

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    #[ORM\PostPersist]
    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = DateTimeService::getCurrentUTC();

        if (method_exists($this, 'setCreatedAt')) {
            $this->setCreatedAt();
        }
    }
}
