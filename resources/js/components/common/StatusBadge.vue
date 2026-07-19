<template>
    <span
        :class="[
            'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold',
            colorClasses,
        ]"
    >
        <span class="h-1.5 w-1.5 rounded-full" :class="dotClass"></span>
        {{ label || displayStatus }}
    </span>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    status: { type: String, required: true },
    label: { type: String, default: "" },
});

const statusConfig = {
    active: { bg: "bg-green-50 text-green-700", dot: "bg-green-500" },
    approved: { bg: "bg-green-50 text-green-700", dot: "bg-green-500" },
    completed: { bg: "bg-green-50 text-green-700", dot: "bg-green-500" },
    checked_in: { bg: "bg-blue-50 text-blue-700", dot: "bg-blue-500" },
    checked_out: { bg: "bg-gray-100 text-gray-600", dot: "bg-gray-400" },
    pending: { bg: "bg-amber-50 text-amber-700", dot: "bg-amber-500" },
    rejected: { bg: "bg-red-50 text-red-700", dot: "bg-red-500" },
    cancelled: { bg: "bg-red-50 text-red-700", dot: "bg-red-500" },
    suspended: { bg: "bg-orange-50 text-orange-700", dot: "bg-orange-500" },
    expired: { bg: "bg-gray-100 text-gray-500", dot: "bg-gray-400" },
    draft: { bg: "bg-gray-100 text-gray-500", dot: "bg-gray-400" },
    published: { bg: "bg-green-50 text-green-700", dot: "bg-green-500" },
    archived: { bg: "bg-gray-100 text-gray-500", dot: "bg-gray-400" },
    upcoming: { bg: "bg-blue-50 text-blue-700", dot: "bg-blue-500" },
};

const defaultConfig = { bg: "bg-gray-100 text-gray-700", dot: "bg-gray-400" };

const normalized = computed(() =>
    props.status?.toLowerCase().replace(/\s+/g, "_"),
);
const config = computed(() => statusConfig[normalized.value] || defaultConfig);
const colorClasses = computed(() => config.value.bg);
const dotClass = computed(() => config.value.dot);

const displayStatus = computed(() => {
    const s = normalized.value || "";
    return s.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
});
</script>
