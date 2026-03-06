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

