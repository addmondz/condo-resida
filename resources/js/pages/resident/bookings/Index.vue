<template>
  <div>
    <PageHeader title="My Bookings" subtitle="View and manage your facility bookings.">
      <template #actions>
        <AppButton :to="{ name: 'resident.bookings.create' }">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          Book Facility
        </AppButton>
      </template>
    </PageHeader>

    <!-- Tabs -->
    <div class="mb-4 border-b border-gray-200">
      <nav class="-mb-px flex space-x-6" aria-label="Tabs">
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
      :rows="bookings"
      :loading="loading"
      empty-message="No bookings found."
    >
      <template #cell-facility="{ row }">
        {{ row.facility?.name || '-' }}
      </template>
      <template #cell-booking_date="{ value }">
        {{ formatDate(value) }}
      </template>
      <template #cell-time="{ row }">
        {{ row.start_time }} - {{ row.end_time }}
      </template>
      <template #cell-status="{ row }">
        <StatusBadge :status="row.status" />
      </template>
      <template #cell-actions="{ row }">
        <AppButton
          v-if="canCancel(row)"
          variant="danger"
          size="xs"
          @click.stop="confirmCancel(row)"
        >
          Cancel
        </AppButton>
      </template>
    </AppTable>

    <AppPagination :meta="meta" @page-change="loadBookings" />

    <ConfirmationDialog
      :show="showCancelDialog"
      title="Cancel Booking"
      message="Are you sure you want to cancel this booking? This action cannot be undone."
      confirm-label="Cancel Booking"
      :loading="cancelling"
      @confirm="handleCancel"
      @cancel="showCancelDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import residentApi from '@/api/resident';
import PageHeader from '@/components/common/PageHeader.vue';
import AppButton from '@/components/common/AppButton.vue';
import AppTable from '@/components/common/AppTable.vue';
import AppPagination from '@/components/common/AppPagination.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import ConfirmationDialog from '@/components/common/ConfirmationDialog.vue';

const tabs = [
  { label: 'Upcoming', value: 'upcoming' },
  { label: 'Past', value: 'past' },
];

const columns = [
  { key: 'facility', label: 'Facility' },
  { key: 'booking_date', label: 'Date' },
  { key: 'time', label: 'Time' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: '' },
];

const activeTab = ref('upcoming');
const bookings = ref([]);
const meta = ref(null);
const loading = ref(true);

const showCancelDialog = ref(false);
const cancelTarget = ref(null);
const cancelling = ref(false);

function formatDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-MY', { year: 'numeric', month: 'short', day: 'numeric' });
}

function canCancel(booking) {
  return ['pending', 'approved'].includes(booking.status);
}

function confirmCancel(booking) {
  cancelTarget.value = booking;
  showCancelDialog.value = true;
}

async function handleCancel() {
  if (!cancelTarget.value) return;
  cancelling.value = true;
  try {
    await residentApi.cancelBooking(cancelTarget.value.uuid);
    showCancelDialog.value = false;
    cancelTarget.value = null;
    loadBookings();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to cancel booking.');
  } finally {
    cancelling.value = false;
  }
}

async function loadBookings(page = 1) {
  loading.value = true;
  try {
    const { data } = await residentApi.getBookings({
      filter: activeTab.value,
      page,
      per_page: 15,
    });
    bookings.value = data.data;
    meta.value = data.meta;
  } catch {
    bookings.value = [];
  } finally {
    loading.value = false;
  }
}

watch(activeTab, () => {
  loadBookings(1);
});

onMounted(() => {
  loadBookings();
});
</script>
