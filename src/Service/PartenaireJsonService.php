<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PartenaireJsonService
{
    private string $jsonFilePath;

    public function __construct(ParameterBagInterface $params)
    {
        $this->jsonFilePath = $params->get('kernel.project_dir') . '/config/data/partenaire.json';
    }

    /**
     * Récupère tous les partenaires
     * @return array
     */
    public function findAll(): array
    {
        if (!file_exists($this->jsonFilePath)) {
            return [];
        }

        $content = file_get_contents($this->jsonFilePath);
        $data = json_decode($content, true);

        return $data ?? [];
    }

    /**
     * Trouve un partenaire par son ID
     * @param string $id
     * @return array|null
     */
    public function findById(string $id): ?array
    {
        $partenaires = $this->findAll();
        
        foreach ($partenaires as $partenaire) {
            if ($partenaire['id'] === $id) {
                return $partenaire;
            }
        }

        return null;
    }

    /**
     * Trouve un partenaire par son nom
     * @param string $nom
     * @return array|null
     */
    public function findByNom(string $nom): ?array
    {
        $partenaires = $this->findAll();
        
        foreach ($partenaires as $partenaire) {
            if ($partenaire['nom'] === $nom) {
                return $partenaire;
            }
        }

        return null;
    }

    /**
     * Ajoute un nouveau partenaire
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $partenaires = $this->findAll();
        
        // Générer un nouvel ID
        $newId = $this->generateId($partenaires);
        
        $newPartenaire = [
            'id' => $newId,
            'nom' => $data['nom'] ?? '',
            'telephone' => $data['telephone'] ?? '',
            'email' => $data['email'] ?? '',
            'adresse' => $data['adresse'] ?? '',
            'ville' => $data['ville'] ?? '',
            'codePostal' => $data['codePostal'] ?? '',
            'siteWeb' => $data['siteWeb'] ?? '',
            'description' => $data['description'] ?? '',
        ];

        $partenaires[] = $newPartenaire;
        $this->save($partenaires);

        return $newPartenaire;
    }

    /**
     * Met à jour un partenaire existant
     * @param string $id
     * @param array $data
     * @return bool
     */
    public function update(string $id, array $data): bool
    {
        $partenaires = $this->findAll();
        $updated = false;

        foreach ($partenaires as &$partenaire) {
            if ($partenaire['id'] === $id) {
                $partenaire['nom'] = $data['nom'] ?? $partenaire['nom'];
                $partenaire['telephone'] = $data['telephone'] ?? $partenaire['telephone'] ?? '';
                $partenaire['email'] = $data['email'] ?? $partenaire['email'] ?? '';
                $partenaire['adresse'] = $data['adresse'] ?? $partenaire['adresse'] ?? '';
                $partenaire['ville'] = $data['ville'] ?? $partenaire['ville'] ?? '';
                $partenaire['codePostal'] = $data['codePostal'] ?? $partenaire['codePostal'] ?? '';
                $partenaire['siteWeb'] = $data['siteWeb'] ?? $partenaire['siteWeb'] ?? '';
                $partenaire['description'] = $data['description'] ?? $partenaire['description'] ?? '';
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->save($partenaires);
        }

        return $updated;
    }

    /**
     * Supprime un partenaire
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        $partenaires = $this->findAll();
        $initialCount = count($partenaires);

        $partenaires = array_filter($partenaires, function($partenaire) use ($id) {
            return $partenaire['id'] !== $id;
        });

        if (count($partenaires) < $initialCount) {
            $this->save(array_values($partenaires));
            return true;
        }

        return false;
    }

    /**
     * Supprime plusieurs partenaires
     * @param array $ids
     * @return int Nombre de partenaires supprimés
     */
    public function deleteMultiple(array $ids): int
    {
        $partenaires = $this->findAll();
        $initialCount = count($partenaires);

        $partenaires = array_filter($partenaires, function($partenaire) use ($ids) {
            return !in_array($partenaire['id'], $ids);
        });

        $deletedCount = $initialCount - count($partenaires);
        
        if ($deletedCount > 0) {
            $this->save(array_values($partenaires));
        }

        return $deletedCount;
    }

    /**
     * Sauvegarde les partenaires dans le fichier JSON
     * @param array $partenaires
     */
    private function save(array $partenaires): void
    {
        $json = json_encode($partenaires, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->jsonFilePath, $json);
    }

    /**
     * Génère un nouvel ID unique
     * @param array $partenaires
     * @return string
     */
    private function generateId(array $partenaires): string
    {
        if (empty($partenaires)) {
            return '1';
        }

        $maxId = 0;
        foreach ($partenaires as $partenaire) {
            $id = (int) $partenaire['id'];
            if ($id > $maxId) {
                $maxId = $id;
            }
        }

        return (string) ($maxId + 1);
    }
}
