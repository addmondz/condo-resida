<template>
    <div>
        <PageHeader
            title="Visitor Management"
            subtitle="View and manage all visitor registrations."
            :breadcrumbs="[{ label: 'Visitors' }]"
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
                    placeholder="Search name, reference, contact..."
                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-10 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-100"
                    @keyup.enter="loadVisitors(1)"
                />
                <button
                    v-if="search"
                    @click="
                        search = '';
                        loadVisitors(1);
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
                    v-else-if="!visitors.length"
                    title="No visitors found"
                    message="Try adjusting your search or filter criteria."
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
                                d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.75l.001-.031m11.998 0A5.971 5.971 0 0 0 12 13.5a5.971 5.971 0 0 0-5.999 5.219m11.998 0a8.955 8.955 0 0 0-.919-3.468m-10.16 0a8.955 8.955 0 0 0-.919 3.468M15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                            />
                        </svg>
                    </template>
                </EmptyState>

                <template v-else>
                    <!-- Mobile: Card List -->
                    <div class="space-y-2 sm:hidden">
                        <div
                            v-for="visitor in visitors"
                            :key="visitor.uuid"
                            @click="viewVisitor(visitor)"
                            class="flex items-center gap-3 rounded-xl bg-white border border-gray-200 px-5 py-3.5 active:bg-gray-50 cursor-pointer transition-colors"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-50 text-sm font-semibold text-primary-600"
                            >
                                {{
                                    visitor.visitor_name
                                        ?.charAt(0)
                                        ?.toUpperCase() || "?"
                                }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex items-center justify-between gap-2"
                                >
                                    <p
                                        class="text-sm font-medium text-gray-900 truncate"
                                    >
                                        {{ visitor.visitor_name }}
                                    </p>
                                    <StatusBadge :status="visitor.status" />
                                </div>
                                <div
                                    class="mt-0.5 flex items-center gap-2 text-xs text-gray-500"
                                >
                                    <span class="truncate capitalize">{{
                                        visitor.purpose
                                    }}</span>
                                    <span>&middot;</span>
                                    <span class="shrink-0">{{
                                        formatDate(visitor.visit_date)
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
                            :rows="visitors"
                            :loading="loading"
                            :sort-key="sortBy"
                            :sort-direction="sortDirection"
                            empty-message="No visitors found."
                            @sort="sortVisitors"
                            @row-click="viewVisitor"
                        >
                            <template #cell-visitor_name="{ row }">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-50 text-sm font-semibold text-primary-600"
                                    >
                                        {{
                                            row.visitor_name
                                                ?.charAt(0)
                                                ?.toUpperCase() || "?"
                                        }}
                                    </div>
                                    <span
                                        class="min-w-0 truncate text-sm font-medium text-gray-900"
                                    >
                                        {{ row.visitor_name }}
                                    </span>
                                </div>
                            </template>
                            <template #cell-visit_date="{ value }">
                                {{ formatDate(value) }}
                            </template>
                            <template #cell-resident="{ row }">
                                {{ row.resident?.name || "-" }}
                            </template>
                            <template #cell-status="{ row }">
                                <StatusBadge :status="row.status" />
                            </template>
                            <template #cell-actions="{ row }">
                                <div class="flex justify-end gap-1" @click.stop>
                                    <RouterLink :to="{ name: 'admin.visitors.show', params: { uuid: row.uuid } }" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:text-gray-600" title="View details" aria-label="View visitor details">
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
                    @page-change="loadVisitors"
                />
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { useRouter, RouterLink } from "vue-router";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppTable from "@/components/common/AppTable.vue";
import AppPagination from "@/components/common/AppPagination.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import EmptyState from "@/components/common/EmptyState.vue";

const router = useRouter();

const statusTabs = [
    { label: "All", value: "" },
    { label: "Active", value: "active" },
    { label: "Checked In", value: "checked_in" },
    { label: "Checked Out", value: "checked_out" },
    { label: "Expired", value: "expired" },
    { label: "Cancelled", value: "cancelled" },
];

const columns = [
    { key: "visitor_name", label: "Visitor", sortable: true },
    { key: "purpose", label: "Purpose", sortable: true },
    { key: "resident", label: "Registered By" },
    { key: "visit_date", label: "Visit Date", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "actions", label: "" },
];

const visitors = ref([]);
const meta = ref(null);
const loading = ref(true);
const search = ref("");
const statusFilter = ref("");
const dateFilter = ref("");
const sortBy = ref("visit_date");
const sortDirection = ref("desc");

function formatDate(dateStr) {
    if (!dateStr) return "";
    return new Date(dateStr).toLocaleDateString("en-MY", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

async function loadVisitors(page = 1) {
    loading.value = true;
    try {
        const params = { page, per_page: 10 };
        if (search.value) params.search = search.value;
        if (statusFilter.value) params.status = statusFilter.value;
        if (dateFilter.value) params.date = dateFilter.value;
        params.sort = sortBy.value;
        params.direction = sortDirection.value;
        const { data } = await adminApi.getVisitors(params);
        visitors.value = data.data.data;
        meta.value = data.data.meta;
    } catch {
        visitors.value = [];
    } finally {
        loading.value = false;
    }
}

function viewVisitor(row) {
    router.push({ name: "admin.visitors.show", params: { uuid: row.uuid } });
}

function sortVisitors(key) {
    if (sortBy.value === key) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
        return;
    }

    sortBy.value = key;
    sortDirection.value = "asc";
}

watch([statusFilter, dateFilter, sortBy, sortDirection], () => loadVisitors(1));

onMounted(() => loadVisitors());
</script>
