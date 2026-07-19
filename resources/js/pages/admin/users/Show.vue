<template>
    <div>
        <PageHeader
            title="User Details"
            subtitle="Review account, residence, and access status."
            :breadcrumbs="[
                { label: 'Users', to: { name: 'admin.users' } },
                { label: user.name || 'Details' },
            ]"
        >
            <template #actions>
                <AppButton
                    variant="secondary"
                    :to="{ name: 'admin.users' }"
                    size="sm"
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
                            d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"
                        />
                    </svg>
                    Back to Users
                </AppButton>
            </template>
        </PageHeader>

        <SkeletonLoader
            v-if="loading"
            variant="form"
            :rows="5"
            container-class="rounded-xl border border-gray-200 bg-white px-5 py-5"
        />

        <div
            v-else-if="error"
            class="flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-5 py-4"
        >
            <svg
                class="mt-0.5 h-5 w-5 shrink-0 text-red-500"
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
            <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <template v-else>
            <div class="mb-4 rounded-xl border border-gray-200 bg-white">
                <div
                    class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4"
                >
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">
                            {{ user.name }}
                        </h2>
                        <p class="mt-0.5 text-sm text-gray-500">
                            {{ user.email }}
                        </p>
                    </div>
                    <StatusBadge :status="user.status" />
                </div>

                <div class="px-5 py-5">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ user.phone || "-" }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Role</dt>
                            <dd
                                class="mt-1 text-sm font-medium text-gray-900 capitalize"
                            >
                                {{ (user.roles || []).join(", ") }}
                            </dd>
                        </div>
                        <div v-if="user.resident_type">
                            <dt class="text-sm text-gray-500">Resident Type</dt>
                            <dd
                                class="mt-1 text-sm font-medium text-gray-900 capitalize"
                            >
                                {{ user.resident_type }}
                            </dd>
                        </div>
                        <div v-if="user.unit">
                            <dt class="text-sm text-gray-500">
                                Property / Unit
                            </dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ user.unit.property_name }} /
                                {{ user.unit.block_name }}, {{ user.unit.name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Registered</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatDateTime(user.created_at) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Actions -->
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <AppButton
                    v-if="user.status === 'pending'"
                    @click="approve"
                    :loading="approving"
                    >Approve</AppButton
                >
                <AppButton
                    v-if="user.status === 'pending'"
                    variant="danger"
                    @click="showRejectDialog = true"
                    >Reject</AppButton
                >
                <AppButton
                    v-if="user.status === 'approved'"
                    variant="danger"
                    @click="showSuspendDialog = true"
                    >Suspend</AppButton
                >
                <AppButton
                    v-if="user.status === 'suspended'"
                    @click="reactivate"
                    :loading="reactivating"
                    >Reactivate</AppButton
                >
                <AppButton
                    variant="secondary"
                    @click="resetPassword"
                    :loading="resettingPassword"
                    >Send Password Reset</AppButton
                >
            </div>

            <!-- Reject Dialog -->
            <AppModal
                :show="showRejectDialog"
                title="Reject User"
                @close="showRejectDialog = false"
            >
                <AppTextarea
                    v-model="rejectReason"
                    label="Reason"
                    placeholder="Enter rejection reason"
                    required
                    :rows="3"
                />
                <template #footer>
                    <AppButton
                        variant="secondary"
                        @click="showRejectDialog = false"
                        >Cancel</AppButton
                    >
                    <AppButton
                        variant="danger"
                        :loading="rejecting"
                        @click="reject"
                        >Reject</AppButton
                    >
                </template>
            </AppModal>

            <!-- Suspend Dialog -->
            <AppModal
                :show="showSuspendDialog"
                title="Suspend User"
                @close="showSuspendDialog = false"
            >
                <AppTextarea
                    v-model="suspendReason"
                    label="Reason (optional)"
                    placeholder="Enter reason for suspension"
                    :rows="3"
                />
                <template #footer>
                    <AppButton
                        variant="secondary"
                        @click="showSuspendDialog = false"
                        >Cancel</AppButton
                    >
                    <AppButton
                        variant="danger"
                        :loading="suspending"
                        @click="suspend"
                        >Suspend</AppButton
                    >
                </template>
            </AppModal>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import adminApi from "@/api/admin";
import AppButton from "@/components/common/AppButton.vue";
import AppModal from "@/components/common/AppModal.vue";
import AppTextarea from "@/components/common/AppTextarea.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import { useToast } from "@/composables/useToast";

const props = defineProps({
    uuid: { type: String, required: true },
});

const user = ref({});
const loading = ref(true);
const error = ref("");
const toast = useToast();

const approving = ref(false);
const rejecting = ref(false);
const suspending = ref(false);
const reactivating = ref(false);
const resettingPassword = ref(false);

const showRejectDialog = ref(false);
const rejectReason = ref("");
const showSuspendDialog = ref(false);
const suspendReason = ref("");

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

async function loadUser() {
    try {
        const { data } = await adminApi.getUser(props.uuid);
        user.value = data.data;
    } catch {
        error.value = "Failed to load user details.";
    } finally {
        loading.value = false;
    }
}

async function approve() {
    approving.value = true;
    try {
        const { data } = await adminApi.approveUser(props.uuid);
        user.value = data.data;
        toast.success("User approved successfully.");
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to approve user.");
    } finally {
        approving.value = false;
    }
}

async function reject() {
    if (!rejectReason.value.trim()) return;
    rejecting.value = true;
    try {
        const { data } = await adminApi.rejectUser(props.uuid, {
            reason: rejectReason.value,
        });
        user.value = data.data;
        showRejectDialog.value = false;
        toast.success("User rejected.");
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to reject user.");
    } finally {
        rejecting.value = false;
    }
}

async function suspend() {
    suspending.value = true;
    try {
        const { data } = await adminApi.suspendUser(props.uuid, {
            reason: suspendReason.value || undefined,
        });
        user.value = data.data;
        showSuspendDialog.value = false;
        toast.success("User suspended.");
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to suspend user.");
    } finally {
        suspending.value = false;
    }
}

async function reactivate() {
    reactivating.value = true;
    try {
        const { data } = await adminApi.reactivateUser(props.uuid);
        user.value = data.data;
        toast.success("User reactivated.");
    } catch (err) {
        toast.error(
            err.response?.data?.message || "Failed to reactivate user.",
        );
    } finally {
        reactivating.value = false;
    }
}

async function resetPassword() {
    resettingPassword.value = true;
    try {
        await adminApi.resetUserPassword(props.uuid);
        toast.success("Password reset link sent to user.");
    } catch (err) {
        toast.error(
            err.response?.data?.message || "Failed to send reset link.",
        );
    } finally {
        resettingPassword.value = false;
    }
}

onMounted(loadUser);
</script>
