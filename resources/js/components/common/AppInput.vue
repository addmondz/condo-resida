<template>
    <div>
        <label
            v-if="label"
            :for="id"
            class="mb-1.5 block text-sm font-medium text-gray-700"
        >
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="relative">
            <div
                v-if="$slots.prefix"
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"
            >
                <slot name="prefix" />
            </div>
            <input
                :id="id"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :min="min"
                :max="max"
                :class="[
                    'block w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm transition-colors',
                    'placeholder:text-gray-400',
                    'focus:outline-none focus:ring-2 focus:border-primary-400 focus:ring-primary-100',
                    'disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed',
                    error
                        ? 'border-red-300 text-red-900 focus:border-red-400 focus:ring-red-100'
                        : 'border-gray-300 text-gray-900',
                    $slots.prefix ? 'pl-10' : '',
                ]"
                @input="$emit('update:modelValue', $event.target.value)"
            />
        </div>
        <p v-if="error" class="mt-1.5 text-sm text-red-600">{{ error }}</p>
        <p v-else-if="hint" class="mt-1.5 text-sm text-gray-500">{{ hint }}</p>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    modelValue: { type: [String, Number], default: "" },
    label: { type: String, default: "" },
    type: { type: String, default: "text" },
    placeholder: { type: String, default: "" },
    error: { type: String, default: "" },
    hint: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    min: { type: String, default: undefined },
    max: { type: String, default: undefined },
});

defineEmits(["update:modelValue"]);

const id = computed(
    () =>
        `input-${props.label?.toLowerCase().replace(/\s+/g, "-") || Math.random().toString(36).slice(2)}`,
);
</script>
