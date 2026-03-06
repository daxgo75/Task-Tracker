# AI Usage Documentation

Dokumen ini mencatat penggunaan AI selama proses pengembangan proyek **Task-Tracker**.

---

## Tools & AI yang Digunakan

| AI / Tool             | Model             | Keterangan                                                     |
| --------------------- | ----------------- | -------------------------------------------------------------- |
| GitHub Copilot (Chat) | Claude Sonnet 4.6 | Digunakan untuk implementasi fitur, debugging, dan refactoring |

---

## MCP & Built-in Tools yang Digunakan

| Tool                           | Fungsi                                                                |
| ------------------------------ | --------------------------------------------------------------------- |
| `create_file`                  | Membuat file baru (komponen Vue, Dockerfile, config, dll.)            |
| `replace_string_in_file`       | Mengedit potongan spesifik dalam file yang sudah ada                  |
| `multi_replace_string_in_file` | Mengedit beberapa bagian file sekaligus dalam satu operasi            |
| `read_file`                    | Membaca isi file untuk memahami konteks sebelum melakukan perubahan   |
| `run_in_terminal`              | Menjalankan perintah terminal (artisan, npm, composer, vue-tsc, dll.) |
| `get_terminal_output`          | Membaca output dari proses terminal yang berjalan di background       |
| `grep_search`                  | Mencari teks/pola spesifik dalam file-file workspace                  |
| `file_search`                  | Mencari file berdasarkan pola nama/path                               |
| `semantic_search`              | Mencari kode secara semantik berdasarkan deskripsi natural language   |
| `list_dir`                     | Melihat struktur direktori proyek                                     |
| `get_errors`                   | Memeriksa error TypeScript/lint pada file                             |
| `manage_todo_list`             | Membuat dan melacak daftar tugas selama sesi pengerjaan               |
| `memory`                       | Menyimpan catatan teknis lintas sesi (user memory & repo memory)      |
| `runTests`                     | Menjalankan unit test (Vitest) langsung dari agen                     |
| `search_subagent`              | Mendelegasikan eksplorasi kodebase ke subagen pencarian               |

---

## Tools Spesifik untuk Backend Implementation

| Tool                     | Kasus Penggunaan Backend                                                      |
| ------------------------ | ----------------------------------------------------------------------------- |
| `run_in_terminal`        | `php artisan migrate`, `php artisan seed`, `php artisan test`, `composer` cmd |
| `read_file`              | Membaca controller, model, migration untuk verify logic sebelum edit          |
| `replace_string_in_file` | Edit controller method, tambah relation di model, fix Swagger annotation      |
| `get_errors`             | Verify migration, check PHP syntax error, validate test results               |
| `runTests`               | Jalankan PHPUnit test suite untuk verify implementasi backend                 |
| `create_file`            | Create controller, model, request class, resource, migration, test file       |

---

## Daftar Prompt yang Digunakan

### Bagian 1: Backend Implementation

#### 1.1 Setup & Database Design

```
Buatkan proyek Laravel 12 dengan PostgreSQL. Database design:
- users: id, name, email unique, password, is_admin boolean
- categories: id, name unique (untuk kanban: Todo, InProgress, Testing, Done, Pending)
- projects: id, created_by FK, name, description, status enum(active/archived), timestamps
- tasks: id, project_id FK, category_id FK, created_by FK, title, description, due_date, deleted_at, deleted_by, timestamps
Gunakan migrations dan seeders.
```

```
Buat seeder CategorySeeder dengan 5 kategori: Todo, InProgress, Testing, Done, Pending.
Buat AdminSeeder untuk user admin@tasktracker.com dengan password "password" dan is_admin=true.
```

#### 1.2 Authentication & API Setup

```
Implementasikan Sanctum Personal Access Token (PAT) authentication di Laravel 12.
Config sanctum guard di config/auth.php:
- 'sanctum' => ['driver' => 'sanctum', 'provider' => 'users']
Endpoint login POST /api/auth/login harus return token + expires_at + user data.
Endpoint logout POST /api/auth/logout harus revoke token dengan delete dari database.
```

```
Implementasikan token expiration dengan durasi configurable.
Default 1440 menit (24 jam). Simpan expires_at di database saat login.
Response login minimal include: token, expires_at (datetime), user (id, name, email, is_admin).
```

