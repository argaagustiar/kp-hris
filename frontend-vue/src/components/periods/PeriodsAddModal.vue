<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue'
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'
import { CalendarDate, DateFormatter, getLocalTimeZone, parseDate } from '@internationalized/date'
import { usePeriodStore } from '../../stores/period'

// 1. Definisikan Schema Validasi
const schema = z.object({
  description: z.string({ message: 'Required' }).min(1, 'Description is required'),
  start_date: z.custom<CalendarDate>((v) => v instanceof CalendarDate, 'Start date is required'),
  end_date: z.custom<CalendarDate>((v) => v instanceof CalendarDate, 'End date is required'),
//   is_active: z.boolean()
})

type Schema = z.output<typeof schema>

const props = defineProps<{
  // 'modelValue' adalah standar Vue untuk v-model
  modelValue?: boolean 
  // Jika ada ID, berarti Edit Mode. Jika null/undefined, berarti Create Mode
  periodId?: string | null 
  // Opsional: Data awal untuk edit
  initialData?: any 
}>()

const emit = defineEmits(['update:modelValue', 'saved'])

const isOpen = computed({
  get: () => props.modelValue ?? false,
  set: (value) => emit('update:modelValue', value)
})

const isEditMode = computed(() => !!props.periodId)

const modalTitle = computed(() => isEditMode.value ? 'Edit Evaluation Period' : 'New Evaluation Period')
const modalDescription = computed(() => isEditMode.value ? 'Update existing period data' : 'Create a new evaluation period')
const buttonLabel = computed(() => isEditMode.value ? 'Save Changes' : 'Create Period')

const toast = useToast()
const df = new DateFormatter('id-ID', { dateStyle: 'medium' })
const periodStore = usePeriodStore()

// 3. Initial State
const state = reactive<Partial<Schema>>({
  description: undefined,
  start_date: undefined,
  end_date: undefined,
//   is_active: true
})

// 4. Submit Handler
async function onSubmit(event: FormSubmitEvent<Schema>) {
  console.log('Payload:', event.data)

  const payload = {
    description: event.data.description,
    start_date: event.data.start_date.toString(),
    end_date: event.data.end_date.toString(),
    // is_active: event.data.is_active
  }

  try {
    if (isEditMode.value && props.periodId) {
      await periodStore.updatePeriod(props.periodId, payload)
      toast.add({ 
        title: 'Success', 
        description: `Period "${event.data.description}" updated successfully`, 
        color: 'success' 
      })
    } else {
      await periodStore.createPeriod(payload)
      toast.add({ 
        title: 'Success', 
        description: `Period "${event.data.description}" created successfully`, 
        color: 'success' 
      })
    }
    emit('saved')
    isOpen.value = false
  } catch (error) {
    console.error('Error saving period:', error)
    toast.add({ 
      title: `Error ${error.response?.status}`, 
      description: `Failed to ${isEditMode.value ? 'update' : 'create'} period: ${error.response?.data?.message}`, 
      color: 'error' 
    })
  }
}

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    if (isEditMode.value && props.initialData) {
      // Logic isi form untuk Edit
      state.description = props.initialData.description
    //   state.is_active = props.initialData.is_active ?? true
      
      if (props.initialData.start_date) {
        state.start_date = parseDate(props.initialData.start_date)
      }

      if (props.initialData.end_date) {
        state.end_date = parseDate(props.initialData.end_date)
      }
    } else {
      // Logic reset form untuk New
      state.description = undefined
      state.start_date = undefined
      state.end_date = undefined
    //   state.is_active = true
    }
  }
})
</script>

<template>
  <UModal 
    v-model:open="isOpen" 
    :title="modalTitle"
    :description="modalDescription"
    :ui="{ content: 'sm:max-w-2xl' }" 
  >
    <template #body>
      <UForm
        :schema="schema"
        :state="state"
        class="space-y-4"
        @submit="onSubmit"
      >
        <div class="grid grid-cols-1 gap-4">
          <UFormField label="Description" name="description" required>
            <UInput 
              v-model="state.description" 
              class="w-full" 
            />
          </UFormField>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <UFormField label="Start Date" name="start_date" required>
            <UPopover :ui="{ content: 'p-0' }">
              <UButton
                color="neutral"
                variant="subtle"
                icon="i-lucide-calendar"
                class="w-full justify-start text-left font-normal"
                :class="!state.start_date && 'text-gray-500'"
              >
                {{ state.start_date ? df.format(state.start_date.toDate(getLocalTimeZone())) : 'Select date' }}
              </UButton>
              <template #content>
                <UCalendar v-model="state.start_date" class="p-2" />
              </template>
            </UPopover>
          </UFormField>

          <UFormField label="End Date" name="end_date" required>
            <UPopover :ui="{ content: 'p-0' }">
              <UButton
                color="neutral"
                variant="subtle"
                icon="i-lucide-calendar"
                class="w-full justify-start text-left font-normal"
                :class="!state.end_date && 'text-gray-500'"
              >
                {{ state.end_date ? df.format(state.end_date.toDate(getLocalTimeZone())) : 'Select date' }}
              </UButton>
              <template #content>
                <UCalendar v-model="state.end_date" class="p-2" />
              </template>
            </UPopover>
          </UFormField>
        </div>

        <!-- <div class="grid grid-cols-1 gap-4">
          <UFormField label="Status" name="is_active">
            <div class="flex items-center gap-2 mt-1">
              <USwitch v-model="state.is_active" />
              <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ state.is_active ? 'Active Period' : 'Closed' }}
              </span>
            </div>
          </UFormField>
        </div> -->

        <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
          <UButton
            label="Cancel"
            color="neutral"
            variant="subtle"
            class="cursor-pointer"
            @click="isOpen = false"
          />
          <UButton
            label="Save Period"
            color="primary"
            variant="solid"
            type="submit"
            :loading="periodStore.loading"
            class="cursor-pointer"
          />
        </div>
      </UForm>
    </template>
  </UModal>
</template>