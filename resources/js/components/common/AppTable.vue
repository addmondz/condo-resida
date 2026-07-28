<template>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500"
                        >
                            <button
                                v-if="col.sortable"
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-md text-[11px] font-semibold uppercase tracking-wider text-gray-500 transition-colors hover:text-gray-800"
                                @click="$emit('sort', col.key)"
                            >
                                {{ col.label }}
                                <svg
                                    class="h-3.5 w-3.5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    :class="
                                        sortKey === col.key
                                            ? 'text-primary-600'
                                            : 'text-gray-300'
                                    "
                                >
                                    <path
                                        v-if="
                                            sortKey === col.key &&
                                            sortDirection === 'desc'
                                        "
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m19.5 8.25-7.5 7.5-7.5-7.5"
                                    />
                                    <path
                                        v-else-if="sortKey === col.key"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m4.5 15.75 7.5-7.5 7.5 7.5"
                                    />
                                    <path
                                        v-else
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m8.25 15 3.75 3.75L15.75 15m-7.5-6L12 5.25 15.75 9"
                                    />
                                </svg>
                            </button>
                            <span v-else>{{ col.label }}</span>
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
    sortKey: { type: String, default: "" },
    sortDirection: { type: String, default: "asc" },
});

const emit = defineEmits(["row-click", "sort"]);
const attrs = useAttrs();
const hasRowClick = computed(
    () => !!attrs["onRow-click"] || !!attrs.onRowClick,
);

function getValue(row, key) {
    return key.split(".").reduce((value, part) => value?.[part], row) ?? "";
}
</script>
