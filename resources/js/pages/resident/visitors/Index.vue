<template>
    <div class="pb-16 lg:pb-0">
        <PageHeader title="My Visitors" :breadcrumbs="[{ label: 'Visitors' }]">
            <template #actions>
                <AppButton :to="{ name: 'resident.visitors.create' }">
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
                    Register Visitor
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
                    placeholder="Search visitors..."
                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-10 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-100"
                />
                <button
                    v-if="search"
                    @click="search = ''"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
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

        <!-- Tab Filters -->
        <div class="mb-4">
            <div class="inline-flex rounded-xl bg-gray-100 p-1">
                <button
                    v-for="tab in tabs"
                    :key="tab.value"
                    @click="activeTab = tab.value"
                    :class="[
                        'rounded-lg px-4 py-1.5 text-sm font-medium transition-all cursor-pointer',
                        activeTab === tab.value
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-500 hover:text-gray-700',
                    ]"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <Transition name="page-fade" mode="out-in">
            <div :key="activeTab + '-' + loading">
                <!-- Loading -->
                <SkeletonLoader v-if="loading" variant="list" :rows="5" />

                <!-- Empty State -->
                <EmptyState
                    v-else-if="!visitors.length"
                    :title="
                        activeTab === 'upcoming'
                            ? 'No upcoming visitors'
                            : 'No past visitors'
                    "
                    :message="
                        activeTab === 'upcoming'
                            ? 'Register a visitor to generate a QR pass for easy check-in.'
                            : 'Your visitor history will appear here.'
                    "
                    :action-label="
                        activeTab === 'upcoming' ? 'Register Visitor' : ''
                    "
                    :action-to="
                        activeTab === 'upcoming'
                            ? { name: 'resident.visitors.create' }
                            : null
                    "
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
                                    <span
                                        v-if="visitor.purpose"
                                        class="truncate capitalize"
                                        >{{
                                            formatPurpose(visitor.purpose)
                                        }}</span
                                    >
                                    <span
                                        v-if="
                                            visitor.purpose &&
                                            visitor.visit_date
                                        "
                                        >&middot;</span
                                    >
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
                            empty-message="No visitors found."
                            @row-click="viewVisitor"
                        >
                            <template #cell-purpose="{ value }">
                                <span class="capitalize">{{
                                    formatPurpose(value)
                                }}</span>
                            </template>
                            <template #cell-visit_date="{ value }">
                                {{ formatDate(value) }}
                            </template>
                            <template #cell-status="{ row }">
                                <StatusBadge :status="row.status" />
                            </template>
                            <template #cell-actions="{ row }">
                                <router-link
                                    :to="{
                                        name: 'resident.visitors.show',
                                        params: { uuid: row.uuid },
                                    }"
                                    class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors"
                                    @click.stop
                                >
                                    View
                                    <svg
                                        class="h-3.5 w-3.5"
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
                                </router-link>
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
import { useRoute, useRouter } from "vue-router";
import residentApi from "@/api/resident";
import PageHeader from "@/components/common/PageHeader.vue";
import AppButton from "@/components/common/AppButton.vue";
import AppTable from "@/components/common/AppTable.vue";
import AppPagination from "@/components/common/AppPagination.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import EmptyState from "@/components/common/EmptyState.vue";

const router = useRouter();
const route = useRoute();

const tabs = [
    { label: "Upcoming", value: "upcoming" },
    { label: "History", value: "history" },
];

const columns = [
    { key: "visitor_name", label: "Visitor Name" },
    { key: "purpose", label: "Purpose" },
    { key: "visit_date", label: "Visit Date" },
    { key: "status", label: "Status" },
    { key: "actions", label: "" },
];

const activeTab = ref(
    typeof route.query.filter === "string" ? route.query.filter : "upcoming",
);
const search = ref(
    typeof route.query.search === "string" ? route.query.search : "",
);
const visitors = ref([]);
const meta = ref(null);
const loading = ref(true);
let searchTimer = null;

function formatDate(dateStr) {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    return date.toLocaleDateString("en-MY", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

function formatPurpose(purpose) {
    if (!purpose) return "";
    return purpose.replace(/_/g, " ");
}

async function loadVisitors(page = 1) {
    loading.value = true;
    try {
        const params = { page, per_page: 15, filter: activeTab.value };
        if (search.value.trim()) {
            params.search = search.value.trim();
        }

        router.replace({ name: "resident.visitors", query: params });

        const { data } = await residentApi.getVisitors(params);
        visitors.value = data.data.data;
        meta.value = data.data.meta;
    } catch {
        visitors.value = [];
    } finally {
        loading.value = false;
    }
}

function viewVisitor(row) {
    router.push({
        name: "resident.visitors.show",
        params: { uuid: row.uuid },
    });
}

watch(activeTab, () => {
    loadVisitors(1);
});

watch(search, () => {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => loadVisitors(1), 250);
});

onMounted(() => {
    const page = Number.parseInt(route.query.page, 10) || 1;
    loadVisitors(page);
});
</script>
