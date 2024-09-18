<?php

namespace App\Repository;

use App\Entity\Activity;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activity>
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    public function findUpcomingActivities(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.date >= :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('a.date', 'ASC') // Optionnel : pour trier par date ascendante
            ->getQuery()
            ->getResult();
    }


    /**
     * @param User $userAdmin
     * @return Activity[]
     */
    public function findActivitiesByAdmin(User $userAdmin)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.userAdminOrganizer = :userAdmin')
            ->setParameter('userAdmin', $userAdmin)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $userAdmin
     * @return Activity[]
     */
    public function findUpcomingActivitiesByAdmin(User $userAdmin)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.userAdminOrganizer = :userAdmin')
            ->andWhere('a.date > CURRENT_DATE()')
            ->setParameter('userAdmin', $userAdmin)
            ->getQuery()
            ->getResult();
    }


//    /**
//     * @return Activity[] Returns an array of Activity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Activity
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
