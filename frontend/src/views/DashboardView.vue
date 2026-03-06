<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { dashboardService } from '@/services/dashboardService'
import StatCard from '@/components/ui/StatCard.vue'
import UpcomingTaskItem from '@/components/ui/UpcomingTaskItem.vue'
import type { DashboardData } from '@/types/dashboard'

const authStore = useAuthStore()

const data = ref<DashboardData | null>(null)
const isLoading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    const response = await dashboardService.getDashboard()
    data.value = response.data
  } catch {
    error.value = 'Gagal memuat data dashboard. Silakan coba lagi.'
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="dashboard">
    <div class="dashboard__header">
      <h1 class="dashboard__title">Dashboard</h1>
      <p class="dashboard__subtitle">Selamat datang 👋</p>
    </div>

    <!-- Loading skeleton -->
    <div v-if="isLoading" class="dashboard__loading" aria-label="Memuat data...">
      <div class="skeleton skeleton--card" />
      <div class="skeleton skeleton--card" />
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="dashboard__error" role="alert">
      {{ error }}
    </div>

    <template v-else-if="data">
      <!-- Stat Cards -->
      <div class="dashboard__stats">
        <StatCard label="Project Aktif" :value="data.total_active_projects" icon="folder" />
        <StatCard label="Task Belum Selesai" :value="data.total_incomplete_tasks" icon="hourglass" />
      </div>

      <!-- Upcoming Tasks -->
      <div class="dashboard__card">
        <h2 class="dashboard__card-title">Task Mendekati Due Date</h2>

        <div v-if="data.upcoming_tasks.length === 0" class="dashboard__empty">
          Tidak ada task yang mendekati due date.
        </div>

        <div v-else class="dashboard__task-list">
          <UpcomingTaskItem
            v-for="task in data.upcoming_tasks"
            :key="task.id"
            :task="task"
          />
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.dashboard {
  padding: 2rem 2.5rem;
  max-width: 900px;
}

.dashboard__header {
  margin-bottom: 1.75rem;
}

.dashboard__title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.25rem;
}

.dashboard__subtitle {
  font-size: 0.9375rem;
  color: #64748b;
  margin: 0;
}

.dashboard__stats {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.dashboard__card {
  background: #ffffff;
  border-radius: 0.75rem;
  padding: 1.25rem 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.dashboard__card-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.75rem;
}

.dashboard__task-list {
  display: flex;
  flex-direction: column;
}

.dashboard__empty {
  font-size: 0.875rem;
  color: #94a3b8;
  padding: 1rem 0;
}

.dashboard__error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 0.875rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.dashboard__loading {
  display: flex;
  gap: 1rem;
}

.skeleton {
  border-radius: 0.75rem;
  background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}

.skeleton--card {
  flex: 1;
  height: 5.5rem;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
