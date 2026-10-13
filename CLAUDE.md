# Luminest

Real-estate management system for one subdivision: house reservations and purchases, maintenance requests, and user administration. There are five roles: Admin, Property Manager, Maintenance Staff, Tenant, Prospect.

**Current task: convert the native-PHP app into a Laravel REST API + React SPA + MySQL** (ITELEC 4100 final project, "Web API").

## Read before working

1. `docs/LARAVEL_REACT_PLAN.md` covers architecture, folder structure, the API contract, the page map, build phases, conventions and open decisions. **It is authoritative for how to build.**
2. `docs/SYSTEM_DOCUMENTATION.md` covers the legacy app's workflows, status rules, DB schema and known defects (§7). **It is authoritative for business rules.** Do not reproduce the defects listed in §7.

## Ground rules

- Stack is fixed: Laravel 12 (API only, Sanctum SPA cookie auth) in `backend/`, React (Vite, JavaScript) in `frontend/`, MySQL. No Blade UI, no Inertia, no Livewire.
- Work one build phase (plan §7) at a time. Meet its acceptance criteria and stop. Do not jump ahead.
- MVC is graded:
  - controllers stay thin (FormRequest → Policy → Service → API Resource);
  - business rules and transactions go in `app/Services`;
  - status transitions go in `app/Enums`.
- Every API endpoint gets a Pest feature test: happy path, forbidden role, and one validation error.
- Before finishing: `cd backend && ./vendor/bin/pint && php artisan test`, and `cd frontend && npm run lint`.
- The legacy native-PHP code is reference only (moved to `legacy/` in Phase 0; until then it sits at the repo root). Never edit it.
- If an open decision in plan §12 blocks the task, ask instead of guessing.

## Commands (after Phase 0)

```bash
cd backend && php artisan migrate:fresh --seed && php artisan serve   # :8000
cd frontend && npm run dev                                             # :5173
cd backend && php artisan test
```

Seeded logins: `admin@`, `manager@`, `staff.plumbing@`, `staff.electrical@`, `tenant@`, `prospect@luminest.test` / `password`.
