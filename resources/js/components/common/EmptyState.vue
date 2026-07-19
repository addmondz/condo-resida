<template>
    <div
        class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center"
    >
        <div
            class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 mb-4"
        >
            <slot name="icon">
                <svg
                    class="h-6 w-6 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"
                    />
                </svg>
            </slot>
        </div>
        <h3 class="text-sm font-semibold text-gray-900">{{ title }}</h3>
        <p class="mt-1 max-w-sm text-sm text-gray-500">{{ message }}</p>
        <div v-if="actionLabel" class="mt-5">
            <AppButton :to="actionTo" size="sm" @click="$emit('action')">
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
                        d="M12 4.5v15m7.5-7.5h-15"
                    />
                </svg>
                {{ actionLabel }}
            </AppButton>
        </div>
    </div>
</template>

<script setup>
import AppButton from "./AppButton.vue";

defineProps({
    title: { type: String, default: "No results" },
    message: { type: String, default: "No data found." },
    actionLabel: { type: String, default: "" },
    actionTo: { type: [String, Object], default: null },
});

defineEmits(["action"]);
</script>
