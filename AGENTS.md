# AGENTS.md — PROMAN5 (Construction Project Management ERP)

## Project Overview
- **Laravel 12** monolith with **Livewire 3.6** + **TailwindCSS** + **Vite** frontend
- Construction ERP: RAB (budgeting), AHSP (unit price analysis), procurement (PR/PO/GR), inventory, scheduling, progress/weekly/monthly reports
- Locale: `id` (Indonesian), Timezone: `Asia/Jakarta`
- Proprietary project — do not distribute

## Developer Commands
```bash
composer install && npm install          # Initial setup
cp .env.example .env && php artisan key:generate
php artisan migrate                       # Run migrations
composer run dev                          # Dev: server + queue + logs + Vite (via concurrently)
composer run test                         # Run PHPUnit tests
php artisan test --filter=ClassName       # Run single test class
vendor/bin/pint                           # Format code (Laravel Pint)
```

## Architecture
| Layer | Location | Notes |
|-------|----------|-------|
| Web Controllers | `app/Http/Controllers/` | Blade views via `resources/views/` |
| API Controllers | `app/Http/Controllers/Api/` | JSON responses, `auth:sanctum` middleware |
| Livewire Components | `app/Livewire/` | 22 components for interactive UI |
| Services | `app/Services/` | Business logic: approval, AI analysis, reports, scheduling |
| Models | `app/Models/` | 40 models, all standard Laravel |
| Jobs | `app/Jobs/` | Queue workers |

### Key Route Groups
- **Web** (`routes/web.php`): Auth required via Jetstream. Project-scoped routes under `projects/{project}` with `project_member` middleware
- **API** (`routes/api.php`): `auth:sanctum`, project-scoped with `scopeBindings()`
- **Owner Portal**: Separate views/API under `/owner` and `/api/owner` with `owner_portal` middleware
- **Settings**: Superadmin-only at `/settings/system`, user/role management at `/settings/users`, `/settings/roles`

### Important Middleware
- `owner_portal` — restricts access to owner portal views/API
- `project_member` — ensures user is member of the project

## RBAC & Permissions
- Uses `spatie/laravel-permission`
- **4 roles**: Superadmin/PM, Estimator/QS, Site Manager/Engineer, Logistik/Purchasing
- Approval permissions are **separate** from manage permissions (e.g., `mr.approve` vs `mr.manage`)
- **Self-approval is prohibited** — creator cannot approve their own documents
- Full matrix: see `PERMISSION_MATRIX.md`
- Financial fields (`unit_price`, `total_price`) hidden from users without `financials.view`

## Database & Testing
- **Dev DB**: MySQL (`bege_proman5`), see `.env`
- **Test DB**: SQLite `:memory:` (configured in `phpunit.xml`)
- Tests use in-memory SQLite with `sync` queue, `array` cache/session
- Test suites: `tests/Unit` and `tests/Feature`
- **No CI/CD workflows** configured

## External Services
| Service | Config | Purpose |
|---------|--------|---------|
| Firebase (FCM) | `storage/app/firebase-credentials.json` | Push notifications |
| Pusher | `BROADCAST_CONNECTION=pusher` | Real-time events (Livewire) |
| OpenAI | `OPENAI_API_KEY` | AI material analysis |
| Gemini | `GEMINI_API_KEY` | Default AI provider (`AI_DEFAULT_PROVIDER=gemini`) |
| AWS S3 | `AWS_*` vars | Optional cloud storage |

## Notable Quirks
- **Debug files in root**: `debug_dump.php`, `debug_trace.php`, `debug_hierarchy.php`, `debug_output.txt`, `debug_sections.php`, `scratch/` — leftover dev artifacts, ignore or clean
- **AHSP routes require careful ordering**: static routes (`/ahsp/prices/search`) must be defined before parameter routes (`/ahsp/{ahspWorkType}`) — see `routes/web.php` comments
- **Weekly/Monthly Reports** have complex approval workflows: draft → submit → approve/reject → publish
- **Procurement chain**: Material Request → Purchase Request → Purchase Order → Goods Receipt → Inventory
- **Scribe** + **Scramble** for API docs — regenerate with `php artisan scribe:generate` after API changes
- **Excel import/export** via `maatwebsite/excel` — RAB, materials, schedules
- **Document versioning** for project files (`ProjectFile` → `ProjectFileVersion` → `ProjectFileComment`)

## Code Style
- PHP 8.2+, Laravel Pint for formatting
- `prefer-stable: true` in composer
- PSR-4 autoloading: `App\` → `app/`, `Tests\` → `tests/`
