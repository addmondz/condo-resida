<template>
  <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', colorClasses]">
    {{ label || status }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, required: true },
    label: { type: String, default: '' },
});

const statusColors = {
    active: 'bg-green-100 text-green-800',
    approved: 'bg-green-100 text-green-800',
    completed: 'bg-green-100 text-green-800',
    checked_in: 'bg-blue-100 text-blue-800',
    checked_out: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
    rejected: 'bg-red-100 text-red-800',
    cancelled: 'bg-red-100 text-red-800',
    suspended: 'bg-orange-100 text-orange-800',
    expired: 'bg-gray-100 text-gray-600',
    draft: 'bg-gray-100 text-gray-600',
    published: 'bg-green-100 text-green-800',
    archived: 'bg-gray-100 text-gray-500',
};

const colorClasses = computed(() => statusColors[props.status] || 'bg-gray-100 text-gray-800');
</script>
