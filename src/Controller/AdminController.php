<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\PartenaireJsonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AdminController extends AbstractController
{
    #[Route('/administrateur/dashboard', name: 'app_admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'logs' => [],
            'recentActivities' => [],
        ]);
    }

    #[Route('/administrateur/users', name: 'app_admin_users')]
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
    public function deleteUsers(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userIds = $data['ids'] ?? [];

        if (empty($userIds)) {
            return new JsonResponse(['success' => false, 'message' => 'Aucun utilisateur sélectionné'], 400);
        }

        $count = 0;
        foreach ($userIds as $id) {
            $user = $em->getRepository(User::class)->find($id);
            if ($user && !in_array('ROLE_ADMINISTRATEUR', $user->getRoles())) {
                $em->remove($user);
                $count++;
            }
        }

        $em->flush();

        return new JsonResponse(['success' => true, 'message' => "$count utilisateur(s) supprimé(s)"]);
    }

    #[Route('/administrateur/users/{id}/edit', name: 'app_admin_user_edit', methods: ['POST'])]
    public function editUser(int $id, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);
        
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }

        if (isset($data['prenom'])) {
            $user->setPrenom($data['prenom']);
        }

        if (isset($data['nom'])) {
            $user->setNom($data['nom']);
        }

        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        if (isset($data['partnaire_id'])) {
            $user->setPartnaireId($data['partnaire_id'] === '' ? null : $data['partnaire_id']);
        }

        if (!empty($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Utilisateur modifié']);
    }

    #[Route('/administrateur/users/create', name: 'app_admin_user_create', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['success' => false, 'message' => 'Email et mot de passe requis'], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['username'] ?? '');
        $user->setPrenom($data['prenom'] ?? '');
        $user->setNom($data['nom'] ?? '');
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);

        if (isset($data['partnaire_id']) && $data['partnaire_id']) {
            $user->setPartnaireId($data['partnaire_id']);
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Utilisateur créé']);
    }

    #[Route('/administrateur/partenaire', name: 'app_admin_partenaire')]
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
    public function deletePartenaires(Request $request, PartenaireJsonService $partenaireService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $partenaireIds = $data['ids'] ?? [];

        if (empty($partenaireIds)) {
            return new JsonResponse(['success' => false, 'message' => 'Aucun partenaire sélectionné'], 400);
        }

        $count = $partenaireService->deleteMultiple($partenaireIds);

        return new JsonResponse(['success' => true, 'message' => "$count partenaire(s) supprimé(s)"]);
    }

    #[Route('/administrateur/partenaires/{id}/edit', name: 'app_admin_partenaire_edit', methods: ['POST'])]
    public function editPartenaire(string $id, Request $request, PartenaireJsonService $partenaireService): JsonResponse
    {
        $partenaire = $partenaireService->findById($id);
        
        if (!$partenaire) {
            return new JsonResponse(['success' => false, 'message' => 'Partenaire introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $partenaireService->update($id, $data);

        return new JsonResponse(['success' => true, 'message' => 'Partenaire modifié']);
    }

    #[Route('/administrateur/partenaires/create', name: 'app_admin_partenaire_create', methods: ['POST'])]
    public function createPartenaire(Request $request, PartenaireJsonService $partenaireService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $partenaireService->create($data);

        return new JsonResponse(['success' => true, 'message' => 'Partenaire créé']);
    }
}
