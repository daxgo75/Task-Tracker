import api from './api'
import type { ApiResponse } from '@/types/auth'
import type { DashboardData } from '@/types/dashboard'

export const dashboardService = {
  async getDashboard(upcomingDays = 7): Promise<ApiResponse<DashboardData>> {
    const response = await api.get<ApiResponse<DashboardData>>('/dashboard', {
      params: { upcoming_days: upcomingDays },
    })
    return response.data
  },
}
