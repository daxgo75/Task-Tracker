<script setup lang="ts">
import { ref } from 'vue'
import type { Task } from '@/types/task'
import type { KanbanColumnDef } from '@/types/task'
import TaskCard from './TaskCard.vue'

const props = defineProps<{
  column: KanbanColumnDef
  tasks: Task[]
}>()

const emit = defineEmits<{
  (e: 'task-dropped', payload: { taskId: number; targetColumnId: string }): void
  (e: 'task-edit', task: Task): void
  (e: 'task-delete', task: Task): void
}>()

const isDragOver = ref(false)

function onDragEnter(evt: DragEvent): void {
  evt.preventDefault()
  isDragOver.value = true
}

function onDragOver(evt: DragEvent): void {
  evt.preventDefault()
}

function onDragLeave(evt: DragEvent): void {
  const target = evt.currentTarget as HTMLElement
  if (!target.contains(evt.relatedTarget as Node)) {
    isDragOver.value = false
  }
}

function onDrop(evt: DragEvent): void {
  evt.preventDefault()
  isDragOver.value = false
  const taskId = Number(evt.dataTransfer?.getData('taskId'))
  if (taskId) {
    emit('task-dropped', { taskId, targetColumnId: props.column.id })
  }
}
</script>

<template>
  <div
    class="kanban-col"
    :class="{ 'kanban-col--over': isDragOver }"
    @dragenter="onDragEnter"
    @dragover="onDragOver"
    @dragleave="onDragLeave"
    @drop="onDrop"
  >
    <!-- Header -->
    <div class="kanban-col__header" :style="{ borderBottomColor: column.color }">
      <span class="kanban-col__label">{{ column.label }}</span>
      <span class="kanban-col__count" :style="{ backgroundColor: column.color }">
        {{ tasks.length }}
      </span>
    </div>

    <!-- Cards -->
    <div class="kanban-col__body">
      <TransitionGroup name="card-list" tag="div">
        <TaskCard
          v-for="task in tasks"
          :key="task.id"
          :task="task"
          @edit="emit('task-edit', $event)"
          @delete="emit('task-delete', $event)"
        />
      </TransitionGroup>

      <div v-if="tasks.length === 0" class="kanban-col__empty">
        Tidak ada task
      </div>
    </div>
  </div>
</template>

<style scoped>
.kanban-col {
  background: #f8fafc;
  border-radius: 0.625rem;
  display: flex;
  flex-direction: column;
  min-width: 200px;
  flex: 1;
  border: 2px solid transparent;
  transition: border-color 0.15s, background-color 0.15s;
}

.kanban-col--over {
  border-color: #94a3b8;
  background: #f1f5f9;
}

.kanban-col__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 0.875rem;
  border-bottom: 3px solid;
  border-radius: 0.625rem 0.625rem 0 0;
  background: #ffffff;
}

.kanban-col__label {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  color: #475569;
}

.kanban-col__count {
  color: #ffffff;
  font-size: 0.6875rem;
  font-weight: 700;
  width: 1.375rem;
  height: 1.375rem;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.kanban-col__body {
  padding: 0.75rem;
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.kanban-col__empty {
  text-align: center;
  font-size: 0.75rem;
  color: #cbd5e1;
  padding: 1.5rem 0;
}

/* Card list transition */
.card-list-move,
.card-list-enter-active,
.card-list-leave-active {
  transition: all 0.2s ease;
}

.card-list-enter-from {
  opacity: 0;
  transform: translateY(-6px);
}

.card-list-leave-to {
  opacity: 0;
  transform: translateY(6px);
}
</style>
