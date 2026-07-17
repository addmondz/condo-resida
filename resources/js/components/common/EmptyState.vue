<template>
  <div class="text-center py-8">
    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
    </svg>
    <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ title }}</h3>
    <p class="mt-1 text-sm text-gray-500">{{ message }}</p>
    <div v-if="actionLabel" class="mt-4">
      <AppButton :to="actionTo" @click="$emit('action')">{{ actionLabel }}</AppButton>
    </div>
  </div>
</template>

<script setup>
import AppButton from './AppButton.vue';

defineProps({
    title: { type: String, default: 'No results' },
    message: { type: String, default: 'No data found.' },
    actionLabel: { type: String, default: '' },
    actionTo: { type: [String, Object], default: null },
});

defineEmits(['action']);
</script>
