<?php

namespace App\Controller;

use App\Service\TimeService;
use App\Service\PartenaireJsonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdministrationController extends AbstractController
{
    #[Route('/administration/dashboard', name: 'app_administration_dashboard')]
    public function index(TimeService $timeService, PartenaireJsonService $partenaireService): Response
    {
        $currentYear = $timeService->getCurrentYear();
        $partenaires = $partenaireService->findAll();
        
        // Extraire uniquement les noms
        $nomsPartenaires = array_map(fn($p) => $p['nom'] ?? '', $partenaires);
        
        return $this->render('administration/index.html.twig', [
            'currentYear' => $currentYear,
            'partenaires' => $nomsPartenaires,
        ]);
    }

    #[Route('/administration/statistics', name: 'app_administration_statistics')]
    public function statistics(): Response
    {
        return $this->render('administration/statistics.html.twig');
    }

    #[Route('/administration/create-entreprise', name: 'app_administration_create_entreprise')]
    public function createEntreprise(): Response
    {
        return $this->render('administration/create_entreprise.html.twig');
    }
}
