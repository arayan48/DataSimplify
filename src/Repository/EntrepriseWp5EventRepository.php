<?php

namespace App\Repository;

use App\Entity\EntrepriseWp5Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EntrepriseWp5EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntrepriseWp5Event::class);
    }
}
