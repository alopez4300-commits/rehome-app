# ReHome v2 — Stack & Operations (AI-Centered + MyHome + Single Admin)

> A practical, production-minded overview of the AI-centered stack with MyHome system and single admin role.
> Hosting: **Render**. Storage: **Local/S3**. Email: **Resend**. **Light by default.**

---

## Backend

- Language/runtime: **PHP 8.3 (FPM)** on Linux
- Framework: **Laravel 11.x**
- **App shape:** one Laravel instance serving **two surfaces**

  - **Filament (admin panel):** `/admin/*` – session auth, admin-only, minimal operations
  - **API (everyone):** `/api/*` – Sanctum cookie auth, full application data

- Admin panel: **Filament v3** (Forms, Tables, Resources, Pages)
- **Realtime:** Polling-first (light profile) → WebSockets when needed

- **Reactivity (admin):** Livewire v3 + Alpine.js (Filament)
- ORM: Eloquent + Migrations/Seeders/Factories
- Auth:

  - **Web (admin):** Session (Filament login)
  - **API (everyone):** **Sanctum** (cookie-based for SPA)
  - **Single admin role:** `has_admin_role` boolean on users

- Validation/Serialization: Form Requests + **API Resources** (JSON)

### Jobs / Queues / Scheduler

- **Queues:** **sync** driver (light profile)
- Workers: **sync** processing (no background workers)
- Cron: `php artisan schedule:run` every minute
- AI requests: **synchronous** (2-5s response time)
- Timeouts: per-run AI timeout (`AI_TIMEOUT`, default **60s**)
- Cleanup: `rate-limits:cleanup` daily

### Caching / Sessions / Rate limit

- **File cache** (light profile)
- **File sessions** (light profile)
- **File-based rate limiting** (`storage/rate_limits/{user_id}.json`) with daily cleanup

### Database

- **SQLite (dev)** → **PostgreSQL 15+ (prod)** (or MySQL 8.x)
- **Primary data store:** users, workspaces, projects, tasks, workspace_user pivot, agent tables
- **MyHome audit trail:** append-only NDJSON files for activity log + AI context

### Files / Storage

- **Local filesystem** (light profile)
- **MyHome system:** append-only NDJSON files in `storage/projects/{workspace_id}/{project_id}/myhome/`
- Image/media: `intervention/image` (optional)
- Security: **file type allowlist**

### Mailing

- **Resend** (prod)
- **Mailpit/Mailhog** (dev)

### Search

- **Simple text search** in MyHome files

### Observability

- Errors: Sentry or Bugsnag
- Debug (dev): Telescope
- Logs: Monolog JSON to stdout
- **Audit:** log AI queries & admin actions

### Security

- HTTPS, HSTS, secure cookies
- **CORS** for SPA origins
- API rate limiting (general + **AI-specific**)
- CSRF for Filament (web)
- **AI security:** strict system prompts, PII redaction, no arbitrary tool exec

### Domain add-ons

- **Single admin role:** `has_admin_role` boolean (no complex permissions)
- Activity log: `spatie/laravel-activitylog`
- Backups: `spatie/laravel-backup`

### Domain policies

- **Admin:** acts as workspace owner in all workspaces via `Gate::before` bypass
- **Non-admins:** scoped by **workspace membership** (pivot `workspace_user`)
- **Workspace roles:** owner, member, consultant, client
- Enforcement: API controllers, query scopes

---

## AI Agent (backend service layer)

**Purpose:** AI-centered chat assistants that understand project context via MyHome system

- **Single agent type:** project-scoped assistant for all users
- **Admin context:** workspace-wide scope when admin asks
- **MyHome integration:** primary source of truth for project context

**Execution model:**
API → create `agent_run` → **synchronous** context building from MyHome files → AI call (timeout-bounded) → persist `agent_messages` → return complete response.

**Context-building policy (MyHome-centered):**

- **50% - MyHome entries:** recent activity, notes, tasks, time logs, file uploads (~50-100 entries)
- **30% - Project metadata:** team members, status, important dates
- **20% - File content:** recent document snippets, OCR text
- **Token budget:** 8K context default, configurable
- **Max entry size:** 500 tokens (drop entries larger than this)
- **Truncation:** **drop whole items** oldest-first (avoid mid-entry truncation)
- **Conservative estimates:** Build 5-10% slack into token calculations
- **PII redaction:** mask sensitive fields based on user role

**Minimal data model:**

- `agent_threads` (id, project_id, user_id, title, created_at, updated_at)
- `agent_messages` (id, thread_id, role enum('user','assistant','system'), content, tokens_in, tokens_out, cost_cents, created_at)
- `agent_runs` (id, thread_id, status, total_tokens, total_cost_cents, started_at, finished_at, error) // Cost tracking only

**MyHome entry types (Audit trail + AI context):**

```json
{"ts":"2025-01-15T14:23:10Z","author":12,"kind":"/task.created","task_id":123,"title":"Draft SOW"}
{"ts":"2025-01-15T14:24:15Z","author":12,"kind":"/task.status_changed","task_id":123,"from":"redline","to":"progress"}
{"ts":"2025-01-15T14:30:00Z","author":18,"kind":"/task.assigned","task_id":123,"assigned_to":[5,12]}
{"ts":"2025-01-15T15:00:00Z","author":12,"kind":"/file.uploaded","task_id":123,"path":"assets/documents/brief.pdf"}
{"ts":"2025-01-15T15:30:00Z","author":12,"kind":"/ai.prompt","prompt":"What's the status?"}
{"ts":"2025-01-15T15:30:05Z","author":12,"kind":"/ai.response","text":"Based on recent activity..."}
```

