<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\LogRepository;
use App\Service\PartenaireJsonService;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class AdminController extends AbstractController
{
    #[Route(path: [
        'fr' => '/administrateur/tableau-de-bord',
        'en' => '/administrator/dashboard'
    ], name: 'app_admin_dashboard')]
    public function index(
        UserRepository $userRepository, 
        PartenaireJsonService $partenaireService,
        LogRepository $logRepository
    ): Response
    {
        // Récupérer tous les utilisateurs
        $allUsers = $userRepository->findAll();
        
        // Calculer les statistiques
        $totalUsers = count($allUsers);
        
        // Compter les utilisateurs actifs (ayant au moins un rôle autre que ROLE_USER)
        $activeUsers = count(array_filter($allUsers, function($user) {
            $roles = $user->getRoles();
            return count(array_diff($roles, ['ROLE_USER'])) > 0;
        }));
        
        // Compter les partenaires
        $partenaires = $partenaireService->findAll();
        $totalPartenaires = count($partenaires);
        
        // Récupérer les logs récents
        $logs = $logRepository->findRecent(20);
        
        return $this->render('admin/index.html.twig', [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'totalPartenaires' => $totalPartenaires,
            'conversionRate' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) . '%' : '0%',
            'logs' => $logs,
            'recentActivities' => array_slice($logs, 0, 5),
        ]);
    }

    #[Route(path: [
        'fr' => '/administrateur/utilisateurs',
        'en' => '/administrator/users'
    ], name: 'app_admin_users')]
    public function manageUsers(UserRepository $userRepository, PartenaireJsonService $partenaireService): Response
    {
        $users = $userRepository->findAll();
        $partenaires = $partenaireService->findAll();
        
        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'partenaires' => $partenaires,
        ]);
    }

    #[Route('/administrateur/users/all', name: 'app_admin_users_all', methods: ['GET'])]
    public function getAllUsers(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        
        $usersData = array_map(function($user) {
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'prenom' => $user->getPrenom(),
                'nom' => $user->getNom(),
                'roles' => $user->getRoles(),
                'partnaireId' => $user->getPartnaireId(),
            ];
        }, $users);
        
        return new JsonResponse(['users' => $usersData]);
    }

    #[Route('/administrateur/users/delete', name: 'app_admin_users_delete', methods: ['POST'])]
    public function deleteUsers(
        Request $request, 
        EntityManagerInterface $em, 
        CsrfTokenManagerInterface $csrfTokenManager,
        LogService $logService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validate CSRF token
        $token = new CsrfToken('admin_api', $data['_token'] ?? '');
        if (!$csrfTokenManager->isTokenValid($token)) {
            return new JsonResponse(['success' => false, 'message' => 'Token de sécurité invalide'], 403);
        }

        $userIds = $data['ids'] ?? [];

        if (empty($userIds)) {
            return new JsonResponse(['success' => false, 'message' => 'Aucun utilisateur sélectionné'], 400);
        }

        // Validate that all IDs are integers
        foreach ($userIds as $id) {
            if (!is_int($id) && !ctype_digit((string)$id)) {
                return new JsonResponse(['success' => false, 'message' => 'ID utilisateur invalide'], 400);
            }
        }

        $count = 0;
        $deletedIds = [];
        $skippedIds = [];
        foreach ($userIds as $id) {
            $user = $em->getRepository(User::class)->find($id);
            if ($user && !in_array('ROLE_ADMINISTRATEUR', $user->getRoles())) {
                // Détacher les entreprises liées à cet utilisateur
                $entreprises = $em->getRepository(\App\Entity\Entreprise::class)->findBy(['proprietaire' => $user]);
                foreach ($entreprises as $entreprise) {
                    $entreprise->setProprietaire(null);
                }
                $deletedIds[] = $id;
                $em->remove($user);
                $count++;
            }
        }

        try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de la suppression : une contrainte empêche la suppression'], 500);
        }
        
        // Log l'action
        $logService->logUsersDeleted($deletedIds, $count);

        return new JsonResponse(['success' => true, 'message' => "$count utilisateur(s) supprimé(s)"]);
    }

    #[Route('/administrateur/users/{id}/edit', name: 'app_admin_user_edit', methods: ['POST'])]
    public function editUser(int $id, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, CsrfTokenManagerInterface $csrfTokenManager, ValidatorInterface $validator, LogService $logService): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);
        
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // Validate CSRF token
        $token = new CsrfToken('admin_api', $data['_token'] ?? '');
        if (!$csrfTokenManager->isTokenValid($token)) {
            return new JsonResponse(['success' => false, 'message' => 'Token de sécurité invalide'], 403);
        }

        // Validate email format
        if (isset($data['email'])) {
            $emailConstraint = new Assert\Email();
            $errors = $validator->validate($data['email'], $emailConstraint);
            if (count($errors) > 0) {
                return new JsonResponse(['success' => false, 'message' => 'Format email invalide'], 400);
            }
            $user->setEmail($data['email']);
        }

        if (isset($data['username'])) {
            $user->setUsername(htmlspecialchars($data['username'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($data['prenom'])) {
            $user->setPrenom(htmlspecialchars($data['prenom'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($data['nom'])) {
            $user->setNom(htmlspecialchars($data['nom'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($data['roles'])) {
            // Validate roles are valid
            $allowedRoles = ['ROLE_USER', 'ROLE_ADMINISTRATION', 'ROLE_ADMINISTRATEUR'];
            $roles = array_filter($data['roles'], fn($r) => in_array($r, $allowedRoles));
            $user->setRoles($roles);
        }

        if (isset($data['partnaire_id'])) {
            $user->setPartnaireId($data['partnaire_id'] === '' ? null : $data['partnaire_id']);
        }

        if (!empty($data['password'])) {
            // Validate password length
            if (strlen($data['password']) < 8) {
                return new JsonResponse(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères'], 400);
            }
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        $em->flush();

        // Log l'action
        $changes = array_filter([
            'email' => $data['email'] ?? null,
            'username' => $data['username'] ?? null,
            'roles' => $data['roles'] ?? null,
            'partnaire_id' => $data['partnaire_id'] ?? null,
            'password_changed' => !empty($data['password'])
        ]);
        $logService->logUserUpdated($user->getId(), $user->getEmail(), $changes);

        return new JsonResponse(['success' => true, 'message' => 'Utilisateur modifié']);
    }

    #[Route('/administrateur/users/create', name: 'app_admin_user_create', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, CsrfTokenManagerInterface $csrfTokenManager, ValidatorInterface $validator, LogService $logService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate CSRF token
        $token = new CsrfToken('admin_api', $data['_token'] ?? '');
        if (!$csrfTokenManager->isTokenValid($token)) {
            return new JsonResponse(['success' => false, 'message' => 'Token de sécurité invalide'], 403);
        }

        if (empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['success' => false, 'message' => 'Email et mot de passe requis'], 400);
        }

        // Validate email format
        $emailConstraint = new Assert\Email();
        $errors = $validator->validate($data['email'], $emailConstraint);
        if (count($errors) > 0) {
            return new JsonResponse(['success' => false, 'message' => 'Format email invalide'], 400);
        }

        // Validate password length
        if (strlen($data['password']) < 8) {
            return new JsonResponse(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères'], 400);
        }

        // Check if email already exists
        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['success' => false, 'message' => 'Un utilisateur avec cet email existe déjà'], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername(htmlspecialchars($data['username'] ?? '', ENT_QUOTES, 'UTF-8'));
        $user->setPrenom(htmlspecialchars($data['prenom'] ?? '', ENT_QUOTES, 'UTF-8'));
        $user->setNom(htmlspecialchars($data['nom'] ?? '', ENT_QUOTES, 'UTF-8'));
        
        // Validate roles
        $allowedRoles = ['ROLE_USER', 'ROLE_ADMINISTRATION', 'ROLE_ADMINISTRATEUR'];
        $roles = isset($data['roles']) ? array_filter($data['roles'], fn($r) => in_array($r, $allowedRoles)) : ['ROLE_USER'];
        $user->setRoles($roles);

        if (isset($data['partnaire_id']) && $data['partnaire_id']) {
            $user->setPartnaireId($data['partnaire_id']);
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        // Log l'action
        $logService->logUserCreated($user->getId(), $user->getEmail(), $user->getRoles());

        return new JsonResponse(['success' => true, 'message' => 'Utilisateur créé']);
    }

    #[Route(path: [
        'fr' => '/administrateur/partenaire',
        'en' => '/administrator/partner'
    ], name: 'app_admin_partenaire')]
    public function managePartenaire(PartenaireJsonService $partenaireService, UserRepository $userRepository): Response
    {
        $partenaires = $partenaireService->findAll();
        $users = $userRepository->findAll();
        
        return $this->render('admin/partenaire.html.twig', [
            'partenaires' => $partenaires,
            'users' => $users,
        ]);
    }

    #[Route('/administrateur/partenaires/{id}/users', name: 'app_admin_partenaire_users', methods: ['GET'])]
    public function getPartenaireUsers(string $id, UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findBy(['partnaireId' => $id]);
        
        $usersData = array_map(function($user) {
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'prenom' => $user->getPrenom(),
                'nom' => $user->getNom(),
                'roles' => $user->getRoles(),
            ];
        }, $users);
        
        return new JsonResponse(['users' => $usersData]);
    }

    #[Route('/administrateur/partenaires/delete', name: 'app_admin_partenaires_delete', methods: ['POST'])]
    public function deletePartenaires(Request $request, PartenaireJsonService $partenaireService, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validate CSRF token
        $token = new CsrfToken('admin_api', $data['_token'] ?? '');
        if (!$csrfTokenManager->isTokenValid($token)) {
            return new JsonResponse(['success' => false, 'message' => 'Token de sécurité invalide'], 403);
        }

        $partenaireIds = $data['ids'] ?? [];

        if (empty($partenaireIds)) {
            return new JsonResponse(['success' => false, 'message' => 'Aucun partenaire sélectionné'], 400);
        }

        // Convertir les IDs en strings pour correspondre au format JSON
        $partenaireIds = array_map('strval', $partenaireIds);
        
        // Log pour déboguer
        error_log('Tentative de suppression des partenaires: ' . json_encode($partenaireIds));
        
        $count = $partenaireService->deleteMultiple($partenaireIds);
        
        error_log('Nombre de partenaires supprimés: ' . $count);

        return new JsonResponse(['success' => true, 'message' => "$count partenaire(s) supprimé(s)"]);
    }

    #[Route('/administrateur/partenaires/{id}/edit', name: 'app_admin_partenaire_edit', methods: ['POST'])]
    public function editPartenaire(string $id, Request $request, PartenaireJsonService $partenaireService, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $partenaire = $partenaireService->findById($id);
        
        if (!$partenaire) {
            return new JsonResponse(['success' => false, 'message' => 'Partenaire introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // Validate CSRF token
        $token = new CsrfToken('admin_api', $data['_token'] ?? '');
        if (!$csrfTokenManager->isTokenValid($token)) {
            return new JsonResponse(['success' => false, 'message' => 'Token de sécurité invalide'], 403);
        }

        $partenaireService->update($id, $data);

        return new JsonResponse(['success' => true, 'message' => 'Partenaire modifié']);
    }

    #[Route('/administrateur/partenaires/create', name: 'app_admin_partenaire_create', methods: ['POST'])]
    public function createPartenaire(Request $request, PartenaireJsonService $partenaireService, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate CSRF token
        $token = new CsrfToken('admin_api', $data['_token'] ?? '');
        if (!$csrfTokenManager->isTokenValid($token)) {
            return new JsonResponse(['success' => false, 'message' => 'Token de sécurité invalide'], 403);
        }
        $data = json_decode($request->getContent(), true);

        $partenaireService->create($data);

        return new JsonResponse(['success' => true, 'message' => 'Partenaire créé']);
    }
}