```
Setup CORS dan error handler untuk respons JSON API.
Error response format: {"success": false, "message": "...", "errors": {...}}.
Validasi error harus per-field, bukan generic.
```

#### 1.3 Controllers & Business Logic

```
Buatkan AuthController dengan method:
- login(LoginRequest): return token + expires_at + user
- logout(Request): revoke current token
- me(Request): return authenticated user profile
Gunakan $request->user()->currentAccessToken()->delete() untuk revoke token.
```

```
Buatkan ProjectController (RESTful, except destroy):
- index: list own projects (search by name, filter by status, pagination)
- store: create project (auto-set created_by)
- show: show project + activeTasks (not deleted) with category + creator relations
- update: update project
- toggleStatus: PATCH untuk toggle active/archived
Query harus scoped ke created_by user.
```

```
Buatkan TaskController:
- index(projectId): tasks in specific project (search, filter by category_id, ordered by due_date)
- globalIndex: all tasks across user's projects (search, filter by category_id + project_id)
- store(projectId): create task (auto-set created_by)
- show(id): show task
- update(id): update category_id, title, description, due_date
- destroy(id): SOFT delete - set deleted_at + deleted_by manually (tidak use SoftDeletes trait)
```

```
Buatkan DashboardController::index:
- total_active_projects: count projects where status='active'
- total_incomplete_tasks: count tasks where deleted_at=null AND category != 'Done'
- tasks_by_category: group tasks by category name (with JOIN)
- upcoming_tasks: tasks with category != 'Done' AND due_date >= today, limit 10
Data untuk dashboard saja, bukan sebagai form endpoint.
```

```
Buatkan CategoryController::index: return all categories ordered by name (untuk dropdown).
```

#### 1.4 Resources & Responses

```
Buatkan UserResource yang return: id, name, email, is_admin.
Buatkan CategoryResource: id, name (NO color field).
Buatkan ProjectResource: id, name, description, status, owner (UserResource),
  task_counts (total), tasks (TaskResource collection), timestamps.
Buatkan TaskResource: id, project_id, title, description, due_date, category (CategoryResource),
  created_by (UserResource), project (ProjectBriefResource), is_deleted, deleted_at, timestamps.
Buatkan ProjectBriefResource untuk nested project di TaskResource: id, name, status.
```

#### 1.5 Form Requests & Validation

```
Buatkan LoginRequest: validate email required+email, password required+min:6.
Buatkan StoreProjectRequest: name required, description nullable, status sometimes+in:active,archived.
Buatkan UpdateProjectRequest: name sometimes, description nullable, status sometimes.
Buatkan StoreTaskRequest: category_id required+exists, title required, description nullable,
  due_date nullable+date+after_or_equal:today.
Buatkan UpdateTaskRequest: category_id sometimes, title sometimes, description nullable,
  due_date nullable+date.
Validate custom messages dalam bahasa Indonesia.
```

#### 1.6 Models & Relations

```
User model: use HasFactory, Notifiable, HasApiTokens. Fillable: name, email, password, is_admin.
  Relations: projects() hasMany Project dengan FK created_by, tasks() hasMany Task dengan FK created_by.
```

```
Project model: use HasFactory. Fillable: created_by, name, description, status.
  Relations: creator belongsTo User, tasks hasMany Task, activeTasks (filtered:whereNull('deleted_at')).
  Scopes: scopeActive, scopeArchived, scopeSearch (nama, DB-agnostic ilike/like).
```

```
Task model: use HasFactory. Fillable: project_id, category_id, created_by, title, description,
  due_date, deleted_at, deleted_by.
  Relations: project, category, creator (FK created_by), deletedBy (FK deleted_by).
  Scopes: scopeActive (whereNull), scopeOnlyTrashed (whereNotNull), scopeSearch,
    scopeUpcomingDue (whereHas category != 'Done').
  Helper: isDeleted() return !is_null(deleted_at).
```

```
Category model: use HasFactory. Fillable: name. No timestamps needed (atau keep auto timestamps).
```

#### 1.7 Testing

```
Buatkan AuthTest: test login valid, login invalid password, login invalid email,
  login requires fields, logout, protected routes, get profile.
Buatkan ProjectTest: test create, list, search, filter, update, toggle status, cannot access other user.
Buatkan TaskTest: test create, list, search, filter, update, soft delete, global list.
Buatkan DashboardTest: test structure, active projects count, incomplete tasks count, upcoming tasks.
Minimum 31 test cases total dengan 90+ assertions.
```

