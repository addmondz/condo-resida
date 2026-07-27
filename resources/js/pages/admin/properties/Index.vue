<template>
    <div>
        <PageHeader
            title="Properties"
            subtitle="Manage all properties in the system."
            :breadcrumbs="[{ label: 'Properties' }]"
        >
            <template #actions>
                <AppButton :to="{ name: 'admin.properties.create' }">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Property
                </AppButton>
            </template>
        </PageHeader>

        <SkeletonLoader v-if="loading" variant="list" :rows="4" />

        <EmptyState
            v-else-if="!properties.length"
            title="No properties yet"
            message="Add your first property to get started."
            action-label="Add Property"
            :action-to="{ name: 'admin.properties.create' }"
        >
            <template #icon>
                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </template>
        </EmptyState>

        <template v-else>
            <div class="space-y-2 sm:hidden">
                <div
                    v-for="property in properties"
                    :key="property.uuid"
                    @click="viewProperty(property)"
                    class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-5 py-3.5 cursor-pointer transition-colors active:bg-gray-50"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900">{{ property.name }}</p>
                        <p class="mt-0.5 text-xs text-gray-500">{{ property.blocks_count }} blocks &middot; {{ property.units_count }} units</p>
                    </div>
                    <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
            </div>

            <div class="hidden sm:block">
                <AppTable :columns="columns" :rows="properties" :loading="loading" empty-message="No properties found." @row-click="viewProperty">
                    <template #cell-blocks_count="{ value }">{{ value ?? 0 }}</template>
                    <template #cell-units_count="{ value }">{{ value ?? 0 }}</template>
                    <template #cell-status="{ value }">
                        <StatusBadge :status="value === 'active' ? 'approved' : 'draft'" :label="value || 'active'" />
                    </template>
                </AppTable>
            </div>
        </template>

        <AppPagination v-if="meta" :meta="meta" @page-change="loadProperties" />
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppButton from "@/components/common/AppButton.vue";
import AppTable from "@/components/common/AppTable.vue";
import AppPagination from "@/components/common/AppPagination.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import EmptyState from "@/components/common/EmptyState.vue";

const router = useRouter();

const columns = [
    { key: "name", label: "Name" },
    { key: "address", label: "Address" },
    { key: "blocks_count", label: "Blocks" },
    { key: "units_count", label: "Units" },
    { key: "status", label: "Status" },
];

const properties = ref([]);
const meta = ref(null);
const loading = ref(true);

async function loadProperties(page = 1) {
    loading.value = true;
    try {
        const { data } = await adminApi.getProperties({ page, per_page: 15 });
        properties.value = data.data.data;
        meta.value = data.data.meta;
    } catch {
        properties.value = [];
    } finally {
        loading.value = false;
    }
}

function viewProperty(row) {
    router.push({ name: "admin.properties.show", params: { uuid: row.uuid } });
}

onMounted(() => loadProperties());
</script>
