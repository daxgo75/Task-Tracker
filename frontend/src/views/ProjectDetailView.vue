<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { projectService } from '@/services/projectService'
import { useTasksStore } from '@/stores/tasks'
import { useCategoriesStore } from '@/stores/categories'
import type { Project, ProjectFormPayload } from '@/types/project'
import type { Task, TaskFormPayload } from '@/types/task'
import { KANBAN_COLUMNS } from '@/types/task'
import { formatDate } from '@/plugins/date'
import KanbanColumn from '@/components/ui/KanbanColumn.vue'
import TaskFormModal from '@/components/ui/TaskFormModal.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import ProjectFormModal from '@/components/ui/ProjectFormModal.vue'
import ProjectStatusBadge from '@/components/ui/ProjectStatusBadge.vue'

const route = useRoute()
const router = useRouter()
const tasksStore = useTasksStore()
const categoriesStore = useCategoriesStore()

const projectId = computed(() => Number(route.params['id']))

const project = ref<Project | null>(null)
const isLoadingProject = ref(false)
const projectError = ref('')

const isEditProjectOpen = ref(false)
const isSavingProject = ref(false)
const projectFieldErrors = ref<Record<string, string>>({})
const projectGlobalError = ref('')

const isTaskModalOpen = ref(false)
const editingTask = ref<Task | null>(null)

const isConfirmOpen = ref(false)
const taskToDelete = ref<Task | null>(null)
const isDeleting = ref(false)

const doneCount = computed(() => tasksStore.doneCount)
const totalCount = computed(() => tasksStore.totalCount)

async function loadProject(): Promise<void> {
  isLoadingProject.value = true
  projectError.value = ''
  try {
    const res = await projectService.getProject(projectId.value)
    project.value = res.data
  } catch {
    projectError.value = 'Gagal memuat data project.'
  } finally {
    isLoadingProject.value = false
  }
}

async function submitProjectEdit(payload: ProjectFormPayload): Promise<void> {
  isSavingProject.value = true
  projectFieldErrors.value = {}
  projectGlobalError.value = ''
  try {
    const res = await projectService.updateProject(projectId.value, payload)
    project.value = res.data
    isEditProjectOpen.value = false
  } catch (err: unknown) {
    const e = err as { response?: { status?: number; data?: { message?: string; errors?: Record<string, string[]> } } }
    if (e.response?.status === 422 && e.response.data?.errors) {
      const errs: Record<string, string> = {}
      for (const [field, messages] of Object.entries(e.response.data.errors)) {
        errs[field] = (messages as string[])[0] ?? ''
      }
      projectFieldErrors.value = errs
    } else {
      projectGlobalError.value = e.response?.data?.message || 'Gagal menyimpan project. Silakan coba lagi.'
    }
  } finally {
    isSavingProject.value = false
  }
}

function openCreateTask(): void {
  editingTask.value = null
  tasksStore.clearErrors()
  isTaskModalOpen.value = true
}

function openEditTask(task: Task): void {
  editingTask.value = task
  tasksStore.clearErrors()
  isTaskModalOpen.value = true
}

async function submitTask(payload: TaskFormPayload): Promise<void> {
  let result: { success: boolean }
  if (editingTask.value) {
    result = await tasksStore.updateTask(editingTask.value.id, payload)
  } else {
    result = await tasksStore.createTask(projectId.value, payload)
  }
  if (result.success) {
    isTaskModalOpen.value = false
  }
}

function openDeleteConfirm(task: Task): void {
  taskToDelete.value = task
  isConfirmOpen.value = true
}

async function confirmDelete(): Promise<void> {
  if (!taskToDelete.value) return
  isDeleting.value = true
  const result = await tasksStore.deleteTask(taskToDelete.value.id)
  isDeleting.value = false
  if (result.success) {
    isConfirmOpen.value = false
    taskToDelete.value = null
  }
}

function handleTaskDropped(payload: { taskId: number; targetColumnId: string }): void {
  const task = tasksStore.tasks.find((t) => t.id === payload.taskId)
  if (!task) return
  if (task.category.name === payload.targetColumnId) return
  tasksStore.moveTask(payload.taskId, payload.targetColumnId)
}

onMounted(async () => {
  await Promise.all([
    loadProject(),
    categoriesStore.fetchIfEmpty(),
    tasksStore.fetchTasks(projectId.value),
  ])
})

onUnmounted(() => {
  tasksStore.reset()
})
</script>

