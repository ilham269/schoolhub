<div align="center">

# 🏫 SCHOOLHUB

### **School Information & Management System**

<p>
  <strong>One Platform. One School. Better Management.</strong>
</p>

<p>
  <em>A modern web-based platform for managing school information, academics, admissions, administration, and finance.</em>
</p>

<br>

![Status](https://img.shields.io/badge/status-in--development-orange?style=for-the-badge)
![Version](https://img.shields.io/badge/version-1.0.0-blue?style=for-the-badge)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge\&logo=laravel\&logoColor=white)
![Vue](https://img.shields.io/badge/Vue.js-3-42B883?style=for-the-badge\&logo=vue.js\&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8+-4479A1?style=for-the-badge\&logo=mysql\&logoColor=white)

<br>

**[📖 Documentation](#-documentation)** ·
**[🚀 Installation](#-quick-start)** ·
**[✨ Features](#-features)** ·
**[🗺️ Roadmap](#️-roadmap)**

</div>

---

## 🧭 Overview

**SCHOOLHUB** adalah sistem informasi dan manajemen sekolah berbasis web yang dirancang untuk mengintegrasikan berbagai kebutuhan sekolah ke dalam satu platform.

Mulai dari **informasi sekolah, PPDB, ujian calon siswa, tugas, nilai, jadwal, administrasi, hingga keuangan**, semuanya dirancang agar dapat dikelola secara terstruktur melalui sistem dengan hak akses berbasis role.

> 🎯 **Goal:** Mengurangi proses yang terpisah dan menghadirkan satu platform digital yang mudah digunakan oleh seluruh lingkungan sekolah.

---

## 💡 Why SCHOOLHUB?

Sistem sekolah sering kali menggunakan banyak media yang berbeda untuk mengelola informasi dan aktivitas.

SCHOOLHUB dirancang untuk menyatukan proses tersebut:

```text
        INFORMATION
             │
             ▼
      ┌─────────────┐
      │  SCHOOLHUB  │
      └─────────────┘
       │     │     │
       ▼     ▼     ▼
   Academic  Admin  Finance
       │     │     │
       └─────┼─────┘
             ▼
      Centralized Data
```

### Fokus utama

| 🎯 Area            | Fokus                              |
| ------------------ | ---------------------------------- |
| 📢 Information     | Berita & pengumuman sekolah        |
| 🎓 Academic        | Tugas, nilai, materi & jadwal      |
| 📝 Admission       | PPDB & ujian calon siswa           |
| 🗂️ Administration | Data & administrasi siswa          |
| 💰 Finance         | Pembayaran & keuangan              |
| 🔐 Security        | Authentication & role-based access |
| ♿ Accessibility    | UI nyaman dan mudah digunakan      |

---

# ✨ Features

## 🌐 Public Website

Pengunjung dapat mengakses informasi sekolah tanpa login.

* 🏠 Homepage
* 🏫 Profil Sekolah
* 🎯 Visi & Misi
* 📰 Berita
* 📢 Pengumuman
* 📝 Informasi PPDB
* 📋 Pendaftaran Online
* 📞 Kontak

---

## 📝 PPDB & Online Examination

Sistem penerimaan siswa baru yang terintegrasi dengan ujian online.

```text
Registration
     │
     ▼
Document Upload
     │
     ▼
Verification
     │
     ▼
Exam Account
     │
     ▼
Online Examination
     │
     ▼
Result
```

Fitur:

* Form pendaftaran
* Upload dokumen
* Tracking status pendaftaran
* Akun ujian calon siswa
* Online examination
* Question management
* Automatic scoring

---

## 👨‍🎓 Student Portal

Siswa mendapatkan satu dashboard untuk kebutuhan akademik dan informasi pribadi.

* 📊 Dashboard
* 👤 Profile
* 📚 Assignments
* 📤 Assignment Submission
* 📝 Grades
* 🗓️ Schedule
* 🧪 Exams
* 💳 Administration
* 💰 Finance
* 📢 Announcements

---

## 👨‍🏫 Teacher Portal

Guru dapat mengelola aktivitas pembelajaran.

* 📊 Teacher Dashboard
* 🏫 Class Management
* 📝 Assignment Management
* 📥 Submission Monitoring
* 🧮 Grade Management
* 📚 Learning Materials
* 📢 Announcements

---

## 🧑‍💼 Employee Portal

Karyawan berfokus pada administrasi dan keuangan sekolah.

* 👥 Student Data
* 🗂️ Administration
* 💳 Payments
* 💰 Financial Records
* 📊 Reports

---

## 👨‍💻 Admin Dashboard

Admin memiliki akses terhadap pengelolaan sistem secara keseluruhan.

* 📊 System Dashboard
* 👥 User Management
* 🎓 Student Management
* 👨‍🏫 Teacher Management
* 🧑‍💼 Employee Management
* 🏫 Class Management
* 📚 Subject Management
* 📝 Exam Management
* ❓ Question Management
* 📋 Admission Management
* 📰 News Management
* 📢 Announcement Management
* ⚙️ System Settings
* 📊 Reports

---

# 🔐 Authentication & Authorization

SCHOOLHUB menggunakan **Laravel Sanctum** untuk authentication dan menerapkan **role-based authorization**.

### User Roles

```text
                    ┌───────────────┐
                    │   SCHOOLHUB   │
                    │   AUTH SYSTEM  │
                    └───────┬───────┘
                            │
          ┌─────────────────┼─────────────────┐
          │                 │                 │
          ▼                 ▼                 ▼
       ADMIN              GURU             SISWA
          │                 │                 │
          └─────────────────┼─────────────────┘
                            │
                            ▼
                       KARYAWAN
```

| Role           | Access Level                    |
| -------------- | ------------------------------- |
| 👨‍💻 Admin    | Full system access              |
| 👨‍🏫 Guru     | Academic management             |
| 👨‍🎓 Siswa    | Academic & personal information |
| 🧑‍💼 Karyawan | Administration & finance        |

> 🔒 Data pribadi, nilai, administrasi, dan keuangan dibatasi berdasarkan hak akses pengguna.

---

# 🏗️ System Architecture

SCHOOLHUB menggunakan pendekatan **separated frontend & backend architecture**.

```text
┌──────────────────────────────────────┐
│              FRONTEND                │
│                                      │
│             Vue.js 3                 │
│                                      │
│   Vue Router │ Pinia │ Axios         │
└──────────────────┬───────────────────┘
                   │
                   │ REST API
                   │ HTTP / JSON
                   ▼
┌──────────────────────────────────────┐
│               BACKEND                │
│                                      │
│             Laravel 12               │
│                                      │
│ Sanctum │ Controller │ Service       │
│ Request │ Resource   │ Middleware    │
└──────────────────┬───────────────────┘
                   │
                   │ Eloquent ORM
                   ▼
┌──────────────────────────────────────┐
│              DATABASE                │
│                                      │
│              MySQL 8+                │
└──────────────────────────────────────┘
```

---

# 🛠️ Tech Stack

### Frontend

| Technology       | Purpose             |
| ---------------- | ------------------- |
| **Vue.js 3**     | Frontend framework  |
| **Vue Router**   | Client-side routing |
| **Pinia**        | State management    |
| **Axios**        | HTTP client         |
| **Tailwind CSS** | UI styling          |

### Backend

| Technology          | Purpose                |
| ------------------- | ---------------------- |
| **Laravel 12**      | Backend framework      |
| **PHP 8.2+**        | Programming language   |
| **Laravel Sanctum** | Authentication         |
| **REST API**        | Frontend communication |
| **Eloquent ORM**    | Database interaction   |

### Database & Development

```text
MySQL 8+
Git
GitHub
Figma
Postman
Laragon
Visual Studio Code
```

---

# 🗄️ Database

SCHOOLHUB menggunakan relational database dengan beberapa domain utama:

```text
USER MANAGEMENT
│
├── users
├── students
├── teachers
└── employees

ACADEMIC
│
├── classes
├── subjects
├── assignments
├── submissions
├── grades
├── materials
└── schedules

EXAMINATION
│
├── exams
├── questions
├── options
├── exam_attempts
└── exam_answers

ADMISSION
│
└── enrollments

ADMINISTRATION
│
├── administrative_records
├── payments
└── financial_records

INFORMATION
│
├── announcements
├── news
└── settings
```

---

# 📁 Project Structure

```text
schoolhub/
│
├── backend/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Middleware/
│   │   │   └── Requests/
│   │   ├── Models/
│   │   └── Services/
│   │
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── factories/
│   │
│   ├── routes/
│   │   ├── api.php
│   │   └── web.php
│   │
│   └── .env
│
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   ├── layouts/
│   │   ├── views/
│   │   ├── router/
│   │   ├── stores/
│   │   └── services/
│   │
│   └── .env
│
├── docs/
│
└── README.md
```

---

# 🚀 Quick Start

## Prerequisites

Pastikan sudah terinstall:

```text
PHP 8.2+
Composer
Node.js LTS
MySQL 8+
Git
```

---

## 1. Clone Repository

```bash
git clone <repository-url>

cd schoolhub
```

---

## 2. Setup Backend

```bash
cd backend

composer install

cp .env.example .env

php artisan key:generate
```

Konfigurasi database:

```env
DB_DATABASE=schoolhub
DB_USERNAME=root
DB_PASSWORD=
```

Install API & Sanctum:

```bash
php artisan install:api
```

Migrasi database:

```bash
php artisan migrate
```

Jalankan server:

```bash
php artisan serve
```

Backend:

```text
http://127.0.0.1:8000
```

---

## 3. Setup Frontend

Buka terminal baru:

```bash
cd frontend

npm install
```

Buat `.env`:

```env
VITE_API_URL=http://127.0.0.1:8000/api
```

Jalankan:

```bash
npm run dev
```

Frontend:

```text
http://localhost:5173
```

---

# 🔌 API Overview

Contoh endpoint utama:

| Method | Endpoint                 | Role              |
| ------ | ------------------------ | ----------------- |
| `POST` | `/api/login`             | Public            |
| `POST` | `/api/logout`            | Auth              |
| `GET`  | `/api/user`              | Auth              |
| `POST` | `/api/ppdb`              | Public            |
| `GET`  | `/api/assignments`       | Student / Teacher |
| `POST` | `/api/assignments`       | Teacher           |
| `POST` | `/api/submissions`       | Student           |
| `GET`  | `/api/grades`            | Student / Teacher |
| `POST` | `/api/grades`            | Teacher           |
| `GET`  | `/api/exams`             | Student / Admin   |
| `POST` | `/api/exams`             | Admin             |
| `POST` | `/api/exams/{id}/submit` | Student           |
| `GET`  | `/api/admin/users`       | Admin             |
| `POST` | `/api/admin/users`       | Admin             |

> 📚 Dokumentasi API lengkap dapat ditempatkan di `docs/api.md`.

---

# 🎨 UI/UX Philosophy

SCHOOLHUB menggunakan konsep:

> **Modern · Minimalist · Calm · Accessible · Professional**

Prinsip desain:

* Clean interface
* Clear visual hierarchy
* Readable typography
* Consistent navigation
* Comfortable button size
* Sufficient whitespace
* Minimal animation
* Responsive layout
* Accessible color contrast
* Easy-to-understand forms

### Design System

```text
Primary      #2563EB
Secondary    #64748B
Background   #F8FAFC
Surface      #FFFFFF
Text         #1E293B
Success      #16A34A
Warning      #D97706
Danger       #DC2626
Border       #E2E8F0
```

**Typography**

```text
Heading → Poppins
Body    → Inter
Fallback → system-ui
```

---

# 📱 Responsive Design

SCHOOLHUB ditargetkan dapat digunakan pada:

```text
┌───────────────┐
│ 📱 Mobile     │
├───────────────┤
│ 📱 Tablet     │
├───────────────┤
│ 💻 Laptop     │
├───────────────┤
│ 🖥️ Desktop    │
└───────────────┘
```

Target breakpoint:

| Device  |            Width |
| ------- | ---------------: |
| Mobile  |        `< 640px` |
| Tablet  | `640px – 1024px` |
| Desktop |       `> 1024px` |

---

# 🧪 Testing

Testing mencakup beberapa area:

```text
Functional
    ↓
Authentication
    ↓
Authorization
    ↓
Validation
    ↓
API
    ↓
Responsive
    ↓
Accessibility
    ↓
Security
```

Contoh:

* Login valid → ✅
* Password salah → ❌
* Siswa mengakses Admin → ❌
* Guru membuat tugas → ✅
* Siswa mengumpulkan tugas → ✅
* Guru input nilai → ✅
* Karyawan input pembayaran → ✅
* Mobile layout → ✅

---

# 🗺️ Roadmap

### Phase 01 — Foundation

* [x] Project planning
* [x] Requirement analysis
* [x] System architecture
* [x] Database planning
* [ ] ERD
* [ ] UI/UX design

### Phase 02 — Core System

* [ ] Laravel setup
* [ ] Vue setup
* [ ] Database migration
* [ ] Model & relationships
* [ ] REST API
* [ ] Sanctum authentication
* [ ] Role authorization

### Phase 03 — Modules

* [ ] Public website
* [ ] Admin dashboard
* [ ] Student dashboard
* [ ] Teacher dashboard
* [ ] Employee dashboard
* [ ] PPDB
* [ ] Online examination
* [ ] Assignment
* [ ] Grade
* [ ] Administration
* [ ] Finance

### Phase 04 — Quality

* [ ] API testing
* [ ] Security testing
* [ ] Responsive testing
* [ ] Accessibility review
* [ ] Bug fixing
* [ ] Performance optimization

### Phase 05 — Release

* [ ] Production configuration
* [ ] Database production
* [ ] Deployment
* [ ] Backup system
* [ ] Documentation
* [ ] v1.0.0 Release

---

# 🔮 Future Development

SCHOOLHUB dirancang agar dapat dikembangkan lebih jauh.

Beberapa fitur yang direncanakan:

* 🔔 Notification System
* 📧 Email Notification
* 📄 PDF Export
* 📊 Excel Export
* 📅 Academic Calendar
* 📝 Digital Report Card
* 💳 Online Payment
* 📱 Mobile Application
* 🌐 Progressive Web App
* 📈 Advanced Dashboard
* 💾 Automatic Backup
* 📋 Audit Log
* 📚 Digital Library
* 📢 Internal Chat
* 🧾 Complaint System

---

# 👥 Team

| Name                 | Responsibility                      |
| -------------------- | ----------------------------------- |
| **Muhamad Ilham**    | Project Manager · Backend Developer |
| **Fatahillah Akbar** | Frontend Developer · UI/UX Designer |

**Class:** XII RPL 1
**Department:** Rekayasa Perangkat Lunak (RPL)

### Project Timeline

```text
Start
01 September 2026
      │
      ▼
Development
      │
      ▼
Testing
      │
      ▼
Deployment
      │
      ▼
Target
30 September 2026
```

---

# 📚 Documentation

Dokumentasi project lengkap dapat dipisahkan ke dalam:

```text
docs/
├── project-requirement.md
├── database.md
├── api.md
├── authentication.md
├── authorization.md
├── architecture.md
├── user-flow.md
├── erd.png
├── use-case.png
└── deployment.md
```

---

# ⚠️ Development Notes

> SCHOOLHUB masih dalam tahap **development**. Struktur database, API, UI, maupun fitur dapat berubah selama proses pengembangan.

**Security reminder:**

```text
.env
credentials
API keys
database passwords
production secrets
```

**Jangan pernah di-commit ke repository public.**

---

# 📊 Project Status

<div align="center">

### 🚧 Currently in Development

**SCHOOLHUB v1.0.0**

<br>

| Area           | Status |
| -------------- | ------ |
| Planning       | 🟢     |
| Architecture   | 🟢     |
| Database       | 🟡     |
| Backend        | 🟡     |
| Frontend       | 🟡     |
| Authentication | 🟡     |
| Testing        | ⚪      |
| Deployment     | ⚪      |

</div>

---

# 📄 License

This project is developed for **educational and school information system development purposes**.

---

<div align="center">

### 🏫 SCHOOLHUB

**One Platform. One School. Better Management.**

<br>

Made with ❤️ by **Muhamad Ilham & Fatahillah Akbar**

<br>

`Laravel 12` · `Vue.js 3` · `Tailwind CSS` · `MySQL`

</div>
