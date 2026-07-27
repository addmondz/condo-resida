<template>
    <RouterLink v-if="to && !disabled && !loading" :to="to" :class="classes">
        <slot />
    </RouterLink>
    <button
        v-else
        :type="type"
        :disabled="disabled || loading"
        :class="classes"
    >
        <svg
            v-if="loading"
            class="h-4 w-4 animate-spin"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            />
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
        </svg>
        <span v-if="!loading"><slot /></span>
    </button>
</template>

<script setup>
import { computed } from "vue";
import { RouterLink } from "vue-router";

const props = defineProps({
    variant: { type: String, default: "primary" },
    size: { type: String, default: "md" },
    type: { type: String, default: "button" },
    disabled: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    to: { type: [String, Object], default: null },
});

const baseClasses =
    "relative inline-flex items-center justify-center gap-2 font-medium rounded-[10px] transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:pointer-events-none cursor-pointer";

const variantClasses = {
    primary:
        "bg-primary-600 text-white hover:bg-primary-700 focus-visible:ring-primary-500 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.1),0_1px_3px_0_rgba(0,0,0,0.1),0_1px_2px_-1px_rgba(0,0,0,0.1)] hover:shadow-[inset_0_1px_0_0_rgba(255,255,255,0.1),0_4px_6px_-1px_rgba(0,0,0,0.1),0_2px_4px_-2px_rgba(0,0,0,0.1)] active:scale-[0.98] active:shadow-[inset_0_1px_0_0_rgba(255,255,255,0.05),0_1px_2px_0_rgba(0,0,0,0.05)]",
    secondary:
        "bg-white text-zinc-700 border border-zinc-200 hover:bg-zinc-50 hover:border-zinc-300 focus-visible:ring-primary-500 shadow-sm active:scale-[0.98]",
    danger:
        "bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-500 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.1),0_1px_3px_0_rgba(0,0,0,0.1)] active:scale-[0.98]",
    success:
        "bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:ring-emerald-500 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.1),0_1px_3px_0_rgba(0,0,0,0.1)] active:scale-[0.98]",
    ghost:
        "text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 focus-visible:ring-zinc-500 active:scale-[0.98]",
};

const sizeClasses = {
    xs: "px-2.5 py-1.5 text-[12px]",
    sm: "px-3 py-1.5 text-[13px]",
    md: "px-4 py-2.5 text-[14px]",
    lg: "px-5 py-2.5 text-[14px]",
    xl: "px-6 py-3 text-[15px]",
};

const classes = computed(() => [
    baseClasses,
    variantClasses[props.variant],
    sizeClasses[props.size],
]);
</script>
