<script setup lang="ts">
const CATEGORY_MAP: Record<string, { label: string; cls: string }> = {
  Todo:       { label: 'Todo',        cls: 'badge--todo' },
  InProgress: { label: 'In Progress', cls: 'badge--in-progress' },
  Testing:    { label: 'Testing',     cls: 'badge--testing' },
  Done:       { label: 'Done',        cls: 'badge--done' },
  Pending:    { label: 'Pending',     cls: 'badge--pending' },
}

const props = defineProps<{ name: string }>()

const cfg = CATEGORY_MAP[props.name] ?? { label: props.name, cls: '' }
</script>

<template>
  <span class="badge" :class="cfg.cls">{{ cfg.label }}</span>
</template>

<style scoped>
.badge {
  display: inline-block;
  padding: 0.2rem 0.625rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
}

.badge--todo {
  background: #eff6ff;
  color: #1d4ed8;
}

.badge--in-progress {
  background: #fffbeb;
  color: #b45309;
}

.badge--testing {
  background: #f5f3ff;
  color: #6d28d9;
}

.badge--done {
  background: #f0fdf4;
  color: #15803d;
}

.badge--pending {
  background: #fff7ed;
  color: #c2410c;
}
</style>
