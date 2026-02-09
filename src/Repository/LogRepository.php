<?php

namespace App\Repository;

use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Log>
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    /**
     * Récupère les logs récents
     */
    public function findRecent(int $limit = 50): array
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les logs par type
     */
    public function findByType(string $type, int $limit = 50): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.type = :type')
            ->setParameter('type', $type)
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les logs d'un utilisateur
     */
    public function findByUser(int $userId, int $limit = 50): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les logs par statut
     */
    public function countByStatus(): array
    {
        return $this->createQueryBuilder('l')
            ->select('l.status', 'COUNT(l.id) as count')
            ->groupBy('l.status')
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime les anciens logs (plus de X jours)
     */
    public function deleteOlderThan(int $days): int
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        return $this->createQueryBuilder('l')
            ->delete()
            ->where('l.createdAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }
}