**Cost/rate governance**

- Track **tokens + cost_cents** on `agent_runs`
- **Rate limits:** 5/min/user, 50/day/user (configurable)
- **Budget warnings** when approaching limits
- **Graceful degradation:** serve cached responses when over budget

**Provider/config**

- `.env`: `AI_PROVIDER`, `AI_MODEL`, `AI_TEMPERATURE`, `AI_MAX_TOKENS`, `AI_TIMEOUT`
- `config/ai.php`:

  ```php
  return [
    'provider'   => 'openai', // Only one provider
    'model'      => 'gpt-4o-mini',
    'max_tokens' => 8000,
    'temperature'=> 0.7,

    // Context policy (MyHome-centered)
    'context_budget' => [
      'myhome_entries' => 0.60,     // ~200 entries at ~20 tokens each
      'project_metadata' => 0.40,   // team, tasks, dates
      'file_excerpts' => 0.00       // deferred to Phase 2
    ],
    'max_entry_tokens' => 500,      // Drop entries larger than this
    'truncate_strategy' => 'drop_whole',

    // Governance
    'rate_limits' => [
      'per_user_minute' => 5,
      'per_user_day' => 50
    ],
    'timeout_seconds' => 60,

    // PII Redaction
    'pii_patterns' => [
      'email' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
      'phone' => '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/',
      'ssn' => '/\b\d{3}-\d{2}-\d{4}\b/',
    ],
    'redaction_by_role' => [
      'admin' => [],  // No redaction
      'member' => [],
      'consultant' => ['email', 'phone'],
      'client' => ['email', 'phone', 'ssn'],
    ],

    // Costs (USD per 1M tokens)
    'costs' => [
      'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
    ],
  ];
  ```

> **OpenAI GPT-4o-mini** - reliable, cheap, good for structured responses.

---

## Frontend

### Admin (web + wrappers)

- Build tool: **Vite 5**
- CSS: **Tailwind 3** + PostCSS + Autoprefixer
- JS: Alpine.js 3 (Filament), Livewire morphdom
- Icons: Heroicons/Lucide
- **Desktop (Tauri)**: wrap `/admin` (Filament) as desktop app
- **iPad (PWA)**: lightweight shell loading `/admin`

**Admin UX (Minimal Operations)**

- **User management:** CRUD with admin toggle
- **Workspace overview:** read-only list
- **Project list:** read-only list

**Admin in SPA:**

- Acts as workspace owner in all workspaces
- Sees all workspaces in workspace list
- Full CRUD access to everything
- Uses same interface as regular owners
- No special admin UI (except /admin panel)

### Team / Consultant / Client SPA (web now, app later)

- **Today (web):** SPA served from **Laravel** (light profile)
- **Later (app):** **Expo RN** targets iOS/Android using same API
- UI: **Tailwind CSS** (web) → **NativeWind** (RN)
- Data: **Axios** + simple state management
- Validation: **Zod** (optional)
- Auth: Sanctum cookie to `/api/*`

**SPA UX (Full Application)**

Primary Interfaces:

- **Workspace Dashboard** - List user's workspaces, recent activity
- **Project Overview** - Dashboard with key metrics
- **Task Board** - Kanban board with grouping/filtering (primary interface)
- **Activity Feed** - Chronological stream of MyHome entries
- **Time Tracking** - Time logging and reports
- **File Browser** - File management and uploads

AI Enhancement:

- **AI Chat** - Optional assistant for summaries and insights
- Contextual prompts: "Summarize this week", "What's blocking?"
- Available when budget allows, not required for daily use

---

## Infrastructure (Light Profile)

- Web server: **Nginx** proxy → **php-fpm**
- PHP extensions: intl, mbstring, bcmath, ctype, fileinfo, tokenizer, pdo_pgsql/pdo_mysql, curl, openssl, xml, gd/imagick, zip
- Environment/config:

  - `.env` essentials: `APP_KEY`, `APP_URL`, DB*, MAIL*, **AI\***, `SANCTUM_STATEFUL_DOMAINS`, `CORS*`
  - **Light profile:** file cache, file sessions, sync queues

- Storage/Uploads: **Local filesystem**
- Background workers: **sync** processing
- Backups: automated DB + storage; retention policy
- Time: UTC everywhere

**Render hosting sketch**

- **Render Web Service:** Laravel API + Filament (`/api/*`, `/system/*`)
- **Render Managed Postgres:** primary DB
- **Local filesystem:** MyHome files + assets
- **Resend:** transactional mail (invites, alerts, summaries)

---

## Developer Tooling

- Composer 2.x, PHP 8.3 CLI
- Static analysis: Larastan (PHPStan)
- Code style: Laravel Pint
- Tests: Pest or PHPUnit
- Fixtures: Factories + Seeders
- Git hooks: pre-commit (Pint, PHPStan, tests)
- VS Code extensions: Intelephense, Laravel Artisan, Blade formatter, Tailwind IntelliSense

