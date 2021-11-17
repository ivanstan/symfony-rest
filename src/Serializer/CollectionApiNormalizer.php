<?php

namespace Ivanstan\SymfonySupport\Serializer;

use Ivanstan\SymfonySupport\Services\QueryBuilderPaginator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CollectionApiNormalizer extends HydraApiNormalizer
{
    public function __construct(protected RouterInterface $router, protected RequestStack $stack, ObjectNormalizer $normalizer)
    {
    }

    /**
     * @param QueryBuilderPaginator $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $request = $this->getRequest($context);

        $data = parent::normalize($object, $format, $context);

        return array_merge(
            $data,
            [
                '@id' => $this->router->generate($request->attributes->get('_route'), [], UrlGeneratorInterface::ABSOLUTE_URL),
                '@type' => $object->getType(),
                'totalItems' => $object->getTotal(),
                'member' => $object->getCurrentPageResult(),
                'parameters' => array_merge($request->request->all(), $request->query->all()),
                'view' => $object->getView($request, $this->router),
            ]
        );
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return parent::supportsNormalization($data, $format, $context) && $data instanceof QueryBuilderPaginator;
    }
}
