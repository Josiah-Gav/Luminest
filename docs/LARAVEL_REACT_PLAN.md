# Luminest: Laravel + React Conversion Plan

> **Who this is for:** the team and Claude Code. Read this before writing any code for the rebuild.
> **Companion document:** [`SYSTEM_DOCUMENTATION.md`](SYSTEM_DOCUMENTATION.md) is the source of truth for **business rules** (workflows, statuses, validation, known defects). This plan covers **how** to build it with the required stack. Where the two disagree, this plan wins on architecture (it supersedes §8.1 and §8.8 of the system documentation, which assumed Blade/Livewire).

---

## 1. What the course requires (ITELEC 4100 Final Project: Web API)

| # | Requirement | How this plan meets it |
|---|---|---|
| 1 | Convert the native PHP app into **Laravel + React JS** | `backend/` = Laravel REST API, `frontend/` = React SPA (Vite) |
| 2 | May enhance features based on comments on the previous project | §9 Enhancements. **The team must fill in the instructor's comments.** |
| 3 | Optional Google or Facebook login (bonus points) | Phase 7: Google login via Laravel Socialite |
| 4 | 15–20 min video: backend, frontend, code structure; every member presents | §11 Presentation plan |
| — | Backend **Laravel**, frontend **React JS**, DB **MySQL** | Fixed. No other frameworks for these three layers. |

**Rubric, and what it means for the code**

| Category | Points | What graders will look for → what we do |
|---|---|---|
| Functionality | 35 | Every module works, no errors, **MVC pattern** → full feature parity (§6), feature tests, thin controllers + models + API Resources |
| Design | 15 | Looks good, user-friendly → consistent layout, loading/empty/error states, toasts, confirm dialogs, responsive |
| Coding standard | 25 | Indentation, naming, easy to follow → Laravel Pint + ESLint/Prettier enforced, conventions in §8 |
| Presentation | 25 | Explain code confidently, everyone has a part → module ownership in §11 |

---

## 2. Architecture decisions (settled, do not revisit without reason)

| Decision | Choice | Why |
|---|---|---|
| App shape | **Separate React SPA + Laravel JSON API** | The project is titled "Web API". Inertia would render pages from Laravel and expose no real API. |
| Repo layout | Monorepo: `backend/`, `frontend/`, legacy code moved to `legacy/` | One repo, one history, and the old app stays available for reference |
| Laravel | 12.x, PHP 8.2+ | Current LTS-track version |
| API auth | **Laravel Sanctum, SPA cookie auth** (stateful, CSRF-protected) | Safer than storing tokens in localStorage; works with Socialite redirects |
| React tooling | **Vite + React 18 + JavaScript (JSX)** | The brief says "React JS". TypeScript is optional; only adopt it if the whole team is comfortable explaining it. |
| Routing (FE) | React Router 6 | Standard |
| Server state (FE) | **TanStack Query** + **axios** | Caching, loading/error states, refetch-on-interval (replaces the legacy 10-second polling) |
| Forms (FE) | react-hook-form | Less boilerplate. Laravel 422 errors map onto fields. |
| UI kit | **Bootstrap 5 + react-bootstrap** + Bootstrap Icons | The team already knows Bootstrap from the legacy app, which keeps the brand look |
| Notifications (FE) | react-hot-toast (or react-bootstrap Toast) | Feedback on every action |
| DB | MySQL 8, database `luminest_v2` (do not reuse the legacy DB) | Clean schema, seeded from scratch |
| Tests | Pest (backend feature tests) | Protects the business rules during refactors |
| Lint/format | Laravel Pint (PSR-12) · ESLint + Prettier | Rubric: coding standard |

### Request flow (the MVC story for the presentation)

```
React page ──axios──▶ routes/api.php ──▶ middleware (auth:sanctum, role)
                                          │
                                          ▼
                       FormRequest (validate) ──▶ Controller (thin)
                                                    │
                                                    ▼
                                    Policy (may I?) ─▶ Service (business rules, DB transaction)
                                                    │
                                                    ▼
                                         Eloquent Model ◀──▶ MySQL
                                                    │
                                                    ▼
                                   API Resource (JSON shape) ──▶ React renders
```

