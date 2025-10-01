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
if [ -f "composer.json" ]; then
    print_pass "composer.json exists"
    if [ -f "artisan" ]; then
        print_pass "artisan command available"
    fi
    if [ -f ".env" ]; then
        print_pass ".env file exists"
    else
        print_warn ".env file missing"
    fi
    if [ -f "vendor/autoload.php" ]; then
        print_pass "Composer dependencies installed"
    else
        print_warn "Composer dependencies not installed - run 'composer install'"
    fi
else
    print_fail "composer.json not found"
fi

# Check database
print_header "Database"
if [ -f "database/database.sqlite" ]; then
    print_pass "SQLite database exists"
else
    print_warn "SQLite database not found - run 'php artisan migrate'"
fi

# Check Filament
print_header "Filament Admin Panel"
if [ -d "app/Filament" ]; then
    print_pass "Filament directory exists"
    if [ -f "app/Providers/Filament/AdminPanelProvider.php" ]; then
        print_pass "AdminPanelProvider exists"
    fi
else
    print_warn "Filament not installed"
fi

# Check MyHome system
print_header "MyHome System"
if [ -d "app/Services/MyHome" ]; then
    print_pass "MyHome services directory exists"
else
    print_fail "MyHome services directory not found"
fi

if [ -f "app/Services/MyHome/MyHomeService.php" ]; then
    print_pass "MyHomeService exists"
else
    print_fail "MyHomeService not found"
fi

if [ -f "app/Services/MyHome/MyHomeQueryService.php" ]; then
    print_pass "MyHomeQueryService exists"
else
    print_fail "MyHomeQueryService not found"
fi

if [ -d "app/Services/Agent" ]; then
    print_pass "AI Agent services directory exists"
else
    print_fail "AI Agent services directory not found"
fi

if [ -f "app/Services/Agent/AgentService.php" ]; then
    print_pass "AgentService exists"
else
    print_fail "AgentService not found"
fi

if [ -f "config/ai.php" ]; then
    print_pass "AI configuration exists"
else
    print_fail "AI configuration not found"
fi

# Check admin user
print_header "Admin User"
if php artisan tinker --execute="echo App\Models\User::where('has_admin_role', true)->count();" 2>/dev/null | grep -q "1"; then
    print_pass "Admin user exists"
else
    print_warn "Admin user not found - create with: php artisan tinker --execute=\"App\Models\User::create(['name' => 'Admin', 'email' => 'admin@rehome.com', 'password' => bcrypt('password'), 'has_admin_role' => true]);\""
fi

print_header "Health Check Complete"
echo -e "${GREEN}🎉 Environment is ready for development!${NC}"
echo -e "\n${BLUE}Next steps:${NC}"
echo -e "1. Start development server: ${YELLOW}make dev${NC} or ${YELLOW}php artisan serve${NC}"
echo -e "2. Access admin panel: ${YELLOW}http://localhost:8000/admin${NC}"
echo -e "3. Login with: ${YELLOW}admin@rehome.com / password${NC}"
