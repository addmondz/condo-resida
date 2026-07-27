<template>
    <div>
        <label
            v-if="label"
            :for="id"
            class="mb-2 block text-[13px] font-medium text-zinc-700"
        >
            {{ label }}
        </label>
        <div class="relative">
            <div
                v-if="$slots.prefix"
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-400"
            >
                <slot name="prefix" />
            </div>
            <input
                :id="id"
                ref="inputRef"
                :type="computedType"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :min="min"
                :max="max"
                :class="[
                    'block w-full rounded-[10px] border bg-white px-3.5 py-2.5 text-[14px] text-zinc-900 shadow-sm shadow-zinc-900/[0.04] transition-all duration-200',
                    'placeholder:text-zinc-400',
                    'hover:border-zinc-300',
                    'focus:border-primary-500 focus:outline-none focus:ring-[3px] focus:ring-primary-500/[0.08]',
                    'disabled:bg-zinc-50 disabled:text-zinc-400 disabled:cursor-not-allowed disabled:shadow-none',
                    error
                        ? 'border-red-300 focus:border-red-400 focus:ring-red-500/[0.08]'
                        : 'border-zinc-200',
                    $slots.prefix ? 'pl-10' : '',
                    isPassword ? 'pr-10' : '',
                ]"
                @input="$emit('update:modelValue', $event.target.value)"
                @keydown="handleKeydown"
                @focus="onFocus"
                @blur="onBlur"
            />
            <button
                v-if="isPassword"
                type="button"
                tabindex="-1"
                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-zinc-400 transition-colors hover:text-zinc-600"
                @click="togglePasswordVisibility"
            >
                <svg v-if="!showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
            </button>
        </div>
        <div v-if="capsLockOn && isFocused && isPassword" class="mt-1.5 flex items-center gap-1.5 text-[12px] text-amber-600">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            Caps Lock is on
        </div>
        <p v-if="error" class="mt-1.5 text-[12px] text-red-500">{{ error }}</p>
        <p v-else-if="hint" class="mt-1.5 text-[12px] text-zinc-400">{{ hint }}</p>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";

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

const inputRef = ref(null);
const showPassword = ref(false);
const capsLockOn = ref(false);
const isFocused = ref(false);

const isPassword = computed(() => props.type === "password");

const computedType = computed(() => {
    if (isPassword.value && showPassword.value) return "text";
    return props.type;
});

const id = computed(
    () =>
        `input-${props.label?.toLowerCase().replace(/\s+/g, "-") || Math.random().toString(36).slice(2)}`,
);

function togglePasswordVisibility() {
    showPassword.value = !showPassword.value;
}

function handleKeydown(event) {
    if (typeof event.getModifierState === "function") {
        capsLockOn.value = event.getModifierState("CapsLock");
    }
}

function onFocus() {
    isFocused.value = true;
}

function onBlur() {
    isFocused.value = false;
}
</script>