---

## Filament specifics (system admin only)

- Resource pattern: Resource + Pages (List/Create/Edit) + form/table schemas
- Components: TextInput, Select, FileUpload, DatePicker, etc.
- Policies: Laravel policies + single admin bypass
- Navigation: groups, icons, badges

**System admin scoping**

- `UserResource::getEloquentQuery()` shows all users
- `WorkspaceResource` shows all workspaces
- `ProjectResource` shows all projects
- Admin toggle on UserResource for `has_admin_role`

**System operations**

- User management with admin role toggle
- Workspace overview and management
- Project export system
- AI usage monitoring and cost tracking

---

## Recommended packages (Composer)

**Essential:**

- `laravel/framework: ^11.0`
- `filament/filament: ^3.0`
- `laravel/sanctum: ^4.0`
- `openai-php/laravel: ^0.10` (or `anthropic-php/anthropic-sdk-php`)

**Development:**

- `laravel/pint`
- `nunomaduro/larastan`
- `laravel/telescope` (require-dev)

**Optional:**

- `spatie/laravel-activitylog`
- `spatie/laravel-backup`
- `intervention/image`
- `sentry/sentry-laravel`

## Recommended packages (Node)

**SPA:**

- `react: ^18.0`
- `react-router-dom: ^6.0`
- `axios: ^1.6`
- `tailwindcss: ^3.0`
- `@tailwindcss/forms`
- `@tailwindcss/typography`

**Admin (Filament):**

- `tailwindcss`, `postcss`, `autoprefixer`, `alpinejs`, `vite`, `laravel-vite-plugin`

---

## Install checklist (Linux)

```bash
# System
sudo apt-get update
sudo apt-get install -y nginx git unzip

# PHP 8.3 + extensions
sudo apt-get install -y php8.3 php8.3-fpm php8.3-cli php8.3-intl php8.3-mbstring php8.3-bcmath php8.3-xml php8.3-curl php8.3-gd php8.3-zip php8.3-pgsql # or php8.3-mysql

# Node & Composer
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# DB (dev/prod)
sudo apt-get install -y postgresql # or mysql-server

# Services up
sudo systemctl enable --now php8.3-fpm nginx
```

---

## CI/CD (single pipeline initially)

### Backend (GitHub Actions)

- Cache Composer/npm
- `composer install --no-dev --optimize-autoloader`
- `php artisan config:cache route:cache view:cache`
- `npm ci && npm run build` (Filament assets)
- Run tests (Pest/PHPUnit)
- Deploy to **Render**
- Post-deploy: `php artisan migrate --force`

### SPA (when separate)

- Lint, typecheck, unit tests
- Build → serve from Laravel

**Secrets:** `APP_KEY`, DB creds, MAIL (Resend), **AI_PROVIDER/AI_MODEL/KEYS**, `SANCTUM/CORS`

---

## Configuration essentials

- Generate `APP_KEY`
- Set `APP_URL`; configure **CORS** and **SANCTUM_STATEFUL_DOMAINS** for SPA origins
- Session domain/cookie for admin; Sanctum cookies for SPA
- **Light profile:** file cache, file sessions, sync queues
- `php artisan storage:link`
- Mailer per env (Resend in prod)
- **AI**: provider/model keys; rate limits; PII redaction
- **Storage**: local filesystem

---

## Minimal API surface (SPA)

```
POST   /login              // Handled by Filament
GET    /sanctum/csrf-cookie
GET    /api/me
POST   /api/logout

GET    /api/workspaces
GET    /api/workspaces/{id}/projects
GET    /api/projects/{id}

// Task Management (Primary Interface)
GET    /api/projects/{id}/tasks?group_by=status
POST   /api/projects/{id}/tasks
PUT    /api/tasks/{id}
DELETE /api/tasks/{id}
PUT    /api/tasks/{id}/assign
PUT    /api/tasks/{id}/status

// File Management
GET    /api/tasks/{id}/files
POST   /api/tasks/{id}/files

// MyHome Activity Feed
GET    /api/projects/{id}/myhome/feed?kind=task&limit=50
GET    /api/projects/{id}/myhome/search?q=design
POST   /api/projects/{id}/myhome
```

### AI endpoints (Optional Enhancement)

```
GET    /api/agent/threads                     // List all my threads
GET    /api/projects/{id}/agent/threads       // List threads for project
POST   /api/projects/{id}/agent/threads       // Create thread
GET    /api/agent/threads/{id}/messages       // Get messages
POST   /api/agent/threads/{id}/messages       // Send message
```

---

## MyHome System (Core Innovation)

**Purpose:** Append-only activity stream that serves as the primary source of truth for project context

**Storage Structure:**

```
storage/projects/{workspace_id}/{project_id}/
├── myhome/
│   └── myhome.ndjson              // Complete activity stream (including AI)
├── assets/                        // User uploads
└── metadata/                      // Computed data
```

**Entry Format:**

