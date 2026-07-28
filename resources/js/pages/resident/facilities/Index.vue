<template>
    <div class="pb-16 lg:pb-0">
        <PageHeader
            title="Facilities"
            subtitle="Browse and book available facilities."
            :breadcrumbs="[{ label: 'Facilities' }]"
        />

        <!-- Search -->
        <div class="mb-4">
            <div class="relative">
                <svg
                    class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
                    />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search facilities..."
                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-10 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-100"
                />
                <button
                    v-if="search"
                    @click="search = ''"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                    aria-label="Clear search"
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
                            d="M6 18 18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Tab Filters -->
        <div class="mb-4">
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

                <!-- Error -->
                <div
                    v-else-if="error"
                    class="flex items-start gap-3 rounded-xl bg-red-50 border border-red-200 px-5 py-4"
                >
                    <svg
                        class="h-5 w-5 text-red-500 mt-0.5 shrink-0"
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
                    <div>
                        <p class="text-sm font-medium text-red-800">
                            Something went wrong
                        </p>
                        <p class="mt-0.5 text-sm text-red-700">{{ error }}</p>
                    </div>
                </div>

                <!-- Empty -->
                <EmptyState
                    v-else-if="!facilities.length"
                    title="No facilities"
                    message="There are no matching facilities available at the moment."
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
                                d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"
                            />
                        </svg>
                    </template>
                </EmptyState>

                <template v-else>
                    <!-- Mobile: Card List -->
                    <div class="space-y-2 sm:hidden">
                        <div
                            v-for="facility in facilities"
                            :key="facility.uuid"
                            @click="viewFacility(facility)"
                            class="flex items-center gap-3 rounded-xl bg-white border border-gray-200 px-5 py-3.5 active:bg-gray-50 cursor-pointer transition-colors"
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
                                        d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"
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
                                        {{ facility.name }}
                                    </p>
                                    <StatusBadge
                                        :status="
                                            facility.is_under_maintenance
                                                ? 'suspended'
                                                : 'active'
                                        "
                                        :label="
                                            facility.is_under_maintenance
                                                ? 'Maintenance'
                                                : 'Available'
                                        "
                                    />
                                </div>
                                <div
                                    class="mt-0.5 flex items-center gap-2 text-xs text-gray-500"
                                >
                                    <span v-if="facility.capacity"
                                        >Capacity:
                                        {{ facility.capacity }}</span
                                    >
                                    <span
                                        v-if="
                                            facility.capacity &&
                                            facility.opening_time
                                        "
                                        >&middot;</span
                                    >
                                    <span
                                        v-if="
                                            facility.opening_time &&
                                            facility.closing_time
                                        "
                                        >{{ facility.opening_time }} -
                                        {{ facility.closing_time }}</span
                                    >
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
                            :rows="facilities"
                            :loading="loading"
                            :sort-key="sortBy"
                            :sort-direction="sortDirection"
                            empty-message="No facilities found."
                            @sort="sortFacilities"
                            @row-click="viewFacility"
                        >
                            <template #cell-name="{ row }">
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
                                                d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"
                                            />
                                        </svg>
                                    </div>
                                    <span
                                        class="min-w-0 truncate text-sm font-medium text-gray-900"
                                    >
                                        {{ row.name }}
                                    </span>
                                </div>
                            </template>
                            <template #cell-capacity="{ value }">
                                {{ value || "-" }}
                            </template>
                            <template #cell-hours="{ row }">
                                {{
                                    row.opening_time && row.closing_time
                                        ? `${row.opening_time} - ${row.closing_time}`
                                        : "-"
                                }}
                            </template>
                            <template #cell-status="{ row }">
                                <StatusBadge
                                    :status="
                                        row.is_under_maintenance
                                            ? 'suspended'
                                            : 'active'
                                    "
                                    :label="
                                        row.is_under_maintenance
                                            ? 'Maintenance'
                                            : 'Available'
                                    "
                                />
                            </template>
                            <template #cell-actions="{ row }">
                                <router-link
                                    :to="{
                                        name: 'resident.facilities.show',
                                        params: { uuid: row.uuid },
                                    }"
                                    class="inline-flex rounded-lg p-1.5 text-gray-400 transition-colors hover:text-gray-600"
                                    title="View details"
                                    aria-label="View facility details"
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

                    <AppPagination
                        v-if="meta"
                        :meta="meta"
                        @page-change="loadFacilities"
                    />
                </template>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import residentApi from "@/api/resident";
import PageHeader from "@/components/common/PageHeader.vue";
import AppTable from "@/components/common/AppTable.vue";
import AppPagination from "@/components/common/AppPagination.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import EmptyState from "@/components/common/EmptyState.vue";

const router = useRouter();
const route = useRoute();

const tabs = [
    { label: "All", value: "all" },
    { label: "Available", value: "available" },
    { label: "Maintenance", value: "maintenance" },
];

const columns = [
    { key: "name", label: "Name", sortable: true },
    { key: "capacity", label: "Capacity", sortable: true },
    { key: "hours", label: "Hours", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "actions", label: "" },
];

const initialStatus =
    typeof route.query.status === "string" &&
    tabs.some((tab) => tab.value === route.query.status)
        ? route.query.status
        : "all";
const initialSort =
    route.query.sort === "opening_time"
        ? "hours"
        : typeof route.query.sort === "string"
          ? route.query.sort
          : "name";

const activeTab = ref(initialStatus);
const search = ref(
    typeof route.query.search === "string" ? route.query.search : "",
);
const facilities = ref([]);
const meta = ref(null);
const loading = ref(true);
const error = ref("");
const sortBy = ref(initialSort);
const sortDirection = ref(route.query.direction === "desc" ? "desc" : "asc");
let searchTimer = null;

async function loadFacilities(page = 1) {
    loading.value = true;
    error.value = "";

    const sort = sortBy.value === "hours" ? "opening_time" : sortBy.value;
    const params = {
        page,
        per_page: 10,
        sort,
        direction: sortDirection.value,
    };

    if (activeTab.value !== "all") {
        params.status = activeTab.value;
    }

    if (search.value.trim()) {
        params.search = search.value.trim();
    }

    try {
        router.replace({ name: "resident.facilities", query: params });

        const { data } = await residentApi.getFacilities(params);
        facilities.value = data.data.data;
        meta.value = data.data.meta;
    } catch {
        facilities.value = [];
        meta.value = null;
        error.value = "Failed to load facilities. Please try again later.";
    } finally {
        loading.value = false;
    }
}

function viewFacility(row) {
    router.push({
        name: "resident.facilities.show",
        params: { uuid: row.uuid },
    });
}

function sortFacilities(key) {
    if (sortBy.value === key) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
        return;
    }

    sortBy.value = key;
    sortDirection.value = "asc";
}

watch(activeTab, () => loadFacilities(1));
watch([sortBy, sortDirection], () => loadFacilities(1));

watch(search, () => {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => loadFacilities(1), 250);
});

onMounted(() => {
    const page = Number.parseInt(route.query.page, 10) || 1;
    loadFacilities(page);
});
</script>
