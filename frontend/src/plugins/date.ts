import type { DueDateStatus } from '@/types/dashboard'

function parseLocalDate(dateStr: string): Date {
  const [year, month, day] = dateStr.split('-').map(Number)
  return new Date(year!, month! - 1, day!)
}

function getDiffDays(dueDate: string): number {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const due = parseLocalDate(dueDate)
  return Math.round((due.getTime() - today.getTime()) / (1000 * 60 * 60 * 24))
}

export function getDueDateStatus(dueDate: string | null): DueDateStatus {
  if (!dueDate) return 'future'
  const diff = getDiffDays(dueDate)
  if (diff < 0) return 'overdue'
  if (diff === 0) return 'today'
  if (diff === 1) return 'tomorrow'
  return 'future'
}

export function formatDueLabel(dueDate: string | null): string {
  if (!dueDate) return '-'
  const diff = getDiffDays(dueDate)
  if (diff < 0) return `${Math.abs(diff)} hari lalu`
  if (diff === 0) return 'Hari ini'
  if (diff === 1) return 'Besok'
  return parseLocalDate(dueDate).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

export function formatDate(dateStr: string): string {
  // Accept "YYYY-MM-DD HH:mm:ss" or ISO strings
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}
