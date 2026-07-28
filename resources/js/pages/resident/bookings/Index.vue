<template>
    <div class="pb-16 lg:pb-0">
        <PageHeader title="My Bookings" :breadcrumbs="[{ label: 'Bookings' }]">
            <template #actions>
                <AppButton :to="{ name: 'resident.bookings.create' }">
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
                            d="M12 4.5v15m7.5-7.5h-15"
                        />
                    </svg>
                    Book Facility
                </AppButton>
            </template>
        </PageHeader>

        <!-- Tab Filters -->
        <div class="mb-5">
            <div class="inline-flex rounded-xl bg-gray-100 p-1">
                <button
                    v-for="tab in tabs"
                    :key="tab.value"
                    @click="activeTab = tab.value"
                    :class="[
                        'rounded-lg px-4 py-1.5 text-[13px] font-medium leading-5 transition-all cursor-pointer',
                        activeTab === tab.value
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-500 hover:text-gray-700',
                    ]"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <Transition name="page-fade" mode="out-in">
            <div :key="activeTab + '-' + loading">
                <!-- Loading -->
                <template v-if="loading">
                    <div class="sm:hidden">
                        <SkeletonLoader variant="list" :rows="5" />
                    </div>
                    <div class="hidden sm:block">
                        <SkeletonLoader variant="table" :rows="5" />
                    </div>
                </template>

                <!-- Empty -->
                <EmptyState
                    v-else-if="!bookings.length"
                    title="No bookings found"
                    message="You don't have any bookings yet. Browse facilities to make your first booking."
                    action-label="Browse Facilities"
                    :action-to="{ name: 'resident.facilities' }"
                />

                <template v-else>
                    <!-- Mobile: Card List -->
                    <div class="space-y-2.5 sm:hidden">
                        <div
                            v-for="booking in bookings"
                            :key="booking.uuid || booking.id"
                            @click="viewBooking(booking)"
                            class="flex items-center gap-3 rounded-xl bg-white border border-gray-200 px-5 py-3.5 cursor-pointer active:bg-gray-50 transition-colors"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600"
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
                            <div
                                class="flex min-w-0 flex-1 items-start justify-between gap-3"
                            >
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm font-semibold text-gray-900 truncate"
                                    >
                                        {{
                                            booking.facility?.name || "Facility"
                                        }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ formatDate(booking.booking_date) }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ booking.start_time }} -
                                        {{ booking.end_time }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <StatusBadge :status="booking.status" />
                                    <svg
                                        class="h-4 w-4 text-gray-300"
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
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600"
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
                            <template #cell-booking_date="{ value }">
                                <span class="text-sm text-gray-700">{{
                                    formatDate(value)
                                }}</span>
                            </template>
                            <template #cell-time="{ row }">
                                <span class="text-sm text-gray-700"
                                    >{{ row.start_time }} -
                                    {{ row.end_time }}</span
                                >
                            </template>
                            <template #cell-status="{ row }">
                                <StatusBadge :status="row.status" />
                            </template>
                            <template #cell-actions="{ row }">
                                <router-link
                                    :to="{
                                        name: 'resident.bookings.show',
                                        params: { uuid: row.uuid },
                                    }"
                                    class="inline-flex rounded-lg p-1.5 text-gray-400 transition-colors hover:text-gray-600"
                                    title="View details"
                                    aria-label="View booking details"
                                    @click.stop
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
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.64 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.64 0-8.573-3.007-9.963-7.178Z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                        />
                                    </svg>
                                </router-link>
                            </template>
                        </AppTable>
                    </div>

                    <AppPagination :meta="meta" @page-change="loadBookings" />
                </template>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { useRouter } from "vue-router";
import residentApi from "@/api/resident";
import PageHeader from "@/components/common/PageHeader.vue";
import AppButton from "@/components/common/AppButton.vue";
import AppTable from "@/components/common/AppTable.vue";
import AppPagination from "@/components/common/AppPagination.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import EmptyState from "@/components/common/EmptyState.vue";

const router = useRouter();

const tabs = [
    { label: "Upcoming", value: "upcoming" },
    { label: "Past", value: "past" },
];

const columns = [
    { key: "facility", label: "Facility" },
    { key: "booking_date", label: "Date", sortable: true },
    { key: "time", label: "Time", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "actions", label: "" },
];

const activeTab = ref("upcoming");
const bookings = ref([]);
const meta = ref(null);
const loading = ref(true);
const sortBy = ref("booking_date");
const sortDirection = ref("desc");

function formatDate(dateStr) {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    return date.toLocaleDateString("en-MY", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

function viewBooking(row) {
    router.push({ name: "resident.bookings.show", params: { uuid: row.uuid } });
}

async function loadBookings(page = 1) {
    loading.value = true;
    try {
        const { data } = await residentApi.getBookings({
            filter: activeTab.value,
            page,
            per_page: 10,
            sort: sortBy.value === "time" ? "start_time" : sortBy.value,
            direction: sortDirection.value,
        });
        bookings.value = data.data.data;
        meta.value = data.data.meta;
    } catch {
        bookings.value = [];
    } finally {
        loading.value = false;
    }
}

watch(activeTab, () => {
    loadBookings(1);
});

function sortBookings(key) {
    if (sortBy.value === key) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
        return;
    }

    sortBy.value = key;
    sortDirection.value = "asc";
}

watch([sortBy, sortDirection], () => loadBookings(1));

onMounted(() => {
    loadBookings();
});
</script>
