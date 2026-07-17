<template>
  <div>
    <PageHeader title="My Visitors" subtitle="Manage your visitor registrations.">
      <template #actions>
        <AppButton :to="{ name: 'resident.visitors.create' }">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          Register Visitor
        </AppButton>
      </template>
    </PageHeader>

    <!-- Status Tabs -->
    <div class="mb-4 border-b border-gray-200">
      <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          @click="activeTab = tab.value"
          :class="[
            activeTab === tab.value
              ? 'border-primary-500 text-primary-600'
              : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
            'whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors',
          ]"
        >
          {{ tab.label }}
        </button>
      </nav>
    </div>

    <AppTable
      :columns="columns"
      :rows="visitors"
      :loading="loading"
      empty-message="No visitors found."
      @row-click="viewVisitor"
    >
      <template #cell-visit_date="{ value }">
        {{ formatDate(value) }}
      </template>
      <template #cell-status="{ row }">
        <StatusBadge :status="row.status" />
      </template>
    </AppTable>

    <AppPagination :meta="meta" @page-change="loadVisitors" />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import residentApi from '@/api/resident';
import PageHeader from '@/components/common/PageHeader.vue';
import AppButton from '@/components/common/AppButton.vue';
import AppTable from '@/components/common/AppTable.vue';
import AppPagination from '@/components/common/AppPagination.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';

const router = useRouter();

const tabs = [
  { label: 'All', value: '' },
  { label: 'Active', value: 'active' },
  { label: 'Checked In', value: 'checked_in' },
  { label: 'Checked Out', value: 'checked_out' },
  { label: 'Expired', value: 'expired' },
  { label: 'Cancelled', value: 'cancelled' },
];

const columns = [
  { key: 'visitor_name', label: 'Visitor Name' },
  { key: 'purpose', label: 'Purpose' },
  { key: 'visit_date', label: 'Visit Date' },
  { key: 'status', label: 'Status' },
];

const activeTab = ref('');
const visitors = ref([]);
const meta = ref(null);
const loading = ref(true);

function formatDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-MY', { year: 'numeric', month: 'short', day: 'numeric' });
}

async function loadVisitors(page = 1) {
  loading.value = true;
  try {
    const params = { page, per_page: 15 };
    if (activeTab.value) {
      params.status = activeTab.value;
    }
    const { data } = await residentApi.getVisitors(params);
    visitors.value = data.data;
    meta.value = data.meta;
  } catch {
    visitors.value = [];
  } finally {
    loading.value = false;
  }
}

function viewVisitor(row) {
  router.push({ name: 'resident.visitors.show', params: { uuid: row.uuid } });
}

watch(activeTab, () => {
  loadVisitors(1);
});

onMounted(() => {
  loadVisitors();
});
</script>