```json
{"ts":"2025-01-15T14:23:10Z","author":12,"author_name":"John Doe","kind":"note","text":"Client approved design"}
{"ts":"2025-01-15T14:24:15Z","author":12,"author_name":"John Doe","kind":"/task","title":"Draft SOW","due":"2025-01-20","status":"pending"}
{"ts":"2025-01-15T14:30:00Z","author":18,"author_name":"Jane Smith","kind":"/time","hours":2.5,"task":"Draft SOW","description":"Research phase"}
{"ts":"2025-01-15T15:00:00Z","author":12,"author_name":"John Doe","kind":"/file","path":"assets/documents/brief.pdf","size":1024000,"type":"application/pdf"}
{"ts":"2025-01-15T15:30:00Z","author":12,"author_name":"John Doe","kind":"/ai.prompt","prompt":"What's the current status of this project?"}
{"ts":"2025-01-15T15:30:05Z","author":12,"author_name":"John Doe","kind":"/ai.response","text":"Based on recent activity, the project is on track..."}
```

**Entry Types:**

- `note` → simple text note
- `/task` → task with due date, status
- `/time` → time tracking entry
- `/file` → file upload reference
- `/ai.prompt` → user question to AI
- `/ai.response` → AI answer
- `/status` → project status change

**AI Context Building:**

- **60% tokens:** Recent MyHome entries (~200 entries at ~20 tokens each)
- **40% tokens:** Project metadata (team, status, dates)
- **0% tokens:** File content (deferred to Phase 2)
- **Max entry size:** 500 tokens (drop entries larger than this)
- **Intelligent truncation:** Drop whole items oldest-first
- **PII redaction:** Based on user role (client sees most redaction)

**MyHome Query Performance:**

- Small files (<1MB): Read entire file, split lines, reverse, take 100
- Large files (>1MB): Migrate to database table with indexes
- Cache frequently accessed projects in memory

**MyHome Service (Primary Interface):**

```php
app/Services/MyHome/MyHomeService.php
- append(Project $project, User $user, array $entry): array
- read(Project $project, int $limit = 100): Collection
- getByKind(Project $project, string $kind): Collection
- search(Project $project, string $query): Collection
- getTasks(Project $project): Collection           // Helper: getByKind('/task')
- getTimeLogs(Project $project): Collection        // Helper: getByKind('/time')
- getFiles(Project $project): Collection           // Helper: getByKind('/file')
- getStats(Project $project): array                // Count by type, sum time, etc.

app/Services/Agent/ContextBuilder.php
- buildContext(Project $project, User $user, int $maxTokens): array
- truncateToFit(array $entries, int $tokenBudget): array
- estimateTokens(string $text): int
- redactPII(string $text, string $userRole): string
```

---

## AI Request Flow

**Synchronous (Light Profile):**

```
1. POST /api/agent/threads/{id}/messages
2. Server reads MyHome (50ms)
3. Server calls OpenAI (2-5s)
4. Server writes response to MyHome (50ms)
5. Return complete response
```

**Async (When Scaling):**

```
1. POST creates agent_run (status: pending)
2. Return 202 Accepted with run_id
3. Client polls GET /api/agent/runs/{id}
4. Status transitions: pending → processing → completed
```

**When to Scale:**

- AI requests taking >3 seconds
- 10+ concurrent users
- 1GB+ file storage
- Need real-time collaboration
- Multiple app servers

**Phase 2 Additions (File Content):**

- OCR for PDFs (Tesseract)
- Text extraction for DOCX/ODT
- Image analysis for screenshots
- File content indexing strategy
- Context budget: 50/30/20 (MyHome/Metadata/Files)

**Implementation Order:**

1. **Phase 0-1:** Foundation (Laravel + Filament + Database)
2. **Phase 2:** MyHome Proof (MyHome service + notes API)
3. **Phase 3:** Tasks (Task CRUD + Task board UI)
4. **Phase 4:** AI (Agent tables + AI chat)
5. **Phase 5:** WebSockets (if polling proves inadequate)

**Start light, scale when metrics demand it.**

---

## Real-Time Updates (Lightweight Approach)

**Scope:** Task board synchronization and team communication only

**Implementation:**

- **Polling-first** (Phase 1-3): Check for updates every 5-10 seconds
- **WebSockets later** (Phase 4): Add when active collaboration becomes painful

### Polling Strategy (Initial)

**Task Board:**

```javascript
// Poll for task updates
useEffect(() => {
  const interval = setInterval(() => {
    fetchTasks(projectId);
  }, 5000); // Every 5 seconds

  return () => clearInterval(interval);
}, [projectId]);
```

**Detection:** Backend returns `last_updated_at` timestamp

- Client compares with local state
- Only re-render if changes detected
- Show "Updates available" banner with manual refresh option

**Benefits:**

- No WebSocket infrastructure needed
- Works with light profile (no Redis)
- Simple to implement and debug
- Adequate for <10 concurrent users

### WebSocket Migration Path (When Needed)

**Add when:**

- 10+ concurrent users on same project
- Polling creates noticeable server load
- Users complain about stale data
- Team collaboration becomes core feature

**Stack additions:**

- Laravel Reverb (WebSocket server on Render)
- Redis for pub/sub
- Laravel Echo (frontend)
- Pusher protocol compatibility

**Channels:**

```php
Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    return $user->canAccessProject($projectId);
});
```

**Events:**

- `TaskUpdated`
- `TaskCreated`
- `TaskDeleted`
- `UserTyping` (if chat added)

### Reality Check

