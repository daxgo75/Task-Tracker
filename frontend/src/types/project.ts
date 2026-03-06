import type { User } from './auth'

export type ProjectStatus = 'active' | 'archived'

export interface Project {
  id: number
  name: string
  description: string | null
  status: ProjectStatus
  owner: User
  task_counts: { total: number }
  created_at: string
  updated_at: string
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number | null
  to: number | null
}

export interface PaginatedData<T> {
  data: T[]
  meta: PaginationMeta
  links: {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
  }
}

export interface ProjectFilters {
  search: string
  status: ProjectStatus | ''
  per_page: number
  page: number
}

export interface ProjectFormPayload {
  name: string
  description: string
  status: ProjectStatus
}
