<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\EntrepriseWp2;
use App\Entity\EntrepriseWp5Event;
use App\Entity\EntrepriseWp5Formation;
use App\Entity\EntrepriseWp6;
use App\Entity\EntrepriseWp7;
use App\Entity\EntrepriseMiseEnRelation;
use App\Entity\User;
use App\Repository\EntrepriseRepository;
use App\Service\TimeService;
use App\Service\PartenaireJsonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdministrationController extends AbstractController
{
    #[Route(path: [
        'fr' => '/administration/tableau-de-bord',
        'en' => '/administration/dashboard'
    ], name: 'app_administration_dashboard')]
    public function index(Request $request, TimeService $timeService, PartenaireJsonService $partenaireService, EntrepriseRepository $entrepriseRepository): Response
    {
        $currentYear = $timeService->getCurrentYear();
        $partenaires = $partenaireService->findAll();
        
        // Extraire uniquement les noms
        $nomsPartenaires = array_map(fn($p) => $p['nom'] ?? '', $partenaires);
        
        // Récupérer les paramètres de filtrage
        $searchQuery = $request->query->get('search', '');
        $workPackageFilter = $request->query->get('wp', '');
        $yearFilter = $request->query->get('year', '');
        $partenaireFilter = $request->query->get('partner', '');
        
        // Récupérer toutes les entreprises
        $entreprises = $entrepriseRepository->findAll();
        
        // Grouper les catégories par entreprise
        $entreprisesGrouped = [];
        foreach ($entreprises as $entreprise) {
            // Filtrer par partenaire si spécifié
            if ($partenaireFilter && $entreprise->getProprietaire() && $entreprise->getProprietaire()->getPartnaireId() !== $partenaireFilter) {
                continue;
            }
            
            // Filtrer par recherche textuelle
            if ($searchQuery && stripos($entreprise->getNom(), $searchQuery) === false) {
                continue;
            }
            
            $categories = [];
            
            // WP2
            if ($entreprise->getWp2()) {
                $shouldInclude = !$workPackageFilter || $workPackageFilter === 'wp2';
                if ($shouldInclude) {
                    $categories[] = [
                        'categorie' => 'WP2',
                        'data' => $entreprise->getWp2(),
                    ];
                }
            }
            
            // WP5 Events
            foreach ($entreprise->getWp5Events() as $wp5Event) {
                $shouldInclude = !$workPackageFilter || $workPackageFilter === 'wp5';
                $matchYear = !$yearFilter || ($wp5Event->getYear() && $wp5Event->getYear() == $yearFilter);
                
                if ($shouldInclude && $matchYear) {
                    $categories[] = [
                        'categorie' => 'WP5 Event',
                        'data' => $wp5Event,
                    ];
                }
            }
            
            // WP5 Formations
            foreach ($entreprise->getWp5Formations() as $wp5Formation) {
                $shouldInclude = !$workPackageFilter || $workPackageFilter === 'wp5';
                $matchYear = !$yearFilter || 
                    ($wp5Formation->getStartDate() && $wp5Formation->getStartDate()->format('Y') == $yearFilter) ||
                    ($wp5Formation->getFinishDate() && $wp5Formation->getFinishDate()->format('Y') == $yearFilter);
                
                if ($shouldInclude && $matchYear) {
                    $categories[] = [
                        'categorie' => 'WP5 Formation',
                        'data' => $wp5Formation,
                    ];
                }
            }
            
            // WP6
            foreach ($entreprise->getWp6() as $wp6) {
                $shouldInclude = !$workPackageFilter || $workPackageFilter === 'wp6';
                $matchYear = !$yearFilter || 
                    ($wp6->getStartDate() && $wp6->getStartDate()->format('Y') == $yearFilter) ||
                    ($wp6->getFinishDate() && $wp6->getFinishDate()->format('Y') == $yearFilter);
                
                if ($shouldInclude && $matchYear) {
                    $categories[] = [
                        'categorie' => 'WP6',
                        'data' => $wp6,
                    ];
                }
            }
            
            // WP7
            foreach ($entreprise->getWp7() as $wp7) {
                $shouldInclude = !$workPackageFilter || $workPackageFilter === 'wp7';
                $matchYear = !$yearFilter || 
                    ($wp7->getStartDate() && $wp7->getStartDate()->format('Y') == $yearFilter) ||
                    ($wp7->getFinishDate() && $wp7->getFinishDate()->format('Y') == $yearFilter);
                
                if ($shouldInclude && $matchYear) {
                    $categories[] = [
                        'categorie' => 'WP7',
                        'data' => $wp7,
                    ];
                }
            }
            
            // Mise en Relation
            foreach ($entreprise->getMiseEnRelations() as $miseEnRelation) {
                $shouldInclude = !$workPackageFilter; // Pas de filtre WP spécifique pour mise en relation
                $matchYear = !$yearFilter || 
                    ($miseEnRelation->getStartDate() && $miseEnRelation->getStartDate()->format('Y') == $yearFilter) ||
                    ($miseEnRelation->getFinishDate() && $miseEnRelation->getFinishDate()->format('Y') == $yearFilter);
                
                if ($shouldInclude && $matchYear) {
                    $categories[] = [
                        'categorie' => 'Mise en Relation',
                        'data' => $miseEnRelation,
                    ];
                }
            }
            
            if (!empty($categories)) {
                $entreprisesGrouped[] = [
                    'entreprise' => $entreprise,
                    'categories' => $categories,
                ];
            }
        }
        
        return $this->render('administration/index.html.twig', [
            'currentYear' => $currentYear,
            'partenaires' => $nomsPartenaires,
            'entreprises' => $entreprises,
            'entreprisesGrouped' => $entreprisesGrouped,
            'searchQuery' => $searchQuery,
            'workPackageFilter' => $workPackageFilter,
            'yearFilter' => $yearFilter,
            'partenaireFilter' => $partenaireFilter,
        ]);
    }

    #[Route(path: [
        'fr' => '/administration/statistiques',
        'en' => '/administration/statistics'
    ], name: 'app_administration_statistics')]
    public function statistics(): Response
    {
        return $this->render('administration/statistics.html.twig');
    }

    #[Route(path: [
        'fr' => '/administration/creer-entreprise',
        'en' => '/administration/create-company'
    ], name: 'app_administration_create_entreprise', methods: ['GET', 'POST'])]
    public function createEntreprise(Request $request, EntityManagerInterface $entityManager, PartenaireJsonService $partenaireService): Response
    {
        if ($request->isMethod('POST')) {
            // Assigner automatiquement l'utilisateur connecté comme propriétaire
            $proprietaire = $this->getUser();
            
            if (!$proprietaire) {
                $this->addFlash('error', 'Vous devez être connecté pour créer une entreprise.');
                return $this->redirectToRoute('app_login');
            }

            // Pour les administrateurs, permettre de choisir un partenaire différent
            $partenaireIdOverride = null;
            if ($this->isGranted('ROLE_ADMINISTRATEUR')) {
                $partenaireIdOverride = $request->request->get('partenaire_id');
            }
            
            // Vérifier que l'utilisateur a un partenaire, sauf si un admin override est fourni
            if (!$proprietaire->getPartnaireId() && !$partenaireIdOverride) {
                $this->addFlash('error', 'Vous devez être relié à un partenaire pour créer une entreprise.');
                $partenaires = $partenaireService->findAll();
                return $this->render('administration/create_entreprise.html.twig', ['partenaires' => $partenaires]);
            }

            // Créer l'entreprise principale
            $entreprise = new Entreprise();
            $entreprise->setNom($request->request->get('nom'));
            
            // Si admin a choisi un partenaire différent, modifier temporairement le partenaire du propriétaire
            $partenaireOriginal = $proprietaire->getPartnaireId();
            if ($partenaireIdOverride) {
                $proprietaire->setPartnaireId($partenaireIdOverride);
            }
            
            $entreprise->setProprietaire($proprietaire);
            
            // Gérer la date EDIH (format date complet au lieu d'une année)
            $anneeEdihStr = $request->request->get('annee_edih');
            if ($anneeEdihStr) {
                try {
                    $anneeEdihDate = new \DateTime($anneeEdihStr);
                    $entreprise->setAnneeEdih($anneeEdihDate->format('Y-m-d'));
                } catch (\Exception $e) {
                    $entreprise->setAnneeEdih(null);
                }
            } else {
                $entreprise->setAnneeEdih(null);
            }
            
            $entreprise->setTypeStructure($request->request->get('type_structure'));
            $entreprise->setAnneeCreation((int)$request->request->get('annee_creation'));
            $entreprise->setSecteur($request->request->get('secteur'));
            $entreprise->setSiret($request->request->get('siret'));
            $entreprise->setTaille($request->request->get('taille'));
            $entreprise->setChiffreAffaires($request->request->get('chiffre_affaires'));
            $entreprise->setCodePostal($request->request->get('codepostal'));
            $entreprise->setVille($request->request->get('ville'));
            $entreprise->setRegion($request->request->get('region'));
            $entreprise->setPays($request->request->get('pays'));
            $entreprise->setAdresse($request->request->get('adresse'));
            $entreprise->setDescription($request->request->get('description'));
            $entreprise->setCreatedAt(new \DateTime());

            $categories = $request->request->all('categories');

            // WP2
            if (in_array('wp2', $categories)) {
                $wp2 = new EntrepriseWp2();
                $wp2->setScoreDmao($request->request->get('wp2_score_dmao'));
                $wp2->setDigitalStrategy($request->request->get('wp2_digital_strategy'));
                $wp2->setDigitalReadiness($request->request->get('wp2_digital_readiness'));
                $wp2->setHumanCentric($request->request->get('wp2_human_centric'));
                $wp2->setDataGovernance($request->request->get('wp2_data_governance'));
                $wp2->setAi($request->request->get('wp2_ai'));
                $wp2->setGreen($request->request->get('wp2_green'));
                $wp2->setScoreDma1($request->request->get('wp2_score_dma1'));
                $wp2->setEntreprise($entreprise);
                $entreprise->setWp2($wp2);
                $entityManager->persist($wp2);
            }

            // WP5 Event
            if (in_array('wp5_event', $categories)) {
                $wp5Event = new EntrepriseWp5Event();
                $wp5Event->setYear($request->request->get('wp5_event_year') ? (int)$request->request->get('wp5_event_year') : null);
                $wp5Event->setPassage($request->request->get('wp5_event_passage'));
                $wp5Event->setResponsableWp5($request->request->get('wp5_event_responsible_wp5'));
                $wp5Event->setResponsableWp4($request->request->get('wp5_event_responsible_wp4'));
                $wp5Event->setNeedWp5($request->request->get('wp5_event_need_wp5'));
                $wp5Event->setActionWp5($request->request->get('wp5_event_action_wp5'));
                $wp5Event->setEntreprise($entreprise);
                $entityManager->persist($wp5Event);
            }

            // WP5 Formation
            if (in_array('wp5_formation', $categories)) {
                $wp5Formation = new EntrepriseWp5Formation();
                $wp5Formation->setResponsible($request->request->get('wp5_formation_responsible'));
                $wp5Formation->setTechnology($request->request->get('wp5_formation_technology'));
                $wp5Formation->setServicePrice($request->request->get('wp5_formation_service_price'));
                $wp5Formation->setPriceInvoiced($request->request->get('wp5_formation_price_invoiced'));
                
                if ($request->request->get('wp5_formation_start_date')) {
                    $wp5Formation->setStartDate(new \DateTime($request->request->get('wp5_formation_start_date')));
                }
                if ($request->request->get('wp5_formation_finish_date')) {
                    $wp5Formation->setFinishDate(new \DateTime($request->request->get('wp5_formation_finish_date')));
                }
                
                $wp5Formation->setEntreprise($entreprise);
                $entityManager->persist($wp5Formation);
            }

            // WP6
            if (in_array('wp6', $categories)) {
                $wp6 = new EntrepriseWp6();
                $wp6->setResponsible($request->request->get('wp6_responsible'));
                $wp6->setTechnology($request->request->get('wp6_technology'));
                $wp6->setServicePrice($request->request->get('wp6_service_price'));
                $wp6->setPriceInvoiced($request->request->get('wp6_price_invoiced'));
                
                if ($request->request->get('wp6_start_date')) {
                    $wp6->setStartDate(new \DateTime($request->request->get('wp6_start_date')));
                }
                if ($request->request->get('wp6_finish_date')) {
                    $wp6->setFinishDate(new \DateTime($request->request->get('wp6_finish_date')));
                }
                
                $wp6->setEntreprise($entreprise);
                $entityManager->persist($wp6);
            }

            // WP7
            if (in_array('wp7', $categories)) {
                $wp7 = new EntrepriseWp7();
                $wp7->setResponsible($request->request->get('wp7_responsible'));
                $wp7->setAmountTrigger($request->request->get('wp7_amount_trigger'));
                $wp7->setTypeInvestment($request->request->get('wp7_type_investment'));
                $wp7->setSourceFinancing($request->request->get('wp7_source_financing'));
                $wp7->setAmountObtained($request->request->get('wp7_amount_obtained'));
                $wp7->setServicePrice($request->request->get('wp7_service_price'));
                $wp7->setPriceInvoiced($request->request->get('wp7_price_invoiced'));
                
                if ($request->request->get('wp7_start_date')) {
                    $wp7->setStartDate(new \DateTime($request->request->get('wp7_start_date')));
                }
                if ($request->request->get('wp7_finish_date')) {
                    $wp7->setFinishDate(new \DateTime($request->request->get('wp7_finish_date')));
                }
                
                $wp7->setEntreprise($entreprise);
                $entityManager->persist($wp7);
            }

            // Mise en Relation
            if (in_array('relation', $categories)) {
                $relation = new EntrepriseMiseEnRelation();
                $relation->setResponsible($request->request->get('relation_responsible'));
                $relation->setTechnology($request->request->get('relation_technology'));
                $relation->setServicePrice($request->request->get('relation_service_price'));
                $relation->setPriceInvoiced($request->request->get('relation_price_invoiced'));
                
                if ($request->request->get('relation_start_date')) {
                    $relation->setStartDate(new \DateTime($request->request->get('relation_start_date')));
                }
                if ($request->request->get('relation_finish_date')) {
                    $relation->setFinishDate(new \DateTime($request->request->get('relation_finish_date')));
                }
                
                $relation->setEntreprise($entreprise);
                $entityManager->persist($relation);
            }

            // Persister l'entreprise
            $entityManager->persist($entreprise);
            $entityManager->flush();
            
            // Restaurer le partenaire original si modifié temporairement
            if ($partenaireIdOverride && $partenaireOriginal !== $partenaireIdOverride) {
                $proprietaire->setPartnaireId($partenaireOriginal);
                $entityManager->flush();
            }

            // Ajouter un message flash de succès
            $this->addFlash('success', 'L\'entreprise a été créée avec succès.');

            // Redirection obligatoire pour Turbo
            return $this->redirectToRoute('app_administration_dashboard');
        }

        // Récupérer les partenaires pour la liste déroulante (visible uniquement pour les admins)
        $partenaires = $partenaireService->findAll();

        return $this->render('administration/create_entreprise.html.twig', [
            'partenaires' => $partenaires,
        ]);
    }
}
