<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useStorage } from '@vueuse/core'
import type { NavigationMenuItem } from '@nuxt/ui'
import { useColorMode } from '@vueuse/core'
import { useAuthStore } from '../stores/auth'

const toast = useToast()
const route = useRoute()
const colorMode = useColorMode()
const authStore = useAuthStore()
const userRole = computed(() => authStore.user?.role || 'guest')
const toaster = { position: "top-center" }

const appConfig = useAppConfig()

// Set default primary color if not set
appConfig.ui.colors.primary = 'blue'

const open = ref(false)

watch(() => route.fullPath, () => {
  open.value = false
})

const links = computed<NavigationMenuItem[][]>(() => {
  const firstSection = [
    {
      label: 'Employees',
      icon: 'i-lucide-users',
      to: '/employees'
    }
  ]
  
  // Only show Period menu for admin/hr/hr2 role
  if (userRole.value === 'admin' || userRole.value === 'hr' || userRole.value === 'hr2') {
    firstSection.push({
      label: 'Periods',
      icon: 'i-lucide-calendar',
      to: '/period'
    })
  }

  if (userRole.value === 'admin' || userRole.value === 'hr') {
    firstSection.push({
      label: 'Reports',
      icon: 'i-lucide-file-text',
      to: '/reports'
    })
  }
  
  return [
    firstSection,
    [
      {
        label: colorMode.value === 'dark' ? 'Dark Mode' : 'Light Mode',
        icon: colorMode.value === 'dark' ? 'i-lucide-moon' : 'i-lucide-sun',
        checked: colorMode.value === 'dark',
        onSelect: (e: Event) => {
          e.preventDefault()
          colorMode.value = colorMode.value === 'dark' ? 'light' : 'dark'
        }
      }
    ]
  ]
})

const groups = computed(() => [{
  id: 'links',
  label: 'Go to',
  items: links.value.flat()
}, {
  id: 'code',
  label: 'Code',
  items: [{
    id: 'source',
    label: 'View page source',
    icon: 'simple-icons:github',
    to: `https://github.com/nuxt-ui-templates/dashboard-vue/blob/main/src/pages${route.path === '/' ? '/index' : route.path}.vue`,
    target: '_blank'
  }]
}])

const cookie = useStorage('cookie-consent', 'pending')
if (cookie.value !== 'accepted') {
  toast.add({
    title: 'We use first-party cookies to enhance your experience on our website.',
    duration: 0,
    close: false,
    actions: [{
      label: 'Accept',
      color: 'neutral',
      variant: 'outline',
      onClick: () => {
        cookie.value = 'accepted'
      }
    }, {
      label: 'Opt out',
      color: 'neutral',
      variant: 'ghost'
    }]
  })
}
</script>

<template>
  <Suspense>
    <UDashboardGroup unit="rem" storage="local">
      <UDashboardSidebar
        id="default"
        v-model:open="open"
        collapsible
        resizable
        class="bg-elevated/25"
        :ui="{ footer: 'lg:border-t lg:border-default' }"
      >
        <template #header="{ collapsed }">
          <TeamsMenu :collapsed="collapsed" />
        </template>

        <template #default="{ collapsed }">
          <!-- <UDashboardSearchButton :collapsed="collapsed" class="bg-transparent ring-default" /> -->

          <UNavigationMenu
            :collapsed="collapsed"
            :items="links[0]"
            orientation="vertical"
            tooltip
            popover
          />

          <UNavigationMenu
            :collapsed="collapsed"
            :items="links[1]"
            orientation="vertical"
            tooltip
            class="mt-auto"
          />
        </template>

        <template #footer="{ collapsed }">
          <UserMenu :collapsed="collapsed" />
        </template>
      </UDashboardSidebar>

      <UDashboardSearch :groups="groups" />

      <RouterView />

      <NotificationsSlideover />
    </UDashboardGroup>
  </Suspense>
</template>
