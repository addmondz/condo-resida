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
                        'rounded-lg px-4 py-1.5 text-[13px] font-medium leading-5 transition-all cursor-pointer whitespace-nowrap',
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
                            :sort-key="sortBy"
                            :sort-direction="sortDirection"
                            empty-message="No bookings found."
                            @sort="sortBookings"
                            @row-click="viewBooking"
                        >
                            <template #cell-facility="{ row }">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600"
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
                                    <span
                                        class="min-w-0 truncate text-sm font-medium text-gray-900"
                                    >
                                        {{ row.facility?.name || "-" }}
                                    </span>
                                </div>
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
                                <div class="flex justify-end gap-1" @click.stop>
                                    <RouterLink :to="{ name: 'admin.bookings.show', params: { uuid: row.uuid } }" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:text-gray-600" title="View details" aria-label="View booking details">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.64 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.64 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    </RouterLink>
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

    </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { useRouter, useRoute, RouterLink } from "vue-router";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppTable from "@/components/common/AppTable.vue";
import AppPagination from "@/components/common/AppPagination.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import EmptyState from "@/components/common/EmptyState.vue";

const router = useRouter();
const route = useRoute();

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
    { key: "booking_date", label: "Date", sortable: true },
    { key: "time", label: "Time", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "actions", label: "" },
];

const bookings = ref([]);
const meta = ref(null);
const loading = ref(true);
const statusFilter = ref(route.query.status || "");
const dateFilter = ref("");
const sortBy = ref("booking_date");
const sortDirection = ref("desc");

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
        const params = { page, per_page: 10 };
        if (statusFilter.value) params.status = statusFilter.value;
        if (dateFilter.value) params.date = dateFilter.value;
        params.sort = sortBy.value === "time" ? "start_time" : sortBy.value;
        params.direction = sortDirection.value;
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

function sortBookings(key) {
    if (sortBy.value === key) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
        return;
    }

    sortBy.value = key;
    sortDirection.value = "asc";
}

watch([statusFilter, dateFilter, sortBy, sortDirection], () => loadBookings(1));

onMounted(() => loadBookings());
</script>
