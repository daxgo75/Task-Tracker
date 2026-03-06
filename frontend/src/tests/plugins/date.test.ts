import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest'
import { getDueDateStatus, formatDueLabel, formatDate } from '@/plugins/date'

// Fix "today" to March 6, 2026 so tests are deterministic
const TODAY = new Date('2026-03-06T00:00:00.000')

describe('getDueDateStatus', () => {
  beforeEach(() => {
    vi.useFakeTimers()
    vi.setSystemTime(TODAY)
  })
  afterEach(() => {
    vi.useRealTimers()
  })

  it('returns "overdue" for a past date', () => {
    expect(getDueDateStatus('2026-03-01')).toBe('overdue')
  })

  it('returns "today" for today\'s date', () => {
    expect(getDueDateStatus('2026-03-06')).toBe('today')
  })

  it('returns "tomorrow" for tomorrow\'s date', () => {
    expect(getDueDateStatus('2026-03-07')).toBe('tomorrow')
  })

  it('returns "future" for a date further ahead', () => {
    expect(getDueDateStatus('2026-03-15')).toBe('future')
  })

  it('returns "future" when dueDate is null', () => {
    expect(getDueDateStatus(null)).toBe('future')
  })
})

describe('formatDueLabel', () => {
  beforeEach(() => {
    vi.useFakeTimers()
    vi.setSystemTime(TODAY)
  })
  afterEach(() => {
    vi.useRealTimers()
  })

  it('returns "Hari ini" for today', () => {
    expect(formatDueLabel('2026-03-06')).toBe('Hari ini')
  })

  it('returns "Besok" for tomorrow', () => {
    expect(formatDueLabel('2026-03-07')).toBe('Besok')
  })

  it('returns "X hari lalu" for overdue dates', () => {
    expect(formatDueLabel('2026-03-01')).toBe('5 hari lalu')
  })

  it('returns formatted date string for future dates', () => {
    const label = formatDueLabel('2026-03-15')
    // Should contain "15" and "Mar" or "2026"
    expect(label).toContain('15')
    expect(label).toContain('2026')
  })

  it('returns "-" when dueDate is null', () => {
    expect(formatDueLabel(null)).toBe('-')
  })
})

describe('formatDate', () => {
  it('formats an ISO date string to locale string', () => {
    const result = formatDate('2026-03-06T00:00:00.000Z')
    // Indonesian locale: "6 Mar 2026" or similar
    expect(result).toContain('2026')
  })

  it('formats a date-only string', () => {
    const result = formatDate('2026-01-15T00:00:00.000Z')
    expect(result).toContain('2026')
  })
})
