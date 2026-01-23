<?php

namespace App\Twig;

use App\Service\PartenaireJsonService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PartenaireExtension extends AbstractExtension
{
    private PartenaireJsonService $partenaireService;

    public function __construct(PartenaireJsonService $partenaireService)
    {
        $this->partenaireService = $partenaireService;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('partenaire_name', [$this, 'getPartenaireName']),
        ];
    }

    public function getPartenaireName(?string $id): string
    {
        if (!$id) {
            return '';
        }

        $partenaire = $this->partenaireService->findById($id);
        
        return $partenaire ? $partenaire['nom'] : '';
    }
}
