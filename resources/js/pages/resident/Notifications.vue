<template>
    <div class="pb-16 lg:pb-0">
        <PageHeader
            title="Notifications"
            subtitle="Stay updated with announcements and alerts."
            :breadcrumbs="[{ label: 'Notifications' }]"
        >
            <template #actions>
                <AppButton
                    v-if="hasUnread"
                    variant="secondary"
                    size="sm"
                    :loading="markingAll"
                    @click="markAllRead"
                >
                    Mark All Read
                </AppButton>
            </template>
        </PageHeader>

        <SkeletonLoader v-if="loading" variant="list" :rows="5" />

        <EmptyState
            v-else-if="notifications.length === 0"
            title="No notifications"
            message="You have no notifications at the moment."
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

        <div v-else class="space-y-3">
            <div
                v-for="n in notifications"
                :key="n.uuid"
                :class="[
                    'rounded-xl border cursor-pointer transition-colors',
                    n.read_at
                        ? 'border-gray-200 bg-white'
                        : 'border-primary-200 bg-primary-50/30',
                ]"
                @click="markRead(n)"
            >
                <div class="flex items-start justify-between gap-3 px-5 py-3.5">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span
                                v-if="!n.read_at"
                                class="inline-block h-2 w-2 rounded-full bg-primary-500 shrink-0"
                            ></span>
                            <h3
                                class="text-sm font-semibold text-gray-900 truncate"
                            >
                                {{ n.title }}
                            </h3>
                        </div>
                        <p class="mt-1 text-sm text-gray-600 line-clamp-2">
                            {{ n.body }}
                        </p>
                    </div>
                    <span
                        class="text-xs text-gray-400 whitespace-nowrap shrink-0"
                        >{{ formatTimeAgo(n.published_at) }}</span
                    >
                </div>
            </div>
        </div>

        <AppPagination :meta="meta" @page-change="loadNotifications" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import residentApi from "@/api/resident";
import PageHeader from "@/components/common/PageHeader.vue";
import AppButton from "@/components/common/AppButton.vue";
import AppPagination from "@/components/common/AppPagination.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import EmptyState from "@/components/common/EmptyState.vue";

const notifications = ref([]);
const meta = ref(null);
const loading = ref(true);
const markingAll = ref(false);

const hasUnread = computed(() => notifications.value.some((n) => !n.read_at));

function formatTimeAgo(dateStr) {
    if (!dateStr) return "";
    const diff = Date.now() - new Date(dateStr).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return "Just now";
    if (mins < 60) return `${mins}m ago`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days}d ago`;
    return new Date(dateStr).toLocaleDateString("en-MY", {
        month: "short",
        day: "numeric",
    });
}

async function loadNotifications(page = 1) {
    loading.value = true;
    try {
        const { data } = await residentApi.getNotifications({
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

async function markRead(notification) {
    if (notification.read_at) return;
    try {
        await residentApi.markNotificationRead(notification.uuid);
        notification.read_at = new Date().toISOString();
    } catch {
        // silent fail
    }
}

async function markAllRead() {
    markingAll.value = true;
    try {
        await residentApi.markAllNotificationsRead();
        notifications.value.forEach((n) => {
            if (!n.read_at) n.read_at = new Date().toISOString();
        });
    } catch {
        // silent fail
    } finally {
        markingAll.value = false;
    }
}

onMounted(() => loadNotifications());
</script>
