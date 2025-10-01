# ReHome v2 - Codespace AI Implementation Instructions

> **Complete implementation guide for building ReHome v2 from scratch**
>
> **Follow these instructions exactly to build the AI-centered project management platform**

---

## 🎯 **Project Overview**

You are building **ReHome v2**, an AI-centered project management platform with the following key features:

- **MyHome System** - Append-only NDJSON activity stream as primary source of truth
- **Single Admin Role** - Boolean flag instead of complex permissions
- **Light Profile** - File cache, file sessions, sync queues (no Redis initially)
- **AI Agent System** - OpenAI GPT-4o-mini with project context from MyHome
- **Two Application Surfaces** - `/system` (Filament admin) and `/app` (React SPA)

### **Core Philosophy**

- **Build the minimum that works**
- **Start light, scale when metrics demand it**
- **AI as enhancement, not requirement**
- **Iterate based on real usage**

---

## 🏗️ **Environment Setup**

### **1. Create Project Structure**

```bash
mkdir rehome-v2 && cd rehome-v2
mkdir -p backend frontend docker scripts/dev docs ai/prompts
```

### **2. Create Root Package.json**

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

### **3. Create Docker Compose Configuration**

```yaml
# docker-compose.yml
version: "3.8"

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

### **4. Create Docker Configuration Files**

**docker/Dockerfile.php:**

```dockerfile
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

**docker/nginx.conf:**

```nginx
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

**docker/php.ini:**

```ini
upload_max_filesize=100M
post_max_size=100M
memory_limit=512M
max_execution_time=300
```

### **5. Create Makefile**

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

---

## 🚀 **Phase 0-1: Foundation Implementation**

### **Step 1: Create Laravel Backend**

```bash
cd backend
composer create-project laravel/laravel . --prefer-dist
```

### **Step 2: Install Essential Packages**

```bash
composer require laravel/framework:^11.0
composer require laravel/sanctum:^4.0
composer require filament/filament:^3.0
composer require --dev laravel/pint
composer require --dev nunomaduro/larastan
composer require --dev laravel/telescope
```

### **Step 3: Environment Configuration**

```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
```

**Configure .env for Light Profile:**

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

### **Step 4: Database Schema**

**Create migrations:**

1. **Add admin role to users:**

```bash
php artisan make:migration add_admin_role_to_users_table
```

**Migration content:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_admin_role')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('has_admin_role');
        });
    }
};
```

2. **Create workspaces table:**

```bash
php artisan make:migration create_workspaces_table
```

**Migration content:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('owner_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
```

3. **Create projects table:**

```bash
php artisan make:migration create_projects_table
```

**Migration content:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
```

4. **Create workspace_user pivot:**

```bash
php artisan make:migration create_workspace_user_table
```

**Migration content:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member'); // owner/member/consultant/client
            $table->timestamps();

            $table->unique(['workspace_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_user');
    }
};
```

### **Step 5: Create Models**

**Update User model:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'has_admin_role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'has_admin_role' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->has_admin_role;
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function canAccessProject(Project $project): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->workspaces()
            ->where('workspace_id', $project->workspace_id)
            ->exists();
    }

    public function getWorkspaceRole(Workspace $workspace): string
    {
        if ($this->isAdmin()) {
            return 'owner';
        }

        $member = $this->workspaces()
            ->where('workspace_id', $workspace->id)
            ->first();

        return $member ? $member->pivot->role : null;
    }
}
```

**Create Workspace model:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
```

**Create Project model:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'workspace_id',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
```

### **Step 6: Filament Admin Panel Setup**

**Install Filament:**

```bash
php artisan filament:install --panels
```

**Create Filament Resources:**

1. **User Resource:**

```bash
php artisan make:filament-resource User --generate
```

