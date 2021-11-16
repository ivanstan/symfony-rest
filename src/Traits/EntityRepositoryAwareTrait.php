<?php

namespace Ivanstan\SymfonyRest\Traits;

use Ivanstan\SymfonyRest\Repository\EntityRepository;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @codeCoverageIgnore
 */
trait EntityRepositoryAwareTrait
{
    protected EntityRepository $repository;

    #[Required]
    public function setEntityRepository(
        EntityRepository $repository
    ): void {
        $this->repository = $repository;
    }
}
