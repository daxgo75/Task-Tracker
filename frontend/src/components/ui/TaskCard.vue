<script setup lang="ts">
import { computed } from 'vue'
import type { Task } from '@/types/task'
import { getDueDateStatus, formatDueLabel } from '@/plugins/date'

const props = defineProps<{ task: Task }>()

const emit = defineEmits<{
  (e: 'edit', task: Task): void
  (e: 'delete', task: Task): void
}>()

const dueDateStatus = computed(() => getDueDateStatus(props.task.due_date))
const dueDateLabel = computed(() => formatDueLabel(props.task.due_date))
const isOverdue = computed(() => dueDateStatus.value === 'overdue')

function onDragStart(evt: DragEvent): void {
  evt.dataTransfer!.effectAllowed = 'move'
  evt.dataTransfer!.setData('taskId', String(props.task.id))
}
</script>

<template>
  <div
    class="task-card"
    draggable="true"
    @dragstart="onDragStart"
  >
    <p class="task-card__title">{{ task.title }}</p>

    <p v-if="task.due_date" class="task-card__due" :class="`task-card__due--${dueDateStatus}`">
      {{ dueDateLabel }}
      <svg
        v-if="isOverdue"
        class="task-card__warn"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2.5"
      >
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
        <line x1="12" y1="9" x2="12" y2="13" />
        <line x1="12" y1="17" x2="12.01" y2="17" />
      </svg>
    </p>
    <p v-else class="task-card__due task-card__due--none">—</p>

    <div class="task-card__actions">
      <button
        class="task-btn task-btn--edit"
        @click.stop="emit('edit', task)"
        @mousedown.stop
      >
        Edit
      </button>
      <button
        class="task-btn task-btn--delete"
        @click.stop="emit('delete', task)"
        @mousedown.stop
      >
        Hapus
      </button>
    </div>
  </div>
</template>

<style scoped>
.task-card {
  background: #ffffff;
  border-radius: 0.5rem;
  padding: 0.75rem;
  cursor: grab;
  border: 1px solid #f1f5f9;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
  transition: box-shadow 0.15s, opacity 0.15s;
  user-select: none;
}

.task-card:active {
  cursor: grabbing;
}

.task-card[draggable='true']:hover {
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
}

.task-card__title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.25rem;
  word-break: break-word;
}

.task-card__due {
  font-size: 0.75rem;
  font-weight: 500;
  margin: 0 0 0.625rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.task-card__due--overdue {
  color: #ef4444;
}

.task-card__due--today {
  color: #ef4444;
}

.task-card__due--tomorrow {
  color: #f59e0b;
}

.task-card__due--future {
  color: #94a3b8;
}

.task-card__due--none {
  color: #cbd5e1;
}

.task-card__warn {
  width: 0.75rem;
  height: 0.75rem;
}

.task-card__actions {
  display: flex;
  gap: 0.375rem;
}

.task-btn {
  padding: 0.2rem 0.625rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1.5px solid;
  cursor: pointer;
  transition: background-color 0.15s;
}

.task-btn--edit {
  background-color: #eff6ff;
  border-color: #bfdbfe;
  color: #2563eb;
}

.task-btn--edit:hover {
  background-color: #dbeafe;
}

.task-btn--delete {
  background-color: #fff1f2;
  border-color: #fecdd3;
  color: #e11d48;
}

.task-btn--delete:hover {
  background-color: #ffe4e6;
}
</style>
