<template>
    <div class="pb-16 lg:pb-0">
        <!-- Loading -->
        <div v-if="loading" class="space-y-4">
            <SkeletonLoader variant="text" :rows="2" />
            <SkeletonLoader variant="card" />
        </div>

        <!-- Error -->
        <div
            v-else-if="error"
            class="flex items-start gap-3 rounded-xl bg-red-50 border border-red-100 px-5 py-4"
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
            <PageHeader
                :title="booking.facility?.name || 'Booking'"
                :breadcrumbs="[
                    { label: 'Bookings', to: { name: 'resident.bookings' } },
                    { label: booking.facility?.name || 'Details' },
                ]"
            >
                <template #actions>
                    <StatusBadge :status="booking.status" />
                </template>
            </PageHeader>

            <!-- Booking Details Card -->
            <div class="mb-4 rounded-xl bg-white border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">
                        Booking Details
                    </h2>
                </div>
                <div class="px-5 py-5">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        <div>
                            <dt class="text-sm text-gray-500">Facility</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ booking.facility?.name || "-" }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Date</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatDate(booking.booking_date) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Time</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ booking.start_time }} -
                                {{ booking.end_time }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <StatusBadge :status="booking.status" />
                            </dd>
                        </div>
                        <div v-if="booking.approved_by">
                            <dt class="text-sm text-gray-500">Approved By</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ booking.approved_by }}
                            </dd>
                        </div>
                        <div v-if="booking.approved_at">
                            <dt class="text-sm text-gray-500">Approved At</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatDateTime(booking.approved_at) }}
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
                        <div v-if="booking.cancelled_at">
                            <dt class="text-sm text-gray-500">Cancelled At</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatDateTime(booking.cancelled_at) }}
                            </dd>
                        </div>
                        <div v-if="booking.notes" class="sm:col-span-2">
                            <dt class="text-sm text-gray-500">Notes</dt>
                            <dd
                                class="mt-1 text-sm font-medium text-gray-900 whitespace-pre-line"
                            >
                                {{ booking.notes }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Booked On</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatDateTime(booking.created_at) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Facility Link -->
            <div v-if="booking.facility?.uuid" class="mb-4">
                <router-link
                    :to="{
                        name: 'resident.facilities.show',
                        params: { uuid: booking.facility.uuid },
                    }"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-3 text-[13px] font-medium leading-5 text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    <svg
                        class="h-4 w-4 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21"
                        />
                    </svg>
                    View Facility Details
                    <svg
                        class="h-3.5 w-3.5 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m8.25 4.5 7.5 7.5-7.5 7.5"
                        />
                    </svg>
                </router-link>
            </div>

            <!-- Cancel Button -->
            <div v-if="canCancel" class="mt-6">
                <AppButton
                    variant="danger"
                    @click="showCancelDialog = true"
                    class="w-full sm:w-auto"
                >
                    Cancel Booking
                </AppButton>
            </div>

            <!-- Cancel Dialog -->
            <AppModal
                :show="showCancelDialog"
                title="Cancel Booking"
                @close="showCancelDialog = false"
            >
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to cancel this booking? This action
                    cannot be undone.
                </p>
                <template #footer>
                    <AppButton
                        variant="secondary"
                        @click="showCancelDialog = false"
                        >Keep Booking</AppButton
                    >
                    <AppButton
                        variant="danger"
                        :loading="cancelling"
                        @click="handleCancel"
                        >Cancel Booking</AppButton
                    >
                </template>
            </AppModal>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import residentApi from "@/api/resident";
import { useToast } from "@/composables/useToast";
import PageHeader from "@/components/common/PageHeader.vue";
import AppButton from "@/components/common/AppButton.vue";
import AppModal from "@/components/common/AppModal.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";

const props = defineProps({
    uuid: { type: String, required: true },
});

const toast = useToast();

const booking = ref({});
const loading = ref(true);
const error = ref("");
const showCancelDialog = ref(false);
const cancelling = ref(false);

const canCancel = computed(() =>
    ["Pending", "Approved"].includes(booking.value.status),
);

function formatDate(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("en-MY", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
}

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
        const { data } = await residentApi.cancelBooking(props.uuid);
        booking.value = data.data;
        showCancelDialog.value = false;
        toast.success("Booking cancelled successfully.");
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to cancel booking.");
    } finally {
        cancelling.value = false;
    }
}

onMounted(async () => {
    try {
        const { data } = await residentApi.getBooking(props.uuid);
        booking.value = data.data;
    } catch {
        error.value = "Failed to load booking details.";
    } finally {
        loading.value = false;
    }
});
</script>
