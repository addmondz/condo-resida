import { defineStore } from "pinia";
import { ref, computed } from "vue";
import residentApi from "@/api/resident";

export const useNotificationStore = defineStore("notification", () => {
    const notifications = ref([]);
    const unreadCount = ref(0);
    const loading = ref(false);

    const hasUnread = computed(() => unreadCount.value > 0);

    async function fetchNotifications(params = {}) {
        loading.value = true;
        try {
            const { data } = await residentApi.getNotifications(params);
            notifications.value = data.data;
            unreadCount.value = data.meta?.unread_count ?? 0;
        } finally {
            loading.value = false;
        }
    }

    async function markAsRead(id) {
        await residentApi.markNotificationRead(id);
        const n = notifications.value.find((n) => n.id === id);
        if (n) n.read_at = new Date().toISOString();
        unreadCount.value = Math.max(0, unreadCount.value - 1);
    }

    async function markAllAsRead() {
        await residentApi.markAllNotificationsRead();
        notifications.value.forEach((n) => {
            if (!n.read_at) n.read_at = new Date().toISOString();
        });
        unreadCount.value = 0;
    }

    return {
        notifications,
        unreadCount,
        loading,
        hasUnread,
        fetchNotifications,
        markAsRead,
        markAllAsRead,
    };
});
