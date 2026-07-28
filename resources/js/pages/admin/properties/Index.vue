<template>
    <div>
        <PageHeader
            title="Properties"
            subtitle="Manage all properties in the system."
            :breadcrumbs="[{ label: 'Properties' }]"
        >
            <template #actions>
                <div class="relative" @click.stop>
                    <button
                        type="button"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-[10px] border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:border-gray-300 hover:bg-gray-50"
                        title="Filter properties"
                        @click="showFilterMenu = !showFilterMenu"
                    >
                        <svg
                            class="h-4 w-4 text-gray-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 20.5v-6.068a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"
                            />
                        </svg>
                        <span class="hidden sm:inline">{{ activeStatusLabel }}</span>
                    </button>

                    <div
                        v-if="showFilterMenu"
                        class="absolute right-0 z-20 mt-2 w-40 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-lg"
                    >
                        <button
                            v-for="tab in statusTabs"
                            :key="tab.value"
                            type="button"
                            class="flex w-full items-center justify-between px-3 py-2 text-left text-sm transition-colors hover:bg-gray-50"
                            :class="
                                statusFilter === tab.value
                                    ? 'font-medium text-gray-900'
                                    : 'text-gray-600'
                            "
                            @click="
                                statusFilter = tab.value;
                                showFilterMenu = false;
                            "
                        >
                            {{ tab.label }}
                            <svg
                                v-if="statusFilter === tab.value"
                                class="h-4 w-4 text-primary-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m4.5 12.75 6 6 9-13.5"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <AppButton :to="{ name: 'admin.properties.create' }">
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
                    Add Property
                </AppButton>
            </template>
        </PageHeader>

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
                    placeholder="Search name or address..."
                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-10 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-100"
                    @keyup.enter="loadProperties(1)"
                />
                <button
                    v-if="search"
                    @click="
                        search = '';
                        loadProperties(1);
                    "
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer"
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

        <Transition name="page-fade" mode="out-in">
            <div :key="statusFilter + '-' + sortBy + '-' + sortDirection + '-' + loading">
                <SkeletonLoader v-if="loading" variant="list" :rows="5" />

                <EmptyState
                    v-else-if="!properties.length"
                    title="No properties found"
                    message="Try adjusting your search, filters, or sort options."
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
                            v-for="property in properties"
                            :key="property.uuid"
                            @click="viewProperty(property)"
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
                                        class="truncate text-sm font-medium text-gray-900"
                                    >
                                        {{ property.name }}
                                    </p>
                                    <StatusBadge
                                        :status="propertyStatus(property)"
                                        :label="property.status || 'active'"
                                    />
                                </div>
                                <div
                                    class="mt-0.5 flex items-center gap-2 text-xs text-gray-500"
                                >
                                    <span class="shrink-0">
                                        {{ property.blocks_count ?? 0 }} blocks
                                    </span>
                                    <span>&middot;</span>
                                    <span class="shrink-0">
                                        {{ property.units_count ?? 0 }} units
                                    </span>
                                </div>
                                <p
                                    v-if="property.address"
                                    class="mt-0.5 truncate text-xs text-gray-500"
                                >
                                    {{ property.address }}
                                </p>
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
                            :rows="properties"
                            :loading="loading"
                            :sort-key="sortBy"
                            :sort-direction="sortDirection"
                            empty-message="No properties found."
                            @sort="sortProperties"
                            @row-click="viewProperty"
                        >
                            <template #cell-name="{ row }">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600"
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
                                                d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"
                                            />
                                        </svg>
                                    </div>
                                    <span
                                        class="truncate font-medium text-gray-900"
                                    >
                                        {{ row.name }}
                                    </span>
                                </div>
                            </template>
                            <template #cell-address="{ value }">
                                <span
                                    class="block max-w-md truncate text-gray-600"
                                >
                                    {{ value || "-" }}
                                </span>
                            </template>
                            <template #cell-blocks_count="{ value }">
                                {{ value ?? 0 }}
                            </template>
                            <template #cell-units_count="{ value }">
                                {{ value ?? 0 }}
                            </template>
                            <template #cell-status="{ row }">
                                <StatusBadge
                                    :status="propertyStatus(row)"
                                    :label="row.status || 'active'"
                                />
                            </template>
                            <template #cell-actions="{ row }">
                                <div class="flex justify-end gap-1" @click.stop>
                                    <RouterLink
                                        :to="{
                                            name: 'admin.properties.show',
                                            params: { uuid: row.uuid },
                                        }"
                                        class="rounded-lg p-1.5 text-gray-400 transition-colors hover:text-gray-600"
                                        title="View details"
                                        aria-label="View property details"
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
                                    </RouterLink>
                                    <RouterLink
                                        :to="{
                                            name: 'admin.properties.edit',
                                            params: { uuid: row.uuid },
                                        }"
                                        class="rounded-lg p-1.5 text-gray-400 transition-colors hover:text-gray-600"
                                        title="Edit"
                                        aria-label="Edit property"
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
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"
                                            />
                                        </svg>
                                    </RouterLink>
                                </div>
                            </template>
                        </AppTable>
                    </div>
                </template>

                <AppPagination
                    v-if="meta"
                    :meta="meta"
                    @page-change="loadProperties"
                />
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { computed, ref, watch, onMounted } from "vue";
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

const statusTabs = [
    { label: "All", value: "" },
    { label: "Active", value: "active" },
    { label: "Inactive", value: "inactive" },
];

const columns = [
    { key: "name", label: "Name", sortable: true },
    { key: "address", label: "Address", sortable: true },
    { key: "blocks_count", label: "Blocks", sortable: true },
    { key: "units_count", label: "Units", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "actions", label: "" },
];

const properties = ref([]);
const meta = ref(null);
const loading = ref(true);
const search = ref("");
const statusFilter = ref("");
const sortBy = ref("name");
const sortDirection = ref("asc");
const showFilterMenu = ref(false);

const activeStatusLabel = computed(
    () =>
        statusTabs.find((tab) => tab.value === statusFilter.value)?.label ||
        "All",
);

function propertyStatus(property) {
    return property.status === "active" ? "approved" : "draft";
}

function sortProperties(key) {
    if (sortBy.value === key) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
        return;
    }

    sortBy.value = key;
    sortDirection.value = "asc";
}

async function loadProperties(page = 1) {
    loading.value = true;
    try {
        const { data } = await adminApi.getProperties({
            page,
                            per_page: 10,
            search: search.value || undefined,
            status: statusFilter.value || undefined,
            sort: sortBy.value,
            direction: sortDirection.value,
        });
        properties.value = data.data.data;
        meta.value = data.data.meta;
    } catch {
        properties.value = [];
        meta.value = null;
    } finally {
        loading.value = false;
    }
}

function viewProperty(row) {
    router.push({ name: "admin.properties.show", params: { uuid: row.uuid } });
}

watch([statusFilter, sortBy, sortDirection], () => loadProperties(1));

onMounted(() => loadProperties());
</script>
