<template>
    <div>
        <PageHeader
            title="Notification Details"
            subtitle="View notification content and delivery status."
            :breadcrumbs="[
                { label: 'Notifications', to: { name: 'admin.notifications' } },
                { label: notification.title || 'Details' },
            ]"
        >
            <template #actions>
                <AppButton variant="secondary" :to="{ name: 'admin.notifications' }" size="sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </AppButton>
            </template>
        </PageHeader>

        <SkeletonLoader v-if="loading" variant="form" :rows="5" container-class="rounded-xl border border-gray-200 bg-white px-5 py-5" />

        <div v-else-if="error" class="flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-5 py-4">
            <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <template v-else>
            <div class="mb-4 rounded-xl border border-gray-200 bg-white">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">{{ notification.title }}</h2>
                        <p class="mt-0.5 text-sm text-gray-500">
                            Created {{ formatDateTime(notification.created_at) }}
                            <span v-if="notification.creator"> by {{ notification.creator.name }}</span>
                        </p>
                    </div>
                    <StatusBadge
                        :status="notification.status === 'published' ? 'approved' : notification.status === 'archived' ? 'suspended' : 'pending'"
                        :label="notification.status"
                    />
                </div>

                <div class="px-5 py-5">
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm text-gray-500">Type</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 capitalize">{{ notification.type || "-" }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Target</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 capitalize">
                                {{ notification.target_type?.replace('_', ' ') || "All" }}
                                <span v-if="notification.property"> &mdash; {{ notification.property.name }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Recipients</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ notification.recipients_count ?? "-" }}</dd>
                        </div>
                        <div v-if="notification.published_at">
                            <dt class="text-sm text-gray-500">Published</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ formatDateTime(notification.published_at) }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5 border-t border-gray-100 pt-5">
                        <dt class="text-sm text-gray-500">Content</dt>
                        <dd class="mt-2 text-sm text-gray-900 whitespace-pre-line">{{ notification.body }}</dd>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-3 sm:flex-row">
                <AppButton v-if="notification.status === 'draft'" @click="publish" :loading="publishing">Publish</AppButton>
                <AppButton v-if="notification.status !== 'archived'" variant="secondary" @click="archive" :loading="archiving">Archive</AppButton>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppButton from "@/components/common/AppButton.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import { useToast } from "@/composables/useToast";

const props = defineProps({ uuid: { type: String, required: true } });
const toast = useToast();

const notification = ref({});
const loading = ref(true);
const error = ref("");
const publishing = ref(false);
const archiving = ref(false);

function formatDateTime(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("en-MY", {
        year: "numeric", month: "short", day: "numeric",
        hour: "2-digit", minute: "2-digit",
    });
}

async function loadNotification() {
    try {
        const { data } = await adminApi.getNotification(props.uuid);
        notification.value = data.data;
    } catch {
        error.value = "Failed to load notification.";
    } finally {
        loading.value = false;
    }
}

async function publish() {
    publishing.value = true;
    try {
        const { data } = await adminApi.publishNotification(props.uuid);
        notification.value = data.data;
        toast.success("Notification published.");
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to publish.");
    } finally {
        publishing.value = false;
    }
}

async function archive() {
    archiving.value = true;
    try {
        const { data } = await adminApi.archiveNotification(props.uuid);
        notification.value = data.data;
        toast.success("Notification archived.");
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to archive.");
    } finally {
        archiving.value = false;
    }
}

onMounted(loadNotification);
</script>
