<template>
    <div>
        <PageHeader
            title="Visitor Details"
            subtitle="Review visitor pass information and activity."
            :breadcrumbs="[
                { label: 'Visitors', to: { name: 'admin.visitors' } },
                { label: visitor.visitor_name || 'Details' },
            ]"
        >
            <template #actions>
                <AppButton
                    variant="secondary"
                    :to="{ name: 'admin.visitors' }"
                    size="sm"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"
                        />
                    </svg>
                    Back to Visitors
                </AppButton>
            </template>
        </PageHeader>

        <SkeletonLoader
            v-if="loading"
            variant="form"
            :rows="5"
            container-class="rounded-xl border border-gray-200 bg-white px-5 py-5"
        />

        <div
            v-else-if="error"
            class="flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-5 py-4"
        >
            <svg
                class="mt-0.5 h-5 w-5 shrink-0 text-red-500"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"
                />
            </svg>
            <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <template v-else>
            <div class="mb-4 rounded-xl border border-gray-200 bg-white">
                <div
                    class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4"
                >
                    <h2 class="text-base font-semibold text-gray-900">
                        {{ visitor.visitor_name }}
                    </h2>
                    <StatusBadge :status="visitor.status" />
                </div>

                <div class="px-5 py-5">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm text-gray-500">Contact</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.contact_number || "-" }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Purpose</dt>
                            <dd
                                class="mt-1 text-sm font-medium text-gray-900 capitalize"
                            >
                                {{ visitor.purpose }}
                            </dd>
                        </div>
                        <div v-if="visitor.vehicle_number">
                            <dt class="text-sm text-gray-500">Vehicle</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.vehicle_number }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Visit Date</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.visit_date }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Reference</dt>
                            <dd class="mt-1 text-sm font-mono text-gray-900">
                                {{ visitor.reference_number }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Registered By</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.resident?.name || "-" }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Property</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.property_name || "-" }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Unit</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.block_name || ""
                                }}{{
                                    visitor.block_name && visitor.unit_name
                                        ? ", "
                                        : ""
                                }}{{ visitor.unit_name || "-" }}
                            </dd>
                        </div>
                        <div v-if="visitor.checked_in_at">
                            <dt class="text-sm text-gray-500">Checked In</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatDateTime(visitor.checked_in_at)
                                }}{{
                                    visitor.checked_in_by
                                        ? ` by ${visitor.checked_in_by}`
                                        : ""
                                }}
                            </dd>
                        </div>
                        <div v-if="visitor.checked_out_at">
                            <dt class="text-sm text-gray-500">Checked Out</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatDateTime(visitor.checked_out_at)
                                }}{{
                                    visitor.checked_out_by
                                        ? ` by ${visitor.checked_out_by}`
                                        : ""
                                }}
                            </dd>
                        </div>
                        <div
                            v-if="visitor.cancellation_reason"
                            class="sm:col-span-2"
                        >
                            <dt class="text-sm text-gray-500">
                                Cancellation Reason
                            </dt>
                            <dd class="mt-1 text-sm font-medium text-red-700">
                                {{ visitor.cancellation_reason }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Cancel Action -->
            <div v-if="canCancel" class="mb-4">
                <AppButton variant="danger" @click="showCancelDialog = true"
                    >Cancel Visitor Pass</AppButton
                >
            </div>

            <AppModal
                :show="showCancelDialog"
                title="Cancel Visitor Pass"
                @close="showCancelDialog = false"
            >
                <AppTextarea
                    v-model="cancelReason"
                    label="Reason (optional)"
                    placeholder="Enter reason"
                    :rows="3"
                />
                <template #footer>
                    <AppButton
                        variant="secondary"
                        @click="showCancelDialog = false"
                        >Keep</AppButton
                    >
                    <AppButton
                        variant="danger"
                        :loading="cancelling"
                        @click="handleCancel"
                        >Cancel Pass</AppButton
                    >
                </template>
            </AppModal>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import adminApi from "@/api/admin";
import AppButton from "@/components/common/AppButton.vue";
import AppModal from "@/components/common/AppModal.vue";
import AppTextarea from "@/components/common/AppTextarea.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import { useToast } from "@/composables/useToast";

const props = defineProps({
    uuid: { type: String, required: true },
});

const visitor = ref({});
const loading = ref(true);
const error = ref("");
const toast = useToast();
const showCancelDialog = ref(false);
const cancelReason = ref("");
const cancelling = ref(false);

const canCancel = computed(() =>
    ["Active", "Checked In"].includes(visitor.value.status),
);

function formatDateTime(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("en-MY", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

async function handleCancel() {
    cancelling.value = true;
    try {
        const { data } = await adminApi.cancelVisitor(props.uuid, {
            reason: cancelReason.value || undefined,
        });
        visitor.value = data.data;
        showCancelDialog.value = false;
        toast.success("Visitor pass cancelled.");
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to cancel.");
    } finally {
        cancelling.value = false;
    }
}

onMounted(async () => {
    try {
        const { data } = await adminApi.getVisitor(props.uuid);
        visitor.value = data.data;
    } catch {
        error.value = "Failed to load visitor details.";
    } finally {
        loading.value = false;
    }
});
</script>
