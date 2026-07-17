<template>
  <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <p class="text-sm text-gray-700">
        Showing <span class="font-medium">{{ meta.from }}</span> to <span class="font-medium">{{ meta.to }}</span>
        of <span class="font-medium">{{ meta.total }}</span> results
      </p>
      <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
        <button
          v-for="page in visiblePages"
          :key="page"
          :disabled="page === '...'"
          :class="[
            page === meta.current_page
              ? 'z-10 bg-primary-600 text-white focus-visible:outline-primary-600'
              : page === '...'
                ? 'cursor-default text-gray-700 bg-white'
                : 'text-gray-900 bg-white hover:bg-gray-50',
            'relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300',
          ]"
          @click="page !== '...' && $emit('page-change', page)"
        >
          {{ page }}
        </button>
      </nav>
    </div>
    <div class="flex flex-1 justify-between sm:hidden">
      <button
        :disabled="!meta.prev_page_url"
        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
        @click="$emit('page-change', meta.current_page - 1)"
      >Previous</button>
      <button
        :disabled="!meta.next_page_url"
        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
        @click="$emit('page-change', meta.current_page + 1)"
      >Next</button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    meta: { type: Object, default: null },
});

defineEmits(['page-change']);

const visiblePages = computed(() => {
    if (!props.meta) return [];
    const { current_page, last_page } = props.meta;
    const pages = [];
    const delta = 2;

    for (let i = 1; i <= last_page; i++) {
        if (i === 1 || i === last_page || (i >= current_page - delta && i <= current_page + delta)) {
            pages.push(i);
        } else if (pages[pages.length - 1] !== '...') {
            pages.push('...');
        }
    }
    return pages;
});
</script>
