<script setup lang="ts">
import * as z from 'zod'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { ref, reactive } from 'vue'

const toast = useToast()
const router = useRouter()
const authStore = useAuthStore()
const isLoading = ref(false)
const isChangePasswordMode = ref(false)

// State untuk menyimpan username sementara saat diminta ganti password
const tempUsername = ref('')

// Schema Login Biasa
const loginSchema = z.object({
  login: z.string().min(1, 'Username is required'),
  password: z.string().min(1, 'Password is required')
})

// Schema Ganti Password
const changePasswordSchema = z.object({
  newPassword: z.string().min(8, 'Password must be at least 8 characters'),
  confirmPassword: z.string()
}).refine((data) => data.newPassword === data.confirmPassword, {
  message: "Passwords don't match",
  path: ["confirmPassword"],
})

// Field Login
const loginFields = [
  {
    name: 'login',
    type: 'text',
    label: 'Username',
    placeholder: 'Enter your username',
  },
  {
    name: 'password',
    type: 'password',
    label: 'Password',
    placeholder: 'Enter your password'
  }
]

// Field Ganti Password
const changePasswordFields = [
  {
    name: 'newPassword',
    type: 'password',
    label: 'New Password',
    placeholder: 'Enter new password (min 8 chars)',
  },
  {
    name: 'confirmPassword',
    type: 'password',
    label: 'Confirm Password',
    placeholder: 'Repeat new password'
  }
]

// Handler: Login Submit
async function onLoginSubmit(event: any) {
  const { login, password } = event.data

  // 1. Cek apakah password default
  if (password === 'kane1234') {
    tempUsername.value = login // Simpan username untuk proses selanjutnya
    isChangePasswordMode.value = true // Ganti mode ke Ganti Password
    // toast.add({
    //   title: 'Security Alert',
    //   description: 'Default password detected. Please change your password immediately.',
    //   color: 'warning'
    // })
    return
  }

  // 2. Login Biasa
  isLoading.value = true
  try {
    await authStore.login({ login, password })
    toast.add({ title: 'Success', description: 'Login successful', color: 'success' })
    router.push('/')
  } catch (error: any) {
    toast.add({
      title: 'Login Failed',
      description: error?.response?.data?.message || 'Invalid credentials',
      color: 'error',
    })
  } finally {
    isLoading.value = false
  }
}

// Handler: Change Password Submit
async function onChangePasswordSubmit(event: any) {
  isLoading.value = true
  try {
    await authStore.login({ login: tempUsername.value, password: 'kane1234' })
    toast.add({ title: 'Success', description: 'Login successful', color: 'success' })

    await authStore.changePassword({
      current_password: 'kane1234',
      new_password: event.data.newPassword,
      new_password_confirmation: event.data.confirmPassword
    })

    router.push('/')
    
    // Reset ke mode login
    // isChangePasswordMode.value = false
    
  } catch (error: any) {
    toast.add({
      title: 'Error',
      description: error?.message || 'Failed to change password',
      color: 'error',
    })
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="flex flex-col items-center justify-center gap-4 p-4 min-h-screen">
    <UPageCard class="w-full max-w-md">      
      <UAuthForm
        v-if="!isChangePasswordMode"
        :schema="loginSchema"
        :fields="loginFields"
        :loading="isLoading"
        title="Log In"
        icon="i-lucide-user"
        @submit="onLoginSubmit"
      >
        <template #footer>
          For password recovery and account access assistance, please contact the IT Department.
        </template>
      </UAuthForm>

      <div v-else>
        <div class="mb-4 text-center">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">Change Password</h2>
          <p class="text-sm text-gray-500 mt-1">
            You are using a default password. <br>
            Please set a new secure password for <strong>{{ tempUsername }}</strong>.
          </p>
        </div>

        <UAuthForm
          :schema="changePasswordSchema"
          :fields="changePasswordFields"
          :loading="isLoading"
          submit-button-label="Update Password"
          icon="i-lucide-lock"
          @submit="onChangePasswordSubmit"
        >
          <template #footer>
            <div class="text-center mt-4">
              <UButton 
                variant="link" 
                color="gray" 
                @click="isChangePasswordMode = false"
              >
                Cancel & Return to Login
              </UButton>
            </div>
          </template>
        </UAuthForm>
      </div>
    </UPageCard>
  </div>
</template>