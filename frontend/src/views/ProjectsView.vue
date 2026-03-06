<script setup lang="ts">
import { ref, reactive, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectsStore } from '@/stores/projects'
import ProjectStatusBadge from '@/components/ui/ProjectStatusBadge.vue'
import ProjectFormModal from '@/components/ui/ProjectFormModal.vue'
import type { Project, ProjectFormPayload, ProjectStatus } from '@/types/project'
import { formatDate } from '@/plugins/date'

const router = useRouter()
const store = useProjectsStore()

// ── Filters ───────────────────────────────────────────────────────────────
const filters = reactive({
  search: '',
  status: '' as ProjectStatus | '',
  page: 1,
  per_page: 15,
})

let searchTimer: ReturnType<typeof setTimeout> | null = null

watch(
  () => filters.search,
  () => {
    if (searchTimer) clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
      filters.page = 1
      loadProjects()
    }, 350)
  },
)

watch(
  () => filters.status,
  () => {
    filters.page = 1
    loadProjects()
  },
)

function loadProjects(): void {
  store.fetchProjects({ ...filters })
}

onMounted(loadProjects)

// ── Pagination ────────────────────────────────────────────────────────────
function goToPage(page: number): void {
  filters.page = page
  loadProjects()
}

// ── Modal ─────────────────────────────────────────────────────────────────
const isModalOpen = ref(false)
const editingProject = ref<Project | null>(null)

function openCreate(): void {
  editingProject.value = null
  store.clearErrors()
  isModalOpen.value = true
}

function openEdit(project: Project): void {
  editingProject.value = project
  store.clearErrors()
  isModalOpen.value = true
}

function closeModal(): void {
  isModalOpen.value = false
  editingProject.value = null
  store.clearErrors()
}

async function handleSubmit(payload: ProjectFormPayload): Promise<void> {
  const result = editingProject.value
    ? await store.updateProject(editingProject.value.id, payload)
    : await store.createProject(payload)

  if (result.success) {
    closeModal()
    if (!editingProject.value) loadProjects()
  }
}
</script>

<template>
  <div class="projects">
    <!-- Header -->
    <div class="projects__header">
      <div>
        <h1 class="projects__title">Project</h1>
        <p class="projects__subtitle">Kelola semua project</p>
      </div>
      <button class="btn btn--primary" @click="openCreate">+ Tambah Project</button>
    </div>

    <!-- Global error -->
    <div v-if="store.error" class="alert-error" role="alert">{{ store.error }}</div>

    <!-- Filters -->
    <div class="projects__filters">
      <div class="search-wrapper">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
        <input
          v-model="filters.search"
          type="text"
          class="search-input"
          placeholder="Cari project..."
        />
      </div>
      <select v-model="filters.status" class="filter-select status-filter">
        <option value="">Semua Status</option>
        <option value="active">Active</option>
        <option value="archived">Archived</option>
      </select>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
      <!-- Loading skeleton rows -->
      <template v-if="store.isLoading">
        <div v-for="i in 5" :key="i" class="skeleton-row" />
      </template>

      <table v-else class="table">
        <thead>
          <tr>
            <th class="col-name">NAMA</th>
            <th class="col-desc">DESKRIPSI</th>
            <th class="col-status">STATUS</th>
            <th class="col-task">TASK</th>
            <th class="col-date">DIBUAT</th>
            <th class="col-action">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="store.projects.length === 0">
            <td colspan="6" class="table__empty">Tidak ada project ditemukan.</td>
          </tr>
          <tr v-for="project in store.projects" :key="project.id">
            <td class="col-name">
              <span class="project-name">{{ project.name }}</span>
            </td>
            <td class="col-desc">
              <span class="project-desc">{{ project.description || '—' }}</span>
            </td>
            <td class="col-status">
              <ProjectStatusBadge :status="project.status" />
            </td>
            <td class="col-task">{{ project.task_counts?.total ?? 0 }}</td>
            <td class="col-date">{{ formatDate(project.created_at) }}</td>
            <td class="col-action">
              <div class="action-btns">
                <button
                  class="action-btn action-btn--detail"
                  @click="router.push({ name: 'project-detail', params: { id: project.id } })"
                >
                  Detail
                </button>
                <button class="action-btn action-btn--edit" @click="openEdit(project)">Edit</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="store.meta && store.meta.last_page > 1" class="pagination">
      <button
        class="page-btn"
        :disabled="store.meta.current_page === 1"
        @click="goToPage(store.meta.current_page - 1)"
      >
        ‹
      </button>
      <button
        v-for="page in store.meta.last_page"
        :key="page"
        class="page-btn"
        :class="{ 'page-btn--active': page === store.meta.current_page }"
        @click="goToPage(page)"
      >
        {{ page }}
      </button>
      <button
        class="page-btn"
        :disabled="store.meta.current_page === store.meta.last_page"
        @click="goToPage(store.meta.current_page + 1)"
      >
        ›
      </button>
    </div>

    <!-- Modal -->
    <ProjectFormModal
      :is-open="isModalOpen"
      :project="editingProject"
      :is-saving="store.isSaving"
      :field-errors="store.fieldErrors"
      :global-error="store.error"
      @submit="handleSubmit"
      @close="closeModal"
    />
  </div>
