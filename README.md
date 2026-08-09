# Typhon Cath CRM

A modular PHP/MySQL Customer Relationship Management system built for Typhon Cath. It's a browser-based web application, intended to be hosted on a server and used by multiple people (Admin, Sales, Marketing, Inventory roles) at the same time — it centralizes customer records, sales pipeline (RFQ) tracking, digital campaign management, and inventory in one internal tool.

## Why This Project Exists

Typhon Cath needed a lightweight, self-hosted CRM instead of paying for a commercial platform. The full functional and non-functional requirements are captured in [`requirements.md`](../requirements.md) (the project's SRS); the short version:

- **Centralize customer data** — one place for accounts, contacts, and interaction history instead of scattered spreadsheets.
- **Track the sales pipeline** — RFQs move through defined stages (New → In Review → Quoted → Negotiation → Won/Lost) instead of being tracked ad hoc.
- **Manage campaigns** — plan and measure email/SMS campaigns against customer segments.
- **Track inventory** — keep stock counts accurate and let RFQs reserve inventory against real availability.
- **Give everyone one dashboard** — a unified, multi-user view across all four areas instead of four disconnected tools.

This is also a student capstone project: each functional area was built as an independently owned module by a different student, sharing one codebase, one database, and a common `Core` layer for auth, routing, and permissions.

## How It Works

The app is a monolithic 3-tier application: a PHP presentation/controller layer, a PHP service layer for business rules, and a MySQL data layer, served over HTTP by Apache so any number of users can be logged in and working concurrently through their browser. There is no framework (no Laravel/Symfony) — every piece is a small hand-written class in `app/Core/`, and the modules are plain PHP files that use them.

## Technical Architecture — How the Pieces Fit Together

### 1. Routing: a front controller per module, not a central router

`app/Core/Router.php` and the `*_routes.php` file in each module exist, but **they aren't actually wired up anywhere** — they're placeholders from an earlier design. The routing that really runs the site is simpler and more direct: each module has one "front controller" script under `public/modules/<module>/`, and Apache maps every URL straight to it. That single PHP file reads `$_GET['page']` (and the HTTP method) and branches by hand.

`public/modules/inventory/products.php` is a representative example:

```php
$controller = new InventoryController();
$page       = $_GET['page'] ?? 'list';
$isPost     = $_SERVER['REQUEST_METHOD'] === 'POST';

if ($page === 'detail') {
    if ($isPost) {
        denyUnlessAllowed($isEdit ? 'inventory.edit' : 'inventory.create');
        $controller->save();
        exit;
    }
    denyUnlessAllowed('inventory.view');
    include '.../Shared/header.php';
    include '.../Shared/sidebar.php';
    $controller->show();
} elseif ($page === 'stock') { /* ... */ }
  elseif ($page === 'reservations') { /* ... */ }
  elseif ($page === 'ledger') { /* ... */ }
  elseif ($page === 'delete') { /* ... */ }
  else { /* default: product list */ }

include '.../Shared/footer.php';
```

So a URL like `/modules/inventory/products.php?page=detail&id=7` means: "run the `detail` branch of the Inventory front controller." Every module (`customer/accounts.php`, `rfq/pipeline.php`, `campaign/campaigns.php`, `inventory/products.php`, `admin/users.php`) follows this same pattern — one file, a `page` switch, and the front controller (not the `Controller` class) is what decides whether to wrap the response in the shared header/sidebar/footer chrome or `exit` early (e.g. after a POST redirect, or a JSON response).

### 2. Request lifecycle, end to end

Concretely, saving an edited product walks through every layer of the app. Using `InventoryController::save()` (`app/Modules/Inventory/InventoryController.php`) as the worked example:

```text
Browser POSTs the product form to
  /modules/inventory/products.php?page=detail
        ↓
products.php (front controller) runs first:
  require bootstrap.php        → session_start() + autoloader registered
  Auth::requireLogin()          → no session? redirect to /login.php and stop
  Middleware/csrf.php           → Csrf::check() — POST without a valid token gets a 403
  denyUnlessAllowed('inventory.edit'/'inventory.create')
                                 → Permissions::can() — wrong role gets the shared 403 page
        ↓
InventoryController::save()   → reads/casts $_POST into typed values
        ↓
InventoryService::updateProduct() → BUSINESS RULES: required fields, price >= 0,
                                     duplicate-SKU check, computes a human-readable
                                     change note ("price changed from $10 to $12")
        ↓
InventoryRepository::updateProduct() → one UPDATE via a prepared PDO statement
InventoryRepository::logMovement()   → INSERT into inventory_movements (audit trail)
        ↓
Controller sets $_SESSION['flash'] = ['type' => 'success', 'message' => '...']
Controller sends Location: header and exit()s   (POST/redirect/GET — avoids a
                                                   resubmission if the user refreshes)
        ↓
Browser GETs the redirect target → front controller runs again → Auth/CSRF/permission
checks again → Controller::show() this time → View include → Shared/header+sidebar+footer
        ↓
Shared/sidebar.php reads $_SESSION['flash'], prints the banner once, and unsets it
```

Every module (Customer, RFQ, Campaign, Inventory, Admin) follows this same **Controller → Service → Repository** shape, and every state-changing action ends with a redirect + one-time flash message rather than re-rendering a POST result directly — that's what makes "hit refresh" safe everywhere in the app.

### 3. The three layers, and why the split exists

- **Controller** (`*Controller.php`) — HTTP only. Reads `$_GET`/`$_POST`, casts/trims input, calls the Service, and either `include`s a view or sets a flash message and redirects. It never writes SQL and never contains a business rule (no "is this SKU already taken" logic here).
- **Service** (`*Service.php`) — business rules and orchestration, with no knowledge of `$_GET`/`$_POST`/HTTP at all. `InventoryService::createProduct()` is a good example: it validates every field, checks for a duplicate SKU, calls the repository to insert the row, *then* calls the repository again to log the creation as a movement — two repository calls composed into one business action, so a "create product" is always paired with an audit-trail entry.
- **Repository** (`*Repository.php`) — the only layer that touches the database, always through `App\Core\Database::connection()` (a lazily-created singleton `PDO` instance) and always with prepared statements (`$db->prepare(...)->execute([...])`) — this is what keeps user input out of SQL strings.

This split means a Controller can be rewritten (say, to return JSON instead of HTML) without touching business logic, and business rules can be unit-tested or reused (e.g. by an export endpoint) without going through HTTP at all.

### 4. Shared page chrome and the view layer

Nothing renders a full HTML document by itself. Every page a front controller serves is sandwiched between three shared partials, included directly (there's no template engine):

```text
Shared/header.php   → <!doctype html>, <head>, opens <body>
Shared/sidebar.php  → opens .app-shell/.app-sidebar, nav links, reads+clears $_SESSION['flash']
  ...page-specific view content is include()'d here by the Controller...
Shared/footer.php    → closes .app-main/.app-shell, loads /assets/js/main.js, </body></html>
```

A `*Controller` method typically ends with `include __DIR__ . '/views/some_view.php'` — a plain PHP file that reads whatever variables the controller left in scope (no `extract()`/data-passing layer for these; `App\Core\View::render()` exists as an alternative but is only used in a couple of places). The view is nothing but HTML with inline `<?= ... ?>` — all output is escaped with `htmlspecialchars()` at the point of printing, which is the app's XSS defense.

### 5. Authentication & sessions

`Auth::attempt()` (`app/Core/Auth.php`) is the entire login flow: look up the user by email, `password_verify()` the submitted password against the stored bcrypt hash, then join `role_permissions` to fetch every permission string for that user's role. On success it calls `session_regenerate_id(true)` (prevents session-fixation attacks) and stores a single array in the session:

```php
$_SESSION['user'] = [
    'id' => 7, 'name' => 'Jane', 'email' => '...',
    'role' => 'Sales User',
    'permissions' => ['customers.view', 'rfqs.create', ...],  // cached at login
];
```

Every later `Auth::check()` / `Auth::user()` call just reads this session array — no DB hit per request. That also means a role's permissions are a snapshot taken at login; changing a role's permissions in Admin doesn't affect an already-logged-in user until they log in again.

### 6. Authorization (RBAC)

`Permissions::can('inventory.edit')` (`app/Core/Permissions.php`) checks the current user's cached `permissions` array — unless their role is `Admin`/`Super Admin`, which always short-circuits to `true`. Two enforcement points exist, and modules use them together:

- **Coarse gate**: `Middleware/require_auth.php` (just `Auth::requireLogin()`) — are you logged in at all.
- **Fine-grained gate**: each front controller defines a local `denyUnlessAllowed()` (or calls `Permissions::require()`) before every branch of its `page` switch, keyed to a permission string like `inventory.view`, `rfqs.update_stage`, or `campaigns.create`. A denied check renders the shared `Shared/error_403.php` inside the normal header/sidebar/footer chrome, so a blocked page still looks like part of the app instead of a bare error.

Because permission checks are re-evaluated on every request (not just once at login), one Sales user and one Admin can be using the same page at the same time and each sees only the actions their role allows.

### 7. CSRF protection

`Csrf::token()` (`app/Core/Csrf.php`) lazily creates one random 64-character token per session and reuses it for every form. `Csrf::field()` in a view renders it as a hidden `_csrf` input; `Middleware/csrf.php` (included right after `bootstrap.php`, before any state-changing logic runs) calls `Csrf::check()`, which no-ops on GET/HEAD/OPTIONS and otherwise does a constant-time (`hash_equals`) comparison against the submitted token, returning a 403 on mismatch. `tests/csrf_coverage.php` statically scans the whole codebase to make sure every `<form method="POST">` renders the token and every POST-handling file requires the middleware, so this can't silently regress.

### 8. Cross-module integration: the Dashboard

The Dashboard is the one part of the app that intentionally reaches across module boundaries. `DashboardService` (`app/Modules/Dashboard/DashboardService.php`) constructs `RFQRepository`, `CampaignRepository`, and its own `DashboardRepository` (which queries Inventory's tables directly for cross-module aggregates like "low stock" and "heavily reserved") and exposes one method per dashboard metric — it never writes SQL itself, it only composes calls into each module's existing repository.

The dashboard grid itself is built from small polymorphic **Card** classes (`app/Modules/Dashboard/Cards/*.php`), each extending `DashboardCard`: a card declares a `title()`, an optional `permission()` (so a Marketing user simply never sees an inventory card), and a `body()` built from one of two shared renderers (`stat()` for a single number, `preview()` for a truncated top-N list with a deep link). `DashboardController` registers the full list of cards and renders only the ones `visible()` returns true for — so the dashboard is really N independent read-only queries into other modules' data, each wrapped in the same card shell.

### 9. Shared utilities

- **`Paginator`** (`app/Core/Paginator.php`) — framework-agnostic pagination math shared by every list page (RFQ pipeline, Customer accounts, Inventory products and ledger, Admin users). A controller does `$pager = new Paginator($total, $_GET['per_page'], $_GET['page'], [10,25,50,100])`, then feeds `$pager->limit()`/`$pager->offset()` straight into the repository's SQL `LIMIT`/`OFFSET`, and the shared `Shared/pagination.php` partial renders the page-number nav from `$pager->windowedNumbers()`.
- **`Validator`** (`app/Core/Validator.php`) — currently just `Validator::required($data, $fields)`, a simple "were these fields present and non-blank" check used ahead of the more specific per-field business rules each Service enforces itself (e.g. `price < 0`, duplicate SKU).

### 10. The database layer

`Database::connection()` (`app/Core/Database.php`) lazily creates one `PDO` connection per request (`ATTR_ERRMODE => EXCEPTION`, `FETCH_ASSOC` by default) using `config/database.php`, which itself just reads `getenv('DB_HOST')` etc. — the same environment variables `docker-compose.yml` injects locally. Every repository shares this one connection. `database/schema.sql` is the current baseline schema; `database/migrations/NNN_*.sql` are the incremental changes layered on top of it as the schema evolved (see the Setup section above for how these get applied).

## Project Structure

```text
src/
├── app/
│   ├── Core/            # shared framework: routing, auth, db, permissions, CSRF
│   ├── Middleware/       # auth / role / CSRF guards applied per-route
│   ├── Modules/
│   │   ├── Customer/     # accounts, contacts, interaction history
│   │   ├── RFQ/          # pipeline, quotes, deal conversion
│   │   ├── Campaign/     # email/SMS campaigns, audience segmentation
│   │   ├── Inventory/    # product catalog, stock, RFQ reservations
│   │   ├── Dashboard/    # cross-module metrics/cards
│   │   └── Admin/        # user + role/permission management
│   └── Shared/           # shared layout partials (header, sidebar, login, etc.)
├── config/               # database.php and other app config
├── database/
│   ├── schema.sql        # full baseline schema
│   ├── seed.sql          # demo data (users, sample records)
│   ├── indexes.sql       # supplemental indexes
│   └── migrations/       # numbered incremental schema changes
├── docs/                 # architecture diagrams and write-ups (ERD, sequence, etc.)
├── public/               # web-accessible document root (Apache points here)
├── storage/              # logs
├── tests/                # test plans + a couple of standalone PHP check scripts
└── .env.example          # reference list of the env vars the app reads
```

`public/` is the **only** web-accessible directory — `app/`, `config/`, `database/`, and `storage/` must never be reachable from a browser.

## Module Ownership

| Student | Module | Folder |
|---|---|---|
| Max | Customer Management | `app/Modules/Customer/` |
| Trevor | RFQ / Pipeline Management | `app/Modules/RFQ/` |
| Jonah | Digital Campaign Management | `app/Modules/Campaign/` |
| Casey | Inventory Management | `app/Modules/Inventory/` |
| All | Dashboard, Admin, Integration, Auth | `app/Modules/Dashboard/`, `app/Modules/Admin/`, `app/Core/` |

## Local Development / Testing Setup (Docker)

The app itself is just PHP + MySQL behind Apache — in production it runs on a normal hosted LAMP-style server, no Docker involved. Locally, Docker is used **only** to spin up a disposable, consistent environment for development and testing, via [`docker-compose.yml`](../docker-compose.yml) and [`Dockerfile`](../Dockerfile). The only prerequisite is **Docker Desktop** ([Windows](https://docs.docker.com/desktop/install/windows-install/) / [Mac](https://docs.docker.com/desktop/install/mac-install/) / [Linux](https://docs.docker.com/desktop/install/linux-install/)).

1. **Clone the repo** (from the repository root, one level above `src/`):
   ```bash
   git clone <repo-url>
   cd typhoncath
   ```
2. **Start the local stack**:
   ```bash
   docker compose up
   ```
   The first run downloads images and builds the PHP container, so it takes a minute or two. Subsequent starts are fast. This brings up:
   - `app` — PHP 8.2 + Apache (built from `Dockerfile`), serving `src/public/` on **http://localhost:8080**
   - `db` — MySQL 8.0, auto-initialized on first boot from `database/schema.sql` then `database/seed.sql`
   - `adminer` — a web-based DB browser on **http://localhost:8081**
3. **Log in** at `http://localhost:8080/login.php` with the demo credentials seeded into the database (see `database/seed.sql`, or ask a teammate for the current demo password).

Database credentials are injected as container environment variables directly in `docker-compose.yml` (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, etc.) — `app/config/database.php` reads them via `getenv()`. `.env.example` documents the same variable names for reference (for setting equivalent environment variables on a real Apache/PHP host), but nothing in the app currently loads a `.env` file automatically — on a production server these values should be set as real environment variables (or hardcoded into `config/database.php`) pointing at the production MySQL instance.

## Daily Use (local Docker environment)

| Task | Command |
|---|---|
| Start the app | `docker compose up` |
| Start in background | `docker compose up -d` |
| Stop the app | `docker compose down` |
| View logs | `docker compose logs -f` |
| Reset the database | `docker compose down -v` then `docker compose up` |

> **Reset warning:** `docker compose down -v` deletes the MySQL data volume. All data is wiped and re-seeded from scratch — use it when the local DB gets into a bad state, or after adding a new migration (see below).

### Schema changes / migrations

`docker-entrypoint-initdb.d` only runs `schema.sql` and `seed.sql` on a **first-ever** boot of a fresh volume — it does not re-run on every `docker compose up`. If someone adds a new file to `database/migrations/`, the simplest way to pick it up locally is `docker compose down -v && docker compose up` (rebuilds the local DB from `schema.sql` + `seed.sql`). On a real production database, migrations should be applied by hand (or via a migration runner) against the live schema — never by dropping the volume.

### Connecting a DB client directly (optional, local only)

If you'd rather use TablePlus, DBeaver, or MySQL Workbench instead of Adminer against the local Docker MySQL instance:

| Setting | Value |
|---|---|
| Host | `127.0.0.1` |
| Port | `3306` |
| Database | `typhon_cath_crm` |
| Username | `crm_user` |
| Password | `secret` |

## Testing

`tests/` currently holds:

- `csrf_coverage.php` — a standalone static check (no server/DB needed) that scans the codebase to confirm every POST form renders a CSRF token and every POST handler enforces it. Run with `php tests/csrf_coverage.php`; exits non-zero on failure, so it's suitable for CI or a pre-push hook.
- `http_forms.php` — a helper/checklist script for exercising form submissions.
- `*_tests.md` — manual test-case checklists per module (customer, RFQ, campaign, inventory, integration), meant to be run against the local Docker environment before shipping changes.

There is no automated feature/unit test suite yet — testing is a mix of the static CSRF check and manual walkthroughs against the checklists above.

## Troubleshooting (local Docker environment)

**Port 8080 already in use** — something else is using it. Either stop that process or change `"8080:80"` to e.g. `"8082:80"` in `docker-compose.yml` (note `adminer` already uses 8081) and visit the new port.

**Port 3306 already in use** — you have a local MySQL running. Change `"3306:3306"` to `"3307:3306"` in `docker-compose.yml`. The app itself is unaffected; that port is only for connecting an external DB client.

**Database connection error on first boot** — the `app` container can start slightly before MySQL finishes initializing. Run `docker compose restart app`.

## Security Notes

- Never expose `app/`, `config/`, `database/`, `storage/`, or `docs/` through the web server — only `public/` should be the document root, in local Docker and in production alike.
- Passwords are hashed (bcrypt); never store or log plaintext passwords.
- All protected pages require an active session (`Auth::check()`) and a role check (`Permissions`) via `Middleware/require_auth.php` and `Middleware/require_role.php`, since multiple users with different roles are logged in at once.
- All state-changing (POST) forms must render and validate a CSRF token via `Csrf` — verified automatically by `tests/csrf_coverage.php`.
- All SQL access goes through PDO prepared statements in the `Repository` classes — no raw string-concatenated queries.