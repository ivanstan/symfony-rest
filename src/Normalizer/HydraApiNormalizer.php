<?php

namespace Ivanstan\SymfonySupport\Normalizer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class HydraApiNormalizer implements ContextAwareNormalizerInterface
{
    protected const HYDRA_CONTEXT = 'https://www.w3.org/ns/hydra/context.jsonld';

    protected RequestStack $stack;

    public function getRequest(array $context = []): Request
    {
        return $context['request'] ?? $this->stack->getCurrentRequest();
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        return [
            '@context' => self::HYDRA_CONTEXT,
        ];
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return ($context['hydra'] ?? null) === true;
    }
}
