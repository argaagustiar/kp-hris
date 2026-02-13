<script setup lang="ts">
import { ref, computed } from 'vue'
import type { FormSubmitEvent } from '@nuxt/ui'
import { useAuthStore } from "../stores/auth";
import { api } from '../../services/api'

const props = defineProps<{
  modelValue?: boolean,
  periodId?: string | null
}>()

const emit = defineEmits(['update:modelValue', 'saved'])

const isOpen = computed({
  get: () => props.modelValue ?? false,
  set: (value) => emit('update:modelValue', value)
})

const toast = useToast()

// Upload state
const selectedFile = ref<File | null>(null)
const isLoading = ref(false)
const isDownloading = ref(false)

function handleFileSelect(event: Event) {
  const target = event.target as HTMLInputElement
  const files = target.files
  if (files && files.length > 0) {
    const file = files[0]
    // Validate file type
    if (!['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'].includes(file.type) && !file.name.endsWith('.xlsx') && !file.name.endsWith('.xls')) {
      toast.add({
        title: 'Invalid file type',
        description: 'Please select a valid Excel file (.xlsx, .xls)',
        color: 'error'
      })
      return
    }
    selectedFile.value = file
  }
}

async function handleUpload() {
  if (!selectedFile.value) {
    toast.add({
      title: 'No file selected',
      description: 'Please select an Excel file to upload',
      color: 'error'
    })
    return
  }

  isLoading.value = true
  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)
    formData.append('period_id', props.periodId || '')

    // Call API endpoint for uploading attendance records
    // You need to adjust the endpoint based on your backend API
    // const response = await fetch('/api/attendance-records/import', {
    //   method: 'POST',
    //   body: formData,
    // })

    const response = await api.post('/attendance-records/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    toast.add({
      title: 'Success',
      description: `Attendance records imported successfully. ${response.data.imported_count || 0} records imported.`,
      color: 'success'
    })

    selectedFile.value = null
    emit('saved')
    isOpen.value = false
  } catch (error) {
    console.error('Error uploading file:', error)
    toast.add({
      title: 'Upload Error',
      description: 'Failed to upload attendance records. Please try again.',
      color: 'error'
    })
  } finally {
    isLoading.value = false
  }
}

function clearFile() {
  selectedFile.value = null
}

async function downloadTemplate() {
  isDownloading.value = true
  try {
    const response = await api.get('/attendance-records/template/download', {
      responseType: 'blob'
    })
    
    const url = window.URL.createObjectURL(response.data)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'upload attendance template.xlsx')
    document.body.appendChild(link)
    link.click()
    link.parentElement?.removeChild(link)
    window.URL.revokeObjectURL(url)
    
    toast.add({
      title: 'Success',
      description: 'Template downloaded successfully',
      color: 'success'
    })
  } catch (error) {
    console.error('Error downloading template:', error)
    toast.add({
      title: 'Download Error',
      description: 'Failed to download template. Please try again.',
      color: 'error'
    })
  } finally {
    isDownloading.value = false
  }
}
</script>

<template>
  <UModal 
    v-model:open="isOpen"
    title="Upload Attendance Records"
    description="Upload an Excel file containing attendance records"
    :ui="{ content: 'sm:max-w-2xl' }"
  >
    <template #body>
      <div class="space-y-4">
        <!-- Download Template Button -->
        <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">
              Need a template?
            </p>
            <p class="text-xs text-gray-600 dark:text-gray-400">
              Download our Excel template to get started
            </p>
          </div>
          <UButton
            label="Download Template"
            icon="i-lucide-download"
            color="primary"
            variant="outline"
            size="sm"
            :loading="isDownloading"
            :disabled="isDownloading"
            class="cursor-pointer flex-shrink-0"
            @click="downloadTemplate"
          />
        </div>

        <!-- File Input Area -->
        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-blue-400 transition-colors">
          <div class="space-y-2">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
              <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h24a4 4 0 004-4V20m-6-11l-3-3m0 0l-3 3m3-3v9m6 0a4 4 0 11-8 0 4 4 0 018 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="text-sm font-medium text-gray-900 dark:text-white">
              Drop Excel file here or <label for="file-input" class="text-blue-600 dark:text-blue-400 cursor-pointer hover:underline">browse</label>
            </div>
            <input
              id="file-input"
              type="file"
              accept=".xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
              class="hidden"
              @change="handleFileSelect"
            />
            <p class="text-xs text-gray-500 dark:text-gray-400">Supported formats: .xlsx, .xls</p>
          </div>
        </div>

        <!-- Selected File Info -->
        <div v-if="selectedFile" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-gray-900 dark:text-white">
                Selected file:
              </p>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ selectedFile.name }} ({{ (selectedFile.size / 1024).toFixed(2) }} KB)
              </p>
            </div>
            <UButton
              icon="i-lucide-x"
              color="neutral"
              variant="ghost"
              size="sm"
              class="cursor-pointer"
              @click="clearFile"
            />
          </div>
        </div>

        <!-- Info Box -->
        <!-- <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
          <h4 class="text-sm font-medium text-amber-900 dark:text-amber-100 mb-2">File Requirements:</h4>
          <ul class="text-sm text-amber-800 dark:text-amber-200 space-y-1">
            <li>• File must be in Excel format (.xlsx or .xls)</li>
            <li>• Include columns: Employee Code, Date, Check-in Time, Check-out Time</li>
            <li>• Date format should be YYYY-MM-DD</li>
          </ul>
        </div> -->

        <!-- Action Buttons -->
        <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-800">
          <UButton
            label="Cancel"
            color="neutral"
            variant="subtle"
            class="cursor-pointer"
            @click="isOpen = false"
          />
          <UButton
            label="Upload"
            color="primary"
            variant="solid"
            :loading="isLoading"
            :disabled="!selectedFile || isLoading"
            class="cursor-pointer"
            @click="handleUpload"
          />
        </div>
      </div>
    </template>
  </UModal>
</template>
