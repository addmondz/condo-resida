<template>
    <AppModal :show="show" :title="title" @close="$emit('cancel')">
        <p class="text-sm text-gray-600">{{ message }}</p>
        <template #footer>
            <AppButton variant="secondary" @click="$emit('cancel')"
                >Cancel</AppButton
            >
            <AppButton
                :variant="confirmVariant"
                :loading="loading"
                @click="$emit('confirm')"
            >
                {{ confirmLabel }}
            </AppButton>
        </template>
    </AppModal>
</template>

<script setup>
import AppModal from "./AppModal.vue";
import AppButton from "./AppButton.vue";

defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: "Confirm" },
    message: { type: String, default: "Are you sure?" },
    confirmLabel: { type: String, default: "Confirm" },
    confirmVariant: { type: String, default: "danger" },
    loading: { type: Boolean, default: false },
});

defineEmits(["confirm", "cancel"]);
</script>
