<template>
    <div>
        <PageHeader
            title="Facility Management"
            subtitle="Manage property facilities."
            :breadcrumbs="[{ label: 'Facilities' }]"
        >
            <template #actions>
                <AppButton :to="{ name: 'admin.facilities.create' }">
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
                    Add Facility
                </AppButton>
            </template>
        </PageHeader>

        <!-- Loading -->
        <SkeletonLoader v-if="loading" variant="list" :rows="4" />

        <!-- Empty State -->
        <EmptyState
            v-else-if="!facilities.length"
            title="No facilities yet"
            message="Add your first facility to get started."
            action-label="Add Facility"
            :action-to="{ name: 'admin.facilities.create' }"
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
                        <div class="flex items-center justify-between gap-2">
                            <p
                                class="text-sm font-medium text-gray-900 truncate"
                            >
                                {{ facility.name }}
                            </p>
                            <StatusBadge
                                :status="
                                    facility.is_under_maintenance
                                        ? 'suspended'
                                        : facility.is_active
                                          ? 'active'
                                          : 'draft'
                                "
                                :label="
                                    facility.is_under_maintenance
                                        ? 'Maintenance'
                                        : facility.is_active
                                          ? 'Active'
                                          : 'Inactive'
                                "
                            />
                        </div>
                        <div
                            class="mt-0.5 flex items-center gap-2 text-xs text-gray-500"
                        >
                            <span v-if="facility.capacity"
                                >Capacity: {{ facility.capacity }}</span
                            >
                            <span
                                v-if="
                                    facility.capacity && facility.opening_time
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
                    <template #cell-status="{ row }">
                        <StatusBadge
                            :status="
                                row.is_under_maintenance
                                    ? 'suspended'
                                    : row.is_active
                                      ? 'active'
                                      : 'draft'
                            "
                            :label="
                                row.is_under_maintenance
                                    ? 'Maintenance'
                                    : row.is_active
                                      ? 'Active'
                                      : 'Inactive'
                            "
                        />
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
                    <template #cell-actions="{ row }">
                        <div class="flex justify-end gap-1" @click.stop>
                            <RouterLink :to="{ name: 'admin.facilities.show', params: { uuid: row.uuid } }" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:text-gray-600" title="View details" aria-label="View facility details">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.64 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.64 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            </RouterLink>
                            <RouterLink :to="{ name: 'admin.facilities.edit', params: { uuid: row.uuid } }" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:text-gray-600" title="Edit" aria-label="Edit facility">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                            </RouterLink>
                        </div>
                    </template>
                </AppTable>
            </div>
        </template>

        <AppPagination v-if="meta" :meta="meta" @page-change="loadFacilities" />
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { useRouter, RouterLink } from "vue-router";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppButton from "@/components/common/AppButton.vue";
import AppTable from "@/components/common/AppTable.vue";
import AppPagination from "@/components/common/AppPagination.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import EmptyState from "@/components/common/EmptyState.vue";

const router = useRouter();

const columns = [
    { key: "name", label: "Name", sortable: true },
    { key: "capacity", label: "Capacity", sortable: true },
    { key: "hours", label: "Hours", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "actions", label: "" },
];

const facilities = ref([]);
const meta = ref(null);
const loading = ref(true);
const sortBy = ref("name");
const sortDirection = ref("asc");

async function loadFacilities(page = 1) {
    loading.value = true;
    try {
        const sort = sortBy.value === "hours" ? "opening_time" : sortBy.value;
        const { data } = await adminApi.getFacilities({
            page,
            per_page: 10,
            sort,
            direction: sortDirection.value,
        });
        facilities.value = data.data.data;
        meta.value = data.data.meta;
    } catch {
        facilities.value = [];
    } finally {
        loading.value = false;
    }
}

function viewFacility(row) {
    router.push({ name: "admin.facilities.show", params: { uuid: row.uuid } });
}

function sortFacilities(key) {
    if (sortBy.value === key) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
        return;
    }

    sortBy.value = key;
    sortDirection.value = "asc";
}

watch([sortBy, sortDirection], () => loadFacilities(1));

onMounted(() => loadFacilities());
</script>
