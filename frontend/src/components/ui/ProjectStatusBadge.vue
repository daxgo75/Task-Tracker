<script setup lang="ts">
import type { ProjectStatus } from '@/types/project'

defineProps<{ status: ProjectStatus }>()
</script>

<template>
  <span class="badge" :class="`badge--${status}`">
    {{ status === 'active' ? 'Active' : 'Archived' }}
  </span>
</template>

<style scoped>
.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.2rem 0.625rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  line-height: 1.4;
}

.badge--active {
  background-color: #dcfce7;
  color: #16a34a;
}

.badge--archived {
  background-color: #f1f5f9;
  color: #64748b;
}
</style>
