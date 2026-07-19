<template>
    <router-link
        :to="to"
        :class="[
            isActive
                ? 'bg-primary-50 text-primary-700'
                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
            mobile
                ? 'whitespace-nowrap rounded-xl px-3 py-2 text-sm font-medium transition-colors'
                : 'inline-flex items-center rounded-xl px-3 py-2 text-sm font-medium transition-colors',
        ]"
    >
        <slot />
    </router-link>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";

const props = defineProps({
    to: { type: [String, Object], required: true },
    mobile: { type: Boolean, default: false },
});

const route = useRoute();
const isActive = computed(() => {
    const targetName = typeof props.to === "object" ? props.to.name : props.to;
    return (
        route.name === targetName || route.name?.startsWith(targetName + ".")
    );
});
</script>