<template>
  <div class="detail-page">
    <button class="back-btn" @click="router.push('/projects')">← Kembali</button>

    <div v-if="isLoadingProject" class="skeleton-header" />

    <template v-else-if="project">
      <div class="project-card">
        <div class="project-card__main">
          <div class="project-card__info">
            <h1 class="project-card__name">{{ project.name }}</h1>
            <p v-if="project.description" class="project-card__desc">{{ project.description }}</p>
            <ProjectStatusBadge :status="project.status" />
          </div>
          <button class="edit-project-btn" @click="isEditProjectOpen = true">Edit</button>
        </div>
        <div class="project-stats">
          <div class="stat">
            <span class="stat__label">DIBUAT</span>
            <span class="stat__value">{{ formatDate(project.created_at) }}</span>
          </div>
          <div class="stat-divider" />
          <div class="stat">
            <span class="stat__label">TOTAL TASK</span>
            <span class="stat__value">{{ totalCount }}</span>
          </div>
          <div class="stat-divider" />
          <div class="stat">
            <span class="stat__label">SELESAI</span>
            <span class="stat__value stat__value--done">{{ doneCount }} / {{ totalCount }}</span>
          </div>
        </div>
      </div>

      <div class="tasks-header">
        <h2 class="tasks-title">Task</h2>
        <button class="add-task-btn" @click="openCreateTask">+ Tambah Task</button>
      </div>

      <div v-if="tasksStore.isLoading" class="tasks-loading">
        <div v-for="i in 5" :key="i" class="column-skeleton" />
      </div>

      <div v-else-if="tasksStore.error" class="tasks-error">{{ tasksStore.error }}</div>

      <div v-else class="kanban-board">
        <KanbanColumn
          v-for="col in KANBAN_COLUMNS"
          :key="col.id"
          :column="col"
          :tasks="tasksStore.tasksByCategory[col.id]"
          @task-dropped="handleTaskDropped"
          @task-edit="openEditTask"
          @task-delete="openDeleteConfirm"
        />
      </div>
    </template>

    <div v-else-if="projectError" class="page-error">{{ projectError }}</div>

    <ProjectFormModal
      :is-open="isEditProjectOpen"
      :project="project"
      :is-saving="isSavingProject"
      :field-errors="projectFieldErrors"
      :global-error="projectGlobalError"
      @submit="submitProjectEdit"
      @close="isEditProjectOpen = false"
    />

    <TaskFormModal
      :is-open="isTaskModalOpen"
      :task="editingTask"
      :is-saving="tasksStore.isSaving"
      :field-errors="tasksStore.fieldErrors"
      :global-error="tasksStore.error"
      :categories="categoriesStore.categories"
      @submit="submitTask"
      @close="isTaskModalOpen = false"
    />

    <ConfirmModal
      variant="trash"
      title="Hapus Task?"
      :is-open="isConfirmOpen"
      :message="`Task <strong>&quot;${taskToDelete?.title ?? ''}&quot;</strong> akan dihapus. Data task masih tersimpan di sistem (soft delete) dan bisa dipulihkan jika diperlukan.`"
      :is-loading="isDeleting"
      @confirm="confirmDelete"
      @close="isConfirmOpen = false; taskToDelete = null"
    />
  </div>
</template>

<style scoped>
.detail-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.back-btn {
  align-self: flex-start;
  background: none;
  border: none;
  font-size: 0.875rem;
  font-weight: 600;
  color: #3b82f6;
  cursor: pointer;
  padding: 0.25rem 0;
  transition: color 0.15s;
}

.back-btn:hover { color: #2563eb; }

.project-card {
  background: #ffffff;
  border-radius: 0.75rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.project-card__main {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.project-card__info {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.project-card__name {
  font-size: 1.375rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.project-card__desc {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
  max-width: 60ch;
}

.edit-project-btn {
  flex-shrink: 0;
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  color: #475569;
  font-size: 0.875rem;
  font-weight: 600;
  padding: 0.4rem 1rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background-color 0.15s, border-color 0.15s;
}

.edit-project-btn:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.project-stats {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.stat { display: flex; flex-direction: column; gap: 0.2rem; }

.stat__label {
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  color: #94a3b8;
}

.stat__value {
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
}

.stat__value--done { color: #16a34a; }

.stat-divider {
  width: 1px;
  height: 2rem;
  background: #f1f5f9;
}

.tasks-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.tasks-title {
  font-size: 1.0625rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.add-task-btn {
  background: #3b82f6;
  color: #ffffff;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.15s;
}

.add-task-btn:hover { background: #2563eb; }

.kanban-board {
  display: flex;
  gap: 0.875rem;
  align-items: flex-start;
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.skeleton-header {
  height: 10rem;
  background: #f1f5f9;
  border-radius: 0.75rem;
  animation: pulse 1.5s ease-in-out infinite alternate;
}

.tasks-loading {
  display: flex;
  gap: 0.875rem;
}

.column-skeleton {
  flex: 1;
  min-width: 200px;
  height: 16rem;
  background: #f1f5f9;
  border-radius: 0.625rem;
  animation: pulse 1.5s ease-in-out infinite alternate;
}

@keyframes pulse {
  from { opacity: 1; }
  to { opacity: 0.5; }
}

.tasks-error,
.page-error {
  background: #fff1f2;
  color: #e11d48;
  border: 1px solid #fecdd3;
  border-radius: 0.625rem;
  padding: 1rem 1.25rem;
  font-size: 0.875rem;
}
</style>

