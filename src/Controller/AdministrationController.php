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
use Symfony\Component\HttpFoundation\JsonResponse;
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
        
        // Récupérer les filtres
        $wpFilters = $request->query->all('wp') ?? [];
        $yearFilter = $request->query->get('year');
        $partnerFilter = $request->query->get('partner');
        
        // Récupérer toutes les entreprises de l'utilisateur connecté
        $user = $this->getUser();
        $entreprises = [];
        
        if ($user) {
            // Si admin, afficher toutes les entreprises, sinon seulement celles de l'utilisateur
            if ($this->isGranted('ROLE_ADMINISTRATEUR')) {
                $entreprises = $entrepriseRepository->findAll();
            } else {
                $entreprises = $entrepriseRepository->findBy(['proprietaire' => $user]);
            }
            
            // Appliquer les filtres
            if (!empty($wpFilters) || $yearFilter || $partnerFilter) {
                $entreprises = array_filter($entreprises, function($entreprise) use ($wpFilters, $yearFilter, $partnerFilter, $user) {
                    // Filtre WP
                    if (!empty($wpFilters)) {
                        $hasWp = false;
                        foreach ($wpFilters as $wp) {
                            if ($wp === 'wp2' && $entreprise->getWp2() !== null) {
                                $hasWp = true;
                                break;
                            } elseif ($wp === 'wp5_event' && !$entreprise->getWp5Events()->isEmpty()) {
                                $hasWp = true;
                                break;
                            } elseif ($wp === 'wp5_formation' && !$entreprise->getWp5Formations()->isEmpty()) {
                                $hasWp = true;
                                break;
                            } elseif ($wp === 'wp6' && !$entreprise->getWp6()->isEmpty()) {
                                $hasWp = true;
                                break;
                            } elseif ($wp === 'wp7' && !$entreprise->getWp7()->isEmpty()) {
                                $hasWp = true;
                                break;
                            } elseif ($wp === 'mise_en_relation' && !$entreprise->getMiseEnRelations()->isEmpty()) {
                                $hasWp = true;
                                break;
                            }
                        }
                        if (!$hasWp) return false;
                    }
                    
                    // Filtre année
                    if ($yearFilter) {
                        $anneeCreation = $entreprise->getAnneeCreation();
                        if ($anneeCreation != $yearFilter) return false;
                    }
                    
                    // Filtre partenaire (seulement pour admins)
                    if ($partnerFilter && $this->isGranted('ROLE_ADMINISTRATEUR')) {
                        $proprietaire = $entreprise->getProprietaire();
                        if (!$proprietaire || $proprietaire->getPartenaireId() != $partnerFilter) {
                            return false;
                        }
                    }
                    
                    return true;
                });
            }
        }
        
        return $this->render('administration/index.html.twig', [
            'currentYear' => $currentYear,
            'partenaires' => $partenaires,
            'entreprises' => $entreprises,
        ]);
    }

    #[Route(path: [
        'fr' => '/administration/statistiques',
        'en' => '/administration/statistics'
    ], name: 'app_administration_statistics')]
    public function statistics(TimeService $timeService, PartenaireJsonService $partenaireService, EntrepriseRepository $entrepriseRepository): Response
    {
        $currentYear = $timeService->getCurrentYear();
        $partenaires = $partenaireService->findAll();
        
        // Récupérer toutes les entreprises selon le rôle
        $user = $this->getUser();
        $entreprises = [];
        
        if ($user) {
            if ($this->isGranted('ROLE_ADMINISTRATEUR')) {
                $entreprises = $entrepriseRepository->findAll();
            } else {
                $entreprises = $entrepriseRepository->findBy(['proprietaire' => $user]);
            }
        }
        
        return $this->render('administration/statistics.html.twig', [
            'currentYear' => $currentYear,
            'partenaires' => $partenaires,
            'entreprises' => $entreprises,
        ]);
    }

    #[Route(path: [
        'fr' => '/administration/entreprise/{id}/statistiques',
        'en' => '/administration/company/{id}/statistics'
    ], name: 'app_administration_entreprise_statistics')]
    public function entrepriseStatistics(Entreprise $entreprise): Response
    {
        // Vérifier que l'utilisateur a le droit de voir ces statistiques
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }
        
        // Si pas admin, vérifier que l'entreprise appartient à l'utilisateur
        if (!$this->isGranted('ROLE_ADMINISTRATEUR')) {
            if ($entreprise->getProprietaire() !== $user) {
                throw $this->createAccessDeniedException('Vous n\'avez pas accès à ces statistiques.');
            }
        }
        
        return $this->render('administration/entreprise_statistics.html.twig', [
            'entreprise' => $entreprise,
        ]);
    }

    #[Route(path: [
        'fr' => '/administration/creer-entreprise',
        'en' => '/administration/create-company'
    ], name: 'app_administration_create_entreprise', methods: ['GET', 'POST'])]
    public function createEntreprise(Request $request, EntityManagerInterface $entityManager, PartenaireJsonService $partenaireService): Response
    {
        if ($request->isMethod('POST')) {
            // Validate CSRF token
            $submittedToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('entreprise_form', $submittedToken)) {
                $this->addFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
                return $this->redirectToRoute('app_administration_create_entreprise');
            }

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
                return $this->redirectToRoute('app_administration_create_entreprise');
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
            $entreprise->setStatut($request->request->get('statut') ?: 'vert');
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
                
                // Nouveaux champs
                $wp5Event->setEventNameEnglish($request->request->get('wp5_event_name_english'));
                $wp5Event->setEventNameOriginal($request->request->get('wp5_event_name_original'));
                $wp5Event->setEdihCoOrganiser($request->request->get('wp5_event_edih_organiser'));
                $wp5Event->setCoOrganiser($request->request->get('wp5_event_co_organiser'));
                
                if ($request->request->get('wp5_event_start_date')) {
                    $wp5Event->setStartDate(new \DateTime($request->request->get('wp5_event_start_date')));
                }
                if ($request->request->get('wp5_event_end_date')) {
                    $wp5Event->setEndDate(new \DateTime($request->request->get('wp5_event_end_date')));
                }
                
                $wp5Event->setAttendeesNumber($request->request->get('wp5_event_attendees') ? (int)$request->request->get('wp5_event_attendees') : null);
                $wp5Event->setDeliveryMode($request->request->get('wp5_event_delivery_mode'));
                $wp5Event->setWebsiteUrl($request->request->get('wp5_event_website'));
                $wp5Event->setMainTechnologies($request->request->get('wp5_event_technologies'));
                $wp5Event->setServiceCategory($request->request->get('wp5_event_service_category'));
                $wp5Event->setMainSectors($request->request->get('wp5_event_sectors'));
                $wp5Event->setEventDescription($request->request->get('wp5_event_description'));
                
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

    #[Route(path: [
        'fr' => '/administration/modifier-entreprise/{id}',
        'en' => '/administration/edit-company/{id}'
    ], name: 'app_administration_edit_entreprise', methods: ['GET', 'POST'])]
    public function editEntreprise(int $id, Request $request, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRepository, PartenaireJsonService $partenaireService): Response
    {
        $entreprise = $entrepriseRepository->find($id);
        
        if (!$entreprise) {
            $this->addFlash('error', 'Entreprise introuvable.');
            return $this->redirectToRoute('app_administration_dashboard');
        }

        // Vérification de propriété : seuls les admins ou les users du même partenaire peuvent modifier
        $currentUser = $this->getUser();
        if (!$this->isGranted('ROLE_ADMINISTRATEUR')) {
            $proprietaire = $entreprise->getProprietaire();
            if (!$proprietaire || $proprietaire->getPartnaireId() !== $currentUser->getPartnaireId()) {
                $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cette entreprise.');
                return $this->redirectToRoute('app_administration_dashboard');
            }
        }

        if ($request->isMethod('POST')) {
            // Validate CSRF token
            $submittedToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('entreprise_form', $submittedToken)) {
                $this->addFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
                return $this->redirectToRoute('app_administration_edit_entreprise', ['id' => $id]);
            }

            $action = $request->request->get('action');
            
            // Gestion de la suppression d'un work package
            if ($action === 'delete_wp') {
                $wpType = $request->request->get('wp_type');
                $wpId = $request->request->get('wp_id');
                
                $this->deleteWorkPackage($wpType, $wpId, $entityManager);
                $this->addFlash('success', 'Work package supprimé avec succès.');
                return $this->redirectToRoute('app_administration_edit_entreprise', ['id' => $id]);
            }
            
            // Gestion de l'ajout d'un nouveau work package
            if ($action === 'add_wp' || $action === 'create_wp') {
                $wpType = $request->request->get('wp_type');
                $this->addWorkPackage($wpType, $entreprise, $request, $entityManager);
                $this->addFlash('success', 'Work package ajouté avec succès.');
                return $this->redirectToRoute('app_administration_edit_entreprise', ['id' => $id]);
            }
            
            // Mise à jour de l'entreprise principale
            if ($action === 'update_entreprise') {
                if ($this->isGranted('ROLE_ADMINISTRATEUR')) {
                    $partenaireId = $request->request->get('partenaire_id');
                    if ($partenaireId) {
                        $proprietaire = $entreprise->getProprietaire();
                        if ($proprietaire) {
                            $proprietaire->setPartnaireId($partenaireId);
                        }
                    }
                }

                $entreprise->setNom($request->request->get('nom'));
                $entreprise->setTypeStructure($request->request->get('type_structure'));
                $entreprise->setAnneeCreation((int)$request->request->get('annee_creation'));
                $entreprise->setSecteur($request->request->get('secteur'));
                $entreprise->setStatut($request->request->get('statut') ?: 'vert');
                $entreprise->setSiret($request->request->get('siret'));
                $entreprise->setTaille($request->request->get('taille'));
                $entreprise->setChiffreAffaires($request->request->get('chiffre_affaires'));
                $entreprise->setCodePostal($request->request->get('codepostal'));
                $entreprise->setVille($request->request->get('ville'));
                $entreprise->setRegion($request->request->get('region'));
                $entreprise->setPays($request->request->get('pays'));
                $entreprise->setAdresse($request->request->get('adresse'));
                $entreprise->setDescription($request->request->get('description'));
                
                // Gérer la date EDIH
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

                $entityManager->flush();
                $this->addFlash('success', 'Entreprise mise à jour avec succès.');
                return $this->redirectToRoute('app_administration_edit_entreprise', ['id' => $id]);
            }
            
            // Mise à jour d'un work package existant
            if ($action === 'update_wp') {
                $wpType = $request->request->get('wp_type');
                $wpId = $request->request->get('wp_id');
                
                $this->updateWorkPackage($wpType, $wpId, $request, $entityManager);
                $this->addFlash('success', 'Work package mis à jour avec succès.');
                return $this->redirectToRoute('app_administration_edit_entreprise', ['id' => $id]);
            }
        }

        // Récupérer les partenaires pour la liste déroulante
        $partenaires = $partenaireService->findAll();

        return $this->render('administration/edit_entreprise.html.twig', [
            'entreprise' => $entreprise,
            'partenaires' => $partenaires,
        ]);
    }

    #[Route(path: [
        'fr' => '/administration/supprimer-entreprise/{id}',
        'en' => '/administration/delete-company/{id}'
    ], name: 'app_administration_delete_entreprise', methods: ['POST'])]
    public function deleteEntreprise(int $id, Request $request, EntrepriseRepository $entrepriseRepository, EntityManagerInterface $entityManager): Response
    {
        $entreprise = $entrepriseRepository->find($id);
        
        if (!$entreprise) {
            $this->addFlash('error', 'Entreprise introuvable.');
            return $this->redirectToRoute('app_administration_dashboard');
        }

        // Vérification de propriété : seuls les admins ou les users du même partenaire peuvent supprimer
        $currentUser = $this->getUser();
        if (!$this->isGranted('ROLE_ADMINISTRATEUR')) {
            $proprietaire = $entreprise->getProprietaire();
            if (!$proprietaire || $proprietaire->getPartnaireId() !== $currentUser->getPartnaireId()) {
                $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cette entreprise.');
                return $this->redirectToRoute('app_administration_dashboard');
            }
        }

        // Valider le token CSRF
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_entreprise_' . $id, $submittedToken)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_administration_dashboard');
        }

        // Supprimer l'entreprise (cascade supprimera les work packages associés)
        $entityManager->remove($entreprise);
        $entityManager->flush();

        $this->addFlash('success', 'L\'entreprise a été supprimée avec succès.');
        return $this->redirectToRoute('app_administration_dashboard');
    }

    private function deleteWorkPackage(string $wpType, int $wpId, EntityManagerInterface $entityManager): void
    {
        $entity = null;
        switch ($wpType) {
            case 'wp2':
                $entity = $entityManager->getRepository(EntrepriseWp2::class)->find($wpId);
                break;
            case 'wp5_event':
                $entity = $entityManager->getRepository(EntrepriseWp5Event::class)->find($wpId);
                break;
            case 'wp5_formation':
                $entity = $entityManager->getRepository(EntrepriseWp5Formation::class)->find($wpId);
                break;
            case 'wp6':
                $entity = $entityManager->getRepository(EntrepriseWp6::class)->find($wpId);
                break;
            case 'wp7':
                $entity = $entityManager->getRepository(EntrepriseWp7::class)->find($wpId);
                break;
            case 'relation':
                $entity = $entityManager->getRepository(EntrepriseMiseEnRelation::class)->find($wpId);
                break;
        }
        
        if ($entity) {
            $entityManager->remove($entity);
            $entityManager->flush();
        }
    }

    private function addWorkPackage(string $wpType, Entreprise $entreprise, Request $request, EntityManagerInterface $entityManager): void
    {
        switch ($wpType) {
            case 'wp2':
                if (!$entreprise->getWp2()) {
                    $wp2 = new EntrepriseWp2();
                    $wp2->setEntreprise($entreprise);
                    $wp2->setScoreDmao($request->request->get('score_dmao'));
                    $wp2->setDigitalStrategy($request->request->get('digital_strategy'));
                    $wp2->setDigitalReadiness($request->request->get('digital_readiness'));
                    $wp2->setHumanCentric($request->request->get('human_centric'));
                    $wp2->setDataGovernance($request->request->get('data_governance'));
                    $wp2->setAi($request->request->get('ai'));
                    $wp2->setGreen($request->request->get('green'));
                    $wp2->setScoreDma1($request->request->get('score_dma1'));
                    $entityManager->persist($wp2);
                }
                break;
                
            case 'wp5_event':
                $wp5Event = new EntrepriseWp5Event();
                $wp5Event->setEntreprise($entreprise);
                $wp5Event->setEventNameEnglish($request->request->get('event_name_english'));
                $wp5Event->setEventNameOriginal($request->request->get('event_name_original'));
                $wp5Event->setEdihCoOrganiser($request->request->get('edih_organiser'));
                $wp5Event->setCoOrganiser($request->request->get('co_organiser'));
                if ($request->request->get('start_date')) {
                    $wp5Event->setStartDate(new \DateTime($request->request->get('start_date')));
                }
                if ($request->request->get('end_date')) {
                    $wp5Event->setEndDate(new \DateTime($request->request->get('end_date')));
                }
                $wp5Event->setAttendeesNumber($request->request->get('attendees') ? (int)$request->request->get('attendees') : null);
                $wp5Event->setDeliveryMode($request->request->get('delivery_mode'));
                $wp5Event->setWebsiteUrl($request->request->get('website'));
                $wp5Event->setMainTechnologies($request->request->get('technologies'));
                $wp5Event->setServiceCategory($request->request->get('service_category'));
                $wp5Event->setMainSectors($request->request->get('sectors'));
                $wp5Event->setEventDescription($request->request->get('description'));
                $entityManager->persist($wp5Event);
                break;
                
            case 'wp5_formation':
                $wp5Formation = new EntrepriseWp5Formation();
                $wp5Formation->setEntreprise($entreprise);
                $wp5Formation->setResponsible($request->request->get('responsible'));
                $wp5Formation->setTechnology($request->request->get('technology'));
                $wp5Formation->setServicePrice($request->request->get('service_price'));
                $wp5Formation->setPriceInvoiced($request->request->get('price_invoiced'));
                if ($request->request->get('start_date')) {
                    $wp5Formation->setStartDate(new \DateTime($request->request->get('start_date')));
                }
                if ($request->request->get('finish_date')) {
                    $wp5Formation->setFinishDate(new \DateTime($request->request->get('finish_date')));
                }
                $entityManager->persist($wp5Formation);
                break;
                
            case 'wp6':
                $wp6 = new EntrepriseWp6();
                $wp6->setEntreprise($entreprise);
                $wp6->setResponsible($request->request->get('responsible'));
                $wp6->setTechnology($request->request->get('technology'));
                $wp6->setServicePrice($request->request->get('service_price'));
                $wp6->setPriceInvoiced($request->request->get('price_invoiced'));
                if ($request->request->get('start_date')) {
                    $wp6->setStartDate(new \DateTime($request->request->get('start_date')));
                }
                if ($request->request->get('finish_date')) {
                    $wp6->setFinishDate(new \DateTime($request->request->get('finish_date')));
                }
                $entityManager->persist($wp6);
                break;
                
            case 'wp7':
                $wp7 = new EntrepriseWp7();
                $wp7->setEntreprise($entreprise);
                $wp7->setResponsible($request->request->get('responsible'));
                $wp7->setAmountTrigger($request->request->get('amount_trigger'));
                $wp7->setTypeInvestment($request->request->get('type_investment'));
                $wp7->setSourceFinancing($request->request->get('source_financing'));
                $wp7->setAmountObtained($request->request->get('amount_obtained'));
                $wp7->setServicePrice($request->request->get('service_price'));
                $wp7->setPriceInvoiced($request->request->get('price_invoiced'));
                if ($request->request->get('start_date')) {
                    $wp7->setStartDate(new \DateTime($request->request->get('start_date')));
                }
                if ($request->request->get('finish_date')) {
                    $wp7->setFinishDate(new \DateTime($request->request->get('finish_date')));
                }
                $entityManager->persist($wp7);
                break;
                
            case 'relation':
                $relation = new EntrepriseMiseEnRelation();
                $relation->setEntreprise($entreprise);
                $relation->setResponsible($request->request->get('responsible'));
                $relation->setTechnology($request->request->get('technology'));
                $relation->setServicePrice($request->request->get('service_price'));
                $relation->setPriceInvoiced($request->request->get('price_invoiced'));
                if ($request->request->get('start_date')) {
                    $relation->setStartDate(new \DateTime($request->request->get('start_date')));
                }
                if ($request->request->get('finish_date')) {
                    $relation->setFinishDate(new \DateTime($request->request->get('finish_date')));
                }
                $entityManager->persist($relation);
                break;
        }
        
        $entityManager->flush();
    }

    private function updateWorkPackage(string $wpType, int $wpId, Request $request, EntityManagerInterface $entityManager): void
    {
        $entity = null;
        
        switch ($wpType) {
            case 'wp2':
                $entity = $entityManager->getRepository(EntrepriseWp2::class)->find($wpId);
                if ($entity) {
                    $entity->setScoreDmao($request->request->get('score_dmao'));
                    $entity->setDigitalStrategy($request->request->get('digital_strategy'));
                    $entity->setDigitalReadiness($request->request->get('digital_readiness'));
                    $entity->setHumanCentric($request->request->get('human_centric'));
                    $entity->setDataGovernance($request->request->get('data_governance'));
                    $entity->setAi($request->request->get('ai'));
                    $entity->setGreen($request->request->get('green'));
                    $entity->setScoreDma1($request->request->get('score_dma1'));
                }
                break;
                
            case 'wp5_event':
                $entity = $entityManager->getRepository(EntrepriseWp5Event::class)->find($wpId);
                if ($entity) {
                    $entity->setEventNameEnglish($request->request->get('event_name_english'));
                    $entity->setEventNameOriginal($request->request->get('event_name_original'));
                    $entity->setEdihCoOrganiser($request->request->get('edih_organiser'));
                    $entity->setCoOrganiser($request->request->get('co_organiser'));
                    if ($request->request->get('start_date')) {
                        $entity->setStartDate(new \DateTime($request->request->get('start_date')));
                    }
                    if ($request->request->get('end_date')) {
                        $entity->setEndDate(new \DateTime($request->request->get('end_date')));
                    }
                    $entity->setAttendeesNumber($request->request->get('attendees') ? (int)$request->request->get('attendees') : null);
                    $entity->setDeliveryMode($request->request->get('delivery_mode'));
                    $entity->setWebsiteUrl($request->request->get('website'));
                    $entity->setMainTechnologies($request->request->get('technologies'));
                    $entity->setServiceCategory($request->request->get('service_category'));
                    $entity->setMainSectors($request->request->get('sectors'));
                    $entity->setEventDescription($request->request->get('description'));
                }
                break;
                
            case 'wp5_formation':
                $entity = $entityManager->getRepository(EntrepriseWp5Formation::class)->find($wpId);
                if ($entity) {
                    $entity->setResponsible($request->request->get('responsible'));
                    $entity->setTechnology($request->request->get('technology'));
                    $entity->setServicePrice($request->request->get('service_price'));
                    $entity->setPriceInvoiced($request->request->get('price_invoiced'));
                    if ($request->request->get('start_date')) {
                        $entity->setStartDate(new \DateTime($request->request->get('start_date')));
                    }
                    if ($request->request->get('finish_date')) {
                        $entity->setFinishDate(new \DateTime($request->request->get('finish_date')));
                    }
                }
                break;
                
            case 'wp6':
                $entity = $entityManager->getRepository(EntrepriseWp6::class)->find($wpId);
                if ($entity) {
                    $entity->setResponsible($request->request->get('responsible'));
                    $entity->setTechnology($request->request->get('technology'));
                    $entity->setServicePrice($request->request->get('service_price'));
                    $entity->setPriceInvoiced($request->request->get('price_invoiced'));
                    if ($request->request->get('start_date')) {
                        $entity->setStartDate(new \DateTime($request->request->get('start_date')));
                    }
                    if ($request->request->get('finish_date')) {
                        $entity->setFinishDate(new \DateTime($request->request->get('finish_date')));
                    }
                }
                break;
                
            case 'wp7':
                $entity = $entityManager->getRepository(EntrepriseWp7::class)->find($wpId);
                if ($entity) {
                    $entity->setResponsible($request->request->get('responsible'));
                    $entity->setAmountTrigger($request->request->get('amount_trigger'));
                    $entity->setTypeInvestment($request->request->get('type_investment'));
                    $entity->setSourceFinancing($request->request->get('source_financing'));
                    $entity->setAmountObtained($request->request->get('amount_obtained'));
                    $entity->setServicePrice($request->request->get('service_price'));
                    $entity->setPriceInvoiced($request->request->get('price_invoiced'));
                    if ($request->request->get('start_date')) {
                        $entity->setStartDate(new \DateTime($request->request->get('start_date')));
                    }
                    if ($request->request->get('finish_date')) {
                        $entity->setFinishDate(new \DateTime($request->request->get('finish_date')));
                    }
                }
                break;
                
            case 'relation':
                $entity = $entityManager->getRepository(EntrepriseMiseEnRelation::class)->find($wpId);
                if ($entity) {
                    $entity->setResponsible($request->request->get('responsible'));
                    $entity->setTechnology($request->request->get('technology'));
                    $entity->setServicePrice($request->request->get('service_price'));
                    $entity->setPriceInvoiced($request->request->get('price_invoiced'));
                    if ($request->request->get('start_date')) {
                        $entity->setStartDate(new \DateTime($request->request->get('start_date')));
                    }
                    if ($request->request->get('finish_date')) {
                        $entity->setFinishDate(new \DateTime($request->request->get('finish_date')));
                    }
                }
                break;
        }
        
        if ($entity) {
            $entityManager->flush();
        }
    }

    #[Route(path: [
        'fr' => '/administration/api/entreprises',
        'en' => '/administration/api/companies'
    ], name: 'app_administration_api_entreprises', methods: ['GET'])]
    public function getEntreprises(Request $request, EntrepriseRepository $entrepriseRepository, PartenaireJsonService $partenaireService): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = 10;
        $search = trim($request->query->get('search', ''));
        $partenaireFilter = trim($request->query->get('partenaire', ''));
        $yearFilter = trim($request->query->get('year', ''));
        $wpFilter = trim($request->query->get('wp', ''));
        
        // Charger tous les partenaires depuis le JSON
        $partenairesData = $partenaireService->findAll();
        $partenairesMap = [];
        foreach ($partenairesData as $p) {
            if (isset($p['id'])) {
                $partenairesMap[$p['id']] = $p['nom'] ?? 'N/A';
            }
        }
        
        // Récupérer toutes les entreprises
        $qb = $entrepriseRepository->createQueryBuilder('e')
            ->leftJoin('e.proprietaire', 'u')
            ->addSelect('u');
        
        // Filtrer par recherche
        if (!empty($search)) {
            $qb->andWhere('e.nom LIKE :search OR e.ville LIKE :search OR e.secteur LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        
        // Filtrer par partenaire
        if (!empty($partenaireFilter)) {
            $qb->andWhere('u.partnaireId = :partenaireId')
               ->setParameter('partenaireId', $partenaireFilter);
        }
        
        // Filtrer par année
        if (!empty($yearFilter)) {
            $qb->andWhere('YEAR(e.createdAt) = :year')
               ->setParameter('year', $yearFilter);
        }
        
        // Filtrer par work package
        if (!empty($wpFilter)) {
            switch ($wpFilter) {
                case 'wp2':
                    $qb->innerJoin('e.wp2', 'wp2');
                    break;
                case 'wp5':
                    $qb->leftJoin('e.wp5Events', 'wp5e')
                       ->leftJoin('e.wp5Formations', 'wp5f')
                       ->andWhere('wp5e.id IS NOT NULL OR wp5f.id IS NOT NULL');
                    break;
                case 'wp6':
                    $qb->innerJoin('e.wp6', 'wp6');
                    break;
                case 'wp7':
                    $qb->innerJoin('e.wp7', 'wp7');
                    break;
            }
        }
        
        // Compter le total
        $totalQuery = clone $qb;
        $total = count($totalQuery->getQuery()->getResult());
        
        // Pagination
        $qb->setFirstResult(($page - 1) * $perPage)
           ->setMaxResults($perPage)
           ->orderBy('e.createdAt', 'DESC');
        
        $entreprises = $qb->getQuery()->getResult();
        
        // Formater les données
        $data = [];
        foreach ($entreprises as $entreprise) {
            $proprietaire = $entreprise->getProprietaire();
            $partenaireId = $proprietaire ? $proprietaire->getPartnaireId() : null;
            $partenaireNom = $partenaireId && isset($partenairesMap[$partenaireId]) 
                ? $partenairesMap[$partenaireId] 
                : 'N/A';
            
            $data[] = [
                'id' => $entreprise->getId(),
                'nom' => $entreprise->getNom(),
                'secteur' => $entreprise->getSecteur(),
                'ville' => $entreprise->getVille(),
                'codePostal' => $entreprise->getCodePostal(),
                'taille' => $entreprise->getTaille() ?? 'N/A',
                'siret' => $entreprise->getSiret(),
                'typeStructure' => $entreprise->getTypeStructure(),
                'partenaire' => $partenaireNom,
                'partenaireId' => $partenaireId,
                'createdAt' => $entreprise->getCreatedAt()->format('d/m/Y'),
            ];
        }
        
        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'pages' => ceil($total / $perPage),
            ],
        ]);
    }
}

