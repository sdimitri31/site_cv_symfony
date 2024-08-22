<?php

namespace App\Repository;

use App\Entity\Blog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Blog>
 */
class BlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blog::class);
    }

    public function getQueryBuilderFindByTag(string $tag): QueryBuilder
    {
        return $this->createQueryBuilder('b')
            ->andWhere('JSON_CONTAINS(b.tags, :tag) = 1')
            ->setParameter('tag', json_encode($tag));
    }

    public function findByTag(string $tag): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('JSON_CONTAINS(b.tags, :tag) = 1')
            ->setParameter('tag', json_encode($tag))
            ->getQuery()
            ->getResult();
    }
}
