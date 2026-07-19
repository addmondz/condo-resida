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
                        'rounded-lg px-4 py-1.5 text-sm font-medium transition-all cursor-pointer',
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
                            class="rounded-xl bg-white border border-gray-200 cursor-pointer active:bg-gray-50 transition-colors"
                        >
                            <div
                                class="flex items-start justify-between gap-3 px-5 py-3.5"
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
                            empty-message="No bookings found."
                            @row-click="viewBooking"
                        >
                            <template #cell-facility="{ row }">
                                <span
                                    class="text-sm font-medium text-gray-900"
                                    >{{ row.facility?.name || "-" }}</span
                                >
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
                                    class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors"
                                    @click.stop
                                >
                                    View
                                    <svg
                                        class="h-3.5 w-3.5"
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
    { key: "booking_date", label: "Date" },
    { key: "time", label: "Time" },
    { key: "status", label: "Status" },
    { key: "actions", label: "" },
];

const activeTab = ref("upcoming");
const bookings = ref([]);
const meta = ref(null);
const loading = ref(true);

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
            per_page: 15,
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

onMounted(() => {
    loadBookings();
});
</script>
