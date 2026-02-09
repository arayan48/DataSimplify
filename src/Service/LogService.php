<?php

namespace App\Service;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogService
{
    public const TYPE_AUTH = 'auth';
    public const TYPE_USER = 'user';
    public const TYPE_PARTENAIRE = 'partenaire';
    public const TYPE_ENTREPRISE = 'entreprise';
    public const TYPE_ADMIN = 'admin';
    public const TYPE_SYSTEM = 'system';

    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';
    public const STATUS_WARNING = 'warning';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * Enregistre un log
     */
    public function log(
        string $type,
        string $message,
        string $status = self::STATUS_SUCCESS,
        ?array $context = null
    ): void {
        $log = new Log();
        $log->setType($type);
        $log->setMessage($message);
        $log->setStatus($status);
        $log->setContext($context);

        // Récupérer l'utilisateur connecté
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser()) {
            $user = $token->getUser();
            if (method_exists($user, 'getId')) {
                $log->setUserId($user->getId());
            }
            if (method_exists($user, 'getEmail')) {
                $log->setUserEmail($user->getEmail());
            }
        }

        // Récupérer l'IP
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $log->setIpAddress($request->getClientIp());
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * Log une connexion réussie
     */
    public function logLogin(string $email): void
    {
        $this->log(
            self::TYPE_AUTH,
            "Connexion réussie pour l'utilisateur {$email}",
            self::STATUS_SUCCESS,
            ['email' => $email, 'action' => 'login']
        );
    }

    /**
     * Log une tentative de connexion échouée
     */
    public function logLoginFailed(string $email, string $reason = ''): void
    {
        $this->log(
            self::TYPE_AUTH,
            "Échec de connexion pour {$email}" . ($reason ? ": {$reason}" : ''),
            self::STATUS_ERROR,
            ['email' => $email, 'action' => 'login_failed', 'reason' => $reason]
        );
    }

    /**
     * Log une déconnexion
     */
    public function logLogout(string $email): void
    {
        $this->log(
            self::TYPE_AUTH,
            "Déconnexion de l'utilisateur {$email}",
            self::STATUS_SUCCESS,
            ['email' => $email, 'action' => 'logout']
        );
    }

    /**
     * Log la création d'un utilisateur
     */
    public function logUserCreated(int $userId, string $email, array $roles = []): void
    {
        $this->log(
            self::TYPE_USER,
            "Création de l'utilisateur {$email}",
            self::STATUS_SUCCESS,
            ['userId' => $userId, 'email' => $email, 'roles' => $roles, 'action' => 'user_created']
        );
    }

    /**
     * Log la modification d'un utilisateur
     */
    public function logUserUpdated(int $userId, string $email, array $changes = []): void
    {
        $this->log(
            self::TYPE_USER,
            "Modification de l'utilisateur {$email}",
            self::STATUS_SUCCESS,
            ['userId' => $userId, 'email' => $email, 'changes' => $changes, 'action' => 'user_updated']
        );
    }

    /**
     * Log la suppression d'un utilisateur
     */
    public function logUserDeleted(int $userId, string $email): void
    {
        $this->log(
            self::TYPE_USER,
            "Suppression de l'utilisateur {$email}",
            self::STATUS_SUCCESS,
            ['userId' => $userId, 'email' => $email, 'action' => 'user_deleted']
        );
    }

    /**
     * Log la suppression multiple d'utilisateurs
     */
    public function logUsersDeleted(array $userIds, int $count): void
    {
        $this->log(
            self::TYPE_USER,
            "Suppression de {$count} utilisateur(s)",
            self::STATUS_SUCCESS,
            ['userIds' => $userIds, 'count' => $count, 'action' => 'users_deleted']
        );
    }

    /**
     * Log la création d'un partenaire
     */
    public function logPartenaireCreated(int $partenaireId, string $nom): void
    {
        $this->log(
            self::TYPE_PARTENAIRE,
            "Création du partenaire {$nom}",
            self::STATUS_SUCCESS,
            ['partenaireId' => $partenaireId, 'nom' => $nom, 'action' => 'partenaire_created']
        );
    }

    /**
     * Log la modification d'un partenaire
     */
    public function logPartenaireUpdated(int $partenaireId, string $nom): void
    {
        $this->log(
            self::TYPE_PARTENAIRE,
            "Modification du partenaire {$nom}",
            self::STATUS_SUCCESS,
            ['partenaireId' => $partenaireId, 'nom' => $nom, 'action' => 'partenaire_updated']
        );
    }

    /**
     * Log la suppression d'un partenaire
     */
    public function logPartenaireDeleted(int $partenaireId, string $nom): void
    {
        $this->log(
            self::TYPE_PARTENAIRE,
            "Suppression du partenaire {$nom}",
            self::STATUS_SUCCESS,
            ['partenaireId' => $partenaireId, 'nom' => $nom, 'action' => 'partenaire_deleted']
        );
    }

    /**
     * Log une erreur système
     */
    public function logError(string $message, ?\Throwable $exception = null): void
    {
        $context = ['action' => 'error'];
        if ($exception) {
            $context['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        $this->log(
            self::TYPE_SYSTEM,
            $message,
            self::STATUS_ERROR,
            $context
        );
    }
}
