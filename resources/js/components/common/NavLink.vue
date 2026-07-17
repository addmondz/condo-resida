<template>
  <router-link
    :to="to"
    :class="[
      isActive
        ? mobile ? 'bg-primary-50 text-primary-700' : 'border-primary-500 text-gray-900'
        : mobile ? 'text-gray-600 hover:bg-gray-100' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
      mobile
        ? 'rounded-md px-3 py-2 text-sm font-medium whitespace-nowrap'
        : 'inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium',
    ]"
  >
    <slot />
  </router-link>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const props = defineProps({
    to: { type: [String, Object], required: true },
    mobile: { type: Boolean, default: false },
});

const route = useRoute();
const isActive = computed(() => {
    const targetName = typeof props.to === 'object' ? props.to.name : props.to;
    return route.name === targetName || route.name?.startsWith(targetName + '.');
});
</script>
