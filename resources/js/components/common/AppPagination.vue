<template>
    <div
        v-if="meta && meta.last_page > 1"
        class="mt-4 flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3"
    >
        <p class="hidden text-sm text-gray-500 sm:block">
            Showing
            <span class="font-semibold text-gray-700">{{ meta.from }}</span> to
            <span class="font-semibold text-gray-700">{{ meta.to }}</span> of
            <span class="font-semibold text-gray-700">{{ meta.total }}</span>
        </p>
        <p class="text-sm text-gray-500 sm:hidden">
            Page
            <span class="font-semibold text-gray-700">{{
                meta.current_page
            }}</span>
            of
            <span class="font-semibold text-gray-700">{{
                meta.last_page
            }}</span>
        </p>
        <div class="flex items-center gap-1">
            <button
                :disabled="!meta.prev_page_url"
                class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition-colors hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
                @click="$emit('page-change', meta.current_page - 1)"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 19.5 8.25 12l7.5-7.5"
                    />
                </svg>
            </button>
            <button
                v-for="page in visiblePages"
                :key="page"
                :disabled="page === '...'"
                :class="[
                    'hidden h-8 min-w-8 items-center justify-center rounded-lg px-2 text-[13px] font-medium leading-5 transition-colors sm:flex',
                    page === meta.current_page
                        ? 'bg-primary-600 text-white'
                        : page === '...'
                          ? 'cursor-default text-gray-400'
                          : 'text-gray-600 hover:bg-gray-100',
                ]"
                @click="page !== '...' && $emit('page-change', page)"
            >
                {{ page }}
            </button>
            <button
                :disabled="!meta.next_page_url"
                class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition-colors hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
                @click="$emit('page-change', meta.current_page + 1)"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m8.25 4.5 7.5 7.5-7.5 7.5"
                    />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    meta: { type: Object, default: null },
});

defineEmits(["page-change"]);

const visiblePages = computed(() => {
    if (!props.meta) return [];
    const { current_page, last_page } = props.meta;
    const pages = [];
    const delta = 2;

    for (let i = 1; i <= last_page; i++) {
        if (
            i === 1 ||
            i === last_page ||
            (i >= current_page - delta && i <= current_page + delta)
        ) {
            pages.push(i);
        } else if (pages[pages.length - 1] !== "...") {
            pages.push("...");
        }
    }
    return pages;
});
</script>
