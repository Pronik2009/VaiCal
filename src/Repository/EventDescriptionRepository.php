<?php

namespace App\Repository;

use App\Entity\EventDescription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventDescription>
 *
 * @method EventDescription|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventDescription|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventDescription[]    findAll()
 * @method EventDescription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventDescriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventDescription::class);
    }

//    /**
//     * @return EventDescription[] Returns an array of EventDescription objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EventDescription
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
