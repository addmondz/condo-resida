<template>
    <div>
        <label
            v-if="label"
            :for="id"
            class="mb-1.5 block text-sm font-medium text-gray-700"
        >
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </label>
        <textarea
            :id="id"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :rows="rows"
            :class="[
                'block w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm transition-colors',
                'placeholder:text-gray-400',
                'focus:outline-none focus:ring-2 focus:border-primary-400 focus:ring-primary-100',
                'disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed',
                error
                    ? 'border-red-300 focus:border-red-400 focus:ring-red-100'
                    : 'border-gray-300 text-gray-900',
            ]"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <p v-if="error" class="mt-1.5 text-sm text-red-600">{{ error }}</p>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    modelValue: { type: String, default: "" },
    label: { type: String, default: "" },
    placeholder: { type: String, default: "" },
    rows: { type: [String, Number], default: 3 },
    error: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
});

defineEmits(["update:modelValue"]);

const id = computed(
    () =>
        `textarea-${props.label?.toLowerCase().replace(/\s+/g, "-") || Math.random().toString(36).slice(2)}`,
);
</script>
