<template>
    <div>
        <PageHeader
            title="Booking Details"
            subtitle="Review booking request and approval status."
            :breadcrumbs="[
                { label: 'Bookings', to: { name: 'admin.bookings' } },
                { label: booking.facility?.name || 'Details' },
            ]"
        />

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
                        {{ booking.facility?.name || "Booking" }}
                    </h2>
                    <StatusBadge :status="booking.status" />
                </div>

                <div class="px-5 py-5">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm text-gray-500">Resident</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ booking.resident?.name || "-" }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Date</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ booking.booking_date }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Time</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ booking.start_time }} -
                                {{ booking.end_time }}
                            </dd>
                        </div>
                        <div v-if="booking.notes">
                            <dt class="text-sm text-gray-500">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-700">
                                {{ booking.notes }}
                            </dd>
                        </div>
                        <div v-if="booking.approved_by">
                            <dt class="text-sm text-gray-500">Approved By</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ booking.approved_by }}
                            </dd>
                        </div>
                        <div
                            v-if="booking.rejection_reason"
                            class="sm:col-span-2"
                        >
                            <dt class="text-sm text-gray-500">
                                Rejection Reason
                            </dt>
                            <dd class="mt-1 text-sm font-medium text-red-700">
                                {{ booking.rejection_reason }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatDateTime(booking.created_at) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Actions -->
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <AppButton
                    v-if="booking.status === 'Pending'"
                    @click="showApproveDialog = true"
                    >Approve</AppButton
                >
                <AppButton
                    v-if="booking.status === 'Pending'"
                    variant="danger"
                    @click="showRejectDialog = true"
                    >Reject</AppButton
                >
                <AppButton
                    v-if="['Pending', 'Approved'].includes(booking.status)"
                    variant="secondary"
                    @click="showCancelDialog = true"
                    >Cancel Booking</AppButton
                >
            </div>

            <!-- Approve Confirmation -->
            <ConfirmationDialog
                :show="showApproveDialog"
                title="Approve Booking"
                :message="`Are you sure you want to approve this booking for ${booking.facility?.name || 'this facility'}?`"
                confirm-label="Approve"
                confirm-variant="primary"
                :loading="approving"
                @confirm="approve"
                @cancel="showApproveDialog = false"
            />

            <!-- Cancel Confirmation -->
            <ConfirmationDialog
                :show="showCancelDialog"
                title="Cancel Booking"
                message="Are you sure you want to cancel this booking? This action cannot be undone."
                confirm-label="Cancel Booking"
                confirm-variant="danger"
                :loading="cancelling"
                @confirm="cancelBooking"
                @cancel="showCancelDialog = false"
            />

            <AppModal
                :show="showRejectDialog"
                title="Reject Booking"
                @close="showRejectDialog = false"
            >
                <AppTextarea
                    v-model="rejectReason"
                    label="Reason"
                    placeholder="Enter rejection reason"
                    required
                    :rows="3"
                />
                <template #footer>
                    <AppButton
                        variant="secondary"
                        @click="showRejectDialog = false"
                        >Cancel</AppButton
                    >
                    <AppButton
                        variant="danger"
                        :loading="rejecting"
                        @click="requestRejectConfirmation"
                        >Reject</AppButton
                    >
                </template>
            </AppModal>

            <!-- Reject Confirmation -->
            <ConfirmationDialog
                :show="showRejectConfirmDialog"
                title="Confirm Rejection"
                :message="`Are you sure you want to reject this booking for ${booking.facility?.name || 'this facility'}?`"
                confirm-label="Reject"
                confirm-variant="danger"
                :loading="rejecting"
                @confirm="reject"
                @cancel="cancelRejectConfirmation"
            />
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import adminApi from "@/api/admin";
import AppButton from "@/components/common/AppButton.vue";
import AppModal from "@/components/common/AppModal.vue";
import AppTextarea from "@/components/common/AppTextarea.vue";
import ConfirmationDialog from "@/components/common/ConfirmationDialog.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import { useToast } from "@/composables/useToast";

const props = defineProps({
    uuid: { type: String, required: true },
});

const booking = ref({});
const loading = ref(true);
const error = ref("");
const toast = useToast();

const approving = ref(false);
const rejecting = ref(false);
const cancelling = ref(false);
const showApproveDialog = ref(false);
const showRejectDialog = ref(false);
const showRejectConfirmDialog = ref(false);
const rejectReason = ref("");
const showCancelDialog = ref(false);

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

async function approve() {
    approving.value = true;
    try {
        const { data } = await adminApi.approveBooking(props.uuid);
        booking.value = data.data;
        showApproveDialog.value = false;
        toast.success("Booking approved.");
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to approve.");
    } finally {
        approving.value = false;
    }
}

async function reject() {
    if (!rejectReason.value.trim()) return;
    rejecting.value = true;
    try {
        const { data } = await adminApi.rejectBooking(props.uuid, {
            reason: rejectReason.value,
        });
        booking.value = data.data;
        showRejectConfirmDialog.value = false;
        rejectReason.value = "";
        toast.success("Booking rejected.");
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to reject.");
    } finally {
        rejecting.value = false;
    }
}

function requestRejectConfirmation() {
    if (!rejectReason.value.trim()) return;
    showRejectDialog.value = false;
    showRejectConfirmDialog.value = true;
}

function cancelRejectConfirmation() {
    showRejectConfirmDialog.value = false;
    showRejectDialog.value = true;
}

async function cancelBooking() {
    cancelling.value = true;
    try {
        const { data } = await adminApi.cancelBooking(props.uuid);
        booking.value = data.data;
        showCancelDialog.value = false;
        toast.success("Booking cancelled.");
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to cancel.");
    } finally {
        cancelling.value = false;
    }
}

onMounted(async () => {
    try {
        const { data } = await adminApi.getBooking(props.uuid);
        booking.value = data.data;
    } catch {
        error.value = "Failed to load booking details.";
    } finally {
        loading.value = false;
    }
});
</script>
