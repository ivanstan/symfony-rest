<?php

namespace Ivanstan\SymfonySupport\Traits;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @codeCoverageIgnore
 */
trait FileSystemAwareTrait
{
    private ParameterBagInterface $parameters;

    #[Required]
    public function setParameterBag(ParameterBagInterface $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getProjectDir(): string
    {
        return $this->parameters->get('kernel.project_dir');
    }
}
