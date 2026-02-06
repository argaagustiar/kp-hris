<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
  collapsed?: boolean
}>()

// Data tim sekarang hanya digunakan untuk ditampilkan, tidak untuk menu
const team = ref({
  label: 'HRIS',
  avatar: {
    src: '/logo-kane.webp',
    alt: 'Kane Package'
  }
})

</script>

<template>
  <UButton
    :avatar="team.avatar"
    :label="collapsed ? undefined : team.label"
    color="neutral"
    variant="ghost"
    block
    :square="collapsed"
    class="py-2"
  />
</template>