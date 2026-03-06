import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { nextTick } from 'vue'
import TasksView from '@/views/TasksView.vue'
import type { Task } from '@/types/task'
import type { Project } from '@/types/project'

vi.mock('@/services/taskService', () => ({
  taskService: {
    getGlobalTasks: vi.fn(),
    createTask: vi.fn(),
    updateTask: vi.fn(),
    deleteTask: vi.fn(),
    getProjectTasks: vi.fn(),
  },
}))

vi.mock('@/services/categoryService', () => ({
  categoryService: {
    getCategories: vi.fn(),
  },
}))

vi.mock('@/services/projectService', () => ({
  projectService: {
    getProjects: vi.fn(),
    getProject: vi.fn(),
    createProject: vi.fn(),
    updateProject: vi.fn(),
    toggleStatus: vi.fn(),
  },
}))

import { taskService } from '@/services/taskService'
import { categoryService } from '@/services/categoryService'
import { projectService } from '@/services/projectService'

const mockMeta = {
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 1,
  from: 1,
  to: 1,
}

const mockLinks = { first: null, last: null, prev: null, next: null }

const mockUser = { id: 1, name: 'Admin', email: 'admin@test.com', is_admin: true }

const mockTask: Task = {
  id: 1,
  project_id: 1,
  title: 'Setup CI/CD',
  description: 'Configure pipeline',
  due_date: '2026-03-09',
  category: { id: 3, name: 'Testing' },
  created_by: mockUser,
  project: { id: 1, name: 'Website E-Commerce', status: 'active' },
  is_deleted: false,
  deleted_at: null,
  created_at: '2026-03-01T00:00:00.000Z',
  updated_at: '2026-03-01T00:00:00.000Z',
}

const mockCategories = [
  { id: 1, name: 'Todo' },
  { id: 2, name: 'InProgress' },
  { id: 3, name: 'Testing' },
]

const mockProjects: Project[] = [
  {
    id: 1,
    name: 'Website E-Commerce',
    description: 'Design platform belanja',
    status: 'active',
    owner: mockUser,
    task_counts: { total: 1 },
    created_at: '2026-03-01T00:00:00.000Z',
    updated_at: '2026-03-01T00:00:00.000Z',
  },
]

function setupMocks(tasks: Task[] = [mockTask]) {
  vi.mocked(taskService.getGlobalTasks).mockResolvedValue({
    success: true,
    message: '',
    data: { data: tasks, meta: { ...mockMeta, total: tasks.length }, links: mockLinks },
  })
  vi.mocked(categoryService.getCategories).mockResolvedValue({
    success: true,
    message: '',
    data: mockCategories,
  })
  vi.mocked(projectService.getProjects).mockResolvedValue({
    success: true,
    message: '',
    data: { data: mockProjects, meta: mockMeta, links: mockLinks },
  })
}

const GlobalTaskFormModalStub = {
  name: 'GlobalTaskFormModal',
  props: ['isOpen', 'task', 'isSaving', 'fieldErrors', 'categories', 'projects'],
  emits: ['submit', 'close'],
  template: '<div />',
}

const ConfirmModalStub = {
  name: 'ConfirmModal',
  props: ['isOpen', 'title', 'message', 'confirmLabel', 'isLoading', 'variant'],
  emits: ['confirm', 'close'],
  template: '<div />',
}

function mountTasks() {
  return mount(TasksView, {
    global: {
      plugins: [createPinia()],
      stubs: { GlobalTaskFormModal: GlobalTaskFormModalStub, ConfirmModal: ConfirmModalStub, CategoryBadge: true },
    },
  })
}

describe('TasksView', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('renders page title "Task" and add button', () => {
    setupMocks()
    const wrapper = mountTasks()
    expect(wrapper.find('.page-title').text()).toBe('Task')
    expect(wrapper.find('.btn-add').text()).toContain('Tambah Task')
  })

  it('shows loading skeleton rows while fetching', async () => {
    setupMocks()
    const wrapper = mountTasks()
    await nextTick() // allow isLoading = true to propagate before promise resolves
    expect(wrapper.findAll('.skeleton-row').length).toBeGreaterThan(0)
  })

  it('shows task rows in table after successful load', async () => {
    setupMocks()
    const wrapper = mountTasks()
    await flushPromises()
    expect(wrapper.find('.td-title').text()).toBe('Setup CI/CD')
    expect(wrapper.find('.td-project').text()).toBe('Website E-Commerce')
  })

  it('shows empty state when no tasks found', async () => {
    setupMocks([])
    const wrapper = mountTasks()
    await flushPromises()
    expect(wrapper.find('.empty-cell').exists()).toBe(true)
    expect(wrapper.find('.empty-cell').text()).toContain('Tidak ada task ditemukan')
  })

  it('opens create modal when "+ Tambah Task" is clicked', async () => {
    setupMocks()
    const wrapper = mountTasks()
    await flushPromises()
    await wrapper.find('.btn-add').trigger('click')
    const modal = wrapper.findComponent({ name: 'GlobalTaskFormModal' })
    expect(modal.props('isOpen')).toBe(true)
  })

  it('opens edit modal with task data when Edit is clicked', async () => {
    setupMocks()
    const wrapper = mountTasks()
    await flushPromises()
    await wrapper.find('.action-btn--edit').trigger('click')
    const modal = wrapper.findComponent({ name: 'GlobalTaskFormModal' })
    expect(modal.props('isOpen')).toBe(true)
  })

  it('opens confirm delete modal when Hapus is clicked', async () => {
    setupMocks()
    const wrapper = mountTasks()
    await flushPromises()
    await wrapper.find('.action-btn--delete').trigger('click')
    const modal = wrapper.findComponent({ name: 'ConfirmModal' })
    expect(modal.props('isOpen')).toBe(true)
  })

  it('closes confirm modal and calls deleteTask on confirm', async () => {
    setupMocks()
    vi.mocked(taskService.deleteTask).mockResolvedValueOnce({
      success: true,
      message: '',
      data: null,
    })
    const wrapper = mountTasks()
    await flushPromises()
    await wrapper.find('.action-btn--delete').trigger('click')
    // Emit confirm from ConfirmModal stub
    const modal = wrapper.findComponent({ name: 'ConfirmModal' })
    await modal.vm.$emit('confirm')
    await flushPromises()
    expect(taskService.deleteTask).toHaveBeenCalledWith(mockTask.id)
  })
})
