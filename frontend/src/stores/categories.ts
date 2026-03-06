import { defineStore } from 'pinia'
import { ref } from 'vue'
import { categoryService } from '@/services/categoryService'
import type { Category } from '@/types/task'

export const useCategoriesStore = defineStore('categories', () => {
  const categories = ref<Category[]>([])
  const isLoaded = ref(false)

  async function fetchIfEmpty(): Promise<void> {
    if (isLoaded.value) return
    try {
      const response = await categoryService.getCategories()
      categories.value = response.data
      isLoaded.value = true
    } catch {
      // silently fail — categories will be empty
    }
  }

  function findByName(name: string): Category | undefined {
    return categories.value.find((c) => c.name === name)
  }

  return { categories, isLoaded, fetchIfEmpty, findByName }
})
