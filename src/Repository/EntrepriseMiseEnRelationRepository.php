<?php

namespace App\Repository;

use App\Entity\EntrepriseMiseEnRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EntrepriseMiseEnRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntrepriseMiseEnRelation::class);
    }
}
