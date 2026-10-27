<?php

namespace App\Repository;

use App\Entity\Album;
use App\Entity\Photo;
use App\Model\AlbumSummary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Album>
 */
class AlbumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }

    /**
     * Visible albums, newest first, with their cover and visible-photo count loaded in a single query.
     *
     * @return AlbumSummary[]
     */
    public function findVisibleSummaries(?int $limit = null): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->addSelect('cover')
            ->leftJoin('a.cover', 'cover')
            ->addSelect(sprintf(
                '(SELECT COUNT(p.id) FROM %s p WHERE p.album = a AND p.visible = true) AS photoCount',
                Photo::class,
            ))
            ->andWhere('a.visible = true')
            ->orderBy('a.createdAt', 'DESC');

        if (null !== $limit) {
            $queryBuilder->setMaxResults($limit);
        }

        return array_map(
            static fn (array $row): AlbumSummary => new AlbumSummary($row[0], (int) $row['photoCount']),
            $queryBuilder->getQuery()->getResult(),
        );
    }
}