- **M** = Eloquent models + enums.
- **C** = API controllers.
- **V** = API Resources (the JSON "view") on the server, plus React components on the client.

Say this explicitly in the video.

---

## 3. Target folder structure

```
/
├── CLAUDE.md                  # Context for Claude Code (short; points here)
├── docs/                      # This plan + system documentation
├── legacy/                    # Old native-PHP app, read-only reference
├── backend/                   # Laravel 12
│   ├── app/
│   │   ├── Enums/             # Role, HouseStatus, ReservationStatus, MaintenanceStatus, MaintenanceCategory, Priority
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   │   ├── Auth/              # AuthController, SocialAuthController, ProfileController
│   │   │   │   ├── HouseTypeController.php
│   │   │   │   ├── ReservationController.php          # prospect/tenant self-service
│   │   │   │   ├── Tenant/MaintenanceRequestController.php
│   │   │   │   ├── Staff/MaintenanceRequestController.php
│   │   │   │   ├── PropertyManager/{Dashboard,House,Reservation,Tenant,MaintenanceRequest,Staff}Controller.php
│   │   │   │   └── Admin/{Dashboard,User}Controller.php
│   │   │   ├── Middleware/EnsureUserHasRole.php
│   │   │   ├── Requests/      # one FormRequest per write endpoint
│   │   │   └── Resources/     # UserResource, HouseResource, ReservationResource, ...
│   │   ├── Models/            # User, HouseType, House, Reservation, Payment, MaintenanceRequest
│   │   ├── Policies/          # ReservationPolicy, MaintenanceRequestPolicy, UserPolicy
│   │   └── Services/          # ReservationService, MaintenanceService
│   ├── database/{migrations,factories,seeders}/
│   ├── routes/{api.php,web.php}
│   └── tests/Feature/
└── frontend/                  # React + Vite
    └── src/
        ├── api/               # client.js (axios instance) + one module per resource
        ├── auth/              # AuthProvider, useAuth, ProtectedRoute, RoleRoute
        ├── components/
        │   ├── layout/        # AppLayout, Sidebar (role-based menu), Topbar, PublicLayout
        │   └── ui/            # StatusBadge, PriorityBadge, DataTable, SearchFilterBar, ConfirmDialog, EmptyState, Pagination
        ├── pages/
        │   ├── public/        # Landing, HouseTypeDetail, Login, Register, OAuthCallback, NotFound
        │   ├── prospect/      # Dashboard, ReserveUnit, MyReservations
        │   ├── tenant/        # Dashboard, MaintenanceList, MaintenanceCreate, MaintenanceDetail
        │   ├── staff/         # Dashboard, AssignedRequests, RequestDetail, History
        │   ├── manager/       # Dashboard, Houses, Reservations, Tenants, Maintenance, MaintenanceHistory, Staff
        │   ├── admin/         # Dashboard, Users, UserForm, UserDetail
        │   └── shared/        # Profile
        ├── hooks/             # useDebounce, etc.
        ├── utils/             # formatters (currency ₱, dates), status labels
        ├── routes.jsx
        └── main.jsx
```

---

## 4. Database (Laravel migrations)

Use the schema in **SYSTEM_DOCUMENTATION.md §8.2** as-is: `users`, `house_types`, `houses`, `reservations`, `payments`, `maintenance_requests`. Additional rules:

- Status/category/priority columns are `string` + **PHP backed enums** with Eloquent casts. Do not use MySQL `ENUM`.
- `houses` has `unique(block, lot)`. `reservations.house_id` is a real FK.
- No `users.owned_house`. Ownership = `houses.owner_id`.
- Add `users.google_id` (nullable, unique) and `users.avatar_url` (nullable) for Phase 7. Make `users.password` nullable for Google-only accounts.
- Store everything raw. **Never** `htmlspecialchars` before saving; React escapes output.

