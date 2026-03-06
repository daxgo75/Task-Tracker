<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useGlobalTasksStore } from '@/stores/globalTasks'
import { useCategoriesStore } from '@/stores/categories'
import { projectService } from '@/services/projectService'
import type { Task, GlobalTaskFormData, GlobalTaskFilters } from '@/types/task'
import type { Project } from '@/types/project'
import { getDueDateStatus, formatDueLabel } from '@/plugins/date'
import CategoryBadge from '@/components/ui/CategoryBadge.vue'
import GlobalTaskFormModal from '@/components/ui/GlobalTaskFormModal.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'

const store = useGlobalTasksStore()
const categoriesStore = useCategoriesStore()

// ── Dropdown data ────────────────────────────────────────────────
const allProjects = ref<Project[]>([])

// ── Filters ──────────────────────────────────────────────────────
const search = ref('')
const selectedCategoryId = ref(0)
const selectedProjectId = ref(0)
let debounceTimer: ReturnType<typeof setTimeout>

function buildFilters(): GlobalTaskFilters {
  return {
    search: search.value || undefined,
    category_id: selectedCategoryId.value || undefined,
    project_id: selectedProjectId.value || undefined,
    per_page: 20,
    page: store.meta.current_page,
  }
}

function onSearchInput(): void {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    store.meta.current_page = 1
    store.fetchTasks(buildFilters())
  }, 350)
}

function onFilterChange(): void {
  store.meta.current_page = 1
  store.fetchTasks(buildFilters())
}

function goToPage(page: number): void {
  store.meta.current_page = page
  store.fetchTasks(buildFilters())
}

// ── Task modal ───────────────────────────────────────────────────
const isModalOpen = ref(false)
const editingTask = ref<Task | null>(null)

function openCreate(): void {
  editingTask.value = null
  store.clearErrors()
  isModalOpen.value = true
}

function openEdit(task: Task): void {
  editingTask.value = task
  store.clearErrors()
  isModalOpen.value = true
}

async function handleSubmit(data: GlobalTaskFormData): Promise<void> {
  let result: { success: boolean }
  if (editingTask.value) {
    const { project_id: _, ...payload } = data
    result = await store.updateTask(editingTask.value.id, payload)
  } else {
    const { project_id, ...payload } = data
    result = await store.createTask(project_id, payload)
  }
  if (result.success) {
    isModalOpen.value = false
  }
}

// ── Confirm delete ───────────────────────────────────────────────
const isConfirmOpen = ref(false)
const taskToDelete = ref<Task | null>(null)
const isDeleting = ref(false)

function openDelete(task: Task): void {
  taskToDelete.value = task
  isConfirmOpen.value = true
}

async function confirmDelete(): Promise<void> {
  if (!taskToDelete.value) return
  isDeleting.value = true
  const result = await store.deleteTask(taskToDelete.value.id)
  isDeleting.value = false
  if (result.success) {
    isConfirmOpen.value = false
    taskToDelete.value = null
  }
}

// ── Helpers ──────────────────────────────────────────────────────
function dueDateClass(task: Task): string {
  if (!task.due_date) return ''
  const s = getDueDateStatus(task.due_date)
  return s === 'overdue' ? 'due--overdue' : s === 'today' ? 'due--today' : ''
}

// ── Init ─────────────────────────────────────────────────────────
onMounted(async () => {
  await Promise.all([
    store.fetchTasks(buildFilters()),
    categoriesStore.fetchIfEmpty(),
    projectService.getProjects({ per_page: 100 }).then((res) => {
      allProjects.value = res.data.data
    }),
  ])
})
</script>

