<?php

namespace Ivanstan\SymfonySupport\Traits;

use Ivanstan\SymfonySupport\Repository\EntityRepository;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @codeCoverageIgnore
 */
trait EntityRepositoryAwareTrait
{
    protected EntityRepository $repository;

    #[Required]
    public function setEntityRepository(EntityRepository $repository): void
    {
        $this->repository = $repository;
    }
}
