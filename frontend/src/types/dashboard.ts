export type DueDateStatus = 'overdue' | 'today' | 'tomorrow' | 'future'

export interface UpcomingTask {
  id: number
  title: string
  due_date: string | null
  project: { id: number; name: string }
  category: { id: number; name: string }
}

export interface DashboardData {
  total_active_projects: number
  total_incomplete_tasks: number
  tasks_by_category: Record<string, number>
  upcoming_tasks: UpcomingTask[]
}
