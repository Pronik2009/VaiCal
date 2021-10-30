<?php

namespace App\Repository;

use App\Entity\Year;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Year|null find($id, $lockMode = null, $lockVersion = null)
 * @method Year|null findOneBy(array $criteria, array $orderBy = null)
 * @method Year[]    findAll()
 * @method Year[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class YearRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Year::class);
    }
}