**Customize UserResource:**

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('has_admin_role')
                    ->label('Admin Role')
                    ->helperText('Grants full platform access'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\IconColumn::make('has_admin_role')
                    ->boolean()
                    ->label('Admin'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
```

2. **Workspace Resource:**

```bash
php artisan make:filament-resource Workspace --generate
```

3. **Project Resource:**

```bash
php artisan make:filament-resource Project --generate
```

### **Step 7: Authorization Setup**

**Create AuthServiceProvider:**

```php
<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Workspace;
use App\Policies\ProjectPolicy;
use App\Policies\WorkspacePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Workspace::class => WorkspacePolicy::class,
        Project::class => ProjectPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Admin bypass for all permissions
        Gate::before(function ($user, $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });
    }
}
```

### **Step 8: Custom Login Redirect**

**Create Custom Login Page:**

```php
<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected function getRedirectUrl(): string
    {
        return '/app'; // Redirect to SPA after login
    }
}
```

**Update Filament Panel Configuration:**

```php
<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/system')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

### **Step 9: Storage Structure**

**Create storage directories:**

```bash
mkdir -p storage/app/projects
mkdir -p storage/rate_limits
chmod -R 775 storage bootstrap/cache
```

### **Step 10: Run Migrations and Create Admin User**

```bash
php artisan migrate
php artisan storage:link
php artisan make:filament-user
```

---

## 🧪 **Testing Phase 0-1**

### **Test Checklist**

- [ ] `make up` starts all containers
- [ ] `make migrate` runs successfully
- [ ] `make admin` creates admin user
- [ ] Admin can login at `/system`
- [ ] Admin sees users, workspaces, projects in Filament
- [ ] Admin can toggle `has_admin_role` on users
- [ ] Regular users cannot access `/system`
- [ ] Login redirects to `/app` (404 expected)
- [ ] Health check script passes

### **Expected URLs**

- **Admin Panel:** http://localhost:8000/system
- **API:** http://localhost:8000/api
- **Frontend:** http://localhost:3000 (not implemented yet)

---

## 📋 **Next Steps**

After completing Phase 0-1, you will have:

1. ✅ **Working Laravel 11 application** with SQLite database
2. ✅ **Filament admin panel** at `/system` with user management
3. ✅ **Database schema** for users, workspaces, projects
4. ✅ **Admin user** with `has_admin_role=true`
5. ✅ **Custom login redirect** to `/app`
6. ✅ **Authorization bypass** for admins
7. ✅ **Storage structure** ready for MyHome system

**Phase 2 (MyHome System) will add:**

- MyHome service for NDJSON file operations
- API endpoints for activity logging
- Activity feed UI
- Project access policies

**Phase 3 (Task Board) will add:**

- Task database schema
- Task management API
- Kanban board UI
- Real-time updates

**Phase 4 (AI Agent) will add:**

- AI chat system
- Context building from MyHome
- OpenAI integration
- Rate limiting and cost tracking

**Phase 5 (React SPA) will add:**

- Complete frontend application
- All major features
- Responsive design

**Phase 6 (Production) will add:**

- UI/UX improvements
- Performance optimization
- Security hardening
- Deployment configuration

---

## 🎯 **Success Criteria**

**Phase 0-1 is complete when:**

- Admin can login and manage users/workspaces/projects
- Admin panel is functional at `/system`
- Database schema is correct
- Authorization system works
- Custom login redirect functions
- All tests pass

**Ready for Phase 2 when:**

- Foundation is solid and tested
- Admin panel is working
- Database is properly set up
- Storage structure exists
- Development environment is stable

---

## 📚 **Documentation**

Create these files after Phase 0-1:

1. **README.md** - Project overview and setup instructions
2. **PROJECT_STRUCTURE.md** - Architecture documentation
3. **docs/PHASE_0_1_COMPLETE.md** - Phase completion summary
4. **ai/prompts/00_readme_first.md** - Quick start guide for AI assistants

---

## 🚨 **Important Notes**

1. **Follow phases in order** - Don't skip ahead
2. **Complete each phase** - Don't move to next phase until current is done
3. **Test thoroughly** - Verify all functionality works
4. **Document everything** - Keep track of what you build
5. **Stay light** - Don't add complexity until needed
6. **Build minimum that works** - Focus on core functionality first

---

**Start with Phase 0-1 and build the foundation. Once it's working, move to Phase 2 (MyHome System).**
