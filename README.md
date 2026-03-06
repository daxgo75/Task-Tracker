# Task Tracker — API Backend

Sistem manajemen project dan task dengan **Laravel 12** dan **PostgreSQL**.

---

## 📋 Requirement

Sebelum mulai, pastikan sudah install:

- **PHP 8.2+** (dengan extension: `pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`)
- **Composer** (package manager PHP)
- **PostgreSQL 14+** (database)

---

## 🚀 Instalasi (Setup Awal)

### Step 1: Download dan Setup Project

```bash
cd Task-Tracker/backend
composer install
```

### Step 2: Setup File Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` yang baru dibuat, ubah bagian database sesuai konfigurasi PostgreSQL Anda:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=task_tracker
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### Step 3: Buat Database di PostgreSQL

Buka PostgreSQL, kemudian jalankan:

```sql
CREATE DATABASE task_tracker;
CREATE DATABASE task_tracker_test;
```

### Step 4: Setup Database (Migrasi & Seeder)

```bash
php artisan migrate --seed
```

**Apa yang terjadi:**

- Membuat semua tabel database
- Membuat akun admin default: `admin@tasktracker.com` / `password`
- Membuat 5 kategori task otomatis

✅ **Setup selesai!**

---

## ▶️ Menjalankan Aplikasi

Cukup jalankan satu command:

```bash
php artisan serve
```

Tunggu sampai muncul:

```
Server running on [http://127.0.0.1:8000]
```

API server sudah berjalan di: **http://localhost:8000**

---

## 📚 Mengakses Dokumentasi API (Swagger)

Setelah server berjalan, buka browser dan pergi ke:

```
http://localhost:8000/api/documentation
```

Di halaman ini Anda bisa:

- 👀 **Lihat** semua endpoint API yang tersedia
- 🧪 **Test** API langsung dari browser
- 📖 **Baca** dokumentasi lengkap setiap endpoint
- 🔐 **Setup** Bearer Token untuk test endpoint yang butuh autentikasi

**Contoh Testing di Swagger:**

1. Scroll ke `POST /api/auth/login`
2. Click "Try it out"
3. Masukkan:
   ```json
   {
     "email": "admin@tasktracker.com",
     "password": "password"
   }
   ```
4. Click "Execute"
5. Lihat response token
6. Copy token, click tombol "Authorize" di atas halaman
7. Paste token dengan format: `Bearer <token>`
8. Sekarang Anda bisa test protected endpoints

---

## 🧪 Menjalankan Testing

Aplikasi punya 31 test cases otomatis untuk memastikan semuanya jalan dengan baik.

### Test Semua (Recommended)

```bash
php artisan test --env=testing
```

**Output yang diharapkan:**

```
PASSED
31 tests, 92 assertions
```

### Test File Spesifik (Opsional)

```bash
# Test autentikasi
php artisan test tests/Feature/AuthTest.php --env=testing

# Test project management
php artisan test tests/Feature/ProjectTest.php --env=testing

# Test task management
php artisan test tests/Feature/TaskTest.php --env=testing

# Test dashboard
php artisan test tests/Feature/DashboardTest.php --env=testing
```

✅ Jika semua test **PASSED**, berarti aplikasi berjalan dengan baik!

<<<<<<< HEAD
---

## 📍 Endpoint Utama API

### 🔐 Authentication

```
POST   /api/auth/login   → Login, dapatkan token
POST   /api/auth/logout  → Logout, hapus token
GET    /api/auth/me      → Lihat profil user
```

### 📁 Projects

```
GET    /api/projects              → Lihat semua project
POST   /api/projects              → Buat project baru
GET    /api/projects/{id}         → Lihat detail project
PUT    /api/projects/{id}         → Edit project
PATCH  /api/projects/{id}/toggle-status → Aktifkan/archive project
```

### ✅ Tasks

```
GET    /api/projects/{id}/tasks   → Lihat task di project
POST   /api/projects/{id}/tasks   → Buat task baru
GET    /api/tasks                 → Lihat semua task
GET    /api/tasks/{id}            → Lihat detail task
PUT    /api/tasks/{id}            → Edit task
DELETE /api/tasks/{id}            → Hapus task
```

### 📊 Dashboard & Categories

```
GET    /api/dashboard   → Statistik ringkasan
GET    /api/categories  → Lihat kategori task
```

### 🔍 Filter & Search (Query Parameters)

**Projects:**

- `?search=website` → Cari project nama "website"
- `?status=active` → Lihat hanya project aktif
- `?per_page=10` → Tampilkan 10 per halaman

**Tasks:**

- `?search=login` → Cari task judul "login"
- `?category_id=1` → Filter kategori tertentu
- `?per_page=20` → Tampilkan 20 per halaman

---

## 🔓 Akun Default

Ketika seeding, otomatis dibuat satu admin account:

| Field    | Value                 |
| -------- | --------------------- |
| Email    | admin@tasktracker.com |
| Password | password              |

Gunakan ini untuk login pertama kali di Swagger atau frontend.

---

## 💾 Format Response API

Semua response API menggunakan format JSON yang sama:

### Success Response

```json
{
  "success": true,
  "message": "Deskripsi pesan",
  "data": {
    "id": 1,
    "name": "Project Name",
    ...
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Penjelasan error",
  "errors": {
    "email": ["Email sudah terdaftar"],
    "password": ["Password minimal 6 karakter"]
  }
}
```

---

## 📁 Struktur File Penting

```
backend/
├── app/Http/Controllers/     # Logic endpoint API
├── app/Models/               # Database models (User, Project, Task, Category)
├── database/migrations/      # SQL table definitions
├── database/seeders/         # Initial data setup
├── routes/api.php            # Semua endpoint API
└── tests/                    # Test cases
```

---

## ❓ FAQ

**Q: Bagaimana kalau lupa password?**  
A: Ubah password di database, atau buat user baru dengan command:

```bash
php artisan tinker
>>> $user = User::create(['name' => 'Test', 'email' => 'test@example.com', 'password' => bcrypt('password'), 'is_admin' => false])
>>> exit
```

**Q: Bagaimana cara generate ulang data (reset)?**  
A: Hapus semua data dan buat ulang:

```bash
php artisan migrate:refresh --seed --env=testing
```

**Q: API error 500, apa yang salah?**  
A: Lihat error di terminal atau run tests:

```bash
php artisan test --env=testing
```

**Q: Bagaimana update dokumentasi Swagger?**  
A: Dokumentasi sudah berjalan di `/api/documentation`. Jika ada perubahan endpoint, file `public/api-docs.json` akan otomatis terupdate.
=======
>>>>>>> 6920f5842d28e0ef9e212f15bfea019c5c262bdd
