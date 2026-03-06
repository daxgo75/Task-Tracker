import type { User } from './auth'

export interface Category {
  id: number
  name: string
}

export interface Task {
  id: number
  project_id: number
  title: string
  description: string | null
  due_date: string | null
  category: Category
  created_by: User
  project?: { id: number; name: string; status: string }
  is_deleted: boolean
  deleted_at: string | null
  created_at: string
  updated_at: string
}

export interface TaskFormPayload {
  title: string
  category_id: number
  description: string
  due_date: string
}

export type KanbanColumnId = 'Todo' | 'InProgress' | 'Testing' | 'Done' | 'Pending'

export interface KanbanColumnDef {
  id: KanbanColumnId
  label: string
  color: string
}

export interface GlobalTaskFilters {
  search?: string
  category_id?: number
  project_id?: number
  per_page?: number
  page?: number
}

export interface GlobalTaskFormData {
  project_id: number
  title: string
  category_id: number
  description: string
  due_date: string
}

export const KANBAN_COLUMNS: KanbanColumnDef[] = [
  { id: 'Todo', label: 'TODO', color: '#3b82f6' },
  { id: 'InProgress', label: 'IN PROGRESS', color: '#f59e0b' },
  { id: 'Testing', label: 'TESTING', color: '#8b5cf6' },
  { id: 'Done', label: 'DONE', color: '#22c55e' },
  { id: 'Pending', label: 'PENDING', color: '#f97316' },
]
