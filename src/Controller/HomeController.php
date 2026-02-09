<?php

namespace App\Controller;

use App\Service\PartenaireJsonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

final class HomeController extends AbstractController
{
    #[Route(path: [
        'fr' => '/accueil',
        'en' => '/home'
    ], name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route(path: [
        'fr' => '/profil',
        'en' => '/profile'
    ], name: 'app_profile')]
    public function profile(PartenaireJsonService $partenaireService): Response
    {
        $user = $this->getUser();
        $partenaire = null;
        
        if ($user && $user->getPartnaireId()) {
            $partenaire = $partenaireService->findById($user->getPartnaireId());
        }

        return $this->render('home/profile.html.twig', [
            'partenaire' => $partenaire,
        ]);
    }

    #[Route(path: [
        'fr' => '/profil/mise-a-jour',
        'en' => '/profile/update'
    ], name: 'app_profile_update', methods: ['POST'])]
    public function updateProfile(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $prenom = $request->request->get('prenom');
        $nom = $request->request->get('nom');
        $username = $request->request->get('username');
        $email = $request->request->get('email');

        $user->setPrenom($prenom);
        $user->setNom($nom);
        $user->setUsername($username);
        $user->setEmail($email);

        $em->flush();

        $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

        return $this->redirectToRoute('app_profile');
    }

    #[Route(path: [
        'fr' => '/profil/changer-mot-de-passe',
        'en' => '/profile/change-password'
    ], name: 'app_profile_change_password', methods: ['POST'])]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $currentPassword = $request->request->get('current_password');
        $newPassword = $request->request->get('new_password');
        $confirmPassword = $request->request->get('confirm_password');

        // Vérifier l'ancien mot de passe
        if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
            $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
            return $this->redirectToRoute('app_profile');
        }

        // Vérifier que les nouveaux mots de passe correspondent
        if ($newPassword !== $confirmPassword) {
            $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas.');
            return $this->redirectToRoute('app_profile');
        }

        // Mettre à jour le mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        $em->flush();

        $this->addFlash('success', 'Votre mot de passe a été changé avec succès.');

        return $this->redirectToRoute('app_profile');
    }
}