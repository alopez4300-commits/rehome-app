# ReHome v2 - Project Structure

## Root Level Organization

```
rehome-app/
├── 🔧 Configuration & Setup
├── 🚀 Laravel Application (Root Level)
├── 🐳 Infrastructure (Docker)
├── 📚 Documentation & Guides
├── 🤖 AI Assistant System
├── 🛠️ Development Tools & Scripts
└── 📋 Project Management Files
```

## Applications

### Laravel Backend (Root Level)
- `app/` - Core application logic
- `config/` - Configuration files
- `database/` - Migrations, seeders, factories
- `routes/` - Route definitions
- `public/` - Web-accessible files
- `composer.json` - PHP dependencies

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
| **Admin Panel** | http://localhost:8000/admin | Filament admin interface |
| **API** | http://localhost:8000/api | REST API endpoints |
| **Frontend** | http://localhost:3000 | React development server |

## Key Technologies

- **Backend**: Laravel 11, Filament 3, SQLite
- **Frontend**: React 18, TypeScript, Vite (future)
- **Infrastructure**: Docker, Nginx
- **Development**: PHPStan, Laravel Pint

## Codespace Compatibility

This project is configured to work in both local development and GitHub Codespaces:

- **Root-level Laravel app** - No separate backend directory
- **Docker configuration** - Works in both environments
- **Development commands** - Compatible with Codespace port forwarding
- **Health checks** - Validate environment setup

## Development Workflow

1. **Local Development**: Use `make dev` or `php artisan serve`
2. **Docker Development**: Use `make up` for containerized setup
3. **Codespace Development**: Use `make dev` with port forwarding
4. **Health Check**: Run `./scripts/dev/health-check.sh`

## File Structure Details

```
rehome-app/
├── app/
│   ├── Filament/Admin/Resources/     # Filament admin resources
│   ├── Models/                       # Eloquent models
│   └── Providers/                    # Service providers
├── database/
│   ├── migrations/                   # Database migrations
│   └── database.sqlite              # SQLite database
├── docker/                          # Docker configuration
├── frontend/                        # React SPA (future)
├── scripts/dev/                     # Development scripts
├── docs/                           # Documentation
├── ai/prompts/                     # AI assistant prompts
├── docker-compose.yml              # Multi-container setup
├── Makefile                        # Development commands
├── package.json                    # Root-level dependencies
└── README.md                       # Project documentation
```
