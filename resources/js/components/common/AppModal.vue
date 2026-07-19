<template>
    <Teleport to="body">
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"
                        @click="$emit('close')"
                    />
                    <Transition
                        enter-active-class="duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 translate-y-2"
                    >
                        <div
                            v-if="show"
                            :class="[
                                'relative w-full transform rounded-2xl bg-white shadow-xl transition-all',
                                sizeClass,
                            ]"
                        >
                            <div
                                v-if="title"
                                class="flex items-center justify-between border-b border-gray-100 px-6 py-4"
                            >
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ title }}
                                </h3>
                                <button
                                    @click="$emit('close')"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 18 18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <div class="px-6 py-5">
                                <slot />
                            </div>
                            <div
                                v-if="$slots.footer"
                                class="flex justify-end gap-3 border-t border-gray-100 px-6 py-4"
                            >
                                <slot name="footer" />
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: "" },
    size: { type: String, default: "md" },
});

defineEmits(["close"]);

const sizeClass = computed(
    () =>
        ({
            sm: "max-w-sm",
            md: "max-w-lg",
            lg: "max-w-2xl",
            xl: "max-w-4xl",
        })[props.size],
);
</script>
