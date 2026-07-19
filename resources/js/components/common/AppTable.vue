<template>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"
                        >
                            {{ col.label }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="loading">
                        <td
                            :colspan="columns.length"
                            class="px-4 py-12 text-center"
                        >
                            <SkeletonLoader variant="table" :rows="5" />
                        </td>
                    </tr>
                    <tr v-else-if="!rows.length">
                        <td
                            :colspan="columns.length"
                            class="px-4 py-12 text-center"
                        >
                            <EmptyState :message="emptyMessage" />
                        </td>
                    </tr>
                    <tr
                        v-else
                        v-for="(row, idx) in rows"
                        :key="row.id || row.uuid || idx"
                        class="group transition-colors hover:bg-gray-50/60"
                        :class="{ 'cursor-pointer': hasRowClick }"
                        @click="hasRowClick && $emit('row-click', row)"
                    >
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            class="whitespace-nowrap px-4 py-3.5 text-sm text-gray-700"
                        >
                            <slot
                                :name="`cell-${col.key}`"
                                :row="row"
                                :value="getValue(row, col.key)"
                            >
                                {{ getValue(row, col.key) }}
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { computed, useAttrs } from "vue";
import SkeletonLoader from "./SkeletonLoader.vue";
import EmptyState from "./EmptyState.vue";

defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    emptyMessage: { type: String, default: "No data found." },
});

const emit = defineEmits(["row-click"]);
const attrs = useAttrs();
const hasRowClick = computed(
    () => !!attrs["onRow-click"] || !!attrs.onRowClick,
);

function getValue(row, key) {
    return key.split(".").reduce((value, part) => value?.[part], row) ?? "";
}
</script>
