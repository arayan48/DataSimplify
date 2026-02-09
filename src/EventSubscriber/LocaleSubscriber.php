<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;

    public function __construct(string $defaultLocale = 'fr')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        // Ne pas traiter les sous-requêtes
        if (!$event->isMainRequest()) {
            return;
        }

        // Si la locale est dans l'URL, on la stocke en session
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
            $request->setLocale($locale);
        } else {
            // Récupérer la locale depuis la session ou utiliser la locale par défaut
            $locale = $request->getSession()->get('_locale', $this->defaultLocale);
            $request->setLocale($locale);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
