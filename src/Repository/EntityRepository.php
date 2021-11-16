<?php

namespace Ivanstan\SymfonyRest\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Ivanstan\SymfonyRest\Services\ApiEntityMetadata;

class EntityRepository
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    public function get(string $entity, string $id)
    {
        $meta = new ApiEntityMetadata($this->em->getClassMetadata($entity));

        $builder = $this->em->createQueryBuilder();
        $identifier = $meta->getIdentifier();
        $alias = $meta->getAlias();

        $builder->select($alias);
        $builder->from($entity, $alias);

        $builder
            ->where("$alias.$identifier = :id")
            ->setParameter('id', $id);

        return $builder->getQuery()->getOneOrNullResult();
    }

    public function collection(string $name): QueryBuilder
    {
        $meta = new ApiEntityMetadata($this->em->getClassMetadata($name));

        $alias = $meta->getAlias();

        $builder = $this->em->createQueryBuilder();

        $builder->select($alias);
        $builder->from($meta->getFQN(), $alias);

//        if ($specification->search !== null) {
//            $builder
//                ->where(
//                    $builder->expr()->orX(
//                        $builder->expr()->like("$alias.email", $builder->expr()->literal('%' . $specification->search . '%'))
//                    )
//                );
//        }
//
//        if ($specification->sort) {
//            $builder->orderBy("$alias." . $specification->sort, $specification->sortDir ?? 'ASC');
//        }

        return $builder;
    }
}
