<template>
    <div class="animate-pulse" :class="containerClass">
        <template v-if="variant === 'card'">
            <div class="rounded-xl bg-gray-200 h-10 w-10 mb-3"></div>
            <div class="h-6 bg-gray-200 rounded-lg w-1/3 mb-2"></div>
            <div class="h-4 bg-gray-100 rounded-lg w-1/2"></div>
        </template>
        <template v-else-if="variant === 'table'">
            <div class="h-10 bg-gray-100 rounded-lg mb-3"></div>
            <div
                v-for="i in rows"
                :key="i"
                class="h-12 bg-gray-50 rounded-lg mb-2"
            ></div>
        </template>
        <template v-else-if="variant === 'list'">
            <div
                v-for="i in rows"
                :key="i"
                class="flex items-center gap-3 py-3"
            >
                <div class="h-10 w-10 rounded-full bg-gray-200 shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-gray-200 rounded-lg w-3/4"></div>
                    <div class="h-3 bg-gray-100 rounded-lg w-1/2"></div>
                </div>
            </div>
        </template>
        <template v-else-if="variant === 'form'">
            <div v-for="i in rows" :key="i" class="mb-4">
                <div class="h-4 bg-gray-200 rounded-lg w-1/4 mb-2"></div>
                <div class="h-10 bg-gray-100 rounded-lg w-full"></div>
            </div>
        </template>
        <template v-else>
            <div
                v-for="i in rows"
                :key="i"
                class="h-4 bg-gray-200 rounded-lg mb-3"
                :class="i === rows ? 'w-2/3' : 'w-full'"
            ></div>
        </template>
    </div>
</template>

<script setup>
defineProps({
    variant: { type: String, default: "text" },
    rows: { type: Number, default: 3 },
    containerClass: { type: String, default: "" },
});
</script>
