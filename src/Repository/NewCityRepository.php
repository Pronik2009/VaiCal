<?php

namespace App\Repository;

use App\Entity\NewCity;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewCity|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewCity|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewCity[]    findAll()
 * @method NewCity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewCityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewCity::class);
    }

    /**
     * @param NewCity $newCity
     *
     * @return bool
     *
     * @throws NonUniqueResultException
     */
    public function checkSpam(NewCity $newCity): bool
    {
        $result = $this->createQueryBuilder('n')
            ->andWhere('n.IP = :IP')
            ->andWhere('n.UserAgent = :agent')
            ->andWhere('n.createdAt > :created')
            ->setParameter('IP', $newCity->getIP())
            ->setParameter('agent', $newCity->getUserAgent())
            ->setParameter('created', new DateTime('-1 day'))
            ->getQuery()
            ->getOneOrNullResult();

        return (bool)$result;
    }
}
