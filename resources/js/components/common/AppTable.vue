<template>
  <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th
            v-for="col in columns"
            :key="col.key"
            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
          >
            {{ col.label }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 bg-white">
        <tr v-if="loading">
          <td :colspan="columns.length" class="px-4 py-12 text-center">
            <LoadingState message="Loading..." />
          </td>
        </tr>
        <tr v-else-if="!rows.length">
          <td :colspan="columns.length" class="px-4 py-12 text-center">
            <EmptyState :message="emptyMessage" />
          </td>
        </tr>
        <tr
          v-else
          v-for="(row, idx) in rows"
          :key="row.id || idx"
          class="hover:bg-gray-50 transition-colors cursor-pointer"
          @click="$emit('row-click', row)"
        >
          <td v-for="col in columns" :key="col.key" class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
            <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
              {{ row[col.key] }}
            </slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import LoadingState from './LoadingState.vue';
import EmptyState from './EmptyState.vue';

defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    emptyMessage: { type: String, default: 'No data found.' },
});

defineEmits(['row-click']);
</script>
