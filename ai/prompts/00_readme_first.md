# ReHome v2 - AI Assistant Quick Start

## Environment Status
Run `./scripts/dev/health-check.sh` to check environment health
Run `make status` to check container status

## Key URLs
- Admin Panel: http://localhost:8000/admin
- API: http://localhost:8000/api
- Frontend: http://localhost:3000 (when implemented)

## Development Commands
- `make dev` - Start development server (Codespace compatible)
- `make up` - Start containers
- `make down` - Stop containers
- `make migrate` - Run migrations
- `make admin` - Create admin user
- `make logs` - View logs

## Project Structure
- Root directory contains Laravel 11 application
- `frontend/` - React 18 SPA (future)
- `docker/` - Container configuration
- `scripts/dev/` - Development scripts
- `docs/` - Documentation

## Admin Access
- Email: admin@rehome.com
- Password: password
- Admin panel: http://localhost:8000/admin

## Current Status
✅ Laravel 11 + Filament v3 installed
✅ Database schema created (users, workspaces, projects)
✅ Admin user created
✅ Filament resources generated
✅ Docker configuration ready
✅ Development tools configured

## Next Steps
1. Test admin panel access
2. Implement MyHome system (Phase 2)
3. Add API endpoints (Phase 3)
4. Build React SPA (Phase 4)