</template>

<style scoped>
.projects {
  padding: 2rem 2.5rem;
}

.projects__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.projects__title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.25rem;
}

.projects__subtitle {
  font-size: 0.9rem;
  color: #64748b;
  margin: 0;
}

/* Filters */
.projects__filters {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  align-items: center;
  margin-bottom: 1.25rem;
}

.search-wrapper {
  position: relative;
  flex: 1;
  min-width: 220px;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 0.9375rem;
  height: 0.9375rem;
  color: #94a3b8;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 0.5rem 0.75rem 0.5rem 2.25rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: #1e293b;
  background: #ffffff;
  outline: none;
  transition: border-color 0.15s;
  box-sizing: border-box;
}

.search-input:focus {
  border-color: #3b82f6;
}

.filter-select {
  padding: 0.5rem 2rem 0.5rem 0.75rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
  background: #ffffff;
  cursor: pointer;
  outline: none;
  appearance: auto;
  transition: border-color 0.15s;
}

.filter-select:focus {
  border-color: #3b82f6;
}

.status-filter {
  flex-shrink: 0;
}

/* Table */
.table-wrapper {
  background: #ffffff;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table thead tr {
  background: #f8fafc;
  border-bottom: 1px solid #f1f5f9;
}

.table th {
  padding: 0.75rem 1.25rem;
  text-align: left;
  font-size: 0.6875rem;
  font-weight: 700;
  color: #94a3b8;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.table td {
  padding: 0.875rem 1.25rem;
  border-bottom: 1px solid #f8fafc;
  font-size: 0.875rem;
  color: #334155;
  vertical-align: middle;
}

.table tbody tr:last-child td {
  border-bottom: none;
}

.table tbody tr:hover {
  background-color: #fafbfc;
}

.table__empty {
  text-align: center;
  color: #94a3b8;
  padding: 3rem 1rem;
  font-size: 0.875rem;
}

.col-name { width: 20%; }
.col-desc { width: 30%; }
.col-status { width: 10%; }
.col-task { width: 8%; text-align: center; }
.col-date { width: 12%; }
.col-action { width: 14%; }

.project-name {
  font-weight: 600;
  color: #1e293b;
}

.project-desc {
  color: #64748b;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Action buttons */
.action-btns {
  display: flex;
  gap: 0.375rem;
}

.action-btn {
  padding: 0.25rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1.5px solid;
  cursor: pointer;
  transition: background-color 0.15s;
  white-space: nowrap;
}

.action-btn--detail {
  background: #f1f5f9;
  border-color: #e2e8f0;
  color: #374151;
}

.action-btn--detail:hover {
  background: #e2e8f0;
}

.action-btn--edit {
  background: #eff6ff;
  border-color: #bfdbfe;
  color: #2563eb;
}

.action-btn--edit:hover {
  background: #dbeafe;
}

/* Pagination */
.pagination {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  margin-top: 1rem;
  justify-content: flex-end;
}

.page-btn {
  min-width: 2rem;
  height: 2rem;
  padding: 0 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.375rem;
  background: #ffffff;
  color: #374151;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background-color 0.15s, border-color 0.15s;
}

.page-btn:hover:not(:disabled) {
  background-color: #f1f5f9;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-btn--active {
  background-color: #3b82f6;
  border-color: #3b82f6;
  color: #ffffff;
  font-weight: 600;
}

/* Alert */
.alert-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

/* Buttons */
.btn {
  padding: 0.4rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.8125rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: background-color 0.15s;
  white-space: nowrap;
}

.btn--primary {
  background-color: #3b82f6;
  color: #ffffff;
  padding: 0.5rem 1.125rem;
  font-size: 0.875rem;
}

.btn--primary:hover {
  background-color: #2563eb;
}

/* Skeleton */
.skeleton-row {
  height: 3.5rem;
  margin: 0.125rem 0;
  border-radius: 0.25rem;
  background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
