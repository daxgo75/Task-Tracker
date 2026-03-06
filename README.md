# Task Tracker — Backend API

API backend untuk aplikasi manajemen project dan task, dibangun dengan **Laravel 12**, **PostgreSQL**, dan dilindungi dengan **Laravel Sanctum**.

---

## Tech Stack

| Layer     | Teknologi                               |
| --------- | --------------------------------------- |
| Framework | Laravel 12 (PHP 8.2+)                   |
| Database  | PostgreSQL                              |
| Auth      | Laravel Sanctum (Personal Access Token) |
| Docs      | L5-Swagger (OpenAPI 3.0)                |
| Testing   | PHPUnit 11                              |

---

## Prasyarat

- PHP 8.2+ dengan ekstensi: `pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`
- Composer 2.x
- PostgreSQL 14+

---

## Instalasi

### 1. Clone dan install dependensi

```bash
git clone <repo-url>
cd Task-Tracker/backend
composer install
```

### 2. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` dan sesuaikan:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=task_tracker
DB_USERNAME=postgres
DB_PASSWORD=your_password

L5_SWAGGER_CONST_HOST=http://localhost:8000
L5_SWAGGER_GENERATE_ALWAYS=true
```

### 3. Buat database PostgreSQL

```sql
CREATE DATABASE task_tracker;
CREATE DATABASE task_tracker_test;   -- untuk testing
```

### 4. Jalankan migrasi dan seeder

```bash
php artisan migrate --seed
```

Seeder akan membuat:

- **Admin user**: `admin@tasktracker.com` / `password`
- **8 kategori task**: Feature, Bug, Improvement, Research, Design, DevOps, Testing, Documentation

### 5. Jalankan server

```bash
php artisan serve
```

API tersedia di: `http://localhost:8000`

---

## Dokumentasi API (Swagger)

Setelah server berjalan, buka:

```
http://localhost:8000/api/documentation
```

Atau generate ulang swagger docs:

```bash
php artisan l5-swagger:generate
```

---

## Endpoint Utama

### Authentication

| Method | Endpoint           | Deskripsi                |
| ------ | ------------------ | ------------------------ |
| POST   | `/api/auth/login`  | Login, mendapatkan token |
| POST   | `/api/auth/logout` | Logout, revoke token     |
| GET    | `/api/auth/me`     | Profil user yang login   |

### Projects

| Method | Endpoint                           | Deskripsi                      |
| ------ | ---------------------------------- | ------------------------------ |
| GET    | `/api/projects`                    | Daftar project (filter/search) |
| POST   | `/api/projects`                    | Buat project baru              |
| GET    | `/api/projects/{id}`               | Detail project + task list     |
| PUT    | `/api/projects/{id}`               | Update project                 |
| PATCH  | `/api/projects/{id}/toggle-status` | Toggle active/archived         |

### Tasks

| Method | Endpoint                   | Deskripsi                          |
| ------ | -------------------------- | ---------------------------------- |
| GET    | `/api/projects/{id}/tasks` | Task dalam project tertentu        |
| POST   | `/api/projects/{id}/tasks` | Buat task dalam project            |
| GET    | `/api/tasks`               | Semua task (lintas project/global) |
| GET    | `/api/tasks/{id}`          | Detail task                        |
| PUT    | `/api/tasks/{id}`          | Update task                        |
| DELETE | `/api/tasks/{id}`          | Soft delete task                   |

### Dashboard & Categories

| Method | Endpoint          | Deskripsi                        |
| ------ | ----------------- | -------------------------------- |
| GET    | `/api/dashboard`  | Statistik ringkasan              |
| GET    | `/api/categories` | Daftar kategori (untuk dropdown) |

---

## Query Parameters

### Projects

- `?search=keyword` — Cari project berdasarkan nama
- `?status=active|archived` — Filter berdasarkan status
- `?per_page=15` — Jumlah data per halaman

### Tasks

- `?search=keyword` — Cari task berdasarkan judul
- `?category_id=1` — Filter berdasarkan kategori
- `?project_id=1` — Filter berdasarkan project (global endpoint)
- `?per_page=20` — Jumlah data per halaman