**Seeders** (must produce a demo-ready database with `php artisan migrate:fresh --seed`):

- `HouseTypeSeeder`: the 4 models from SYSTEM_DOCUMENTATION §5.4 (with prices).
- `HouseSeeder`: the 870 units, using the block layout in §5.3.
- `UserSeeder`: one account per role, password `password`: `admin@`, `manager@`, `staff.plumbing@`, `staff.electrical@`, `tenant@`, `prospect@luminest.test`.
- `DemoSeeder`: reservations and maintenance requests in every status, so every screen has data during the video.

Legacy data import is **not** required for the course. Skip SYSTEM_DOCUMENTATION §9 unless real data must be kept.

---

## 5. API contract

Base URL `/api`. JSON only. All routes except "public" require `auth:sanctum`. Errors use Laravel defaults: `401` unauthenticated, `403` forbidden (policy/role), `404`, `422` `{message, errors: {field: [..]}}`. Lists are paginated (`?page=`, Laravel `meta`/`links`).

### 5.1 Auth & profile

| Method | Path | Who | Notes |
|---|---|---|---|
| GET | `/sanctum/csrf-cookie` | public | Call once before login/register |
| POST | `/api/register` | public | name, email, phone_number, password, password_confirmation → role `prospect` |
| POST | `/api/login` | public | email, password. Reject `is_active = false`. |
| POST | `/api/logout` | auth | |
| GET | `/api/user` | auth | Current user (UserResource incl. role, owned houses) |
| PUT | `/api/profile` | auth | name, email, phone_number |
| PUT | `/api/profile/password` | auth | current_password, password, password_confirmation |
| GET | `/auth/google/redirect` | public (web route) | Phase 7 |
| GET | `/auth/google/callback` | public (web route) | Log in or create a prospect, then redirect to `FRONTEND_URL/oauth/callback` |

### 5.2 Public catalog

| Method | Path | Notes |
|---|---|---|
| GET | `/api/house-types` | 4 models with price and image |
| GET | `/api/house-types/{slug}` | Detail |
| GET | `/api/house-types/{slug}/available-units` | `{ "5": [1,2,7], "6": [3] }` = block → available lots |

### 5.3 Prospect / Tenant: reservations (role: prospect, tenant)

| Method | Path | Rule (see SYSTEM_DOCUMENTATION §4.2) |
|---|---|---|
| GET | `/api/reservations` | Own reservations |
| POST | `/api/reservations` | `{house_id}`. Unit available. No other pending reservation. Row lock. |
| POST | `/api/reservations/{id}/cancel` | Owner. Pending only. Releases the unit. |
| POST | `/api/reservations/{id}/pay` | Owner. Accepted only. Creates a Payment (amount from house_type.price, method `simulated`). House becomes sold with owner. Prospect becomes tenant. |

### 5.4 Tenant: maintenance (role: tenant)

| Method | Path | Rule |
|---|---|---|
| GET | `/api/tenant/maintenance-requests` | Own requests |
| POST | `/api/tenant/maintenance-requests` | title, description, category, priority, house_id (must be a house the tenant owns) |
| GET | `/api/tenant/maintenance-requests/{id}` | Owner |
| POST | `/api/tenant/maintenance-requests/{id}/resolve` | Owner. Completed only. |

### 5.5 Maintenance staff (role: maintenance_staff)

| Method | Path | Rule |
|---|---|---|
| GET | `/api/staff/maintenance-requests?scope=active\|history` | **Only requests assigned to me** (fixes the legacy access hole) |
| GET | `/api/staff/maintenance-requests/{id}` | Assigned to me |
| PATCH | `/api/staff/maintenance-requests/{id}` | `{status: in_progress\|completed, resolution_notes}`. Allowed transitions only. |

### 5.6 Property manager (role: property_manager)

