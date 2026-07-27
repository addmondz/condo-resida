<template>
    <div>
        <label
            v-if="label"
            :for="id"
            class="mb-2 block text-[13px] font-medium text-zinc-700"
        >
            {{ label }}
        </label>
        <select
            :id="id"
            :value="modelValue"
            :disabled="disabled"
            :required="required"
            :class="[
                'block w-full rounded-[10px] border bg-white px-3.5 py-2.5 text-[14px] text-zinc-900 shadow-sm shadow-zinc-900/[0.04] transition-all duration-200',
                'hover:border-zinc-300',
                'focus:border-primary-500 focus:outline-none focus:ring-[3px] focus:ring-primary-500/[0.08]',
                'disabled:bg-zinc-50 disabled:text-zinc-400 disabled:cursor-not-allowed disabled:shadow-none',
                error
                    ? 'border-red-300 focus:border-red-400 focus:ring-red-500/[0.08]'
                    : 'border-zinc-200',
            ]"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option v-if="placeholder" value="" disabled>
                {{ placeholder }}
            </option>
            <option
                v-for="opt in normalizedOptions"
                :key="opt.value"
                :value="opt.value"
            >
                {{ opt.label }}
            </option>
        </select>
        <p v-if="error" class="mt-1.5 text-[12px] text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    modelValue: { type: [String, Number], default: "" },
    options: { type: Array, required: true },
    label: { type: String, default: "" },
    placeholder: { type: String, default: "Select an option" },
    error: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
});

defineEmits(["update:modelValue"]);

const id = computed(
    () =>
        `select-${props.label?.toLowerCase().replace(/\s+/g, "-") || Math.random().toString(36).slice(2)}`,
);

const normalizedOptions = computed(() =>
    props.options.map((opt) =>
        typeof opt === "string" ? { value: opt, label: opt } : opt,
    ),
);
</script>
