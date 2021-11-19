<?php

namespace Ivanstan\SymfonySupport\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Ivanstan\SymfonySupport\Services\ApiEntityMetadata;

class EntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function get(string $entity, string $id)
    {
        $meta = new ApiEntityMetadata($this->_em->getClassMetadata($entity));

        $builder = $this->_em->createQueryBuilder();
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
        $meta = new ApiEntityMetadata($this->_em->getClassMetadata($name));

        $alias = $meta->getAlias();

        $builder = $this->_em->createQueryBuilder();

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
