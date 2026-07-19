<template>
    <div class="flex flex-col items-center justify-center py-10">
        <div class="relative">
            <div
                class="h-10 w-10 rounded-full border-[3px] border-gray-200"
            ></div>
            <div
                class="absolute inset-0 h-10 w-10 animate-spin rounded-full border-[3px] border-transparent border-t-primary-600"
            ></div>
        </div>
        <p v-if="message" class="mt-4 text-sm font-medium text-gray-500">
            {{ message }}
        </p>
    </div>
</template>

<script setup>
defineProps({
    message: { type: String, default: "" },
});
</script>