| Method | Path | Notes |
|---|---|---|
| GET | `/api/manager/dashboard` | House counts by status, maintenance counts by status, recent requests, pending reservations count |
| GET | `/api/manager/houses?search=&status=&house_type=` | Paginated |
| PATCH | `/api/manager/houses/{id}` | `{status}`. Restrict per SYSTEM_DOCUMENTATION §8.9 (no free "sold") |
| GET | `/api/manager/reservations?status=&search=` | |
| PATCH | `/api/manager/reservations/{id}` | `{status: accepted\|rejected}`. **Reject releases the unit.** |
| GET | `/api/manager/tenants?search=` | Tenants with their houses |
| GET | `/api/manager/maintenance-requests?scope=open\|closed&search=&status=&priority=` | |
| PATCH | `/api/manager/maintenance-requests/{id}/assignment` | `{staff_id\|null}`. Expertise must match category or be `general`. |
| GET | `/api/manager/staff?search=&expertise=` | With active/completed counts. Also feeds the assignment dropdown. |
| DELETE | `/api/manager/staff/{id}` | Demote to prospect. Unassign their open requests (decision: set those back to pending). |

### 5.7 Admin (role: admin)

| Method | Path | Notes |
|---|---|---|
| GET | `/api/admin/dashboard` | User counts by role and status |
| GET/POST/GET/PUT/DELETE | `/api/admin/users[/{id}]` | `apiResource`. Expertise is required only for staff. Admin cannot delete or deactivate themselves. |

---

## 6. Frontend page map (feature parity checklist)

Each row is done when it works end-to-end against the API, with loading, empty and error states.

