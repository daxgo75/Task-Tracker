<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useRouter, RouterLink, useRoute } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const isLoggingOut = ref(false)

async function handleLogout(): Promise<void> {
  isLoggingOut.value = true
  try {
    await authStore.logout()
    router.push({ name: 'login' })
  } finally {
    isLoggingOut.value = false
  }
}

function isActive(name: string): boolean {
  return route.name === name
}

const userInitial = computed(() => authStore.user?.name?.charAt(0).toUpperCase() ?? 'U')
const userRole = computed(() => (authStore.user?.is_admin ? 'Administrator' : 'Member'))

import { computed, ref } from 'vue'
</script>

<template>
  <aside class="sidebar">
    <!-- Logo -->
    <div class="sidebar__logo">
      <span class="logo-task">Task</span><span class="logo-tracker">Tracker</span>
    </div>

    <!-- Navigation -->
    <nav class="sidebar__nav">
      <RouterLink :to="{ name: 'dashboard' }" class="nav-item" :class="{ 'nav-item--active': isActive('dashboard') }">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="7" height="7" rx="1" />
          <rect x="14" y="3" width="7" height="7" rx="1" />
          <rect x="3" y="14" width="7" height="7" rx="1" />
          <rect x="14" y="14" width="7" height="7" rx="1" />
        </svg>
        Dashboard
      </RouterLink>

      <RouterLink :to="{ name: 'projects' }" class="nav-item" :class="{ 'nav-item--active': isActive('projects') }">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />
        </svg>
        Project
      </RouterLink>

      <RouterLink :to="{ name: 'tasks' }" class="nav-item" :class="{ 'nav-item--active': isActive('tasks') }">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9 11 12 14 22 4" />
          <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
        </svg>
        Task
      </RouterLink>
    </nav>

    <div class="sidebar__spacer" />

    <!-- Logout -->
    <button class="sidebar__logout" :disabled="isLoggingOut" @click="handleLogout">
      <span v-if="isLoggingOut" class="logout-spinner" />
      <svg v-else class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
        <polyline points="16 17 21 12 16 7" />
        <line x1="21" y1="12" x2="9" y2="12" />
      </svg>
      {{ isLoggingOut ? 'Keluar...' : 'Keluar' }}
    </button>

    <!-- User Info -->
    <div class="sidebar__user">
      <div class="user-avatar">{{ userInitial }}</div>
      <div class="user-info">
        <span class="user-name">{{ authStore.user?.name }}</span>
        <span class="user-role">{{ userRole }}</span>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.sidebar {
  width: 160px;
  min-width: 160px;
  min-height: 100vh;
  background-color: #1a2535;
  display: flex;
  flex-direction: column;
  padding: 1.25rem 0.75rem;
}

.sidebar__logo {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 2rem;
  padding: 0 0.5rem;
}

.logo-task {
  color: #ffffff;
}

.logo-tracker {
  color: #3b82f6;
}

.sidebar__nav {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.625rem 0.75rem;
  border-radius: 0.5rem;
  color: #94a3b8;
  font-size: 0.875rem;
  font-weight: 500;
  text-decoration: none;
  transition: background-color 0.15s, color 0.15s;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.07);
  color: #e2e8f0;
}

.nav-item--active {
  background-color: rgba(59, 130, 246, 0.15);
  color: #60a5fa;
}

.nav-icon {
  width: 1rem;
  height: 1rem;
  flex-shrink: 0;
}

.sidebar__spacer {
  flex: 1;
}

.sidebar__logout {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.625rem 0.75rem;
  border-radius: 0.5rem;
  background: none;
  border: none;
  color: #f59e0b;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  width: 100%;
  text-align: left;
  transition: background-color 0.15s;
  margin-bottom: 0.75rem;
}

.sidebar__logout:hover:not(:disabled) {
  background-color: rgba(245, 158, 11, 0.1);
}

.sidebar__logout:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.logout-spinner {
  width: 0.875rem;
  height: 0.875rem;
  border: 2px solid rgba(245, 158, 11, 0.3);
  border-top-color: #f59e0b;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.sidebar__user {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.625rem 0.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding-top: 0.875rem;
}

.user-avatar {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background-color: #7c3aed;
  color: #ffffff;
  font-size: 0.875rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.user-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.user-name {
  color: #e2e8f0;
  font-size: 0.8125rem;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  color: #64748b;
  font-size: 0.75rem;
}
</style>
