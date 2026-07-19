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
                    class="rounded-xl bg-white border border-gray-200 px-5 py-3.5"
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
                    <div class="mt-3 flex gap-2">
                        <AppButton
                            v-if="notification.status === 'draft'"
                            size="xs"
                            @click="publishNotification(notification)"
                            >Publish</AppButton
                        >
                        <AppButton
                            v-if="notification.status !== 'archived'"
                            size="xs"
                            variant="ghost"
                            @click="archiveNotification(notification)"
                            >Archive</AppButton
                        >
                    </div>
                </div>
            </div>

            <!-- Desktop: Table -->
            <div class="hidden sm:block">
                <AppTable
                    :columns="columns"
                    :rows="notifications"
                    :loading="loading"
                    empty-message="No notifications found."
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
                        <div class="flex justify-end gap-2">
                            <AppButton
                                v-if="row.status === 'draft'"
                                size="sm"
                                @click.stop="publishNotification(row)"
                                >Publish</AppButton
                            >
                            <AppButton
                                v-if="row.status !== 'archived'"
                                size="sm"
                                variant="ghost"
                                @click.stop="archiveNotification(row)"
                                >Archive</AppButton
                            >
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
import { ref, onMounted } from "vue";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppButton from "@/components/common/AppButton.vue";
import AppTable from "@/components/common/AppTable.vue";
import AppPagination from "@/components/common/AppPagination.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import EmptyState from "@/components/common/EmptyState.vue";
import { useToast } from "@/composables/useToast";

const toast = useToast();

const columns = [
    { key: "title", label: "Title" },
    { key: "type", label: "Type" },
    { key: "target_type", label: "Target" },
    { key: "status", label: "Status" },
    { key: "published_at", label: "Published" },
    { key: "actions", label: "" },
];

const notifications = ref([]);
const meta = ref(null);
const loading = ref(true);

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
            per_page: 15,
        });
        notifications.value = data.data.data;
        meta.value = data.data.meta;
    } catch {
        notifications.value = [];
    } finally {
        loading.value = false;
    }
}

async function publishNotification(notification) {
    try {
        const { data } = await adminApi.publishNotification(notification.uuid);
        notifications.value = notifications.value.map((item) =>
            item.uuid === notification.uuid ? data.data : item,
        );
        toast.success("Notification published.");
    } catch (error) {
        toast.error(
            error.response?.data?.message || "Failed to publish notification.",
        );
    }
}

async function archiveNotification(notification) {
    try {
        const { data } = await adminApi.archiveNotification(notification.uuid);
        notifications.value = notifications.value.map((item) =>
            item.uuid === notification.uuid ? data.data : item,
        );
        toast.success("Notification archived.");
    } catch (error) {
        toast.error(
            error.response?.data?.message || "Failed to archive notification.",
        );
    }
}

onMounted(() => loadNotifications());
</script>
