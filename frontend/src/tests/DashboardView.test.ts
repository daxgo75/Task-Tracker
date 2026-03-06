import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import DashboardView from '@/views/DashboardView.vue'

vi.mock('@/services/dashboardService', () => ({
  dashboardService: {
    getDashboard: vi.fn(),
  },
}))

import { dashboardService } from '@/services/dashboardService'

const mockDashboardData = {
  total_active_projects: 5,
  total_incomplete_tasks: 12,
  tasks_by_category: { Todo: 3, InProgress: 2 },
  upcoming_tasks: [
    {
      id: 1,
      title: 'Setup CI/CD',
      due_date: '2026-03-10',
      project: { id: 1, name: 'Website E-Commerce' },
      category: { id: 1, name: 'Testing' },
    },
  ],
}

function mountDashboard() {
  return mount(DashboardView, {
    global: {
      plugins: [createPinia()],
      stubs: { StatCard: true, UpcomingTaskItem: true },
    },
  })
}

describe('DashboardView', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('shows loading skeleton on mount before API resolves', () => {
    vi.mocked(dashboardService.getDashboard).mockResolvedValueOnce({
      success: true,
      message: '',
      data: mockDashboardData,
    })
    const wrapper = mountDashboard()
    expect(wrapper.find('[aria-label="Memuat data..."]').exists()).toBe(true)
  })

  it('hides loading and shows content after successful load', async () => {
    vi.mocked(dashboardService.getDashboard).mockResolvedValueOnce({
      success: true,
      message: '',
      data: mockDashboardData,
    })
    const wrapper = mountDashboard()
    await flushPromises()
    expect(wrapper.find('[aria-label="Memuat data..."]').exists()).toBe(false)
    expect(wrapper.find('.dashboard__stats').exists()).toBe(true)
  })

  it('shows error message when API call fails', async () => {
    vi.mocked(dashboardService.getDashboard).mockRejectedValueOnce(new Error('Network error'))
    const wrapper = mountDashboard()
    await flushPromises()
    const alert = wrapper.find('[role="alert"]')
    expect(alert.exists()).toBe(true)
    expect(alert.text()).toContain('Gagal memuat data dashboard')
  })

  it('shows "Task Mendekati Due Date" section header', async () => {
    vi.mocked(dashboardService.getDashboard).mockResolvedValueOnce({
      success: true,
      message: '',
      data: mockDashboardData,
    })
    const wrapper = mountDashboard()
    await flushPromises()
    expect(wrapper.find('.dashboard__card-title').text()).toBe('Task Mendekati Due Date')
  })

  it('shows empty message when there are no upcoming tasks', async () => {
    vi.mocked(dashboardService.getDashboard).mockResolvedValueOnce({
      success: true,
      message: '',
      data: { ...mockDashboardData, upcoming_tasks: [] },
    })
    const wrapper = mountDashboard()
    await flushPromises()
    expect(wrapper.find('.dashboard__empty').exists()).toBe(true)
    expect(wrapper.find('.dashboard__empty').text()).toContain('Tidak ada task')
  })

  it('renders upcoming task list when tasks exist', async () => {
    vi.mocked(dashboardService.getDashboard).mockResolvedValueOnce({
      success: true,
      message: '',
      data: mockDashboardData,
    })
    const wrapper = mountDashboard()
    await flushPromises()
    expect(wrapper.find('.dashboard__task-list').exists()).toBe(true)
    // UpcomingTaskItem is stubbed — one stub per task
    expect(wrapper.findAllComponents({ name: 'UpcomingTaskItem' }).length).toBe(1)
  })
})
