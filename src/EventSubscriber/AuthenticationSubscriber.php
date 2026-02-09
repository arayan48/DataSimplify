<?php

namespace App\EventSubscriber;

use App\Service\LogService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LogService $logService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InteractiveLoginEvent::class => 'onLogin',
            LoginFailureEvent::class => 'onLoginFailure',
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        
        if (method_exists($user, 'getEmail')) {
            $this->logService->logLogin($user->getEmail());
        }
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $passport = $event->getPassport();
        $email = '';
        
        if ($passport && method_exists($passport, 'getUser')) {
            $user = $passport->getUser();
            if (method_exists($user, 'getUserIdentifier')) {
                $email = $user->getUserIdentifier();
            }
        }

        $exception = $event->getException();
        $reason = $exception ? $exception->getMessage() : 'Unknown error';

        $this->logService->logLoginFailed($email ?: 'Unknown user', $reason);
    }

    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        if ($token && $token->getUser()) {
            $user = $token->getUser();
            if (method_exists($user, 'getEmail')) {
                $this->logService->logLogout($user->getEmail());
            }
        }
    }
}
