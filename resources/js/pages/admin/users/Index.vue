<template>
    <div>
        <PageHeader
            title="User Management"
            subtitle="Manage all registered users."
            :breadcrumbs="[{ label: 'Users' }]"
        />

        <!-- Search & Filters -->
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
                    placeholder="Search name, email, phone..."
                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-10 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-100"
                    @keyup.enter="loadUsers(1)"
                />
                <button
                    v-if="search"
                    @click="
                        search = '';
                        loadUsers(1);
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
        <div class="mb-4">
            <div class="inline-flex rounded-xl bg-gray-100 p-1">
                <button
                    v-for="tab in statusTabs"
                    :key="tab.value"
                    @click="statusFilter = tab.value"
                    :class="[
                        'rounded-lg px-4 py-1.5 text-sm font-medium transition-all cursor-pointer',
                        statusFilter === tab.value
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-500 hover:text-gray-700',
                    ]"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- Role Filter -->
        <div class="mb-4">
            <AppSelect
                v-model="roleFilter"
                :options="roleOptions"
                placeholder="All Roles"
                class="w-44"
            />
        </div>

        <Transition name="page-fade" mode="out-in">
            <div :key="statusFilter + '-' + loading">
                <!-- Loading -->
                <SkeletonLoader v-if="loading" variant="list" :rows="5" />

                <!-- Empty State -->
                <EmptyState
                    v-else-if="!users.length"
                    title="No users found"
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
                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
                            />
                        </svg>
                    </template>
                </EmptyState>

                <template v-else>
                    <!-- Mobile: Card List -->
                    <div class="space-y-2 sm:hidden">
                        <div
                            v-for="user in users"
                            :key="user.uuid"
                            @click="viewUser(user)"
                            class="flex items-center gap-3 rounded-xl bg-white border border-gray-200 px-5 py-3.5 active:bg-gray-50 cursor-pointer transition-colors"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-50 text-sm font-semibold text-primary-600"
                            >
                                {{ user.name?.charAt(0)?.toUpperCase() || "?" }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex items-center justify-between gap-2"
                                >
                                    <p
                                        class="text-sm font-medium text-gray-900 truncate"
                                    >
                                        {{ user.name }}
                                    </p>
                                    <StatusBadge :status="user.status" />
                                </div>
                                <div
                                    class="mt-0.5 flex items-center gap-2 text-xs text-gray-500"
                                >
                                    <span class="truncate">{{
                                        user.email
                                    }}</span>
                                    <span v-if="user.roles?.length"
                                        >&middot;</span
                                    >
                                    <span
                                        v-if="user.roles?.length"
                                        class="shrink-0 capitalize"
                                        >{{ user.roles.join(", ") }}</span
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
                            :rows="users"
                            :loading="loading"
                            empty-message="No users found."
                            @row-click="viewUser"
                        >
                            <template #cell-unit="{ row }">
                                {{ row.unit?.property_name || "-"
                                }}{{
                                    row.unit
                                        ? ` / ${row.unit.block_name}, ${row.unit.name}`
                                        : ""
                                }}
                            </template>
                            <template #cell-roles="{ row }">
                                <span class="text-xs capitalize">{{
                                    (row.roles || []).join(", ")
                                }}</span>
                            </template>
                            <template #cell-status="{ row }">
                                <StatusBadge :status="row.status" />
                            </template>
                            <template #cell-actions="{ row }">
                                <div class="flex gap-2" @click.stop>
                                    <AppButton
                                        v-if="row.status === 'pending'"
                                        size="xs"
                                        @click="approveUser(row)"
                                        >Approve</AppButton
                                    >
                                    <AppButton
                                        v-if="row.status === 'pending'"
                                        size="xs"
                                        variant="danger"
                                        @click="openReject(row)"
                                        >Reject</AppButton
                                    >
                                </div>
                            </template>
                        </AppTable>
                    </div>
                </template>

                <AppPagination
                    v-if="meta"
                    :meta="meta"
                    @page-change="loadUsers"
                />
            </div>
        </Transition>

        <AppModal
            :show="showRejectDialog"
            title="Reject User"
            @close="showRejectDialog = false"
        >
            <p class="text-sm text-gray-600 mb-4">
                Reject <strong>{{ rejectTarget?.name }}</strong
                >?
            </p>
            <AppTextarea
                v-model="rejectReason"
                label="Reason"
                placeholder="Enter rejection reason"
                required
                :rows="3"
            />
            <template #footer>
                <AppButton variant="secondary" @click="showRejectDialog = false"
                    >Cancel</AppButton
                >
                <AppButton
                    variant="danger"
                    :loading="rejecting"
                    @click="handleReject"
                    >Reject</AppButton
                >
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppSelect from "@/components/common/AppSelect.vue";
import AppTable from "@/components/common/AppTable.vue";
import AppPagination from "@/components/common/AppPagination.vue";
import AppButton from "@/components/common/AppButton.vue";
import AppModal from "@/components/common/AppModal.vue";
import AppTextarea from "@/components/common/AppTextarea.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import EmptyState from "@/components/common/EmptyState.vue";
import { useToast } from "@/composables/useToast";

const router = useRouter();
const route = useRoute();
const toast = useToast();

const statusTabs = [
    { label: "All", value: "" },
    { label: "Pending", value: "pending" },
    { label: "Approved", value: "approved" },
    { label: "Rejected", value: "rejected" },
    { label: "Suspended", value: "suspended" },
];

const roleOptions = [
    { value: "resident", label: "Resident" },
    { value: "guard", label: "Guard" },
    { value: "property_admin", label: "Property Admin" },
    { value: "super_admin", label: "Super Admin" },
];

const columns = [
    { key: "name", label: "Name" },
    { key: "email", label: "Email" },
    { key: "unit", label: "Property / Unit" },
    { key: "roles", label: "Role" },
    { key: "status", label: "Status" },
    { key: "actions", label: "" },
];

const users = ref([]);
const meta = ref(null);
const loading = ref(true);
const search = ref("");
const statusFilter = ref(route.query.status || "");
const roleFilter = ref("");

const showRejectDialog = ref(false);
const rejectTarget = ref(null);
const rejectReason = ref("");
const rejecting = ref(false);

async function loadUsers(page = 1) {
    loading.value = true;
    try {
        const params = { page, per_page: 15 };
        if (search.value) params.search = search.value;
        if (statusFilter.value) params.status = statusFilter.value;
        if (roleFilter.value) params.role = roleFilter.value;
        const { data } = await adminApi.getUsers(params);
        users.value = data.data.data;
        meta.value = data.data.meta;
    } catch {
        users.value = [];
    } finally {
        loading.value = false;
    }
}

function viewUser(row) {
    router.push({ name: "admin.users.show", params: { uuid: row.uuid } });
}

async function approveUser(user) {
    try {
        await adminApi.approveUser(user.uuid);
        toast.success("User approved.");
        loadUsers();
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to approve user.");
    }
}

function openReject(user) {
    rejectTarget.value = user;
    rejectReason.value = "";
    showRejectDialog.value = true;
}

async function handleReject() {
    if (!rejectReason.value.trim()) return;
    rejecting.value = true;
    try {
        await adminApi.rejectUser(rejectTarget.value.uuid, {
            reason: rejectReason.value,
        });
        showRejectDialog.value = false;
        toast.success("User rejected.");
        loadUsers();
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to reject user.");
    } finally {
        rejecting.value = false;
    }
}

watch([statusFilter, roleFilter], () => loadUsers(1));

onMounted(() => loadUsers());
</script>
