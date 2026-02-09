# Copilot Instructions for DataSimplify

## Project Overview
- **Framework:** Symfony (PHP)
- **Frontend:** Stimulus controllers (JS in `assets/controllers/`), CSS in `assets/styles/`
- **Backend:** Symfony controllers, entities, forms, services in `src/`
- **Data:** Doctrine ORM, migrations in `migrations/`, config in `config/`
- **Templates:** Twig templates in `templates/`

## Key Workflows
- **Run dev server:** `bin/console server:run` or use Symfony CLI
- **Run tests:** `bin/phpunit` (uses `phpunit.dist.xml`)
- **Install JS/CSS assets:** Managed via Symfony Asset Mapper, JS entry: `assets/app.js`, CSS: `assets/styles/`
- **Migrations:** `bin/console doctrine:migrations:migrate`

## Project Structure
- `src/Controller/` — Symfony controllers (HTTP endpoints)
- `src/Entity/` — Doctrine entities (DB models)
- `src/Form/` — Symfony forms
- `src/Service/` — Custom business logic
- `assets/controllers/` — Stimulus JS controllers (frontend logic)
- `templates/` — Twig templates (views)
- `config/` — Symfony and package configs
- `migrations/` — DB migration scripts

## Patterns & Conventions
- **Twig templates** are used for all HTML rendering. Extend `base.html.twig` for new pages.
- **Stimulus controllers** are registered in `assets/controllers.json` and loaded via `stimulus_bootstrap.js`.
- **CSRF protection** is handled via Symfony's built-in mechanisms and custom JS controller (`csrf_protection_controller.js`).
- **Entities** are mapped with Doctrine annotations or YAML/XML (see `src/Entity/`).
- **Routes** are defined in `config/routes.yaml` and `config/routes/`. Use bilingual routes (FR/EN).
- **Translations** in `translations/` (YAML files). Always provide both `messages.fr.yaml` and `messages.en.yaml`.

## UI/UX Conventions
- **Styling:** Tailwind CSS with dark mode support (`dark:` variants)
- **Modals:** Use static HTML modals in templates with global JS functions (`openModal()`, `closeModal()`)
  - Delete confirmations: Replace native `confirm()` with styled modals
  - Modal z-index: confirmation modals at `z-[9999]`, backdrop at `z-[9998]`
- **Forms:** 
  - Always include CSRF tokens via `csrf_token('token_id')`
  - Disabled fields use grayed-out style with explanatory text
  - Flash messages for user feedback (success/error)
- **Turbo Frames:** Used for partial page updates without full reload
  - Beware: Flowbite dropdowns may break after Turbo updates, use vanilla JS instead

## Data Patterns
- **Partenaires (Partners):** Stored in `config/data/partenaire.json` and managed via `PartenaireJsonService`
  - Users have `partnaireId` (string) linking to JSON data, not a Doctrine relationship
  - Use service to fetch partner details: `$partenaireService->findById($id)`
- **User roles:** `ROLE_USER`, `ROLE_ADMINISTRATION`, `ROLE_ADMINISTRATEUR`

## Code Style
- Keep controllers thin, move business logic to services
- Use constructor dependency injection
- Form submissions return redirects with flash messages
- Avoid destructive operations (e.g., account deletion) unless explicitly required

## Integration Points
- **Mailer:** Configured in `config/packages/mailer.yaml`
- **Doctrine:** DB config in `config/packages/doctrine.yaml`
- **Asset Mapper:** `config/packages/asset_mapper.yaml`
- **Custom services:** Register in `config/services.yaml`

## Examples
- Add a new page: Create a controller in `src/Controller/`, a Twig template in `templates/`, and a route in `config/routes.yaml`.
- Add a JS feature: Create a Stimulus controller in `assets/controllers/`, register in `controllers.json`.

## Common Tasks
- **Clear cache:** `bin/console cache:clear`
- **Build assets:** `npm run build`
- **Create migration:** `bin/console make:migration`
- **Run migration:** `bin/console doctrine:migrations:migrate`

## Working with Copilot
- **Approach:** Action-oriented. Implement changes directly rather than suggesting.
- **Communication:** Short, direct responses. No unnecessary explanations.
- **Workflow:** Iterative corrections are expected and preferred.
- **Errors:** Fix immediately when reported, no need to ask permission.

## Tips
- Use `bin/console` for most Symfony tasks.
- Keep business logic in `src/Service/` for testability.
- Use migrations for DB changes, not manual SQL.
- Always test modals and forms after Turbo Frame updates.
- Check both light and dark mode when styling.

---
_Last updated: February 2026. Update as project conventions evolve._
