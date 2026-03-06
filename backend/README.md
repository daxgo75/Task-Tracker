# Task Tracker — Backend API

API backend untuk aplikasi manajemen project dan task, dibangun dengan **Laravel 12**, **PostgreSQL**, dan dilindungi dengan **Laravel Sanctum**.

---

## Tech Stack

| Komponen  | Teknologi                               |
| --------- | --------------------------------------- |
| Framework | Laravel 12 (PHP 8.2+)                   |
| Database  | PostgreSQL                              |
| Auth      | Laravel Sanctum (Personal Access Token) |
| Testing   | PHPUnit 11                              |

---

## Prasyarat

- PHP 8.2+
- Composer 2.x
- PostgreSQL 14+

---

## Instalasi

### 1. Install dependencies

```bash
cd Task-Tracker/backend
composer install
```

### 2. Setup environment

```bash
cp .env.example .env
php artisan key:generate
```

**Edit `.env`:**

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=task_tracker
DB_USERNAME=postgres
DB_PASSWORD=

L5_SWAGGER_CONST_HOST=http://localhost:8000
L5_SWAGGER_GENERATE_ALWAYS=true
```

### 3. Buat database

```sql
CREATE DATABASE task_tracker;
```

### 4. Jalankan migrasi & seeder

```bash
php artisan migrate --seed
```

---

## Menjalankan Aplikasi

```bash
php artisan serve
```

API tersedia di: **http://localhost:8000**

**Login dengan:**

- Email: `admin@tasktracker.com`
- Password: `password`

**API Documentation (Swagger):**

```
http://localhost:8000/api/documentation
```

---

## Menjalankan Testing

### Jalankan semua test (31 test cases)

```bash
php artisan test --env=testing
```

### Jalankan test per suite

```bash
php artisan test --testsuite=Feature --env=testing
php artisan test --testsuite=Unit --env=testing
```

### Test dengan coverage report

```bash
php artisan test --coverage --env=testing
```