For a task board with 5-10 users:

- **Polling every 5s = 12 requests/min per user = 120 req/min total**
- This is trivial load for PostgreSQL + Laravel
- WebSockets are overkill until you have 20+ concurrent users

**Start with polling. Add WebSockets only when metrics prove you need it.**

---

## Task Board Implementation (Phase 3)

**Prerequisites:** MyHome system working, SPA authentication functional

### Database Schema

```php
// tasks table
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('status')->default('pending');  // pending, in_progress, completed
    $table->integer('priority')->default(0);
    $table->date('due_date')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->boolean('visible_to_client')->default(true);
    $table->integer('order')->default(0);  // For manual sorting
    $table->timestamps();

    $table->index(['project_id', 'status']);
});

// task_user pivot (multiple assignees)
Schema::create('task_user', function (Blueprint $table) {
    $table->foreignId('task_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->primary(['task_id', 'user_id']);
});
```

### API Endpoints

```php
// Task CRUD
GET    /api/projects/{id}/tasks?group_by=status
POST   /api/projects/{id}/tasks
PUT    /api/tasks/{id}
DELETE /api/tasks/{id}
PUT    /api/tasks/{id}/status
PUT    /api/tasks/{id}/assign
PUT    /api/tasks/{id}/reorder
```

### MyHome Integration

Every task change appends to MyHome:

```json
{"kind":"/task.created","task_id":123,"title":"Draft SOW","status":"pending"}
{"kind":"/task.status_changed","task_id":123,"from":"pending","to":"in_progress"}
{"kind":"/task.assigned","task_id":123,"assigned_to":[5,12]}
```

### Frontend: Task Board

- **Kanban columns:** pending, in_progress, completed
- **Polling:** Check for updates every 5 seconds
- **Drag & drop:** Move tasks between status columns
- **Assignee avatars:** Show who's working on what
- **Due dates:** Visual indicators for overdue tasks

### Status Columns

Based on screenshot:

- `TASK/REDLINE` (red badge) → `pending`
- `PROGRESS/UPDATE` (blue badge) → `in_progress`
- `COMPLETED` → `completed`

### Phase 3 Checklist

```bash
[ ] Tasks table migration
[ ] Task model with MyHome integration
[ ] Task API endpoints (CRUD + status)
[ ] Task policy (role-based access)
[ ] TaskBoard React component
[ ] Polling for updates (5s interval)
[ ] Assignee avatar display
[ ] Create task modal/form
```

**Build AFTER Phase 2 (MyHome) proves the architecture works.**

---

## What's Different from v1

1. **Single admin role** - no complex permissions, just `has_admin_role` boolean
2. **MyHome system** - append-only NDJSON files as primary source of truth
3. **MyHome-first interface** - direct data access, AI as optional enhancement
4. **Light by default** - no Redis, no queues, polling-first approach
5. **Local filesystem storage** - simple file-based approach
6. **Simplified architecture** - fewer moving parts, easier to understand
7. **Context from MyHome** - AI gets project context from activity stream
8. **Universal login** - everyone goes to `/app` after login, admin manually types `/admin`
9. **Single AI provider** - OpenAI only, no fallback complexity
10. **Synchronous AI requests** - simple request/response pattern

**Build the minimum that works, then iterate based on real usage.**

---

## Service Directory Structure

```
/backend/app/Services/MyHome/     // MyHome-related services
├── MyHomeService.php             // Core MyHome operations
└── MyHomeQueryService.php        // Query optimization

/backend/app/Services/Agent/      // AI agent services
├── ContextBuilder.php            // Context building logic
├── AgentService.php              // Main orchestration
└── PIIRedactor.php               // PII redaction logic
```

**Keeps concerns separated as you build.**

---

## Frontend Structure

```
/app/
├── workspaces/              - List user's workspaces
├── projects/{id}/
│   ├── overview/           - Dashboard with recent activity
│   ├── tasks/              - Task Board (kanban with grouping/filtering)
│   ├── feed/               - MyHome activity stream
│   ├── time/               - Time tracking
│   ├── files/              - File browser
│   └── chat/               - AI assistant (optional)
```

**Data Flow:**
SPA → API → Database (primary) + MyHome (audit log)
AI Chat → MyHome + Database (context) → OpenAI → Response

---

## Authorization Pattern

**Admin privilege:**

- `has_admin_role` boolean on users table
- Acts as workspace owner in all workspaces
- Full access via Gate::before bypass
- Uses same SPA interface as regular owners
- No special admin views (except /system panel)

**Workspace roles:**

- **Owner:** Full workspace control
- **Member:** Can edit tasks, log time, manage files
- **Consultant:** Limited to assigned projects
- **Client:** Read-only access with PII redaction

**Access control flow:**

1. Check if admin → grant owner access
2. Check workspace membership → apply role permissions
3. Apply PII redaction based on role
4. Filter data visibility (client sees less)

**Authorization logic:**

