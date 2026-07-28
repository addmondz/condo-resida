<template>
    <div class="pb-16 lg:pb-0">
        <PageHeader
            title="Activity Log"
            subtitle="History of all check-in and check-out activities."
            :breadcrumbs="[{ label: 'Activity Log' }]"
        />

        <!-- Filters -->
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
            <AppInput v-model="dateFilter" type="date" class="w-44" />
            <AppSelect
                v-model="actionFilter"
                :options="actionOptions"
                placeholder="All Actions"
                class="w-44"
            />
        </div>

        <!-- Loading -->
        <SkeletonLoader v-if="loading" variant="list" :rows="5" />

        <!-- Empty State -->
        <EmptyState
            v-else-if="!logs.length"
            title="No activity logs"
            message="Activity logs will appear here as visitors are checked in and out."
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
                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                    />
                </svg>
            </template>
        </EmptyState>

        <template v-else>
            <!-- Mobile: Card List -->
            <div class="space-y-2 sm:hidden">
                <div
                    v-for="log in logs"
                    :key="log.uuid || log.id"
                    class="rounded-xl bg-white border border-gray-200 px-5 py-3.5"
                >
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-medium text-gray-900 capitalize">
                            {{ formatAction(log.action) }}
                        </p>
                        <span class="shrink-0 text-xs text-gray-400">{{
                            formatDateTime(log.created_at)
                        }}</span>
                    </div>
                    <div
                        class="mt-0.5 flex items-center gap-2 text-xs text-gray-500"
                    >
                        <span
                            v-if="log.visitor_registration?.visitor_name"
                            class="truncate"
                            >{{ log.visitor_registration.visitor_name }}</span
                        >
                        <span
                            v-if="
                                log.visitor_registration?.visitor_name &&
                                log.guard_user?.name
                            "
                            >&middot;</span
                        >
                        <span v-if="log.guard_user?.name" class="truncate"
                            >by {{ log.guard_user.name }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Desktop: Table -->
            <div class="hidden sm:block">
                <AppTable
                    :columns="columns"
                    :rows="logs"
                    :loading="loading"
                    :sort-key="sortBy"
                    :sort-direction="sortDirection"
                    empty-message="No activity logs found."
                    @sort="sortLogs"
                >
                    <template #cell-action="{ value }">
                        <span class="capitalize">{{
                            formatAction(value)
                        }}</span>
                    </template>
                    <template #cell-created_at="{ value }">
                        {{ formatDateTime(value) }}
                    </template>
                </AppTable>
            </div>
        </template>

        <AppPagination v-if="meta" :meta="meta" @page-change="loadLogs" />
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import guardApi from "@/api/guard";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppSelect from "@/components/common/AppSelect.vue";
import AppTable from "@/components/common/AppTable.vue";
import AppPagination from "@/components/common/AppPagination.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import EmptyState from "@/components/common/EmptyState.vue";

const actionOptions = [
    { value: "qr_scanned", label: "QR Scanned" },
    { value: "visitor_checked_in", label: "Visitor Checked In" },
    { value: "visitor_checked_out", label: "Visitor Checked Out" },
    { value: "entry_rejected", label: "Entry Rejected" },
    { value: "invalid_qr_scanned", label: "Invalid QR Scanned" },
    { value: "expired_qr_scanned", label: "Expired QR Scanned" },
    { value: "cancelled_qr_scanned", label: "Cancelled QR Scanned" },
];

const columns = [
    { key: "action", label: "Action", sortable: true },
    { key: "visitor_registration.visitor_name", label: "Visitor" },
    { key: "guard_user.name", label: "Guard" },
    { key: "created_at", label: "Time", sortable: true },
];

const logs = ref([]);
const meta = ref(null);
const loading = ref(true);
const dateFilter = ref("");
const actionFilter = ref("");
const sortBy = ref("created_at");
const sortDirection = ref("desc");

function formatAction(action) {
    if (!action) return "-";
    return action.replace(/_/g, " ");
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

async function loadLogs(page = 1) {
    loading.value = true;
    try {
        const params = { page, per_page: 10 };
        if (dateFilter.value) params.date = dateFilter.value;
        if (actionFilter.value) params.action = actionFilter.value;
        params.sort = sortBy.value;
        params.direction = sortDirection.value;
        const { data } = await guardApi.getActivityLogs(params);
        logs.value = data.data.data;
        meta.value = data.data.meta;
    } catch {
        logs.value = [];
    } finally {
        loading.value = false;
    }
}

function sortLogs(key) {
    if (sortBy.value === key) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
        return;
    }

    sortBy.value = key;
    sortDirection.value = "asc";
}

watch([dateFilter, actionFilter, sortBy, sortDirection], () => loadLogs(1));

onMounted(() => loadLogs());
</script>
