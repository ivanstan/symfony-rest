<?php

namespace Ivanstan\SymfonyRest\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Ivanstan\SymfonyRest\Services\ApiEntityMetadata;
use Ivanstan\SymfonyRest\Services\Util\DoctrineUtil;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class EntityApiNormalizer extends HydraApiNormalizer
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected UrlGeneratorInterface $router,
        protected ObjectNormalizer $normalizer,
        protected DoctrineUtil $util,
    ) {
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        $metadata = new ApiEntityMetadata($this->em->getClassMetadata(get_class($object)));

        $data['@id'] = $this->getEntityUrl($object, $metadata);
        $data['@type'] = $metadata->getName();

        $normalized = $this->normalizer->normalize($object, $format, $context);

        if (($context['meta'] ?? false) === true) {
            $data['@meta'] = $this->getRequestedMeta($metadata, $normalized);
        }

        $data = array_merge($data, $normalized);

        return $data;
    }

    protected function getEntityUrl($entity, ApiEntityMetadata $meta): ?string
    {
        $options = $meta->getApiOptions();

        if (!isset($options['routes']['get'])) {
            return null;
        }

        $identifierGetter = 'get' . ucfirst($meta->getIdentifier());

        if (!method_exists($entity, $identifierGetter)) {
            return null;
        }

        try {
            return $this->router->generate(
                $options['routes']['get'],
                [
                    $meta->getIdentifier() => $entity->$identifierGetter(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Compares normalized entity and available fields only to return meta for fields that are
     * present in normalized entity. We want to avoid exposing filed information for fields ignored fields.
     */
    public function getRequestedMeta(ApiEntityMetadata $metadata, array $normalized): array
    {
        $meta = $metadata->getFields();

        $data = [];
        foreach ($normalized as $fieldName => $item) {
            if (isset($meta[$fieldName])) {
                $data[$fieldName] = $meta[$fieldName];
            }
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        if (!is_object($data)) {
            return false;
        }

        return $this->util->isDoctrineEntity(get_class($data));
    }
}