---

## Quick Start

Untuk menjalankan project secara cepat:

```bash
# 1. Install
composer install
cp .env.example .env
php artisan key:generate

# 2. Setup database
php artisan migrate --seed

# 3. Run server
php artisan serve

# 4. Login dengan
Email: admin@tasktracker.com
Password: password

# 5. Buka API docs
http://localhost:8000/api/documentation
```

---

## Format Response

Semua response menggunakan format JSON konsisten:

```json
{
  "success": true,
  "message": "Deskripsi pesan yang jelas",
  "data": { ... }
}
```

Error response:

```json
{
    "success": false,
    "message": "Penjelasan error yang deskriptif",
    "errors": {
        "field": ["Pesan validasi per field"]
    }
}
```

---

## Menjalankan Test

Aplikasi memiliki 31 test cases dengan 90 assertions mencakup Authentication, Project Management, Task Management, dan Dashboard.

### Setup database test

```sql
CREATE DATABASE task_tracker_test;
```

### Jalankan semua test

```bash
php artisan test --env=testing
```

### Jalankan test per suite

```bash
php artisan test --testsuite=Feature --env=testing
php artisan test --testsuite=Unit --env=testing
```

### Jalankan test spesifik

```bash
php artisan test tests/Feature/AuthTest.php --env=testing
php artisan test tests/Feature/ProjectTest.php --env=testing
php artisan test tests/Feature/TaskTest.php --env=testing
php artisan test tests/Feature/DashboardTest.php --env=testing
```

### Coverage report

```bash
php artisan test --coverage --env=testing
```

---

## Struktur Direktori

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php      # Login, logout, me
│   │   ├── ProjectController.php   # CRU project + toggle status
│   │   ├── TaskController.php      # CRUD task + global list
│   │   ├── DashboardController.php # Statistik dashboard
│   │   └── CategoryController.php  # Daftar kategori
│   ├── Requests/
│   │   ├── LoginRequest.php
│   │   ├── StoreProjectRequest.php
│   │   ├── UpdateProjectRequest.php
│   │   ├── StoreTaskRequest.php
│   │   └── UpdateTaskRequest.php
│   ├── Resources/
│   │   ├── UserResource.php
│   │   ├── CategoryResource.php
│   │   ├── ProjectResource.php
│   │   ├── ProjectBriefResource.php
│   │   └── TaskResource.php
│   └── Traits/
│       └── ApiResponder.php        # Helper response JSON
├── Models/
│   ├── User.php
│   ├── Category.php
│   ├── Project.php
│   └── Task.php
database/
├── migrations/
│   ├── create_categories_table.php
│   ├── create_projects_table.php
│   └── create_tasks_table.php
├── seeders/
│   ├── AdminSeeder.php
│   └── CategorySeeder.php
└── factories/
    ├── ProjectFactory.php
    ├── TaskFactory.php
    └── CategoryFactory.php
routes/
└── api.php
tests/
├── Feature/
│   ├── AuthTest.php          # 7 test cases
│   ├── ProjectTest.php       # 7 test cases
│   ├── TaskTest.php          # 8 test cases
│   └── DashboardTest.php     # 4 test cases
```

---

## Desain Keputusan

### Soft Delete pada Task

Task menggunakan custom soft delete dengan field `deleted_at` dan `deleted_by` (bukan Laravel `SoftDeletes` bawaan), sehingga kita dapat melacak **siapa** yang menghapus task tersebut.

### Project tidak bisa dihapus

Sesuai spesifikasi, project hanya bisa di-_toggle_ antara `active` dan `archived`. Tidak ada endpoint DELETE untuk project.

### Status Task (Kanban)

Task memiliki tiga status: `todo`, `in_progress`, `done` — dirancang untuk ditampilkan sebagai kolom Kanban board di frontend.

---

## Kredensial Default (Seeder)

| Field    | Value                 |
| -------- | --------------------- |
| Email    | admin@tasktracker.com |
| Password | password              |

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
