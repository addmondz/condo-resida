<template>
    <AppModal :show="show" size="sm" @close="$emit('cancel')">
        <div class="text-center">
            <div
                class="mx-auto flex h-12 w-12 items-center justify-center rounded-full"
                :class="iconBgClass"
            >
                <svg
                    v-if="confirmVariant === 'danger'"
                    class="h-6 w-6 text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
                    />
                </svg>
                <svg
                    v-else-if="confirmVariant === 'success'"
                    class="h-6 w-6 text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2.5"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4.5 12.75l6 6 9-13.5"
                    />
                </svg>
                <svg
                    v-else
                    class="h-6 w-6 text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"
                    />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-gray-900">
                {{ title }}
            </h3>
            <p class="mt-2 text-sm leading-relaxed text-gray-500">
                {{ message }}
            </p>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-3">
            <AppButton variant="secondary" @click="$emit('cancel')">
                Cancel
            </AppButton>
            <AppButton
                :variant="confirmVariant"
                :loading="loading"
                @click="$emit('confirm')"
            >
                {{ confirmLabel }}
            </AppButton>
        </div>
    </AppModal>
</template>

<script setup>
import { computed } from "vue";
import AppModal from "./AppModal.vue";
import AppButton from "./AppButton.vue";

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: "Confirm" },
    message: { type: String, default: "Are you sure?" },
    confirmLabel: { type: String, default: "Confirm" },
    confirmVariant: { type: String, default: "danger" },
    loading: { type: Boolean, default: false },
});

defineEmits(["confirm", "cancel"]);

const iconBgClass = computed(() => {
    const classes = {
        danger: "bg-red-500",
        success: "bg-emerald-500",
        warning: "bg-amber-500",
        primary: "bg-primary-600",
    };
    return classes[props.confirmVariant] || "bg-primary-600";
});
</script>
