<?php

namespace Ivanstan\SymfonySupport\Services;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Ivanstan\SymfonySupport\Request\CollectionRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class QueryBuilderPaginator
{
    protected int|null $total = null;
    protected int $page = 1;
    protected int $pageSize = 20;

    private ApiEntityMetadata $meta;

    public function __construct(protected QueryBuilder $builder)
    {
        $entity = $builder->getRootEntities()[0] ?? null;

        $this->meta = new ApiEntityMetadata($builder->getEntityManager()->getClassMetadata($entity));
    }

    public function getCurrentPageResult(): array
    {
        $builder = clone $this->builder;

        $builder->setMaxResults($this->pageSize);
        $builder->setFirstResult($this->getPageOffset($this->page, $this->pageSize));

        return $builder->getQuery()->getResult();
    }

    protected function getPageOffset(int $page, int $pageSize): int
    {
        $offset = 0;
        if ($page > 1) {
            $offset = ($page - 1) * $pageSize;
        }

        return $offset;
    }

    public function getView(Request $request, RouterInterface $router): array
    {
        $params = $request->query->all();

        $page = $this->page;
        $pages = max(1, ceil($this->getTotal() / $this->pageSize));

        $nextPage = $page;
        if ($page < $pages) {
            $nextPage = $page + 1;
        }

        $previousPage = $page;
        if ($page > 1) {
            $previousPage = $page - 1;
        }

        $result = [
            '@id' => $router->generate(
                $request->attributes->get('_route'),
                array_merge($params, ['page' => $page]),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            '@type' => 'PartialCollectionView',
            'first' => $router->generate(
                $request->attributes->get('_route'),
                array_merge($params, ['page' => 1]),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'previous' => $router->generate(
                $request->attributes->get('_route'),
                array_merge($params, ['page' => $previousPage]),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'next' => $router->generate(
                $request->attributes->get('_route'),
                array_merge($params, ['page' => $nextPage]),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'last' => $router->generate(
                $request->attributes->get('_route'),
                array_merge($params, ['page' => $pages]),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ];

        if ($page === 1) {
            unset($result['previous']);
        }

        if ($page === $nextPage) {
            unset($result['next']);
        }

        return $result;
    }

    public function getTotal(): int
    {
        if ($this->total !== null) {
            return $this->total;
        }

        $builder = clone $this->builder;

        $alias = $builder->getRootAliases()[0] ?? null;
        $identifier = $this->meta->getIdentifier();

        $builder->select("COUNT($alias.$identifier)");

        try {
            $result = $builder->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException) {
            $result = array_sum(
                array_map(static fn($item) => (int)$item, $builder->getQuery()->getScalarResult())
            );
        } catch (NoResultException) {
            $result = 0;
        }

        $this->total = $result;

        return $result;
    }

    public function setCurrentPage(int $page): QueryBuilderPaginator
    {
        $this->page = $page;

        return $this;
    }

    public function setPageSize(int $pageSize): QueryBuilderPaginator
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->builder;
    }

    public function getType(): string
    {
        return $this->meta->getName() . '[]';
    }

    public function setFromRequest(CollectionRequest $request): void
    {
        $this
            ->setPageSize($request->getPageSize())
            ->setCurrentPage($request->getPage());
    }
}