<template>
  <div class="tasks-page">
    <!-- Page header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Task</h1>
        <p class="page-subtitle">Semua task lintas project</p>
      </div>
      <button class="btn-add" @click="openCreate">+ Tambah Task</button>
    </div>

    <!-- Filters -->
    <div class="filters-row">
      <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <path d="M21 21l-4.35-4.35" />
        </svg>
        <input
          v-model="search"
          type="text"
          class="search-input"
          placeholder="Cari task..."
          @input="onSearchInput"
        />
      </div>

      <select v-model="selectedCategoryId" class="filter-select" @change="onFilterChange">
        <option :value="0">Semua Kategori</option>
        <option v-for="cat in categoriesStore.categories" :key="cat.id" :value="cat.id">
          {{ cat.name === 'InProgress' ? 'In Progress' : cat.name }}
        </option>
      </select>

      <select v-model="selectedProjectId" class="filter-select" @change="onFilterChange">
        <option :value="0">Semua Project</option>
        <option v-for="proj in allProjects" :key="proj.id" :value="proj.id">
          {{ proj.name }}
        </option>
      </select>
    </div>

    <!-- Error -->
    <div v-if="store.error" class="alert-error">{{ store.error }}</div>

    <!-- Table -->
    <div class="table-card">
      <table class="task-table">
        <thead>
          <tr>
            <th>JUDUL TASK</th>
            <th>PROJECT</th>
            <th>KATEGORI</th>
            <th>DUE DATE</th>
            <th>AKSI</th>
          </tr>
        </thead>
        <tbody>
          <!-- Loading skeleton -->
          <template v-if="store.isLoading">
            <tr v-for="i in 5" :key="i" class="skeleton-row">
              <td><div class="skel skel--title" /></td>
              <td><div class="skel skel--sm" /></td>
              <td><div class="skel skel--badge" /></td>
              <td><div class="skel skel--sm" /></td>
              <td><div class="skel skel--actions" /></td>
            </tr>
          </template>

          <!-- Empty -->
          <tr v-else-if="store.tasks.length === 0">
            <td colspan="5" class="empty-cell">Tidak ada task ditemukan.</td>
          </tr>

          <!-- Data rows -->
          <tr v-else v-for="task in store.tasks" :key="task.id">
            <td class="td-title">{{ task.title }}</td>
            <td class="td-project">{{ task.project?.name ?? '—' }}</td>
            <td>
              <CategoryBadge :name="task.category.name" />
            </td>
            <td :class="['td-due', dueDateClass(task)]">
              <span v-if="task.due_date" class="due-wrap">
                {{ formatDueLabel(task.due_date) }}
                <svg
                  v-if="getDueDateStatus(task.due_date) === 'overdue'"
                  class="warn-icon"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.5"
                >
                  <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                  <line x1="12" y1="9" x2="12" y2="13" />
                  <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
              </span>
              <span v-else>—</span>
            </td>
            <td class="td-actions">
              <button class="action-btn action-btn--edit" @click="openEdit(task)">Edit</button>
              <button class="action-btn action-btn--delete" @click="openDelete(task)">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="store.meta.last_page > 1" class="pagination">
      <button
        class="page-btn"
        :disabled="store.meta.current_page <= 1"
        @click="goToPage(store.meta.current_page - 1)"
      >
        ‹
      </button>
      <button
        v-for="p in store.meta.last_page"
        :key="p"
        class="page-btn"
        :class="{ 'page-btn--active': p === store.meta.current_page }"
        @click="goToPage(p)"
      >
        {{ p }}
      </button>
      <button
        class="page-btn"
        :disabled="store.meta.current_page >= store.meta.last_page"
        @click="goToPage(store.meta.current_page + 1)"
      >
        ›
      </button>
    </div>

    <!-- Modals -->
    <GlobalTaskFormModal
      :is-open="isModalOpen"
      :task="editingTask"
      :is-saving="store.isSaving"
      :field-errors="store.fieldErrors"
      :global-error="store.error"
      :categories="categoriesStore.categories"
      :projects="allProjects"
      @submit="handleSubmit"
      @close="isModalOpen = false"
    />

    <ConfirmModal
      variant="trash"
      title="Hapus Task?"
      :is-open="isConfirmOpen"
      :message="`Task <strong>&quot;${taskToDelete?.title ?? ''}&quot;</strong> akan dihapus. Data task masih tersimpan di sistem (soft delete) dan bisa dipulihkan jika diperlukan.`"
      note=""
      :is-loading="isDeleting"
      @confirm="confirmDelete"
      @close="isConfirmOpen = false; taskToDelete = null"
    />
  </div>