#### 1.8 Swagger & Documentation

```
Dokumentasikan API dengan L5-Swagger (OpenAPI 3.0).
Tambahkan @OA anotasi pada setiap controller:
- tag: Authentication, Projects, Tasks, Dashboard, Categories
- security: sanctum untuk protected routes
- request/response body dengan PropertySchema yang jelas
- error responses: 401 (unauthenticated), 404 (not found), 422 (validation failed)
GenerateDocs dengan php artisan l5-swagger:generate.
```

#### 1.9 Token Expiration Implementation

```
Setup token expiration dengan config sanctum.php:
- 'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 1440)
Di AuthController::login, setelah token dibuat:
  $expirationMinutes = (int) config('sanctum.expiration');
  if ($expirationMinutes > 0) {
    $token->accessToken->update(['expires_at' => now()->addMinutes($expirationMinutes)]);
  }
Return response include expires_at field untuk client.
```

### 2. Frontend Implementation

#### 2.1 Setup Awal & Struktur Proyek

```
Buatkan proyek Task-Tracker dengan Laravel 12 + PostgreSQL untuk backend dan Vue 3 + TypeScript untuk frontend.
Gunakan arsitektur monorepo. Backend menggunakan Sanctum Personal Access Token.
```

#### 2.2 Autentikasi

```
Implementasikan login dengan Laravel Sanctum. Frontend menggunakan axios dengan Bearer token.
Tambahkan navigation guard di Vue Router: requiresAuth dan requiresGuest.
```

```
Ketika login gagal karena email tidak ditemukan, tampilkan notifikasi per-field yang spesifik
(bukan pesan generik). Bedakan antara "akun tidak ditemukan" dan "password salah".
```

```
Ketika login gagal/bukan akun yang sah jangan refresh di halaman login, berikan notifikasinya saja.
```

#### 2.3 Fitur Frontend

```
Buatkan halaman Dashboard yang menampilkan statistik: total projects, total tasks, tasks by status.
Gunakan Vue 3 Composition API dengan <script setup lang="ts">.
```

```
Buatkan halaman Projects dengan fitur: list project, create, edit, archive/unarchive, delete.
Gunakan Pinia untuk state management.
```

```
Buatkan halaman Kanban board per project dengan drag-and-drop antar kolom status
(todo, in_progress, done). Setiap task bisa di-edit dan di-delete.
```

```
Buatkan halaman Global Tasks (tabel) untuk melihat semua task lintas project,
dengan filter berdasarkan status dan project.
```

#### 2.4 Validasi & Notifikasi Error

```
Tambahkan validasi format email di LoginView: gunakan regex, validasi saat blur,
dan bersihkan error saat user mengetik ulang.
```

```
Tambahkan prop globalError pada modal-modal (ProjectFormModal, GlobalTaskFormModal, TaskFormModal)
untuk menampilkan pesan error non-field (server error 500, dll.) di dalam modal.
```

```
Semua notifikasi error harus deskriptif per-field, bukan pesan generik "Error 500".
```

#### 2.5 Testing Frontend

```
Buatkan unit test untuk frontend menggunakan Vitest dan @vue/test-utils.
Test harus mencakup: LoginView, DashboardView, ProjectsView, TasksView,
store/projects, store/globalTasks, plugins/date.
Minimum 1 test per file.
```

```
Fix error TypeScript di stores/projects.test.ts:
Type 'unknown' is not assignable to type 'void'. Ubah tipe resolve ke (v: any) => void.
```

### 3. Infrastructure & DevOps

#### 3.1 Docker Setup

```
Buatkan Docker Compose untuk menjalankan seluruh aplikasi:
- db: postgres:16
- backend: PHP 8.2 + Laravel, port 8000
- frontend: Node 22 + Vite, port 5173
Backend harus menunggu database siap sebelum menjalankan migrate dan seed.
```

```
Buatkan Dockerfile untuk backend Laravel dengan PHP 8.2-cli + pdo_pgsql.
Buatkan entrypoint.sh yang menjalankan: pg_isready → migrate → seed → artisan serve.
```

```
Buatkan Dockerfile untuk frontend Vue 3 menggunakan node:22-alpine.
Update vite.config.ts agar server berjalan di host 0.0.0.0 port 5173.
```

