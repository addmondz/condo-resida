<template>
    <div>
        <PageHeader
            title="Booking Management"
            subtitle="Manage facility bookings and approvals."
            :breadcrumbs="[{ label: 'Bookings' }]"
        />

        <!-- Status Tabs -->
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="inline-flex rounded-xl bg-gray-100 p-1 overflow-x-auto">
                <button
                    v-for="tab in statusTabs"
                    :key="tab.value"
                    @click="statusFilter = tab.value"
                    :class="[
                        'rounded-lg px-4 py-1.5 text-sm font-medium transition-all cursor-pointer whitespace-nowrap',
                        statusFilter === tab.value
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-500 hover:text-gray-700',
                    ]"
                >
                    {{ tab.label }}
                </button>
            </div>
            <AppInput v-model="dateFilter" type="date" class="w-40" />
        </div>

        <Transition name="page-fade" mode="out-in">
            <div :key="statusFilter + '-' + loading">
                <!-- Loading -->
                <SkeletonLoader v-if="loading" variant="list" :rows="5" />

                <!-- Empty State -->
                <EmptyState
                    v-else-if="!bookings.length"
                    title="No bookings found"
                    message="Try adjusting your filter criteria."
                >
                    <template #icon>
                        <svg
                            class="h-6 w-6 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"
                            />
                        </svg>
                    </template>
                </EmptyState>

                <template v-else>
                    <!-- Mobile: Card List -->
                    <div class="space-y-2 sm:hidden">
                        <div
                            v-for="booking in bookings"
                            :key="booking.uuid"
                            @click="viewBooking(booking)"
                            class="flex items-center gap-3 rounded-xl bg-white border border-gray-200 px-5 py-3.5 active:bg-gray-50 cursor-pointer transition-colors"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-sm font-semibold text-blue-600"
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
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"
                                    />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex items-center justify-between gap-2"
                                >
                                    <p
                                        class="text-sm font-medium text-gray-900 truncate"
                                    >
                                        {{
                                            booking.facility?.name || "Facility"
                                        }}
                                    </p>
                                    <StatusBadge :status="booking.status" />
                                </div>
                                <div
                                    class="mt-0.5 flex items-center gap-2 text-xs text-gray-500"
                                >
                                    <span class="truncate">{{
                                        booking.resident?.name || "-"
                                    }}</span>
                                    <span>&middot;</span>
                                    <span class="shrink-0">{{
                                        formatDate(booking.booking_date)
                                    }}</span>
                                </div>
                            </div>
                            <svg
                                class="h-4 w-4 shrink-0 text-gray-300"
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
                        </div>
                    </div>

                    <!-- Desktop: Table -->
                    <div class="hidden sm:block">
                        <AppTable
                            :columns="columns"
                            :rows="bookings"
                            :loading="loading"
                            empty-message="No bookings found."
                            @row-click="viewBooking"
                        >
                            <template #cell-facility="{ row }">
                                {{ row.facility?.name || "-" }}
                            </template>
                            <template #cell-resident="{ row }">
                                {{ row.resident?.name || "-" }}
                            </template>
                            <template #cell-booking_date="{ value }">
                                {{ formatDate(value) }}
                            </template>
                            <template #cell-time="{ row }">
                                {{ row.start_time }} - {{ row.end_time }}
                            </template>
                            <template #cell-status="{ row }">
                                <StatusBadge :status="row.status" />
                            </template>
                            <template #cell-actions="{ row }">
                                <div class="flex gap-2" @click.stop>
                                    <AppButton
                                        v-if="row.status === 'Pending'"
                                        size="xs"
                                        @click="approveBooking(row)"
                                        >Approve</AppButton
                                    >
                                    <AppButton
                                        v-if="row.status === 'Pending'"
                                        size="xs"
                                        variant="danger"
                                        @click="openReject(row)"
                                        >Reject</AppButton
                                    >
                                </div>
                            </template>
                        </AppTable>
                    </div>
                </template>

                <AppPagination
                    v-if="meta"
                    :meta="meta"
                    @page-change="loadBookings"
                />
            </div>
        </Transition>

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
                <AppButton variant="secondary" @click="showRejectDialog = false"
                    >Cancel</AppButton
                >
                <AppButton
                    variant="danger"
                    :loading="rejecting"
                    @click="handleReject"
                    >Reject</AppButton
                >
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppTable from "@/components/common/AppTable.vue";
import AppPagination from "@/components/common/AppPagination.vue";
import AppButton from "@/components/common/AppButton.vue";
import AppModal from "@/components/common/AppModal.vue";
import AppTextarea from "@/components/common/AppTextarea.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import EmptyState from "@/components/common/EmptyState.vue";
import { useToast } from "@/composables/useToast";

const router = useRouter();
const route = useRoute();
const toast = useToast();

const statusTabs = [
    { label: "All", value: "" },
    { label: "Pending", value: "pending" },
    { label: "Approved", value: "approved" },
    { label: "Rejected", value: "rejected" },
    { label: "Cancelled", value: "cancelled" },
    { label: "Completed", value: "completed" },
];

const columns = [
    { key: "facility", label: "Facility" },
    { key: "resident", label: "Resident" },
    { key: "booking_date", label: "Date" },
    { key: "time", label: "Time" },
    { key: "status", label: "Status" },
    { key: "actions", label: "" },
];

const bookings = ref([]);
const meta = ref(null);
const loading = ref(true);
const statusFilter = ref(route.query.status || "");
const dateFilter = ref("");

const showRejectDialog = ref(false);
const rejectTarget = ref(null);
const rejectReason = ref("");
const rejecting = ref(false);

function formatDate(dateStr) {
    if (!dateStr) return "";
    return new Date(dateStr).toLocaleDateString("en-MY", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

async function loadBookings(page = 1) {
    loading.value = true;
    try {
        const params = { page, per_page: 15 };
        if (statusFilter.value) params.status = statusFilter.value;
        if (dateFilter.value) params.date = dateFilter.value;
        const { data } = await adminApi.getBookings(params);
        bookings.value = data.data.data;
        meta.value = data.data.meta;
    } catch {
        bookings.value = [];
    } finally {
        loading.value = false;
    }
}

function viewBooking(row) {
    router.push({ name: "admin.bookings.show", params: { uuid: row.uuid } });
}

async function approveBooking(booking) {
    try {
        await adminApi.approveBooking(booking.uuid);
        toast.success("Booking approved.");
        loadBookings();
    } catch (err) {
        toast.error(
            err.response?.data?.message || "Failed to approve booking.",
        );
    }
}

function openReject(booking) {
    rejectTarget.value = booking;
    rejectReason.value = "";
    showRejectDialog.value = true;
}

async function handleReject() {
    if (!rejectReason.value.trim()) return;
    rejecting.value = true;
    try {
        await adminApi.rejectBooking(rejectTarget.value.uuid, {
            reason: rejectReason.value,
        });
        showRejectDialog.value = false;
        toast.success("Booking rejected.");
        loadBookings();
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to reject booking.");
    } finally {
        rejecting.value = false;
    }
}

watch([statusFilter, dateFilter], () => loadBookings(1));

onMounted(() => loadBookings());
</script>
