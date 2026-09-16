# Technology Stack

## Architecture

Full-stack web application with separate frontend and backend:
- **Backend**: Laravel 12 REST API
- **Frontend**: Vue.js 3 SPA (Single Page Application)
- **Communication**: RESTful API with Laravel Sanctum authentication

## Backend Stack

### Framework & Language
- **Laravel 12** (PHP 8.2+)
- **Database**: SQLite (default), supports MySQL/PostgreSQL
- **Authentication**: Laravel Sanctum for API token authentication
- **Testing**: PHPUnit

### Key Dependencies
- `laravel/sanctum` - API authentication
- `laravel/tinker` - REPL for debugging
- `laravel/pail` - Log viewer
- `laravel/pint` - Code style fixer
- `fakerphp/faker` - Test data generation

## Frontend Stack

### Framework & Language
- **Vue.js 3.5+** (Composition API)
- **Vue Router 5** - Client-side routing
- **Vite 8** - Build tool and dev server
- **Axios** - HTTP client

### Key Dependencies
- `@vueuse/motion` - Animation utilities
- `chart.js` - Data visualization
- `vue-router` - Routing

### Code Quality Tools
- **ESLint 10** - Linting with Vue plugin
- **Prettier 3.9** - Code formatting
- **Oxlint** - Fast linter

### Node.js Requirements
- Node.js v22.18.0 or v24.12.0+
- npm 10+

## Development Tools

### Backend Commands

```bash
# Setup
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed

# Development
php artisan serve              # Start dev server (http://localhost:8000)
composer run dev               # Start all services (server + queue + logs + vite)
php artisan queue:listen       # Start queue worker
php artisan pail               # View logs in real-time

# Testing
composer run test              # Run PHPUnit tests
php artisan test

# Code Quality
./vendor/bin/pint              # Fix code style

# Database
php artisan migrate            # Run migrations
php artisan migrate:fresh      # Drop all tables and re-run migrations
php artisan db:seed            # Seed database
php artisan migrate:fresh --seed  # Fresh migration + seed

# Tinker (REPL)
php artisan tinker             # Interactive shell
```

### Frontend Commands

```bash
# Setup
npm install --legacy-peer-deps

# Development
npm run dev                    # Start Vite dev server (http://localhost:5173)

# Build
npm run build                  # Production build
npm run preview                # Preview production build

# Code Quality
npm run lint                   # Lint and fix with oxlint + eslint
npm run format                 # Format with Prettier
```

## Configuration

### Backend Environment
- `.env` file for configuration
- Database: SQLite by default (`database/database.sqlite`)
- API runs on port 8000

### Frontend Environment
- `.env` file for configuration
- Key variable: `VITE_API_BASE_URL=http://localhost:8000`
- Dev server runs on port 5173
- Vite proxy configured for `/api/*` routes

### CORS Configuration
Backend must allow requests from `http://localhost:5173` in `config/cors.php`:
```php
'allowed_origins' => [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
],
'supports_credentials' => true,
```

## API Structure

- **Base URL**: `http://localhost:8000/api`
- **Auth**: Bearer token via Laravel Sanctum
- **Prefix**: All routes use `/api` prefix
- **Format**: JSON request/response

## Common Workflows

### Full Development Setup
```bash
# Backend
cd backend-schoolhub
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve

# Frontend (new terminal)
cd frontend-schoolhub
npm install --legacy-peer-deps
cp .env.example .env
npm run dev
```

### Running Both Servers Concurrently
```bash
# Backend (from backend-schoolhub/)
composer run dev  # Runs server, queue, logs, and vite together
```

### Database Reset
```bash
php artisan migrate:fresh --seed
```

## Build System Notes

- Backend uses Composer for dependency management
- Frontend uses npm with `--legacy-peer-deps` flag for compatibility
- Vite handles HMR (Hot Module Replacement) for fast development
- Laravel Pint enforces PSR-12 code style for PHP
- ESLint + Prettier enforce consistent JavaScript/Vue style
