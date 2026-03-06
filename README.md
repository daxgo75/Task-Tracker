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

---

# Task Tracker — Frontend

Antarmuka pengguna berbasis **Vue 3 + TypeScript** dengan **Vite** sebagai build tool.

---

## 📋 Requirement

Sebelum mulai, pastikan sudah install:

- **Node.js 18+**
- **npm 9+**

---

## 🚀 Instalasi (Setup Awal)

### Step 1: Install Dependencies

```bash
cd Task-Tracker/frontend
npm install
```

### Step 2: Setup File Environment

```bash
cp .env.example .env
```

Edit file `.env` sesuai URL backend yang berjalan:

```env
VITE_API_BASE_URL=http://localhost:8000/api
```

---

## ▶️ Menjalankan Aplikasi

```bash
npm run dev
```

Tunggu sampai muncul:

```
VITE ready in ... ms
➜  Local:   http://localhost:5173/
```

Buka browser dan akses: **http://localhost:5173**

**Login default:**

```
Email    : admin@tasktracker.com
Password : password
```

---

## 🏗️ Build untuk Production

```bash
npm run build
```

Hasil build tersimpan di folder `dist/`. Untuk preview hasil build:

```bash
npm run preview
```

---

## 🧪 Menjalankan Testing

Aplikasi punya **56 unit test** menggunakan Vitest dan Vue Test Utils.

### Test Semua

```bash
npm run test
```

**Output yang diharapkan:**

```
✓ src/tests/LoginView.test.ts
✓ src/tests/DashboardView.test.ts
✓ src/tests/ProjectsView.test.ts
✓ src/tests/TasksView.test.ts
✓ src/tests/stores/projects.test.ts
✓ src/tests/stores/globalTasks.test.ts
✓ src/tests/plugins/date.test.ts

Test Files  7 passed (7)
Tests       56 passed (56)
```

### Type Check

```bash
npm run type-check
```

✅ Jika semua test **PASSED** dan type-check bersih, berarti frontend berjalan dengan baik!