#### 3.2 Configuration & Environment

```
Setup .env untuk backend dengan PostgreSQL:
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=task_tracker
DB_USERNAME=postgres
Tambahkan SANCTUM_TOKEN_EXPIRATION=1440 untuk token expiration.
```

### 4. API Documentation & Security

#### 4.1 Swagger & OpenAPI

```
Dokumentasikan API dengan L5-Swagger (OpenAPI 3.0).
Dokumentasikan semua endpoint: auth, projects, tasks, categories.
```

#### 4.2 Security

```
Jalankan npm audit dan pastikan tidak ada vulnerability.
```

---

## Implementasi Detail Backend

### Database Schema

- **users**: Personal Access Token (via Sanctum migration)
- **personal_access_tokens**: Token storage dengan expires_at field
- **categories**: 5 initial data (Todo, InProgress, Testing, Done, Pending)
- **projects**: Scoped per user (created_by), status toggling tidak ada delete
- **tasks**: Custom soft delete dengan deleted_at + deleted_by tracking

### API Endpoints Summary

| Method         | Endpoint                         | Purpose                     | Auth |
| -------------- | -------------------------------- | --------------------------- | ---- |
| POST           | /api/auth/login                  | Login, get PAT + expires_at | ✗    |
| POST           | /api/auth/logout                 | Revoke token                | ✓    |
| GET            | /api/auth/me                     | Get profile                 | ✓    |
| GET            | /api/dashboard                   | Dashboard stats             | ✓    |
| GET            | /api/categories                  | For dropdown                | ✓    |
| GET/POST       | /api/projects                    | List/create                 | ✓    |
| GET/PUT        | /api/projects/{id}               | Show/update                 | ✓    |
| PATCH          | /api/projects/{id}/toggle-status | Toggle active/archive       | ✓    |
| GET/POST       | /api/projects/{id}/tasks         | Scoped tasks                | ✓    |
| GET            | /api/tasks                       | Global tasks                | ✓    |
| GET/PUT/DELETE | /api/tasks/{id}                  | CRUD task                   | ✓    |

### Backend Stack

| Layer           | Technology                                     |
| --------------- | ---------------------------------------------- |
| Framework       | Laravel 12 (PHP 8.2)                           |
| Database        | PostgreSQL 14+                                 |
| Auth            | Laravel Sanctum 4.3                            |
| API Docs        | L5-Swagger 10.1                                |
| Testing         | PHPUnit 11                                     |
| Response Format | JSON API Spec (success, message, data, errors) |

### Test Coverage

- **AuthTest**: 7 tests (login, logout, profile, auth checks)
- **ProjectTest**: 8 tests (CRUD, search, filter, status toggle)
- **TaskTest**: 10 tests (CRUD, soft delete, search, filter)
- **DashboardTest**: 4 tests (stats, structure, counts)
- **ExampleTest**: 1 test (health check)
- **Total**: 31 tests passing, 92 assertions

---

## Catatan Teknis

- Semua perubahan diimplementasikan langsung (bukan hanya saran) menggunakan tools di atas.
- Setiap perubahan pada file yang sudah ada selalu didahului dengan membaca konten file terlebih dahulu.
- TypeScript strict mode diaktifkan; semua error TS diselesaikan sebelum commit.
- Test dijalankan setelah setiap perubahan signifikan untuk memastikan tidak ada regresi.

---

## Key Learnings & Solutions (Backend)

### 1. Token Expiration Implementation

**Challenge**: Sanctum PAT tidak otomatis expire. Field `expires_at` ada di database tapi tidak terisi saat generate token.

**Solution**:

- Manually set `expires_at` dalam controller dengan `$token->accessToken->update(['expires_at' => now()->addMinutes(...)])`
- Config `SANCTUM_TOKEN_EXPIRATION` di env dan phpunit.xml
- Cast ke int: `(int) config('sanctum.expiration')` untuk avoid Carbon error

### 2. Soft Delete dengan Custom Fields

**Challenge**: Default Laravel `SoftDeletes` trait tidak track siapa yang delete. Requirement: track `deleted_by` user.

**Solution**:

- Tidak gunakan `SoftDeletes` trait
- Manual implement soft delete: set `deleted_at` + `deleted_by` di controller `destroy()` method
- Tambah scope `scopeActive()` dan `scopeOnlyTrashed()` untuk filter query
- Helper method `isDeleted()` return `!is_null($this->deleted_at)`

