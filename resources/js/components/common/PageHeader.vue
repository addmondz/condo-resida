<template>
    <div class="mb-6">
        <!-- Breadcrumb -->
        <nav
            v-if="breadcrumbs.length"
            class="mb-2 flex min-w-0 flex-wrap items-center gap-1.5 text-sm text-gray-500"
        >
            <router-link
                :to="{ name: homeRouteName }"
                class="transition-colors hover:text-gray-700"
                >Home</router-link
            >
            <template v-for="(crumb, i) in breadcrumbs" :key="i">
                <svg
                    class="h-3.5 w-3.5 text-gray-400"
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
                <router-link
                    v-if="crumb.to"
                    :to="crumb.to"
                    class="hover:text-gray-700 transition-colors"
                    >{{ crumb.label }}</router-link
                >
                <span v-else class="text-gray-900 font-medium">{{
                    crumb.label
                }}</span>
            </template>
        </nav>
        <div
            class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">
                    {{ title }}
                </h1>
                <p v-if="subtitle" class="mt-0.5 text-sm text-gray-500">
                    {{ subtitle }}
                </p>
            </div>
            <div
                v-if="$slots.actions"
                class="flex flex-wrap items-center gap-2"
            >
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: "" },
    breadcrumbs: { type: Array, default: () => [] },
});

const route = useRoute();
const homeRouteName = computed(() => {
    if (route.name?.startsWith("admin.")) return "admin.dashboard";
    if (route.name?.startsWith("guard.")) return "guard.dashboard";
    return "resident.dashboard";
});
</script>