</template>

<style scoped>
.tasks-page {
  padding: 1.5rem 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  max-width: 1200px;
  margin: 0 auto;
}

/* Header */
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.125rem;
}

.page-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

.btn-add {
  background: #3b82f6;
  color: #ffffff;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background-color 0.15s;
}

.btn-add:hover {
  background: #2563eb;
}

/* Filters */
.filters-row {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  align-items: center;
}

.search-wrap {
  position: relative;
  flex: 1;
  min-width: 220px;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 1rem;
  height: 1rem;
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

/* Table card */
.table-card {
  background: #ffffff;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.task-table {
  width: 100%;
  border-collapse: collapse;
}

.task-table thead tr {
  background: #f8fafc;
  border-bottom: 1px solid #f1f5f9;
}

.task-table th {
  padding: 0.75rem 1.25rem;
  text-align: left;
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  color: #94a3b8;
}

.task-table tbody tr {
  border-bottom: 1px solid #f8fafc;
  transition: background-color 0.1s;
}

.task-table tbody tr:last-child {
  border-bottom: none;
}

.task-table tbody tr:hover {
  background: #fafafa;
}

.task-table td {
  padding: 0.875rem 1.25rem;
  font-size: 0.875rem;
  color: #334155;
  vertical-align: middle;
}

.td-title {
  font-weight: 600;
  color: #1e293b;
  max-width: 280px;
}

.td-project {
  color: #64748b;
}

.td-due {
  white-space: nowrap;
}

.due--overdue,
.due--today {
  color: #ef4444;
  font-weight: 600;
}

.due-wrap {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.warn-icon {
  width: 0.875rem;
  height: 0.875rem;
  flex-shrink: 0;
}

.td-actions {
  white-space: nowrap;
}

/* Action buttons */
.action-btn {
  padding: 0.25rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1.5px solid;
  cursor: pointer;
  transition: background-color 0.15s;
  margin-right: 0.375rem;
}

.action-btn--edit {
  background: #eff6ff;
  border-color: #bfdbfe;
  color: #2563eb;
}

.action-btn--edit:hover {
  background: #dbeafe;
}

.action-btn--delete {
  background: #fff1f2;
  border-color: #fecdd3;
  color: #e11d48;
}

.action-btn--delete:hover {
  background: #ffe4e6;
}

/* Empty */
.empty-cell {
  text-align: center;
  padding: 3rem 1rem !important;
  color: #94a3b8;
  font-size: 0.875rem;
}

/* Skeleton rows */
.skel {
  background: #f1f5f9;
  border-radius: 0.375rem;
  animation: pulse 1.5s ease-in-out infinite alternate;
}

.skel--title { height: 0.875rem; width: 12rem; }
.skel--sm { height: 0.875rem; width: 7rem; }
.skel--badge { height: 1.25rem; width: 4.5rem; border-radius: 9999px; }
.skel--actions { height: 1.5rem; width: 7rem; }

@keyframes pulse {
  from { opacity: 1; }
  to { opacity: 0.45; }
}

/* Pagination */
.pagination {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  justify-content: center;
}

.page-btn {
  min-width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.375rem;
  border: 1.5px solid #e2e8f0;
  background: #ffffff;
  font-size: 0.8125rem;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  transition: background-color 0.15s, border-color 0.15s;
  padding: 0 0.5rem;
}

.page-btn:hover:not(:disabled) {
  background: #f1f5f9;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-btn--active {
  background: #3b82f6;
  border-color: #3b82f6;
  color: #ffffff;
}

.page-btn--active:hover {
  background: #2563eb;
}

/* Error */
.alert-error {
  background: #fff1f2;
  color: #e11d48;
  border: 1px solid #fecdd3;
  border-radius: 0.625rem;
  padding: 0.875rem 1.25rem;
  font-size: 0.875rem;
}
</style>

