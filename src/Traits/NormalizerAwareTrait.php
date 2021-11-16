<?php

namespace Ivanstan\SymfonyRest\Traits;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @codeCoverageIgnore
 */
trait NormalizerAwareTrait
{
    protected NormalizerInterface $normalizer;

    #[Required]
    public function setNormalizer(NormalizerInterface $normalizer): void
    {
        $this->normalizer = $normalizer;
    }
}
