# Project Structure

## Repository Layout

```
school-hub/
├── backend-schoolhub/          # Laravel API backend
├── frontend-schoolhub/         # Vue.js frontend
└── .kiro/                      # Kiro AI configuration
```

## Backend Structure (Laravel)

```
backend-schoolhub/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/            # API controllers (AuthController, GuruController, etc.)
│   │   ├── Middleware/         # Custom middleware (RoleMiddleware, RateLimitExamActions)
│   │   └── Requests/           # Form request validation classes
│   ├── Models/                 # Eloquent models (User, Murid, Guru, etc.)
│   └── Providers/              # Service providers
├── bootstrap/                  # Framework bootstrap files
├── config/                     # Configuration files
├── database/
│   ├── factories/              # Model factories for testing
│   ├── migrations/             # Database migrations
│   └── seeders/                # Database seeders
├── routes/
│   ├── api.php                 # API routes
│   └── web.php                 # Web routes
├── storage/                    # Application storage (logs, cache, uploads)
├── tests/                      # PHPUnit tests
├── .env                        # Environment configuration
├── artisan                     # CLI tool
└── composer.json               # PHP dependencies
```

### Backend Naming Conventions

- **Controllers**: `{Entity}Controller.php` in `Api` namespace
- **Models**: Singular entity names (e.g., `Murid`, `Guru`, `Kelas`)
- **Migrations**: Date-prefixed with descriptive names
- **Routes**: RESTful conventions with Indonesian entity names
- **Database tables**: Plural lowercase (e.g., `murids`, `gurus`, `kelas`)

### Key Backend Directories

- `app/Http/Controllers/Api/` - All API endpoints grouped by entity
- `app/Models/` - Eloquent models with relationships and casts
- `database/migrations/` - Schema definitions (chronologically ordered)
- `database/seeders/` - Test and initial data seeders

## Frontend Structure (Vue.js)

```
frontend-schoolhub/
├── public/                     # Static assets
├── src/
│   ├── assets/                 # CSS, images, fonts
│   ├── components/             # Reusable Vue components
│   │   └── ui/                 # UI component library
│   ├── router/                 # Vue Router configuration
│   │   └── index.js            # Route definitions
│   ├── utils/                  # Utility functions and helpers
│   │   └── api.js              # Axios configuration
│   ├── views/                  # Page components (organized by role)
│   │   ├── admin/              # Admin dashboard and management pages
│   │   ├── auth/               # Login, registration, password reset
│   │   ├── casis/              # Calon siswa (prospective student) pages
│   │   ├── guru/               # Teacher dashboard and tools
│   │   ├── karyawan/           # Staff dashboard
│   │   ├── murid/              # Student dashboard and features
│   │   ├── BeritaView.vue      # News listing/detail
│   │   ├── HomeView.vue        # Public homepage
│   │   └── pengumumanview.vue  # Announcements
│   ├── App.vue                 # Root component
│   └── main.js                 # Application entry point
├── .env                        # Environment variables
├── index.html                  # HTML entry point
├── vite.config.js              # Vite configuration
└── package.json                # Node dependencies
```

### Frontend Naming Conventions

- **Views**: `{Entity}View.vue` or `{Role}Dashboard.vue`
- **Components**: PascalCase (e.g., `NavBar.vue`, `UserCard.vue`)
- **Router files**: kebab-case for route paths
- **Folders**: Organized by user role (admin, guru, murid, karyawan, casis)

### Key Frontend Directories

- `src/views/` - All page components, organized by user role
- `src/components/` - Shared/reusable components
- `src/router/` - Route definitions with role-based guards
- `src/utils/api.js` - Axios instance with interceptors for auth

## API Route Organization

Routes are organized by entity/domain in `routes/api.php`:

- **Public routes** - `/api/public/*` (no authentication)
- **Auth routes** - `/api/auth/*` (login, logout, me)
- **Protected routes** - All other routes require `auth:sanctum` middleware
- **Role-based routes** - Use `role` middleware for access control

### Route Patterns

```
/api/auth/*              # Authentication endpoints
/api/dashboard/*         # Role-specific dashboard data
/api/{entity}/*          # CRUD operations (guru, murid, kelas, etc.)
/api/ppdb/*              # PPDB admission system
/api/public/*            # Public-facing content
```

## Database Models & Relationships

### Core Entities

- **User** - Base authentication model
  - Roles: admin, guru, murid, karyawan, calon_siswa
  - Has one: Murid, Guru, Karyawan, CalonSiswa
  
- **Murid** (Student)
  - Belongs to: User, Kelas
  - Has many: PengumpulanTugas
  
- **Guru** (Teacher)
  - Belongs to: User
  - Has many: Subjekguru, Jadwal
  
- **Kelas** (Class)
  - Has many: Murid, Subjekkelas
  
- **Subjek** (Subject)
  - Has many through: Subjekkelas, Subjekguru
  
- **PPDB Entities**
  - CalonSiswa, PpdbExam, PpdbQuestion, PpdbOption, PpdbExamAttempt, PpdbExamSession, PpdbActivityLog

### Relationship Patterns

- One-to-One: User ↔ Murid/Guru/Karyawan/CalonSiswa
- One-to-Many: Kelas → Murid, Guru → Jadwal
- Many-to-Many: Guru ↔ Subjek (via Subjekguru), Kelas ↔ Subjek (via Subjekkelas)

## Configuration Files

### Backend
- `.env` - Environment configuration (database, app settings)
- `config/cors.php` - CORS settings for frontend access
- `config/sanctum.php` - API authentication configuration

### Frontend
- `.env` - API base URL configuration
- `vite.config.js` - Build config and proxy setup
- `.prettierrc.json` - Code formatting rules
- `eslint.config.js` - Linting rules

## Common File Patterns

### Controllers
All controllers follow Laravel resource controller pattern:
- `index()` - List resources
- `store()` - Create resource
- `show($id)` - Show single resource
- `update($id)` - Update resource
- `destroy($id)` - Delete resource

### Vue Components
Components use Composition API with `<script setup>`:
```vue
<script setup>
import { ref, onMounted } from 'vue'
// Component logic
</script>

<template>
  <!-- Template -->
</template>

<style scoped>
/* Scoped styles */
</style>
```

## Asset Management

### Backend
- Public files: `public/` directory
- Storage files: `storage/app/` (uploads, user files)
- Access via: `storage_path()` or `public_path()` helpers

### Frontend
- Static assets: `public/` (copied as-is)
- Processed assets: `src/assets/` (processed by Vite)
- Access via: `@/assets/...` alias in components