```php
// Check if user can access workspace
public function canAccessWorkspace(User $user, Workspace $workspace): bool
{
    // Admin acts as owner everywhere
    if ($user->isAdmin()) {
        return true;
    }

    // Check actual membership
    return $workspace->users()->where('user_id', $user->id)->exists();
}

// Check workspace role
public function getWorkspaceRole(User $user, Workspace $workspace): string
{
    // Admin always acts as owner
    if ($user->isAdmin()) {
        return 'owner';
    }

    $member = $workspace->users()->where('user_id', $user->id)->first();
    return $member ? $member->pivot->role : null;
}

// Gate bypass for admin
Gate::before(function (User $user, string $ability) {
    if ($user->isAdmin()) {
        return true;  // Bypasses all policy checks
    }
});
```

---

## Application Surfaces

### /admin (Filament - Admin Only)

**Purpose:** Minimal platform operations

**Resources:**

- **User Management**: CRUD with admin role toggle
- **Workspace Overview**: Read-only list
- **Project List**: Read-only list

### /app (React SPA - Everyone)

**Purpose:** All daily work happens here


- Activity Feed (MyHome chronological view)
- Time Tracking
- File Management
- AI Chat Assistant
- Project Dashboard






## Codespace AI Prompt: Build ReHome v2 Foundation + Environment Setup

Build a Laravel 11 project management platform with a Filament admin panel and a development environment that matches the current setup. Follow the Light Development Guide and implement Phases 0–1 with the same structure and tooling.

### Project Overview
ReHome v2 is an AI-centered project management platform with a MyHome system (append-only NDJSON activity logs) and a single admin role. Build the foundation, admin panel, and a development environment that matches the current setup.

### Environment Requirements

**Target Environment Structure:**
```
rehome-v2/
├── backend/                    # Laravel 11 application
├── frontend/                   # React 18 SPA (future)
├── docker/                     # Docker configuration
├── scripts/dev/               # Development scripts
├── docs/                      # Documentation
├── ai/                        # AI assistant prompts
├── docker-compose.yml         # Multi-container setup
├── Makefile                   # Development commands
├── package.json               # Root-level dependencies
└── README.md                  # Project documentation
```

### Phase 0: Environment Setup

1. **Create Project Structure:**
```bash
mkdir rehome-v2 && cd rehome-v2
mkdir -p backend frontend docker scripts/dev docs ai/prompts
```

2. **Create Root Package.json:**
```json
{
  "name": "rehome-v2",
  "version": "2.0.0",
  "description": "AI-Centered Project Management Platform - Laravel 11 + Filament v3",
  "private": true,
  "type": "module",
  "scripts": {
    "dev": "concurrently \"npm run be\" \"npm run fe\"",
    "be": "cd backend && php artisan serve --host=0.0.0.0 --port=8000",
    "fe": "cd frontend && npm run dev",
    "build": "cd frontend && npm run build",
    "test": "npm run test:backend && npm run test:frontend",
    "test:backend": "cd backend && composer run-script test",
    "test:frontend": "cd frontend && npm run test",
    "lint": "npm run lint:backend && npm run lint:frontend",
    "lint:backend": "cd backend && composer run-script lint",
    "lint:frontend": "cd frontend && npm run lint",
    "typecheck": "npm run typecheck:backend && npm run typecheck:frontend",
    "typecheck:backend": "cd backend && composer run-script typecheck",
    "typecheck:frontend": "cd frontend && npm run typecheck",
    "ci": "npm run lint && npm run typecheck && npm run test && npm run build",
    "setup": "npm run setup:backend && npm run setup:frontend",
    "setup:backend": "cd backend && composer install && php artisan key:generate && php artisan migrate --force && php artisan db:seed --force",
    "setup:frontend": "cd frontend && npm install",
    "fresh": "npm run setup && docker-compose up -d"
  },
  "devDependencies": {
    "concurrently": "^8.2.2"
  },
  "engines": {
    "node": ">=20.0.0",
    "php": ">=8.3.0"
  }
}
```

3. **Create Docker Compose Configuration:**
```yaml
# docker-compose.yml
version: '3.8'

services:
  # PHP-FPM Application
  app:
    build:
      context: ./docker
      dockerfile: Dockerfile.php
    working_dir: /var/www/html
    volumes:
      - ./backend:/var/www/html
      - ./docker/php.ini:/usr/local/etc/php/conf.d/custom.ini
    environment:
      - APP_ENV=local
      - DB_CONNECTION=sqlite
      - DB_DATABASE=/var/www/html/database/database.sqlite
      - CACHE_DRIVER=file
      - SESSION_DRIVER=file
      - QUEUE_CONNECTION=sync
    networks:
      - rehome

  # Nginx Web Server
  nginx:
    image: nginx:alpine
    ports:
      - "8000:80"
    volumes:
      - ./backend:/var/www/html
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - rehome

  # Frontend Vite Dev Server (for future)
  frontend:
    image: node:20-alpine
    working_dir: /app
    volumes:
      - ./frontend:/app
    command: sh -c "npm install && npm run dev -- --host 0.0.0.0"
    ports:
      - "3000:3000"
    networks:
      - rehome

volumes:
  postgres_data:
  redis_data:

networks:
  rehome:
    driver: bridge
```

4. **Create Docker Configuration:**
```dockerfile
# docker/Dockerfile.php
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html/storage

EXPOSE 9000
CMD ["php-fpm"]
```

