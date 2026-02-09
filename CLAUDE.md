# DataSimplify - Guide Complet pour Claude AI

> **Dernière mise à jour :** 9 février 2026  
> **Version Symfony :** 8.0  
> **PHP :** 8.4+  
> **Node :** (pour build assets)

---

## 📋 Table des matières

1. [Vue d'ensemble du projet](#vue-densemble-du-projet)
2. [Architecture technique](#architecture-technique)
3. [Structure du projet](#structure-du-projet)
4. [Modules & Fonctionnalités](#modules--fonctionnalités)
5. [Modèle de données](#modèle-de-données)
6. [Frontend & Assets](#frontend--assets)
7. [Routing & Localisation](#routing--localisation)
8. [Sécurité & Authentification](#sécurité--authentification)
9. [Patterns & Conventions](#patterns--conventions)
10. [Commandes utiles](#commandes-utiles)
11. [Problèmes connus & Solutions](#problèmes-connus--solutions)

---

## 🎯 Vue d'ensemble du projet

**DataSimplify** est une application Symfony de gestion d'entreprises avec un système de partenaires. Le projet permet :
- La gestion d'entreprises avec différents work packages (WP2, WP5, WP6, WP7)
- Un système multi-rôles (User, Administration, Administrateur)
- La gestion de partenaires via fichier JSON
- Des tableaux de bord avec statistiques et visualisations
- Une interface bilingue (FR/EN)

### Public cible
- **ROLE_USER** : Utilisateurs standard (partenaires)
- **ROLE_ADMINISTRATION** : Administrateurs de partenaires (niveau intermédiaire)
- **ROLE_ADMINISTRATEUR** : Super administrateurs (contrôle total)

---

## 🏗️ Architecture technique

### Stack technique

**Backend**
- **Framework :** Symfony 8.0
- **PHP :** 8.4+
- **ORM :** Doctrine 3.6
- **Validation :** Symfony Validator
- **Sécurité :** Symfony Security Bundle

**Frontend**
- **CSS Framework :** Tailwind CSS 3.4
- **UI Components :** Flowbite 2.2
- **JavaScript :** Stimulus (Hotwired)
- **Charts :** Chart.js 4.5 & ECharts 6.0
- **Turbo :** Hotwired Turbo (SPA-like navigation)

**Build Tools**
- **Asset Management :** Symfony Asset Mapper
- **CSS Build :** Tailwind CLI
- **Module Bundling :** Import maps (natif)

### Dépendances clés
```json
{
  "symfony/stimulus-bundle": "^2.32",
  "symfony/ux-turbo": "^2.32",
  "symfony/asset-mapper": "8.0.*",
  "doctrine/orm": "^3.6",
  "tailwindcss": "^3.4.0",
  "chart.js": "^4.5.1",
  "echarts": "^6.0.0"
}
```

---

## 📁 Structure du projet

```
DataSimplify/
├── assets/                      # Frontend assets
│   ├── app.js                   # Point d'entrée JS principal
│   ├── controllers/             # Stimulus controllers
│   │   ├── partenaires_controller.js
│   │   ├── users_controller.js
│   │   ├── entreprises_table_controller.js
│   │   ├── dashboard_controller.js
│   │   └── ...
│   ├── styles/                  # CSS avec Tailwind
│   │   ├── app.css              # Fichier principal
│   │   ├── admin/               # Styles admin
│   │   └── components/          # Composants réutilisables
│   └── controllers.json         # Config Stimulus
│
├── config/
│   ├── data/
│   │   └── partenaire.json      # ⚠️ Base de données JSON des partenaires
│   ├── packages/                # Configuration bundles
│   ├── routes/                  # Routes spécifiques
│   ├── routes.yaml              # Routes principales (avec locale)
│   └── services.yaml            # Configuration DI
│
├── migrations/                  # Migrations Doctrine
│
├── public/
│   ├── index.php                # Point d'entrée
│   └── assets/                  # Assets compilés (générés)
│
├── src/
│   ├── Controller/
│   │   ├── AdminController.php               # Interface administrateur
│   │   ├── AdministrationController.php      # Interface administration
│   │   ├── HomeController.php
│   │   ├── SecurityController.php
│   │   └── RegistrationController.php
│   ├── Entity/
│   │   ├── User.php                          # Utilisateurs
│   │   ├── Entreprise.php                    # Entreprises (entité principale)
│   │   ├── EntrepriseWp2.php                 # Work Package 2
│   │   ├── EntrepriseWp5Event.php
│   │   ├── EntrepriseWp5Formation.php
│   │   ├── EntrepriseWp6.php
│   │   ├── EntrepriseWp7.php
│   │   ├── EntrepriseMiseEnRelation.php
│   │   └── Log.php
│   ├── Repository/              # Repositories Doctrine
│   ├── Service/
│   │   ├── PartenaireJsonService.php    # ⚠️ Service JSON (pas BDD!)
│   │   ├── LogService.php
│   │   └── TimeService.php
│   ├── Form/                    # Formulaires Symfony
│   ├── EventSubscriber/         # Event subscribers
│   └── Twig/                    # Extensions Twig
│
├── templates/
│   ├── base.html.twig           # Template de base
│   ├── admin/                   # Templates administrateur
│   │   ├── dashboard.html.twig
│   │   ├── users.html.twig
│   │   └── partenaire.html.twig
│   ├── administration/          # Templates administration
│   │   ├── index.html.twig
│   │   ├── create_entreprise.html.twig
│   │   └── edit_entreprise.html.twig
│   ├── home/
│   ├── security/
│   └── registration/
│
├── translations/
│   ├── messages.fr.yaml         # Traductions françaises
│   └── messages.en.yaml         # Traductions anglaises
│
├── var/
│   ├── cache/                   # Cache Symfony
│   └── log/                     # Logs (dev.log, prod.log)
│
├── vendor/                      # Dépendances Composer
├── node_modules/                # Dépendances npm
│
├── composer.json
├── package.json
├── tailwind.config.js
├── importmap.php                # Import maps config
└── .env                         # Variables d'environnement
```

---

## 🔧 Modules & Fonctionnalités

### 1. Gestion des Utilisateurs (Administrateur)

**Route :** `/administrateur/utilisateurs` (FR) ou `/administrator/users` (EN)  
**Controller :** `AdminController::manageUsers()`  
**Template :** `templates/admin/users.html.twig`  
**Stimulus :** `users_controller.js`

**Fonctionnalités :**
- ✅ Création d'utilisateurs (CRUD complet)
- ✅ Édition inline via modales
- ✅ Suppression multiple avec confirmation
- ✅ Assignation de rôles
- ✅ Association à un partenaire
- ✅ Recherche et filtrage

**API Endpoints :**
- `POST /administrateur/users/create` - Créer un utilisateur
- `POST /administrateur/users/{id}/edit` - Modifier un utilisateur
- `POST /administrateur/users/delete` - Supprimer plusieurs utilisateurs
- `GET /administrateur/users/all` - Récupérer tous les utilisateurs (JSON)

### 2. Gestion des Partenaires (Administrateur)

**⚠️ IMPORTANT : Les partenaires sont stockés dans `config/data/partenaire.json`, PAS en base de données !**

**Route :** `/administrateur/partenaire` (FR) ou `/administrator/partner` (EN)  
**Controller :** `AdminController::managePartenaire()`  
**Service :** `PartenaireJsonService`  
**Template :** `templates/admin/partenaire.html.twig`  
**Stimulus :** `partenaires_controller.js`

**Fonctionnalités :**
- ✅ CRUD complet via fichier JSON
- ✅ Édition dans une modale avec panneau latéral
- ⚠️ Suppression multiple (IDs doivent être des strings)
- ✅ Assignation d'utilisateurs au partenaire
- ✅ Vue des utilisateurs par partenaire

**API Endpoints :**
- `POST /administrateur/partenaires/create` - Créer un partenaire
- `POST /administrateur/partenaires/{id}/edit` - Modifier un partenaire
- `POST /administrateur/partenaires/delete` - Supprimer plusieurs partenaires
- `GET /administrateur/partenaires/{id}/users` - Récupérer les utilisateurs d'un partenaire

**Structure JSON :**
```json
[
  {
    "id": "1",
    "nom": "Google",
    "telephone": "01 42 68 53 00",
    "email": "accounts-support@google.com",
    "adresse": "8 Rue de Londres",
    "ville": "Paris",
    "codePostal": "75009",
    "siteWeb": "https://google.com",
    "description": ""
  }
]
```

### 3. Gestion des Entreprises (Administration)

**Route :** `/administration` (FR) ou `/administration` (EN)  
**Controller :** `AdministrationController::index()`  
**Template :** `templates/administration/index.html.twig`  
**Stimulus :** `entreprises_table_controller.js`

**Fonctionnalités :**
- ✅ Tableau de bord avec liste des entreprises
- ✅ Création d'entreprises
- ✅ Édition d'entreprises (modale ou page dédiée)
- ✅ Suppression avec confirmation
- ✅ Filtres : partenaire, statut, année
- ✅ Recherche dynamique
- ✅ Export Excel
- ✅ Statistiques visuelles

**Relation User-Partenaire :**
- Un `User` possède un champ `partenaireId` (string, nullable)
- Ce champ fait référence à l'ID d'un partenaire dans le JSON
- **Pas de relation Doctrine** : c'est une "foreign key" manuelle

### 4. Work Packages

Les entreprises ont plusieurs work packages liés :

**WP2 - Gestion de la relation**
- Entité : `EntrepriseWp2`
- Relation : OneToOne avec `Entreprise`
- Données : accompagnateur, statut marché, défis, etc.

**WP5 - Événements & Formations**
- Entités : `EntrepriseWp5Event`, `EntrepriseWp5Formation`
- Relation : OneToMany avec `Entreprise`

**WP6 - Sensibilisations**
- Entité : `EntrepriseWp6`
- Relation : OneToMany avec `Entreprise`

**WP7 - Mentorat**
- Entité : `EntrepriseWp7`
- Relation : OneToMany avec `Entreprise`

**Mise en relation**
- Entité : `EntrepriseMiseEnRelation`
- Gestion des contacts et relations avec experts

### 5. Logs & Audit

**Service :** `LogService`  
**Entité :** `Log`

Enregistre toutes les actions importantes :
- Création/modification/suppression d'entités
- Actions utilisateurs
- Horodatage et auteur

---

## 💾 Modèle de données

### User (Doctrine)

```php
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private ?int $id;
    private ?string $email;
    private array $roles = [];        // ['ROLE_USER', 'ROLE_ADMINISTRATION', 'ROLE_ADMINISTRATEUR']
    private ?string $password;
    private ?string $username;
    private ?string $nom;
    private ?string $prenom;
    private ?string $partenaireId;    // ⚠️ Référence JSON, pas Doctrine!
}
```

### Entreprise (Doctrine)

```php
class Entreprise
{
    private ?int $id;
    private ?string $nom;
    private ?string $anneeEdih;
    private ?string $typeStructure;
    private ?int $anneeCreation;
    private ?string $secteur;
    private ?string $siret;
    private ?string $taille;
    private ?string $chiffreAffaires;
    private ?string $codePostal;
    private ?string $ville;
    private ?string $region;
    private ?string $pays;
    private ?string $adresse;
    private ?string $description;
    private ?string $statut;              // 'vert', 'orange', 'rouge'
    private ?\DateTimeInterface $createdAt;
    private ?\DateTimeInterface $updatedAt;
    private ?User $proprietaire;          // ManyToOne vers User
    
    // Relations
    private ?EntrepriseWp2 $wp2;
    private Collection $wp5Events;
    private Collection $wp5Formations;
    private Collection $wp6;
    private Collection $wp7;
    private Collection $miseEnRelations;
}
```

### Partenaire (JSON uniquement)

```json
{
  "id": "string",          // ⚠️ String, pas int!
  "nom": "string",
  "telephone": "string",
  "email": "string",
  "adresse": "string",
  "ville": "string",
  "codePostal": "string",
  "siteWeb": "string",
  "description": "string"
}
```

**Service :** `PartenaireJsonService`
- `findAll()` - Récupère tous les partenaires
- `findById(string $id)` - Trouve un partenaire par ID
- `create(array $data)` - Crée un nouveau partenaire
- `update(string $id, array $data)` - Met à jour un partenaire
- `delete(string $id)` - Supprime un partenaire
- `deleteMultiple(array $ids)` - Supprime plusieurs partenaires

---

## 🎨 Frontend & Assets

### Stimulus Controllers

**Organisation :** Chaque fonctionnalité complexe a son propre controller Stimulus.

**Principaux controllers :**

1. **`partenaires_controller.js`**
   - Gestion CRUD des partenaires
   - Modale d'édition avec panneau utilisateurs
   - Suppression multiple avec confirmation
   - Assignation d'utilisateurs

2. **`users_controller.js`**
   - CRUD des utilisateurs
   - Assignation de rôles
   - Filtrage et recherche

3. **`entreprises_table_controller.js`**
   - Tableau des entreprises
   - Filtres dynamiques
   - Recherche
   - Suppression

4. **`dashboard_controller.js`**
   - Orchestre les statistiques
   - Gère les interactions du tableau de bord

5. **`chart_controller.js` / `entreprise_charts_controller.js`**
   - Intégration Chart.js
   - Graphiques statistiques

6. **`csrf_protection_controller.js`**
   - Gère la protection CSRF globale
   - Ajoute le token aux requêtes fetch

### Conventions Stimulus

**Déclaration dans HTML :**
```html
<div data-controller="partenaires"
     data-partenaires-csrf-token-value="{{ csrf_token('admin_api') }}"
     data-partenaires-target="modal">
  <!-- ... -->
</div>
```

**Actions :**
```html
<button data-action="click->partenaires#deleteSelected">
  Supprimer la sélection
</button>
```

**Targets :**
```html
<div data-partenaires-target="modal"></div>
```

**Dans le controller :**
```javascript
export default class extends Controller {
  static targets = ['modal', 'form'];
  static values = { csrfToken: String };
  
  connect() {
    console.log('Controller connected');
  }
  
  deleteSelected(event) {
    // Action...
  }
}
```

### Tailwind & Styles

**Configuration :** `tailwind.config.js`

**Couleurs primaires personnalisées :**
```javascript
colors: {
  primary: {
    "500": "#00c3c6",  // Cyan/turquoise
    "600": "#00a8ab",
    // ...
  }
}
```

**Dark mode :** Activé via classe `dark` sur `<html>`

**Compilation :**
```bash
npm run build    # Build production minifié
npm run watch    # Watch mode pour développement
```

**Fichier source :** `assets/styles/app.css`  
**Fichier cible :** `public/assets/output.css`

### Modales & Composants

**Pattern standard pour les modales :**

```html
<!-- Modale Tailwind/Flowbite -->
<div id="myModal" class="hidden fixed inset-0 z-50" aria-hidden="true" inert>
  <div class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40"></div>
  <div class="relative p-4">
    <!-- Contenu -->
  </div>
</div>

<script>
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  modal.removeAttribute('inert');
  modal.removeAttribute('aria-hidden');
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  modal.classList.add('hidden');
  modal.classList.remove('flex');
  modal.setAttribute('inert', '');
  modal.setAttribute('aria-hidden', 'true');
}
</script>
```

**⚠️ Important :** Après une mise à jour Turbo Frame, les composants Flowbite (dropdowns) peuvent casser. Préférer du JavaScript vanilla.

---

## 🌍 Routing & Localisation

### Configuration des routes

**Fichier :** `config/routes.yaml`

```yaml
# Routes avec préfixe de locale
controllers:
    resource: routing.controllers
    prefix: /{_locale}
    requirements:
        _locale: fr|en
    defaults:
        _locale: fr
```

**Toutes les routes ont un préfixe `/fr/` ou `/en/`.**

### Routes bilingues

**Dans les controllers :**
```php
#[Route(path: [
    'fr' => '/administration/entreprises',
    'en' => '/administration/companies'
], name: 'app_admin_companies')]
public function index(): Response
{
    // ...
}
```

### Routes API (sans locale dans certaines)

**Attention :** Certaines routes API utilisent un préfixe fixe `/administrateur/`. Avec le préfixe locale, l'URL complète devient `/fr/administrateur/...` ou `/en/administrator/...` sauf si déclaré sans le préfixe.

**Pattern dans JavaScript :**
```javascript
getLocalizedUrl(path) {
    const locale = document.documentElement.lang || 'fr';
    return `/${locale}${path}`;
}
```

### Traductions

**Fichiers :**
- `translations/messages.fr.yaml`
- `translations/messages.en.yaml`

**Utilisation dans Twig :**
```twig
{{ 'app.common.partners_management'|trans }}
```

**Structure des clés :**
```yaml
app:
  common:
    partners_management: "Gestion des partenaires"
    delete_selection: "Supprimer la sélection"
  js:
    partners:
      confirm_delete: "Êtes-vous sûr de vouloir supprimer ce partenaire ?"
```

---

## 🔒 Sécurité & Authentification

### Rôles utilisateurs

1. **ROLE_USER** (par défaut)
   - Accès limité
   - Lecture seule de ses propres données

2. **ROLE_ADMINISTRATION**
   - Gestion des entreprises de son partenaire
   - Création/modification d'entreprises
   - Accès au tableau de bord administration

3. **ROLE_ADMINISTRATEUR**
   - Contrôle total du système
   - Gestion utilisateurs
   - Gestion partenaires
   - Accès à tous les tableaux de bord

### Protection CSRF

**Global controller :** `csrf_protection_controller.js`

Ajoute automatiquement le token CSRF à toutes les requêtes fetch.

**Génération du token dans Twig :**
```twig
data-csrf-token-value="{{ csrf_token('admin_api') }}"
```

**Validation côté serveur :**
```php
use Symfony\Component\Security\Csrf\CsrfToken;

$token = new CsrfToken('admin_api', $data['_token'] ?? '');
if (!$csrfTokenManager->isTokenValid($token)) {
    return new JsonResponse(['success' => false, 'message' => 'Token invalide'], 403);
}
```

### Formulaires

**Protection CSRF automatique dans les formulaires Symfony.**

Pour les formulaires personnalisés :
```twig
<input type="hidden" name="_token" value="{{ csrf_token('form_id') }}">
```

---

## 📐 Patterns & Conventions

### 1. Controllers

**Règles :**
- Controllers fins : logique métier dans les services
- Injection de dépendances via constructeur
- Retour de `Response` ou `JsonResponse`
- Flash messages pour feedback utilisateur

**Exemple :**
```php
final class AdminController extends AbstractController
{
    #[Route('/admin/users', name: 'app_admin_users')]
    public function manageUsers(UserRepository $repo): Response
    {
        $users = $repo->findAll();
        
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }
    
    #[Route('/admin/users/create', methods: ['POST'])]
    public function createUser(Request $request, UserService $service): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $user = $service->createUser($data);
        
        return new JsonResponse(['success' => true, 'user' => $user]);
    }
}
```

### 2. Services

**Organisation :**
- Un service par domaine métier
- Services injectés, autowire activé
- Pas de logique dans les controllers

**Exemple : PartenaireJsonService**
```php
class PartenaireJsonService
{
    private string $jsonFilePath;

    public function __construct(ParameterBagInterface $params)
    {
        $this->jsonFilePath = $params->get('kernel.project_dir') . '/config/data/partenaire.json';
    }

    public function findAll(): array
    {
        $content = file_get_contents($this->jsonFilePath);
        return json_decode($content, true) ?? [];
    }
    
    public function delete(string $id): bool
    {
        $partenaires = $this->findAll();
        $partenaires = array_filter($partenaires, fn($p) => $p['id'] !== $id);
        $this->save(array_values($partenaires));
        return true;
    }
    
    private function save(array $partenaires): void
    {
        $json = json_encode($partenaires, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->jsonFilePath, $json);
    }
}
```

### 3. Templates Twig

**Structure :**
- Héritage de `base.html.twig`
- Includes pour composants réutilisables
- Turbo Frames pour mises à jour partielles

**Exemple :**
```twig
{% extends 'base.html.twig' %}

{% block title %}{{ 'app.admin.partenaires'|trans }}{% endblock %}

{% block stylesheets %}
    {{ parent() }}
    <link rel="stylesheet" href="{{ asset('styles/admin/users.css') }}">
{% endblock %}

{% block body %}
<div class="admin-container" data-controller="partenaires">
    {% include 'admin/_sidebar.html.twig' %}
    
    <main class="admin-content">
        <!-- Contenu -->
    </main>
</div>
{% endblock %}
```

### 4. JavaScript (Stimulus)

**Conventions :**
- Un controller par fonctionnalité
- Pas de jQuery
- Fetch API pour AJAX
- Notifications visuelles pour feedback

**Pattern standard :**
```javascript
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['form', 'list'];
    static values = { csrfToken: String, url: String };
    
    connect() {
        console.log('Controller connecté');
    }
    
    async submitForm(event) {
        event.preventDefault();
        
        const formData = new FormData(this.formTarget);
        const data = Object.fromEntries(formData);
        
        try {
            const response = await fetch(this.urlValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...data, _token: this.csrfTokenValue })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Succès!', 'success');
                this.refreshList();
            }
        } catch (error) {
            console.error(error);
            this.showNotification('Erreur', 'error');
        }
    }
    
    showNotification(message, type) {
        // Afficher une notification
    }
}
```

### 5. API JSON

**Conventions :**
- Toutes les API retournent du JSON
- Format standard : `{ success: boolean, message?: string, data?: any }`
- Status HTTP appropriés (200, 400, 403, 404, 500)
- Validation CSRF obligatoire

**Exemple de réponse :**
```json
{
  "success": true,
  "message": "Partenaire créé avec succès",
  "data": {
    "id": "5",
    "nom": "Nouveau partenaire"
  }
}
```

---

## ⚙️ Commandes utiles

### Symfony

```bash
# Lancer le serveur de développement
symfony server:start
# ou
php -S localhost:8000 -t public/

# Lister les routes
bin/console debug:router

# Vider le cache
bin/console cache:clear

# Créer une migration
bin/console make:migration

# Exécuter les migrations
bin/console doctrine:migrations:migrate

# Créer une entité
bin/console make:entity

# Créer un controller
bin/console make:controller

# Lister les services
bin/console debug:container

# Afficher la configuration d'un bundle
bin/console debug:config doctrine
```

### Base de données

```bash
# Créer la base de données
bin/console doctrine:database:create

# Supprimer la base de données
bin/console doctrine:database:drop --force

# Voir l'état des migrations
bin/console doctrine:migrations:status

# Rollback une migration
bin/console doctrine:migrations:migrate prev

# Valider le schéma
bin/console doctrine:schema:validate
```

### Assets

```bash
# Compiler Tailwind CSS (production)
npm run build

# Compiler en mode watch (développement)
npm run watch

# Installer les dépendances npm
npm install

# Mettre à jour les dépendances
npm update
```

### Composer

```bash
# Installer les dépendances
composer install

# Mettre à jour les dépendances
composer update

# Ajouter un package
composer require vendor/package

# Supprimer un package
composer remove vendor/package

# Vérifier les problèmes
composer diagnose
```

### Tests

```bash
# Lancer les tests PHPUnit
bin/phpunit

# Tests avec coverage
bin/phpunit --coverage-html var/coverage
```

---

## 🐛 Problèmes connus & Solutions

### 1. Suppression des partenaires ne fonctionne pas

**Symptôme :** La suppression de partenaires depuis l'interface ne fonctionne pas.

**Causes possibles :**
- Types incompatibles : IDs en integer vs string
- URL incorrecte (problème de locale)
- Token CSRF invalide
- Permissions fichier JSON

**Solution :**

1. **Vérifier les types (CRITIQUE) :**
   ```javascript
   // Dans partenaires_controller.js
   const selectedIds = this.checkboxTargets
       .filter(cb => cb.checked)
       .map(cb => cb.value);  // ⚠️ PAS parseInt()! Garder en string
   ```

2. **Côté serveur, convertir en strings :**
   ```php
   $partenaireIds = array_map('strval', $data['ids'] ?? []);
   $count = $partenaireService->deleteMultiple($partenaireIds);
   ```

3. **Vérifier la méthode deleteMultiple :**
   ```php
   public function deleteMultiple(array $ids): int
   {
       $partenaires = $this->findAll();
       $partenaires = array_filter($partenaires, function($p) use ($ids) {
           return !in_array($p['id'], $ids, true);  // strict comparison!
       });
       // ...
   }
   ```

4. **Permissions du fichier :**
   ```bash
   chmod 664 config/data/partenaire.json
   chown www-data:www-data config/data/partenaire.json
   ```

### 2. Flowbite dropdowns cassés après Turbo Frame

**Symptôme :** Les dropdowns Flowbite ne fonctionnent plus après un rechargement via Turbo.

**Solution :** Utiliser du JavaScript vanilla au lieu de Flowbite :

```javascript
// Au lieu de Flowbite
<button data-dropdown-toggle="myDropdown">Menu</button>

// Utiliser du JS vanilla
<button onclick="toggleDropdown('myDropdown')">Menu</button>

<script>
function toggleDropdown(id) {
  const dropdown = document.getElementById(id);
  dropdown.classList.toggle('hidden');
}
</script>
```

### 3. Routes 404 pour les API

**Symptôme :** Erreur 404 sur les appels API comme `/administrateur/partenaires/delete`.

**Causes :**
- Préfixe de locale manquant ou incorrect
- Route non déclarée dans `routes.yaml`

**Solution :**
```javascript
// Dans le Stimulus controller
getLocalizedUrl(path) {
    const locale = document.documentElement.lang || 'fr';
    return `/${locale}${path}`;
}

// Utilisation
const url = this.getLocalizedUrl('/administrateur/partenaires/delete');
```

### 4. CSRF Token invalide

**Symptôme :** Erreur 403 "Token de sécurité invalide".

**Solution :**
1. Vérifier que le token est bien passé dans la requête
2. Vérifier l'ID du token (doit correspondre côté serveur)
3. Le token doit être régénéré à chaque page

```javascript
// JavaScript
body: JSON.stringify({
    ids: selectedIds,
    _token: this.csrfTokenValue  // Important!
})
```

```php
// PHP
$token = new CsrfToken('admin_api', $data['_token'] ?? '');
if (!$csrfTokenManager->isTokenValid($token)) {
    return new JsonResponse(['success' => false], 403);
}
```

### 5. Tailwind styles non appliqués

**Symptôme :** Les classes Tailwind ne fonctionnent pas.

**Solutions :**
1. Vérifier que Tailwind compile : `npm run build` ou `npm run watch`
2. Vérifier que `output.css` est bien généré dans `public/assets/`
3. Vérifier la config Tailwind : `content` doit inclure tous les templates

```javascript
// tailwind.config.js
content: [
    "./templates/**/*.html.twig",
    "./assets/**/*.js",
    "./node_modules/flowbite/**/*.js"
]
```

4. Purge du cache : `bin/console cache:clear`

### 6. Relation User-Partenaire cassée

**Symptôme :** Les utilisateurs n'apparaissent pas dans la liste d'un partenaire.

**Cause :** `User.partenaireId` est un string qui référence l'ID JSON du partenaire.

**Solution :**
```php
// PartenaireJsonService::getUsersForPartenaire()
public function getUsersForPartenaire(string $partenaireId, UserRepository $userRepo): array
{
    return $userRepo->createQueryBuilder('u')
        ->where('u.partenaireId = :id')
        ->setParameter('id', $partenaireId)
        ->getQuery()
        ->getResult();
}
```

**Important :** Ce n'est PAS une relation Doctrine, c'est géré manuellement.

---

## 🎨 Guide de Style & Patterns de Développement

### Patterns d'Architecture

#### 1. Separation of Concerns (Séparation des préoccupations)

**Controllers** - Logique de routage uniquement
```php
// ✅ BON - Controller fin
public function manageUsers(UserRepository $repo): Response
{
    $users = $repo->findAll();
    return $this->render('admin/users.html.twig', ['users' => $users]);
}

// ❌ MAUVAIS - Logique métier dans le controller
public function createUser(Request $request): Response
{
    // Éviter la logique complexe ici
    $data = json_decode($request->getContent(), true);
    $user = new User();
    $user->setEmail($data['email']);
    // ... 20 lignes de logique métier ...
}
```

**Services** - Toute la logique métier
```php
// ✅ BON - Logique dans un service
class UserService
{
    public function createUser(array $data): User
    {
        // Validation
        // Transformation des données
        // Création de l'entité
        // Persistance
        return $user;
    }
}
```

#### 2. Pattern Repository

**Utilisation standard :**
```php
// Dans les controllers
public function index(UserRepository $userRepo): Response
{
    $users = $userRepo->findAll();
    $admins = $userRepo->findByRole('ROLE_ADMINISTRATEUR');
    return $this->render('...', ['users' => $users]);
}
```

**Créer des méthodes personnalisées :**
```php
// Dans UserRepository
public function findByPartenaireId(string $partenaireId): array
{
    return $this->createQueryBuilder('u')
        ->where('u.partenaireId = :id')
        ->setParameter('id', $partenaireId)
        ->orderBy('u.nom', 'ASC')
        ->getQuery()
        ->getResult();
}
```

#### 3. Pattern Service Container

**Injection de dépendances :**
```php
// Toujours utiliser l'injection via constructeur
class MyService
{
    public function __construct(
        private EntityManagerInterface $em,
        private LogService $logger,
        private ParameterBagInterface $params
    ) {}
}

// Éviter les services locators ou container injection
```

---

### Conventions de Nommage

#### Fichiers et Classes

**PHP :**
- Classes : `PascalCase` (ex: `UserRepository`, `PartenaireJsonService`)
- Méthodes : `camelCase` (ex: `findById`, `deleteMultiple`)
- Variables : `camelCase` (ex: `$userId`, `$partenaireService`)
- Constantes : `SCREAMING_SNAKE_CASE` (ex: `ROLE_ADMINISTRATEUR`)

**JavaScript (Stimulus) :**
- Controllers : `kebab-case_controller.js` (ex: `users_controller.js`)
- Méthodes : `camelCase` (ex: `submitForm`, `openModal`)
- Targets : `camelCase` (ex: `modalTarget`, `formTarget`)
- Values : `camelCase` (ex: `csrfTokenValue`, `userIdValue`)

**CSS :**
- Classes : `kebab-case` (ex: `.admin-sidebar`, `.users-table`)
- IDs : `kebab-case` (ex: `#global-loader`, `#delete-modal`)
- Variables CSS : `--kebab-case` (si utilisées)

**Templates Twig :**
- Fichiers : `snake_case.html.twig` (ex: `edit_entreprise.html.twig`)
- Partials : préfixe `_` (ex: `_sidebar.html.twig`)

#### Routes

```php
// Pattern standard : app_{role}_{resource}_{action}
#[Route('/administrateur/users/create', name: 'app_admin_users_create')]
#[Route('/administration/entreprises', name: 'app_administration_index')]

// Routes bilingues
#[Route(path: [
    'fr' => '/administrateur/utilisateurs',
    'en' => '/administrator/users'
], name: 'app_admin_users')]
```

#### Traductions

```yaml
# Structure hiérarchique :
app:
  common:        # Commun à tout le site
    save: Enregistrer
  js:           # Messages JavaScript
    users:
      success: Succès
  admin:        # Section admin
    dashboard: Dashboard
```

**Clés :** Toujours en `snake_case`, hiérarchie par point

---

### Patterns CSS & Styling

#### 1. Architecture CSS

**Organisation des fichiers :**
```
assets/styles/
├── app.css              # Tailwind + styles globaux
├── login.css            # Styles page de connexion
├── profile.css          # Styles profil utilisateur
├── admin/
│   ├── sidebar.css      # Sidebar admin
│   ├── users.css        # Styles table users
│   └── dashboard.css    # Dashboard admin
├── administrateur/       # Styles admin partenaires
└── components/
    └── action-buttons.css  # Composants réutilisables
```

**Principe de cascade :**
1. Tailwind base/components/utilities
2. Styles globaux (header, nav)
3. Styles spécifiques par page
4. Composants réutilisables

#### 2. Pattern BEM Simplifié

**Utilisation modérée de BEM :**
```css
/* Block */
.admin-sidebar { }

/* Element */
.sidebar-menu { }
.sidebar-footer { }

/* State (pas Modifier) */
.sidebar-menu a.active { }
```

**Éviter la sur-imbrication :**
```css
/* ✅ BON */
.users-table { }
.users-table thead { }
.users-table tbody tr { }

/* ❌ MAUVAIS - Trop spécifique */
.admin-container .admin-content .users-table-container .users-table thead tr th { }
```

#### 3. Tailwind First, CSS Custom Second

**Priorité à Tailwind :**
```html
<!-- ✅ BON - Utiliser Tailwind en priorité -->
<div class="flex justify-between items-center mb-4 p-4 bg-white rounded-lg shadow">
  <h2 class="text-xl font-semibold text-gray-800">Titre</h2>
  <button class="px-4 py-2 bg-primary-500 text-white rounded hover:bg-primary-600">
    Action
  </button>
</div>

<!-- ❌ ÉVITER - CSS custom pour des choses simples -->
<div class="custom-header-box">
  <h2 class="custom-title">Titre</h2>
  <button class="custom-button">Action</button>
</div>
```

**CSS Custom pour les patterns récurrents :**
```css
/* ✅ BON - Composants réutilisables complexes */
.modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
    justify-content: center;
    align-items: center;
}

.modal.active {
    display: flex;
}

.btn {
    padding: 0.625rem 1.25rem;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}
```

#### 4. Dark Mode (Préparé mais pas finalisé)

```html
<!-- Utiliser les classes dark: de Tailwind -->
<div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
  Contenu
</div>
```

**Toggle dark mode :**
```javascript
// Ajouter/retirer classe 'dark' sur <html>
document.documentElement.classList.toggle('dark');
```

#### 5. Responsive Design

**Mobile First avec Tailwind :**
```html
<!-- ✅ BON - Mobile first -->
<div class="p-4 md:p-6 lg:p-8">
  <h1 class="text-xl md:text-2xl lg:text-3xl">Titre</h1>
</div>

<!-- Breakpoints Tailwind :
  sm: 640px
  md: 768px
  lg: 1024px
  xl: 1280px
  2xl: 1536px
-->
```

---

### Patterns JavaScript (Stimulus)

#### 1. Structure d'un Controller

**Template standard :**
```javascript
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    // 1. Déclaration des targets et values
    static targets = ['modal', 'form', 'list'];
    static values = { 
        csrfToken: String,
        apiUrl: String,
        confirmMessage: String
    };

    // 2. Lifecycle hooks
    connect() {
        console.log('Controller loaded');
        this.initializeState();
    }

    disconnect() {
        this.cleanup();
    }

    // 3. Actions publiques (appelées depuis HTML)
    async submitForm(event) {
        event.preventDefault();
        // ...
    }

    openModal() {
        this.modalTarget.classList.add('active');
    }

    // 4. Méthodes privées (helpers)
    initializeState() {
        // Setup initial
    }

    validateForm() {
        // Validation
    }

    showNotification(message, type) {
        // Feedback utilisateur
    }
}
```

#### 2. Pattern CRUD Standard

**Dans chaque controller de gestion :**
```javascript
export default class extends Controller {
    // CREATE
    async create(data) {
        const response = await this.apiCall('/create', 'POST', data);
        if (response.success) {
            this.refreshList();
            this.closeModal();
        }
    }

    // READ
    async loadItems() {
        const items = await this.apiCall('/list', 'GET');
        this.displayItems(items);
    }

    // UPDATE
    async update(id, data) {
        const response = await this.apiCall(`/${id}/edit`, 'POST', data);
        if (response.success) {
            this.refreshList();
        }
    }

    // DELETE
    async delete(ids) {
        if (!confirm(this.confirmMessageValue)) return;
        const response = await this.apiCall('/delete', 'POST', { ids });
        if (response.success) {
            this.refreshList();
        }
    }

    // Helper pour les appels API
    async apiCall(endpoint, method, data = null) {
        const url = this.getLocalizedUrl(this.apiUrlValue + endpoint);
        const options = {
            method,
            headers: { 'Content-Type': 'application/json' }
        };
        
        if (data) {
            options.body = JSON.stringify({
                ...data,
                _token: this.csrfTokenValue
            });
        }

        window.showLoader();
        try {
            const response = await fetch(url, options);
            const result = await response.json();
            return result;
        } catch (error) {
            console.error(error);
            this.showNotification('Erreur', 'error');
            return { success: false };
        } finally {
            window.hideLoader();
        }
    }
}
```

#### 3. Pattern Locale Helper

**Dans chaque controller :**
```javascript
getLocalizedUrl(path) {
    const locale = document.documentElement.lang || 'fr';
    return `/${locale}${path}`;
}

// Utilisation
const url = this.getLocalizedUrl('/administrateur/users/create');
// -> /fr/administrateur/users/create  OU  /en/administrator/users/create
```

#### 4. Pattern Modal Management

**Ouvrir/Fermer une modale :**
```javascript
openModal() {
    this.modalTarget.classList.add('active');
    this.preventBodyScroll();
}

closeModal() {
    this.modalTarget.classList.remove('active');
    this.enableBodyScroll();
    this.resetForm();
}

preventBodyScroll() {
    document.body.style.overflow = 'hidden';
}

enableBodyScroll() {
    document.body.style.overflow = '';
}

resetForm() {
    if (this.hasFormTarget) {
        this.formTarget.reset();
    }
}
```

**Modale globale (fonctions window) :**
```javascript
// Dans le template
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.removeAttribute('inert');
    modal.removeAttribute('aria-hidden');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('inert', '');
    modal.setAttribute('aria-hidden', 'true');
}
```

#### 5. Pattern Notification

**Afficher une notification temporaire :**
```javascript
showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#ecfdf5' : '#fee2e2'};
        color: ${type === 'success' ? '#059669' : '#dc2626'};
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 2000;
        font-weight: 500;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
```

#### 6. Pattern Global Functions

**Exposer le controller globalement (pour onclick) :**
```javascript
connect() {
    // Rendre accessible depuis les onclick dans le HTML
    window.usersController = this;
    window.partenairesController = this;
}
```

**Fonctions globales dans base.html.twig :**
```javascript
// Loader
window.showLoader = function() { ... };
window.hideLoader = function() { ... };

// Modales
window.openModal = function(modalId) { ... };
window.closeModal = function(modalId) { ... };

// Confirmations
window.confirmDeleteUser = function() { ... };
```

---

### Patterns Twig

#### 1. Héritage de Templates

**Structure standard :**
```twig
{# base.html.twig - Template parent #}
<!DOCTYPE html>
<html lang="{{ app.request.locale }}">
    <head>
        {% block stylesheets %}
            <link href="{{ asset('assets/output.css') }}" rel="stylesheet">
        {% endblock %}
    </head>
    <body>
        {% block body %}{% endblock %}
        {% block javascripts %}{% endblock %}
    </body>
</html>

{# users.html.twig - Template enfant #}
{% extends 'base.html.twig' %}

{% block title %}{{ 'app.admin.users'|trans }}{% endblock %}

{% block stylesheets %}
    {{ parent() }}  {# ⚠️ Important : garder les styles parent #}
    <link rel="stylesheet" href="{{ asset('styles/admin/users.css') }}">
{% endblock %}

{% block body %}
    <div class="admin-container">
        {# Contenu #}
    </div>
{% endblock %}
```

#### 2. Includes & Composants

**Utilisation des includes :**
```twig
{# Include simple #}
{% include 'admin/_sidebar.html.twig' %}

{# Include avec variables #}
{% include '_components/modal.html.twig' with {
    'modalId': 'deleteModal',
    'title': 'Confirmation'
} %}

{# Include conditionnel #}
{% if app.user and 'ROLE_ADMINISTRATEUR' in app.user.roles %}
    {% include 'admin/_admin_nav.html.twig' %}
{% endif %}
```

#### 3. Data Attributes pour Stimulus

**Pattern standard :**
```twig
<div data-controller="users"
     data-users-csrf-token-value="{{ csrf_token('admin_api') }}"
     data-users-add-user-value="{{ 'app.js.users.add_user'|trans }}"
     data-users-api-url-value="{{ path('app_admin_users') }}">
     
    <button data-action="click->users#openCreateModal">
        {{ 'app.common.add_user'|trans }}
    </button>
    
    <div data-users-target="modal">
        {# Modal content #}
    </div>
</div>
```

#### 4. Boucles & Conditions

**Itération sur collections :**
```twig
{% for user in users %}
    <tr>
        <td>{{ user.nom }}</td>
        <td>{{ user.email }}</td>
    </tr>
{% else %}
    <tr>
        <td colspan="2">{{ 'app.common.empty_state'|trans }}</td>
    </tr>
{% endfor %}
```

**Conditions courantes :**
```twig
{# Vérifier si user connecté #}
{% if app.user %}
    {{ app.user.email }}
{% endif %}

{# Vérifier un rôle #}
{% if 'ROLE_ADMINISTRATEUR' in app.user.roles %}
    {# Admin zone #}
{% endif %}

{# Ternaire #}
<span class="{{ user.isActive ? 'active' : 'inactive' }}">
    {{ user.isActive ? 'Actif' : 'Inactif' }}
</span>

{# Coalescence null #}
{{ user.telephone ?? '-' }}
```

#### 5. Assets & Routing

**Gestion des assets :**
```twig
{# Images #}
<img src="{{ asset('img/logo/Logo CITC_Couleur gris.png') }}" alt="Logo">

{# CSS #}
<link href="{{ asset('assets/output.css') }}" rel="stylesheet">

{# Import maps (JS) #}
{{ importmap('app') }}
```

**Génération de routes :**
```twig
{# Route simple #}
<a href="{{ path('app_home') }}">Accueil</a>

{# Route avec paramètre #}
<a href="{{ path('app_admin_user_edit', {id: user.id}) }}">Modifier</a>

{# Route avec locale #}
<a href="{{ path('app_home', {_locale: 'en'}) }}">English</a>

{# Logout #}
<a href="{{ logout_path() }}">Déconnexion</a>
```

#### 6. Traductions

**Utilisation du filtre trans :**
```twig
{# Simple #}
{{ 'app.common.save'|trans }}

{# Avec paramètres #}
{{ 'app.messages.welcome'|trans({'%name%': user.nom}) }}

{# Pluralisation #}
{{ 'app.messages.items_count'|trans({'%count%': items|length}) }}
```

---

### Patterns PHP (Symfony)

#### 1. Controllers

**Structure recommandée :**
```php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    // Injection via constructeur pour services utilisés partout
    public function __construct(
        private LogService $logger
    ) {}

    // Page affichée
    #[Route('/admin/users', name: 'app_admin_users')]
    public function manageUsers(UserRepository $repo): Response
    {
        $users = $repo->findAll();
        
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    // API endpoint
    #[Route('/admin/users/create', name: 'app_admin_users_create', methods: ['POST'])]
    public function createUser(
        Request $request,
        UserService $userService,
        CsrfTokenManagerInterface $csrf
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validation CSRF
        $token = new CsrfToken('admin_api', $data['_token'] ?? '');
        if (!$csrf->isTokenValid($token)) {
            return new JsonResponse(['success' => false, 'message' => 'CSRF invalide'], 403);
        }
        
        try {
            $user = $userService->createUser($data);
            $this->logger->log('user_created', $user->getId());
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Utilisateur créé',
                'data' => ['id' => $user->getId()]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
```

#### 2. Services

**Pattern standard :**
```php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
        private LogService $logger
    ) {}

    public function createUser(array $data): User
    {
        // Validation
        $this->validateUserData($data);
        
        // Création entité
        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        
        // Hash du password
        if (isset($data['password'])) {
            $hashedPassword = $this->hasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }
        
        // Rôles
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);
        
        // Partenaire
        if (isset($data['partenaire_id'])) {
            $user->setPartenaireId($data['partenaire_id']);
        }
        
        // Persistance
        $this->em->persist($user);
        $this->em->flush();
        
        // Log
        $this->logger->log('user_created', $user->getId());
        
        return $user;
    }

    private function validateUserData(array $data): void
    {
        if (empty($data['email'])) {
            throw new \InvalidArgumentException('Email requis');
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email invalide');
        }
        
        // Autres validations...
    }
}
```

#### 3. Repositories

**Queries personnalisées :**
```php
namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class EntrepriseRepository extends ServiceEntityRepository
{
    // Query simple
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Query avec JOIN
    public function findWithProprietaire(): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.proprietaire', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }

    // Query complexe avec filtres multiples
    public function search(array $filters): array
    {
        $qb = $this->createQueryBuilder('e');

        if (!empty($filters['nom'])) {
            $qb->andWhere('e.nom LIKE :nom')
               ->setParameter('nom', '%'.$filters['nom'].'%');
        }

        if (!empty($filters['statut'])) {
            $qb->andWhere('e.statut = :statut')
               ->setParameter('statut', $filters['statut']);
        }

        if (!empty($filters['proprietaire_id'])) {
            $qb->andWhere('e.proprietaire = :user')
               ->setParameter('user', $filters['proprietaire_id']);
        }

        return $qb->orderBy('e.createdAt', 'DESC')
                  ->getQuery()
                  ->getResult();
    }
}
```

#### 4. Entities

**Pattern standard :**
```php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $partenaireId = null;

    // Getters & Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    // ... autres getters/setters
}
```

---

### Patterns de Validation

#### 1. Validation côté serveur (Symfony Validator)

```php
use Symfony\Component\Validator\Constraints as Assert;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new Assert\Length(['min' => 8]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
                        'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre'
                    ]),
                ],
            ]);
    }
}
```

#### 2. Validation côté client (JavaScript)

```javascript
validateForm() {
    const email = this.emailInputTarget.value;
    const password = this.passwordInputTarget.value;
    
    // Email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        this.showError('Email invalide');
        return false;
    }
    
    // Password
    if (password.length < 8) {
        this.showError('Mot de passe trop court (min 8 caractères)');
        return false;
    }
    
    return true;
}

async submitForm(event) {
    event.preventDefault();
    
    if (!this.validateForm()) {
        return;
    }
    
    // Continuer avec la soumission...
}
```

---

### Helpers Globaux

#### JavaScript

```javascript
// Dans loader_controller.js
window.showLoader = function() { ... };
window.hideLoader = function() { ... };

// Dans base.html.twig
window.openModal = function(modalId) { ... };
window.closeModal = function(modalId) { ... };

// Controllers exposés globalement
window.usersController = this;
window.partenairesController = this;
```

#### Twig

```twig
{# app object - toujours disponible #}
{{ app.user }}                    {# User connecté #}
{{ app.request.locale }}          {# Locale actuelle #}
{{ app.request.attributes.get('_route') }}  {# Route actuelle #}

{# Fonctions courantes #}
{{ path('route_name') }}          {# Générer URL #}
{{ asset('path/to/file') }}       {# URL asset #}
{{ csrf_token('form_id') }}       {# Token CSRF #}
{{ 'key'|trans }}                 {# Traduction #}
```

---

## 📝 Best Practices Spécifiques au Projet

### Code

1. **Ne jamais bypass la validation CSRF**
2. **Toujours typer les paramètres et retours** (PHP 8.4+)
3. **Utiliser des services pour la logique métier**
4. **Logger les actions importantes avec LogService**
5. **Valider les données reçues** (côté serveur ET client)
6. **Retourner des JsonResponse pour les API**
7. **Utiliser les flash messages pour le feedback utilisateur**
8. **Controllers finaux** : `final class` pour éviter l'héritage
9. **Injection de dépendances** : toujours via constructeur
10. **IDs partenaires** : ⚠️ toujours en string, jamais en int

### Frontend

1. **Un Stimulus controller par fonctionnalité**
2. **Éviter le JavaScript inline** (sauf fonctions globales modales/loader)
3. **Utiliser les data attributes** pour la configuration Stimulus
4. **Préférer fetch() à XMLHttpRequest**
5. **Toujours gérer les erreurs réseau** (try/catch)
6. **Afficher des notifications** pour le feedback utilisateur
7. **Accessibilité** : aria-labels, inert sur modales fermées
8. **Tailwind first** : utiliser Tailwind en priorité, CSS custom pour patterns complexes
9. **Mobile first** : responsive avec breakpoints Tailwind
10. **Exposer controllers globalement** uniquement si nécessaire pour onclick

### Sécurité

1. **Toujours valider les entrées utilisateur**
2. **Échapper les sorties dans Twig** (automatique, mais vérifier)
3. **Utiliser les rôles Symfony** pour les accès (`#[IsGranted()]`)
4. **Ne jamais exposer de données sensibles dans les logs**
5. **HTTPS en production**
6. **Configurer les CORS** si nécessaire
7. **Tokens CSRF** sur toutes les API modifiantes
8. **Password hashing** : toujours via UserPasswordHasher
9. **Validation stricte** des IDs et paramètres
10. **Rate limiting** sur les endpoints sensibles (à configurer)

### Performance

1. **Cache Symfony** : utiliser pour les données peu changeantes
2. **Tailwind production** : minifier avec `npm run build`
3. **Lazy load** des images si nombreuses
4. **Index DB** : indexer colonnes fréquemment interrogées
5. **Pagination** : pour listes >100 éléments
6. **Query optimization** : utiliser QueryBuilder, éviter N+1
7. **Asset preloading** : précharger les assets critiques
8. **Turbo Drive** : actif par défaut pour navigation SPA-like
9. **Select avec JOIN** : charger relations en une seule requête
10. **Éviter Doctrine::flush()** dans les boucles

---

## 🎓 Cas d'Usage Courants

### Ajouter une nouvelle fonctionnalité

#### 1. Nouvelle page avec CRUD simple

**Étapes complètes :**

**A. Créer l'entité (si nouvelle)**
```bash
bin/console make:entity NomEntite
# Répondre aux questions pour définir les champs
bin/console make:migration
bin/console doctrine:migrations:migrate
```

**B. Créer le controller**
```php
// src/Controller/MonController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class MonController extends AbstractController
{
    #[Route(path: [
        'fr' => '/mon-chemin',
        'en' => '/my-path'
    ], name: 'app_mon_controller')]
    public function index(MonRepository $repo): Response
    {
        $items = $repo->findAll();
        
        return $this->render('mon/index.html.twig', [
            'items' => $items,
        ]);
    }
}
```

**C. Créer le template**
```twig
{# templates/mon/index.html.twig #}
{% extends 'base.html.twig' %}

{% block title %}{{ 'app.mon.title'|trans }}{% endblock %}

{% block stylesheets %}
    {{ parent() }}
    <link rel="stylesheet" href="{{ asset('styles/mon/index.css') }}">
{% endblock %}

{% block body %}
<div class="container" data-controller="mon">
    <h1>{{ 'app.mon.title'|trans }}</h1>
    
    <table class="table">
        <thead>
            <tr>
                <th>{{ 'app.common.name'|trans }}</th>
                <th>{{ 'app.common.actions'|trans }}</th>
            </tr>
        </thead>
        <tbody>
            {% for item in items %}
            <tr>
                <td>{{ item.nom }}</td>
                <td>
                    <button data-action="click->mon#edit" data-id="{{ item.id }}">
                        {{ 'app.common.edit'|trans }}
                    </button>
                </td>
            </tr>
            {% endfor %}
        </tbody>
    </table>
</div>
{% endblock %}
```

**D. Créer le Stimulus controller**
```javascript
// assets/controllers/mon_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['modal', 'form'];
    static values = { csrfToken: String };

    connect() {
        console.log('Mon controller loaded');
    }

    edit(event) {
        const id = event.currentTarget.dataset.id;
        // Logique d'édition
    }
}
```

**E. Ajouter les traductions**
```yaml
# translations/messages.fr.yaml
app:
  mon:
    title: Mon Titre
    description: Ma description

# translations/messages.en.yaml
app:
  mon:
    title: My Title
    description: My description
```

**F. Créer le CSS** (si nécessaire)
```css
/* assets/styles/mon/index.css */
.container {
    padding: 2rem;
}

.table {
    width: 100%;
    border-collapse: collapse;
}
```

#### 2. Ajouter un endpoint API

**A. Dans le controller**
```php
#[Route('/api/mon-entite/create', name: 'app_api_mon_create', methods: ['POST'])]
public function createItem(
    Request $request,
    MonService $service,
    CsrfTokenManagerInterface $csrf
): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    
    // Validation CSRF
    $token = new CsrfToken('api_token', $data['_token'] ?? '');
    if (!$csrf->isTokenValid($token)) {
        return new JsonResponse(['success' => false, 'message' => 'CSRF invalide'], 403);
    }
    
    try {
        $item = $service->create($data);
        
        return new JsonResponse([
            'success' => true,
            'message' => 'Item créé',
            'data' => ['id' => $item->getId()]
        ]);
    } catch (\Exception $e) {
        return new JsonResponse([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
}
```

**B. Appeler depuis JavaScript**
```javascript
async createItem(data) {
    const url = this.getLocalizedUrl('/api/mon-entite/create');
    
    try {
        window.showLoader();
        
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                ...data,
                _token: this.csrfTokenValue
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            this.showNotification('Item créé !', 'success');
            this.refreshList();
        } else {
            this.showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error(error);
        this.showNotification('Erreur réseau', 'error');
    } finally {
        window.hideLoader();
    }
}
```

#### 3. Ajouter un nouveau rôle

**A. Définir le rôle dans User.php**
```php
// Les rôles sont stockés en array dans User
// Pas besoin de modifier l'entité, juste les utiliser
```

**B. Protéger une route**
```php
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/special', name: 'app_admin_special')]
#[IsGranted('ROLE_SPECIAL')]
public function special(): Response
{
    // Seuls les users avec ROLE_SPECIAL peuvent accéder
    return $this->render('admin/special.html.twig');
}
```

**C. Vérifier dans Twig**
```twig
{% if 'ROLE_SPECIAL' in app.user.roles %}
    <a href="{{ path('app_admin_special') }}">Zone spéciale</a>
{% endif %}
```

#### 4. Créer un service métier

**A. Créer le service**
```php
// src/Service/MonService.php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MonRepository;

class MonService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MonRepository $repo,
        private LogService $logger
    ) {}

    public function create(array $data): MonEntite
    {
        // Validation
        $this->validate($data);
        
        // Logique métier
        $item = new MonEntite();
        $item->setNom($data['nom']);
        $item->setDescription($data['description'] ?? '');
        
        // Persistance
        $this->em->persist($item);
        $this->em->flush();
        
        // Log
        $this->logger->log('mon_entite_created', $item->getId());
        
        return $item;
    }

    public function update(int $id, array $data): MonEntite
    {
        $item = $this->repo->find($id);
        
        if (!$item) {
            throw new \Exception('Item non trouvé');
        }
        
        // Mise à jour
        $item->setNom($data['nom']);
        $this->em->flush();
        
        $this->logger->log('mon_entite_updated', $id);
        
        return $item;
    }

    public function delete(int $id): void
    {
        $item = $this->repo->find($id);
        
        if (!$item) {
            throw new \Exception('Item non trouvé');
        }
        
        $this->em->remove($item);
        $this->em->flush();
        
        $this->logger->log('mon_entite_deleted', $id);
    }

    private function validate(array $data): void
    {
        if (empty($data['nom'])) {
            throw new \InvalidArgumentException('Le nom est requis');
        }
        
        if (strlen($data['nom']) < 3) {
            throw new \InvalidArgumentException('Le nom doit faire au moins 3 caractères');
        }
    }
}
```

**B. Utiliser le service**
```php
// Dans un controller
public function create(Request $request, MonService $service): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    
    try {
        $item = $service->create($data);
        return new JsonResponse(['success' => true, 'id' => $item->getId()]);
    } catch (\Exception $e) {
        return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}
```

#### 5. Ajouter une relation Doctrine

**A. Définir la relation dans les entités**
```php
// Dans Entreprise.php
#[ORM\OneToMany(targetEntity: MonEntite::class, mappedBy: 'entreprise', cascade: ['persist', 'remove'])]
private Collection $mesItems;

public function __construct()
{
    $this->mesItems = new ArrayCollection();
}

public function getMesItems(): Collection
{
    return $this->mesItems;
}

public function addMesItem(MonEntite $item): static
{
    if (!$this->mesItems->contains($item)) {
        $this->mesItems->add($item);
        $item->setEntreprise($this);
    }
    return $this;
}

// Dans MonEntite.php
#[ORM\ManyToOne(targetEntity: Entreprise::class, inversedBy: 'mesItems')]
#[ORM\JoinColumn(nullable: false)]
private ?Entreprise $entreprise = null;

public function getEntreprise(): ?Entreprise
{
    return $this->entreprise;
}

public function setEntreprise(?Entreprise $entreprise): static
{
    $this->entreprise = $entreprise;
    return $this;
}
```

**B. Créer la migration**
```bash
bin/console make:migration
bin/console doctrine:migrations:migrate
```

**C. Charger avec JOIN**
```php
// Dans EntrepriseRepository
public function findWithMesItems(): array
{
    return $this->createQueryBuilder('e')
        ->leftJoin('e.mesItems', 'i')
        ->addSelect('i')
        ->getQuery()
        ->getResult();
}
```

---

## 🛠️ Debugging & Troubleshooting

### Commandes de debug

```bash
# Vérifier les routes
bin/console debug:router

# Vérifier une route spécifique
bin/console debug:router app_admin_users

# Voir tous les services
bin/console debug:container

# Voir un service spécifique
bin/console debug:container PartenaireJsonService

# Vider le cache
bin/console cache:clear

# Voir la configuration
bin/console debug:config doctrine

# Valider le schéma DB
bin/console doctrine:schema:validate

# Voir l'état des migrations
bin/console doctrine:migrations:status

# Voir les traductions manquantes
bin/console debug:translation fr
```

### Logs

**Consulter les logs :**
```bash
# Logs en temps réel
tail -f var/log/dev.log

# Dernières erreurs
tail -100 var/log/dev.log | grep ERROR

# Recherche spécifique
grep "partenaire" var/log/dev.log
```

**Ajouter des logs :**
```php
// Dans un controller ou service
error_log('Debug: ' . json_encode($data));
```

### Console navigateur

**Activer les logs détaillés dans Stimulus :**
```javascript
connect() {
    console.log('Controller loaded', this.element);
    console.log('Targets:', this.targets);
    console.log('Values:', this.values);
}
```

**Débugger les requêtes fetch :**
```javascript
async myFetch() {
    console.log('Envoi requête vers:', url);
    console.log('Body:', JSON.stringify(body));
    
    const response = await fetch(url, options);
    console.log('Status:', response.status);
    
    const result = await response.json();
    console.log('Résultat:', result);
}
```

### Problèmes courants et solutions

**1. Route 404**
```bash
# Vérifier que la route existe
bin/console debug:router | grep mon_route

# Vider le cache
bin/console cache:clear
```

**2. Tailwind styles non appliqués**
```bash
# Recompiler Tailwind
npm run build

# En mode watch pour développement
npm run watch
```

**3. Stimulus controller ne se charge pas**
```bash
# Vérifier controllers.json
cat assets/controllers.json

# Vérifier la console navigateur
# F12 -> Console -> chercher erreurs JavaScript
```

**4. Erreur 500 sur API**
```bash
# Voir les logs détaillés
tail -50 var/log/dev.log

# Activer le mode debug dans .env
APP_ENV=dev
APP_DEBUG=1
```

**5. Migration échoue**
```bash
# Voir l'état
bin/console doctrine:migrations:status

# Rollback
bin/console doctrine:migrations:migrate prev

# Recréer la migration
rm migrations/Version*.php
bin/console make:migration
bin/console doctrine:migrations:migrate
```

---

## 🎯 Workflow de Développement Recommandé

### 1. Nouvelle fonctionnalité

```bash
# 1. Créer une branche
git checkout -b feature/ma-nouvelle-fonctionnalite

# 2. Si changement DB
bin/console make:entity MonEntite
bin/console make:migration
bin/console doctrine:migrations:migrate

# 3. Développement
# - Créer controller
# - Créer service si logique métier
# - Créer template
# - Créer Stimulus controller
# - Ajouter traductions FR + EN
# - Créer/adapter CSS

# 4. Tester
# - Tester en FR et EN
# - Tester avec différents rôles
# - Vérifier console navigateur (F12)
# - Vérifier logs Symfony

# 5. Compiler assets
npm run build

# 6. Commit
git add .
git commit -m "feat: Ajout de ma nouvelle fonctionnalité"

# 7. Push
git push origin feature/ma-nouvelle-fonctionnalite
```

### 2. Correction de bug

```bash
# 1. Reproduire le bug
# - Noter les étapes exactes
# - Vérifier logs (var/log/dev.log)
# - Vérifier console navigateur

# 2. Identifier la cause
# - Ajouter des logs si nécessaire
# - Utiliser dump() dans Twig/PHP
# - Console.log() dans JS

# 3. Corriger
# - Modifier le code
# - Tester le fix
# - Vérifier qu'on ne casse rien d'autre

# 4. Commit
git commit -m "fix: Correction du bug XYZ"
```

### 3. Refactoring

```bash
# 1. Identifier le code à refactorer
# 2. Écrire des tests (si pas déjà fait)
# 3. Refactorer progressivement
# 4. Tester après chaque étape
# 5. Commit régulièrement
git commit -m "refactor: Simplification du service User"
```

### 4. Tests manuels recommandés

**Avant chaque commit :**
- [ ] Version FR fonctionne
- [ ] Version EN fonctionne
- [ ] Aucune erreur console navigateur
- [ ] Aucune erreur logs Symfony
- [ ] Responsive (mobile, tablette, desktop)
- [ ] Différents rôles testés
- [ ] Mode connecté/déconnecté

**Test d'une page CRUD :**
- [ ] Liste s'affiche
- [ ] Création fonctionne
- [ ] Édition fonctionne
- [ ] Suppression fonctionne
- [ ] Recherche/filtres fonctionnent
- [ ] Messages de succès/erreur s'affichent
- [ ] Modales s'ouvrent/ferment correctement

**Test d'une API :**
- [ ] Validation CSRF fonctionne
- [ ] Données valides acceptées
- [ ] Données invalides rejetées
- [ ] Erreurs retournent messages clairs
- [ ] Status HTTP corrects (200, 400, 403, 500)

---

## 📚 Ressources & Références

### Documentation Symfony

- [Routing](https://symfony.com/doc/current/routing.html)
- [Controllers](https://symfony.com/doc/current/controller.html)
- [Doctrine](https://symfony.com/doc/current/doctrine.html)
- [Forms](https://symfony.com/doc/current/forms.html)
- [Security](https://symfony.com/doc/current/security.html)
- [Translation](https://symfony.com/doc/current/translation.html)
- [Validation](https://symfony.com/doc/current/validation.html)

### Documentation Frontend

- [Stimulus Handbook](https://stimulus.hotwired.dev/handbook/introduction)
- [Turbo Handbook](https://turbo.hotwired.dev/handbook/introduction)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Flowbite Components](https://flowbite.com/docs/getting-started/introduction/)
- [Chart.js](https://www.chartjs.org/docs/latest/)
- [ECharts](https://echarts.apache.org/en/api.html)

### Outils de développement

- [Symfony CLI](https://symfony.com/download) - Outil CLI Symfony
- [Composer](https://getcomposer.org/) - Gestionnaire de dépendances PHP
- [npm](https://www.npmjs.com/) - Gestionnaire de dépendances JavaScript
- [Symfony Profiler](https://symfony.com/doc/current/profiler.html) - Barre de debug

### Extensions VS Code recommandées

- PHP Intelephense
- Twig Language 2
- Tailwind CSS IntelliSense
- ESLint
- Symfony Support
- GitLens

---

## 🎯 Roadmap & Améliorations Futures

### Fonctionnalités à implémenter

- [ ] **Système de notifications** en temps réel (Mercure)
- [ ] **Export PDF** des entreprises
- [ ] **Import CSV** en masse
- [ ] **Dashboard personnalisable** par utilisateur
- [ ] **Historique des modifications** (audit trail complet)
- [ ] **Recherche avancée** avec filtres sauvegardés
- [ ] **API REST complète** avec authentification token
- [ ] **Tests automatisés** (PHPUnit + Jest)
- [ ] **Dark mode** finalisé et persistant
- [ ] **Permissions granulaires** par fonctionnalité

### Optimisations techniques

- [ ] **Pagination** sur toutes les listes
- [ ] **Cache Redis** pour données stables
- [ ] **Lazy loading** des relations Doctrine
- [ ] **CDN** pour assets statiques
- [ ] **Compression** des assets JS/CSS
- [ ] **Service Workers** pour mode offline
- [ ] **Rate limiting** sur API
- [ ] **Queue system** pour traitements lourds (Messenger)

### Améliorations UX

- [ ] **Skeleton loaders** pendant chargements
- [ ] **Drag & drop** pour upload fichiers
- [ ] **Tooltips** contextuels
- [ ] **Keyboard shortcuts** pour actions courantes
- [ ] **Undo/Redo** sur suppressions
- [ ] **Bulk actions** améliorées
- [ ] **Filtres sauvegardés** par utilisateur
- [ ] **Favoris** et vues personnalisées

---

## 📧 Contact & Support

Pour toute question sur le projet, contacter l'équipe technique.

**Équipe de développement :**
- Backend : Symfony 8.0, PHP 8.4, Doctrine
- Frontend : Stimulus, Tailwind CSS, Turbo
- Infrastructure : Docker, Linux

**Support :**
- Issues : Utiliser le système de tickets interne
- Documentation : Ce fichier CLAUDE.md
- Logs : `var/log/dev.log` et console navigateur

---

**Date de création :** 9 février 2026  
**Dernière mise à jour :** 9 février 2026  
**Auteur :** Claude AI (Assistant développement)  
**Version :** 2.0 (enrichie avec patterns détaillés)

---

## 🔗 Ressources

### Documentation officielle

- [Symfony 8.0](https://symfony.com/doc/8.0/index.html)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)
- [Stimulus](https://stimulus.hotwired.dev/)
- [Turbo](https://turbo.hotwired.dev/)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Flowbite](https://flowbite.com/)
- [Chart.js](https://www.chartjs.org/docs/latest/)
- [ECharts](https://echarts.apache.org/en/index.html)

### Symfony Bundles utilisés

- `symfony/stimulus-bundle`
- `symfony/ux-turbo`
- `symfony/asset-mapper`
- `doctrine/doctrine-bundle`
- `symfony/security-bundle`
- `symfony/mailer`
- `symfony/translation`

---

## 🎯 Workflow de développement recommandé

1. **Nouvelle fonctionnalité**
   - Créer une migration si modèle de données modifié
   - Créer/modifier l'entité
   - Créer/modifier le service
   - Créer/modifier le controller
   - Créer/modifier le template
   - Créer/modifier le Stimulus controller
   - Ajouter les traductions (FR + EN)
   - Tester
   - Commit

2. **Debugging**
   - Vérifier les logs : `tail -f var/log/dev.log`
   - Console navigateur (F12)
   - Symfony Profiler (barre de debug en bas)
   - `dump()` dans Twig ou PHP
   - Vérifier les routes : `bin/console debug:router`

3. **Tests**
   - Tester en mode authentifié et non-authentifié
   - Tester avec différents rôles
   - Tester la version FR et EN
   - Tester en mode dark
   - Vérifier la responsivité mobile

---

## 📧 Contact & Support

Pour toute question sur le projet, contacter l'équipe technique.

---

**Date de création :** 9 février 2026  
**Auteur :** Claude AI (Assistant développement)  
**Version :** 1.0
