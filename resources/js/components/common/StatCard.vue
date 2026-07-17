<template>
  <div class="rounded-lg border border-gray-200 bg-white px-4 py-5 sm:p-6">
    <dt class="truncate text-sm font-medium text-gray-500">{{ label }}</dt>
    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ value }}</dd>
  </div>
</template>

<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
});
</script>