| Role | Page (route) | Legacy equivalent | API used |
|---|---|---|---|
| Public | Landing `/` | `welcome.php` (use **real** house types, not placeholders) | `GET house-types` |
| Public | House detail `/houses/:slug` | `view/house_types/*` | `GET house-types/{slug}` |
| Public | Login `/login`, Register `/register` | `view/auth/*` | auth |
| All | Profile `/profile` | `*/profile.php` | profile |
| Prospect | Dashboard `/prospect` | `Prospect/dashboard.php` | house-types, reservations |
| Prospect | Reserve `/prospect/reserve/:slug` (block → lot pickers) | `Prospect/reservation.php` | available-units, POST reservations |
| Prospect | My reservations `/prospect/reservations` (cancel, pay modal with ₱ price) | `Prospect/history.php` | reservations |
| Tenant | Dashboard `/tenant` (my house, open requests) | `Tenant/dashboard.php` | user, maintenance |
| Tenant | Requests list / new / detail (+ "Mark resolved") | `Tenant/maintenance_*` | tenant maintenance |
| Staff | Dashboard, Assigned list, Detail (status + notes form), History | `Maintenance_Staff/*` | staff maintenance |
| Manager | Dashboard (stat cards, recent, auto-refresh 30 s) | `Property_Manager/dashboard.php` | manager/dashboard |
| Manager | Houses (search, filter, status change) | `listings.php` | manager/houses |
| Manager | Reservations (accept/reject with confirm) | `reservation_list.php` | manager/reservations |
| Manager | Tenants | `tenants.php` | manager/tenants |
| Manager | Maintenance queue (assign staff dropdown filtered by expertise) | `maintenance.php` | manager/maintenance-requests, manager/staff |
| Manager | Maintenance history | `maintenance_history.php` | `scope=closed` |
| Manager | Staff (search, demote with confirm) | `maintenance_staff.php` | manager/staff |
| Admin | Dashboard, Users table, Create/Edit form, View | `Admin/*` | admin/* |

**Role-based routing:** after login, redirect by `user.role` to that role's dashboard. `RoleRoute` blocks other roles client-side; the API is still the real guard. The sidebar menu is generated from the role.

**Design system:** keep the legacy brand. Red `#c1121f`, blue `#1d4ed8`, ink `#111827`, Playfair Display for headings, Source Sans 3 for body text, and the house photos in `legacy/assets/`. Use one `AppLayout` with a sidebar for every logged-in role (the legacy app had two inconsistent navbars).

---

## 7. Build phases

Work top to bottom. Each phase ends with a PR, green tests and lint. **For Claude Code:** do one phase (or one sub-bullet) per session. Read the acceptance criteria before starting, and do not start the next phase uninstructed.

| Phase | Scope | Acceptance criteria |
|---|---|---|
| **0. Scaffold** | Move legacy files to `legacy/`. `backend/`: Laravel 12 + Sanctum (`install:api`), CORS + `SANCTUM_STATEFUL_DOMAINS`, Pint, Pest. `frontend/`: Vite React, React Router, TanStack Query, axios (`withCredentials`, `withXSRFToken`), react-bootstrap, ESLint/Prettier. `.env.example` for both. README with setup commands. | `php artisan serve` + `npm run dev` both run. Frontend calls `GET /api/user` and gets 401. Lint passes. |
| **1. Database** | Enums, migrations, models + relationships + casts + scopes, factories, all seeders | `migrate:fresh --seed` succeeds. 870 houses. One user per role. Tinker relationships work. |
| **2. Auth & shell** | Register/login/logout/user/profile/password endpoints + tests. `role` middleware. React AuthProvider, Login, Register, Profile, AppLayout with role sidebar, ProtectedRoute/RoleRoute, role redirect, 404 page. | Each seeded user logs in and lands on their dashboard. A wrong role gets 403 (API) or a redirect (UI). Inactive users cannot log in. |
| **3. Catalog & reservations** | House-type endpoints, ReservationService (reserve/cancel/accept/reject/pay) + Policy + tests. Landing, house detail, reserve flow, my reservations, PM reservations page. | Full §4.2 flow works in the UI. Tests cover double booking, reject releasing the unit, pay promoting to tenant and creating a Payment. |
| **4. Maintenance** | MaintenanceService (submit/assign/progress/resolve) + Policy + tests. Tenant pages, staff pages, PM queue + history. | Full §4.3 flow works. Staff cannot see or update unassigned requests (test). Expertise rule enforced (test). |
| **5. Manager & admin** | PM dashboard, houses, tenants, staff pages. Admin dashboard + user CRUD. | Every legacy PM/Admin screen has a working equivalent. Self-delete is blocked. |
| **6. Enhancements** | Items chosen from §9 | Each item has its own test or demo steps |
| **7. Google login (bonus)** | Socialite: `/auth/google/redirect` + callback, link by email or create a prospect, "Continue with Google" button | Works with a test Google account. Docs explain how to set the client ID/secret. |
| **8. Polish** | Responsive pass, loading skeletons, empty states, toasts, confirm dialogs, form error mapping, favicon/title, final DemoSeeder | No console errors. Every page is usable at phone width. Demo script runs without errors. |
| **9. Presentation** | §11 | Video recorded |

---

## 8. Coding conventions (rubric: Coding standard)

**Backend**

- Controllers stay thin: validate (FormRequest), authorize (Policy), call a Service, return a Resource. No SQL in controllers.
- Business rules and transactions live in `Services`. Status transitions live in the enums (`canTransitionTo()`).
- Naming: singular models (`House`); plural tables; `camelCase` methods; `snake_case` DB columns and JSON keys; enums `PascalCase` cases with `snake_case` values.
- Always return API Resources, never raw models.
- `composer pint` before every commit. Every endpoint has a feature test (happy path + forbidden role + one validation error).

**Frontend**

- Components `PascalCase.jsx`, hooks `useThing.js`, one component per file.
- All HTTP goes through `src/api/*`. Pages never call axios directly.
- Server data is fetched with TanStack Query (`useQuery`/`useMutation`); invalidate queries after a mutation. No duplicated fetch logic.
- Status → label/colour mapping lives in one place (`utils/status.js`, see SYSTEM_DOCUMENTATION §6.4).
- `npm run lint` clean, Prettier formatted.

**Git**

- Branch per phase/feature. Conventional commits (`feat:`, `fix:`, `refactor:`, `test:`, `docs:`). PR into `main`. Never commit `.env` or `vendor/`/`node_modules/`.

**Do not carry over from legacy**

- Runtime schema checks (`SHOW TABLES`/`SHOW COLUMNS`).
- SQL inside views/pages.
- `htmlspecialchars` before saving.
- Hard-coded `/luminest/` paths.
- Duplicated helper functions.
- Role checks of the form `isset(role) && role !== X`.
- See the full list in SYSTEM_DOCUMENTATION §7.

---

## 9. Enhancements

### 9a. Instructor comments on the previous project (team: fill this in)

> _List every comment from the previous grading here, each with the phase that addresses it. This is requirement #2 and should be shown in the video._

| # | Comment | How we address it | Phase |
|---|---|---|---|
| 1 | _..._ | | |

### 9b. Fixes we get by rebuilding (show these in the video)

These all come from SYSTEM_DOCUMENTATION §7:

- Proper role protection on every endpoint.
- Staff limited to their own assignments.
- CSRF protection.
- Rejecting a reservation releases the unit.
- One consistent payment path with a payment record.
- Passwords are no longer altered before hashing.
- Inactive accounts are blocked at login.
- Validation on every input.

### 9c. Optional new features (pick by value/effort; do not do all of them)

| Feature | Value for demo | Effort |
|---|---|---|
| In-app notifications (Laravel database notifications + bell icon): reservation accepted/rejected, request assigned/completed | High | Medium |
| Payment receipt page (from the `payments` table) | Medium | Low |
| Photo upload on maintenance requests (`storage/` + validation) | Medium | Medium |
| Dashboard charts (Chart.js / Recharts: houses by status, requests per month) | High (design score) | Low |
| CSV export on manager tables | Low | Low |

---

## 10. Local setup (goes in the README after Phase 0)

```bash
# backend
cd backend && cp .env.example .env && composer install && php artisan key:generate
# set DB_DATABASE=luminest_v2, FRONTEND_URL=http://localhost:5173,
# SANCTUM_STATEFUL_DOMAINS=localhost:5173, SESSION_DOMAIN=localhost
php artisan migrate:fresh --seed && php artisan serve          # http://localhost:8000

# frontend
cd frontend && cp .env.example .env && npm install            # VITE_API_URL=http://localhost:8000
npm run dev                                                    # http://localhost:5173
```

Use `localhost` for both apps, not `127.0.0.1` for one and `localhost` for the other. Sanctum cookie auth breaks across different hosts.

---

## 11. Presentation plan (15–20 min, every member speaks)

Assign one module per member. Each member explains **their module end-to-end**: React page → API route → controller → service/policy → model → resource. That covers backend, frontend and code structure, as the brief asks.

| Segment | Minutes | Content |
|---|---|---|
| Intro & architecture | 2–3 | Problem, roles, stack, request-flow diagram (§2), folder structure |
| Auth & roles (+ Google login) | 3 | Sanctum flow, role middleware, policies |
| Reservations | 3–4 | Live demo: reserve → PM accept → pay → user becomes tenant. Show ReservationService transaction. |
| Maintenance | 3–4 | Live demo: submit → assign (expertise rule) → progress → resolve. Show policy limiting staff to their own requests. |
| Manager & admin dashboards | 2–3 | Search/filter, user CRUD |
| Quality & enhancements | 2 | Tests running, lint, instructor comments addressed (§9a), legacy defects fixed (§9b) |

Record against a fresh `migrate:fresh --seed` database so the demo is predictable.

---

## 12. Open decisions (team must answer; Claude Code should ask if still blank)

1. Team size and module owners? (§11)
2. Instructor comments from the previous project? (§9a)
3. Which optional enhancements? (§9c)
4. JavaScript or TypeScript for the frontend? Default: JavaScript.
5. Can one user own several houses? Default: yes (schema allows it). If a tenant owns more than one, they pick the house when filing a request.
6. Product questions in SYSTEM_DOCUMENTATION §8.9. Defaults used in this plan:
   - PM can only accept/reject; payment always goes through the prospect's pay action.
   - PM house status edits are limited to `available ↔ reserved` holds.
   - Unused maintenance statuses (`cancelled`, `rejected`, `on_hold`) are dropped.