### 3. User-Scoped Queries

**Challenge**: Setiap user hanya bisa akses data mereka sendiri (projects, tasks).

**Solution**:

- Filter semua query: `Project::where('created_by', $request->user()->id)`
- Protect di controller, bukan di middleware
- Test: verify user A tidak bisa akses user B's project

### 4. Error Response Consistency

**Challenge**: API harus return deskriptif error per-field, bukan generic 500.

**Solution**:

- Gunakan ApiResponder trait dengan method `error()` yang support `$errors` parameter
- Bootstrap handle ValidationException → return 422 dengan field errors
- FormRequest dengan custom `messages()` untuk bahasa Indonesia
- Test: verify error format per test case

### 5. Database Consistency Checks

**Challenge**: Backend sudah diupdate, tapi API masih return stale field (e.g., `status` di Task, `color` di Category).

**Solution**:

- Audit semua migration: pastikan field di DB match ERD
- Audit semua controller: return data match requirement
- Audit Resource: hanya return active field
- Audit Swagger: jangan dokumentasikan field yang tidak ada
- Unit test: assert response structure match expected format

### 6. Swagger/OpenAPI Documentation Setup

**Challenge**: L5-Swagger `php artisan l5-swagger:generate` command failed with error "Required @OA\PathItem() not found" despite having @OA\Get, @OA\Post annotations in all controllers.

**Root Cause**:

- Swagger PHP 6.0.6 validation error when OpenAPI spec doesn't contain endpoint paths
- Possible file scanning issues or annotation parsing problems

**Solution**:

1. **Manual Swagger JSON Creation**: Created `/public/api-docs.json` with complete OpenAPI 3.0.0 specification containing all endpoints, schemas, and security configuration
2. **Fixed Schema Definitions**: Updated CategoryResource and TaskResource schemas in SwaggerController to remove stale fields (color from Category, status from Task)
3. **Custom Documentation Controller**: Created `DocumentationController` to serve Swagger UI with proper configuration variables
4. **Route Registration**: Added custom routes in `web.php`:
   - `GET /api/documentation` → Swagger UI interface
   - `GET /api/docs.json` → OpenAPI specification JSON file
5. **View Configuration**: Configured L5-Swagger index.blade.php view to load documentation from the manual JSON file

**Access**:

- **Swagger UI**: `http://localhost:8000/api/documentation`
- **Raw OpenAPI**: `http://localhost:8000/api/docs.json`

**Files Modified/Created**:

- `public/api-docs.json` (new) - Complete OpenAPI 3.0.0 specification
- `app/Http/Controllers/DocumentationController.php` (new) - Controller to serve Swagger UI
- `app/Http/Controllers/SwaggerController.php` (updated) - Fixed schema definitions
- `routes/web.php` (updated) - Added documentation routes
- `resources/views/vendor/l5-swagger/index.blade.php` (used as-is)

---

## Statistik Penggunaan

| Kategori                          | Jumlah              |
| --------------------------------- | ------------------- |
| **Backend**                       |                     |
| - Controllers                     | 5 file              |
| - Models                          | 4 file              |
| - Form Requests                   | 6 file              |
| - Resources                       | 5 file              |
| - Migrations                      | 7 file              |
| - Seeders                         | 2 file              |
| - Tests (PHPUnit)                 | 31 test (92 assert) |
| **Frontend**                      |                     |
| - Views                           | ~8 file             |
| - Components                      | ~15 file            |
| - Store (Pinia)                   | ~4 file             |
| - Tests (Vitest)                  | 56 test (7 file)    |
| **Infrastructure**                |                     |
| - Dockerfile                      | 2 file              |
| - Docker Compose                  | 1 file              |
| - Configuration Files             | ~5 file             |
| **Documentation**                 |                     |
| - API Swagger Docs                | 1 file              |
| - README                          | 2 file              |
| - AI Usage (this file)            | 1 file              |
| **Overall**                       |                     |
| Total files created/modified      | ~70+ file           |
| Frontend tests                    | 56 test             |
| Backend tests                     | 31 test             |
| Total test cases                  | 87 test             |
| Total assertions                  | 105+ assertions     |
| Vulnerabilities found (npm audit) | 0                   |
