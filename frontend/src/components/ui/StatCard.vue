<script setup lang="ts">
defineProps<{
  label: string
  value: number | string
  icon: 'folder' | 'hourglass'
}>()
</script>

<template>
  <div class="stat-card">
    <div class="stat-card__icon" :class="`stat-card__icon--${icon}`">
      <!-- Folder icon -->
      <svg v-if="icon === 'folder'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />
      </svg>
      <!-- Hourglass icon -->
      <svg v-else-if="icon === 'hourglass'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
        <path d="M5 22h14" />
        <path d="M5 2h14" />
        <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22" />
        <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2" />
      </svg>
    </div>
    <div class="stat-card__body">
      <span class="stat-card__label">{{ label }}</span>
      <span class="stat-card__value">{{ value }}</span>
    </div>
  </div>
</template>

<style scoped>
.stat-card {
  background: #ffffff;
  border-radius: 0.75rem;
  padding: 1.25rem 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.stat-card__icon {
  width: 3rem;
  height: 3rem;
  border-radius: 0.625rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-card__icon svg {
  width: 1.5rem;
  height: 1.5rem;
}

.stat-card__icon--folder {
  background-color: #f1f5f9;
  color: #64748b;
}

.stat-card__icon--hourglass {
  background-color: #fef9c3;
  color: #ca8a04;
}

.stat-card__body {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.stat-card__label {
  font-size: 0.8125rem;
  color: #64748b;
  font-weight: 500;
}

.stat-card__value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.2;
}
</style>
