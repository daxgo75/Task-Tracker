<script setup lang="ts">
import { computed } from 'vue'
import type { UpcomingTask, DueDateStatus } from '@/types/dashboard'
import { getDueDateStatus, formatDueLabel } from '@/plugins/date'

const props = defineProps<{
  task: UpcomingTask
}>()

const status = computed<DueDateStatus>(() => getDueDateStatus(props.task.due_date))
const label = computed(() => formatDueLabel(props.task.due_date))
</script>

<template>
  <div class="task-item">
    <span class="task-item__dot" :class="`task-item__dot--${status}`" />

    <div class="task-item__content">
      <span class="task-item__title">{{ task.title }}</span>
      <span class="task-item__project">{{ task.project.name }}</span>
    </div>

    <span class="task-item__due" :class="`task-item__due--${status}`">
      {{ label }}
      <svg v-if="status === 'overdue'" class="task-item__warn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
        <line x1="12" y1="9" x2="12" y2="13" />
        <line x1="12" y1="17" x2="12.01" y2="17" />
      </svg>
    </span>
  </div>
</template>

<style scoped>
.task-item {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.875rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.task-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.task-item__dot {
  width: 0.625rem;
  height: 0.625rem;
  border-radius: 50%;
  flex-shrink: 0;
}

.task-item__dot--overdue {
  background-color: #ef4444;
}

.task-item__dot--today {
  background-color: #ef4444;
}

.task-item__dot--tomorrow {
  background-color: #f59e0b;
}

.task-item__dot--future {
  background-color: #3b82f6;
}

.task-item__content {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.task-item__title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.task-item__project {
  font-size: 0.75rem;
  color: #94a3b8;
}

.task-item__due {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.8125rem;
  font-weight: 500;
  white-space: nowrap;
  flex-shrink: 0;
}

.task-item__due--overdue {
  color: #ef4444;
}

.task-item__due--today {
  color: #ef4444;
}

.task-item__due--tomorrow {
  color: #f59e0b;
}

.task-item__due--future {
  color: #94a3b8;
}

.task-item__warn {
  width: 0.875rem;
  height: 0.875rem;
}
</style>
