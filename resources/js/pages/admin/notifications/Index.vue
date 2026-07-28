<template>
    <div>
        <PageHeader
            title="Notifications"
            subtitle="Manage system notifications and announcements."
            :breadcrumbs="[{ label: 'Notifications' }]"
        >
            <template #actions>
                <AppButton :to="{ name: 'admin.notifications.create' }">
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
                    Create Notification
                </AppButton>
            </template>
        </PageHeader>

        <!-- Loading -->
        <SkeletonLoader v-if="loading" variant="list" :rows="5" />

        <!-- Empty State -->
        <EmptyState
            v-else-if="!notifications.length"
            title="No notifications yet"
            message="Create your first notification to communicate with residents."
            action-label="Create Notification"
            :action-to="{ name: 'admin.notifications.create' }"
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
                        d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
                    />
                </svg>
            </template>
        </EmptyState>

        <template v-else>
            <!-- Mobile: Card List -->
            <div class="space-y-2 sm:hidden">
                <div
                    v-for="notification in notifications"
                    :key="notification.uuid"
                    @click="viewNotification(notification)"
                    class="rounded-xl bg-white border border-gray-200 px-5 py-3.5 cursor-pointer transition-colors active:bg-gray-50"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <p
                                class="text-sm font-medium text-gray-900 truncate"
                            >
                                {{ notification.title }}
                            </p>
                            <div
                                class="mt-0.5 flex items-center gap-2 text-xs text-gray-500"
                            >
                                <span class="capitalize">{{
                                    notification.type
                                }}</span>
                                <span>&middot;</span>
                                <span class="capitalize">{{
                                    notification.status
                                }}</span>
                            </div>
                            <p
                                v-if="notification.published_at"
                                class="mt-1 text-xs text-gray-400"
                            >
                                {{ formatDateTime(notification.published_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop: Table -->
            <div class="hidden sm:block">
                <AppTable
                    :columns="columns"
                    :rows="notifications"
                    :loading="loading"
                    :sort-key="sortBy"
                    :sort-direction="sortDirection"
                    empty-message="No notifications found."
                    @sort="sortNotifications"
                    @row-click="viewNotification"
                >
                    <template #cell-type="{ value }">
                        <span class="capitalize text-xs">{{ value }}</span>
                    </template>
                    <template #cell-target_type="{ value }">
                        <span class="capitalize text-xs">{{ value }}</span>
                    </template>
                    <template #cell-status="{ value }">
                        <span class="capitalize text-xs">{{ value }}</span>
                    </template>
                    <template #cell-published_at="{ value }">
                        {{ formatDateTime(value) }}
                    </template>
                    <template #cell-actions="{ row }">
                        <div class="flex justify-end gap-1" @click.stop>
                            <RouterLink :to="{ name: 'admin.notifications.show', params: { uuid: row.uuid } }" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:text-gray-600" title="View details" aria-label="View notification details">
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
            @page-change="loadNotifications"
        />
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
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import EmptyState from "@/components/common/EmptyState.vue";

const router = useRouter();

const columns = [
    { key: "title", label: "Title", sortable: true },
    { key: "type", label: "Type", sortable: true },
    { key: "target_type", label: "Target", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "published_at", label: "Published", sortable: true },
    { key: "actions", label: "" },
];

const notifications = ref([]);
const meta = ref(null);
const loading = ref(true);
const sortBy = ref("created_at");
const sortDirection = ref("desc");

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

async function loadNotifications(page = 1) {
    loading.value = true;
    try {
        const { data } = await adminApi.getNotifications({
            page,
            per_page: 10,
            sort: sortBy.value,
            direction: sortDirection.value,
        });
        notifications.value = data.data.data;
        meta.value = data.data.meta;
    } catch {
        notifications.value = [];
    } finally {
        loading.value = false;
    }
}

function viewNotification(row) {
    router.push({ name: "admin.notifications.show", params: { uuid: row.uuid } });
}

function sortNotifications(key) {
    if (sortBy.value === key) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
        return;
    }

    sortBy.value = key;
    sortDirection.value = "asc";
}

watch([sortBy, sortDirection], () => loadNotifications(1));

onMounted(() => loadNotifications());
</script>