```nginx
# docker/nginx.conf
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```ini
# docker/php.ini
upload_max_filesize=100M
post_max_size=100M
memory_limit=512M
max_execution_time=300
```

### Phase 1: Laravel Backend Setup

1. **Create Laravel Project:**
```bash
cd backend
composer create-project laravel/laravel . --prefer-dist
```

2. **Install Essential Packages:**
```bash
composer require laravel/framework:^11.0
composer require laravel/sanctum:^4.0
composer require filament/filament:^3.0
composer require --dev laravel/pint
composer require --dev nunomaduro/larastan
composer require --dev laravel/telescope
```

3. **Environment Configuration:**
```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
```

4. **Configure .env for Light Profile:**
```env
APP_NAME="ReHome v2"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8000
SESSION_DOMAIN=localhost

AI_PROVIDER=openai
AI_MODEL=gpt-4o-mini
OPENAI_API_KEY=your_key_here
```

### Phase 2: Database Schema & Models

**Create Migrations:**

1. **Add admin role to users:**
```bash
php artisan make:migration add_admin_role_to_users_table
```

2. **Create workspaces table:**
```bash
php artisan make:migration create_workspaces_table
```

3. **Create projects table:**
```bash
php artisan make:migration create_projects_table
```

4. **Create workspace_user pivot:**
```bash
php artisan make:migration create_workspace_user_table
```

**Create Models with Relationships:**
- User model with `isAdmin()` method
- Workspace model with relationships
- Project model with workspace relationship

### Phase 3: Filament Admin Panel

1. **Install Filament:**
```bash
php artisan filament:install --panels
```

2. **Create Filament Resources:**
- UserResource with admin toggle
- WorkspaceResource (read-only)
- ProjectResource (read-only)

3. **Configure Filament Panel:**
- Custom login redirect to `/app`
- Admin panel at `/system`
- Branding and navigation

### Phase 4: Development Tools

1. **Create Makefile:**
```makefile
# Makefile
.DEFAULT_GOAL := help
.PHONY: help setup up down logs clean restart status

help: ## Show this help message
	@echo "🚀 ReHome v2 - Development Commands"
	@echo "=================================="
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

setup: ## Install dependencies & build assets
	@echo "📦 Installing dependencies..."
	cd backend && composer install
	cd frontend && npm install
	@echo "✅ Setup complete!"

up: ## Start all containers
	@echo "🐳 Starting containers..."
	docker compose up -d
	@echo "✅ Containers started!"

down: ## Stop all containers
	@echo "🛑 Stopping containers..."
	docker compose down
	@echo "✅ Containers stopped!"

logs: ## Tail logs for key services
	@echo "📜 Tailing logs (Ctrl+C to exit)..."
	docker compose logs -f app nginx

migrate: ## Run database migrations
	@echo "🗄️  Running migrations..."
	docker compose exec app php artisan migrate --force
	@echo "✅ Migrations complete!"

seed: ## Seed database with test data
	@echo "🌱 Seeding database..."
	docker compose exec app php artisan db:seed --force
	@echo "✅ Database seeded!"

admin: ## Create admin user
	@echo "👤 Creating admin user..."
	docker compose exec app php artisan make:filament-user
	@echo "✅ Admin user created!"

status: ## Show container status
	@echo "📊 Container Status:"
	@docker compose ps

clean: ## Clean up containers and volumes
	@echo "🧹 Cleaning up..."
	docker compose down -v --remove-orphans
	docker system prune -f
```

2. **Create Health Check Script:**
```bash
# scripts/dev/health-check.sh
#!/bin/bash

# Health Check Script - Validates development environment setup
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_header() {
    echo -e "\n${BLUE}=== $1 ===${NC}"
}

print_pass() {
    echo -e "${GREEN}✓${NC} $1"
}

print_fail() {
    echo -e "${RED}✗${NC} $1"
}

print_warn() {
    echo -e "${YELLOW}⚠${NC} $1"
}

# Change to project root
cd "$(dirname "$0")/../.."

print_header "ReHome v2 Development Environment Health Check"

# Check Docker
print_header "Docker Environment"
if command -v docker &> /dev/null; then
    print_pass "Docker CLI available"
    if docker info &> /dev/null; then
        print_pass "Docker daemon running"
    else
        print_fail "Docker daemon not running"
    fi
else
    print_fail "Docker not installed"
fi

# Check containers
print_header "Container Health"
if [ -f "docker-compose.yml" ]; then
    print_pass "docker-compose.yml exists"
    if docker compose ps --services --filter "status=running" | grep -q .; then
        print_pass "Containers running"
    else
        print_warn "No containers running - run 'make up'"
    fi
else
    print_fail "docker-compose.yml not found"
fi

# Check Laravel backend
print_header "Laravel Backend"
if [ -d "backend" ]; then
    print_pass "Backend directory exists"
    if [ -f "backend/composer.json" ]; then
        print_pass "composer.json exists"
    fi
    if [ -f "backend/artisan" ]; then
        print_pass "artisan command available"
    fi
    if [ -f "backend/.env" ]; then
        print_pass ".env file exists"
    else
        print_warn ".env file missing"
    fi
else
    print_fail "Backend directory not found"
fi

print_header "Health Check Complete"
echo -e "${GREEN}🎉 Environment is ready for development!${NC}"
```

3. **Create AI Assistant Prompts:**
```markdown
# ai/prompts/00_readme_first.md
# ReHome v2 - AI Assistant Quick Start

