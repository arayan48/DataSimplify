<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Rate limits login attempts to prevent brute-force attacks.
 */
class LoginRateLimiterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(service: 'limiter.login_limiter')]
        private RateLimiterFactory $loginLimiter,
        private RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', 2048],
            LoginFailureEvent::class => ['onLoginFailure', 0],
            LoginSuccessEvent::class => ['onLoginSuccess', 0],
        ];
    }

    public function onCheckPassport(CheckPassportEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        // Rate limit by IP address and username combined
        $username = $event->getPassport()->getBadge(\Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge::class)?->getUserIdentifier() ?? '';
        $key = $this->createKey($request->getClientIp(), $username);
        
        $limiter = $this->loginLimiter->create($key);
        $limit = $limiter->consume(0);

        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException(
                $limit->getRetryAfter()->getTimestamp() - time(),
                'Trop de tentatives de connexion. Veuillez réessayer dans ' . ceil(($limit->getRetryAfter()->getTimestamp() - time()) / 60) . ' minutes.'
            );
        }
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $request = $event->getRequest();
        $username = $event->getPassport()?->getBadge(\Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge::class)?->getUserIdentifier() ?? '';
        
        $key = $this->createKey($request->getClientIp(), $username);
        $limiter = $this->loginLimiter->create($key);
        
        // Consume one attempt on failure
        $limiter->consume(1);
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $request = $event->getRequest();
        $username = $event->getPassport()->getBadge(\Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge::class)?->getUserIdentifier() ?? '';
        
        $key = $this->createKey($request->getClientIp(), $username);
        $limiter = $this->loginLimiter->create($key);
        
        // Reset the limiter on successful login
        $limiter->reset();
    }

    private function createKey(?string $ip, string $username): string
    {
        return md5(($ip ?? 'unknown') . '-' . strtolower($username));
    }
}
