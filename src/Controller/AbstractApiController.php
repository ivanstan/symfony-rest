<?php

namespace Ivanstan\SymfonySupport\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class AbstractApiController extends AbstractController
{
    use NormalizerAwareTrait;

    protected function getEntity(string $entityClass, string $id)
    {
//        $entity = $this->repository->get($entityClass, $id);
//
//        $meta = new ApiEntityMetadata($this->getDoctrine()->getManager()->getClassMetadata($entityClass));
//
//        if ($entity === null) {
//            throw new NotFoundHttpException(
//                \sprintf('Unable to find entity "%s" with identifier [%s: %s]', ClassUtil::getClassNameFromFqn($entity), $meta->getIdentifier(), $id)
//            );
//        }
//
//        return $this->normalizer->normalize($entity, context: ['hydra' => true, 'meta' => false]);
    }
}