## Environment Status
Run `make status` to check container health
Run `./scripts/dev/health-check.sh` for full validation

## Key URLs
- Admin Panel: http://localhost:8000/system
- API: http://localhost:8000/api
- Frontend: http://localhost:3000 (when implemented)

## Development Commands
- `make up` - Start containers
- `make down` - Stop containers
- `make migrate` - Run migrations
- `make admin` - Create admin user
- `make logs` - View logs

## Project Structure
- `backend/` - Laravel 11 + Filament v3
- `frontend/` - React 18 SPA (future)
- `docker/` - Container configuration
- `scripts/dev/` - Development scripts
- `docs/` - Documentation
```

### Phase 5: Documentation

1. **Create README.md:**
```markdown
# ReHome v2 - AI-Centered Project Management Platform

> **Light by default. MyHome system. Single admin role. Built for Codespace AI.**

## Quick Start

```bash
# Clone and setup
git clone <repository>
cd rehome-v2

# Start development environment
make up
make migrate
make admin

# Access admin panel
open http://localhost:8000/system
```

## Development Commands

- `make help` - Show all commands
- `make up` - Start containers
- `make down` - Stop containers
- `make migrate` - Run migrations
- `make admin` - Create admin user
- `make logs` - View logs
- `make status` - Check container status

## Project Structure

- `backend/` - Laravel 11 + Filament v3
- `frontend/` - React 18 SPA (future)
- `docker/` - Container configuration
- `scripts/dev/` - Development scripts
- `docs/` - Documentation
- `ai/` - AI assistant prompts

## Key Features

- **Single admin role** via `has_admin_role` boolean
- **MyHome system** for append-only activity logging
- **Light profile** with file cache, file sessions, sync queues
- **Filament admin panel** at `/system`
- **Docker-based development** environment

## Next Steps

1. Implement MyHome system (Phase 2)
2. Add API endpoints (Phase 3)
3. Build React SPA (Phase 4)
4. Add AI agent system (Phase 5)
```

2. **Create Project Structure Documentation:**
```markdown
# docs/PROJECT_STRUCTURE.md
# ReHome v2 - Project Structure

## Root Level Organization

```
rehome-v2/
├── 🔧 Configuration & Setup
├── 🚀 Applications (Backend & Frontend)  
├── 🐳 Infrastructure (Docker)
├── 📚 Documentation & Guides
├── 🤖 AI Assistant System
├── 🛠️ Development Tools & Scripts
└── 📋 Project Management Files
```

## Applications

### Backend (`/backend/`) - Laravel 11 + Filament 3
- `app/` - Core application logic
- `config/` - Configuration files
- `database/` - Migrations, seeders, factories
- `routes/` - Route definitions
- `public/` - Web-accessible files

### Frontend (`/frontend/`) - React 18 SPA (Future)
- `src/` - Source code
- `public/` - Public assets
- `package.json` - Node dependencies

## Infrastructure

### Docker Configuration (`/docker/`)
- `Dockerfile.php` - PHP-FPM container
- `nginx.conf` - Nginx web server config
- `php.ini` - PHP configuration

### Development Tools
- `Makefile` - Development commands
- `scripts/dev/` - Health checks and validation
- `ai/prompts/` - AI assistant guidance

## Service URLs

| Service | URL | Purpose |
|---------|-----|---------|
| **Admin Panel** | http://localhost:8000/system | Filament admin interface |
| **API** | http://localhost:8000/api | REST API endpoints |
| **Frontend** | http://localhost:3000 | React development server |

## Key Technologies

- **Backend**: Laravel 11, Filament 3, SQLite
- **Frontend**: React 18, TypeScript, Vite (future)
- **Infrastructure**: Docker, Nginx
- **Development**: PHPStan, Laravel Pint
```

### Phase 6: Run & Test

1. **Start Development Environment:**
```bash
make up
make migrate
make admin
```

2. **Verify Setup:**
```bash
./scripts/dev/health-check.sh
make status
```

3. **Test Admin Panel:**
- Visit http://localhost:8000/system
- Login with created admin user
- Verify user management works
- Check workspace and project resources

### Expected Deliverables

After completion, you should have:

1. **Complete development environment** matching current setup
2. **Working Laravel 11 application** with SQLite database
3. **Filament admin panel** at `/system` with:
   - User management (with admin toggle)
   - Workspace management (read-only)
   - Project management (read-only)
4. **Docker-based development** with proper configuration
5. **Development tools** (Makefile, health checks, scripts)
6. **Documentation** and AI assistant prompts
7. **Project structure** ready for future phases

### Testing Checklist

- [ ] `make up` starts all containers
- [ ] `make migrate` runs successfully
- [ ] `make admin` creates admin user
- [ ] Admin can login at `/system`
- [ ] Admin sees users, workspaces, projects in Filament
- [ ] Admin can toggle `has_admin_role` on users
- [ ] Health check script passes
- [ ] Documentation is complete
- [ ] Project structure matches requirements

### Next Steps (Not in Scope)

This implementation stops at the Filament admin panel with a complete development environment. Future phases will add:
- MyHome system (NDJSON file storage)
- API endpoints for SPA
- React SPA frontend
- AI agent system
- Task management

**Build the foundation first, then iterate based on real usage.**