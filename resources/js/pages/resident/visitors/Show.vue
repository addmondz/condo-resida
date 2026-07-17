<template>
  <div>
    <div class="mb-4">
      <AppButton variant="ghost" :to="{ name: 'resident.visitors' }" size="sm">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Back to Visitors
      </AppButton>
    </div>

    <LoadingState v-if="loading" message="Loading visitor details..." />

    <div v-else-if="error" class="rounded-md bg-red-50 p-4">
      <p class="text-sm text-red-700">{{ error }}</p>
    </div>

    <template v-else>
      <!-- QR Code Card -->
      <div class="bg-gray-900 rounded-2xl p-6 text-white mb-4">
        <div class="text-center mb-4">
          <p class="text-sm text-gray-400 uppercase tracking-wide">{{ visitor.property?.name || 'Property' }}</p>
          <p class="text-xl font-bold mt-1">{{ visitor.block?.name || '' }}{{ visitor.block?.name && visitor.unit?.number ? ', ' : '' }}{{ visitor.unit?.number || '' }}</p>
        </div>

        <div class="flex justify-center mb-4">
          <div class="bg-white rounded-xl p-4">
            <QRCode
              :value="visitor.qr_token || visitor.uuid"
              :size="220"
              level="M"
            />
          </div>
        </div>

        <p class="text-center text-xs text-gray-400">Scan this QR code at the guard house</p>
      </div>

      <!-- Share Button -->
      <AppButton @click="handleShare" class="w-full mb-6" size="lg">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" /></svg>
        Share QR Code
      </AppButton>

      <!-- Visitor Information -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Visitor Information</h2>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
          <div>
            <dt class="text-sm text-gray-500">Purpose Of Visit</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900 capitalize">{{ formatPurpose(visitor.purpose) }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500">Visitor Name</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900">{{ visitor.visitor_name }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500">Contact No.</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900">{{ visitor.visitor_phone || '-' }}</dd>
          </div>
          <div v-if="visitor.vehicle_number">
            <dt class="text-sm text-gray-500">Vehicle Number</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900">{{ visitor.vehicle_number }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500">Visit Date</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900">{{ formatDateLong(visitor.visit_date) }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500">Validity</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900">Single entry only</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500">Status</dt>
            <dd class="mt-1"><StatusBadge :status="visitor.status" /></dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500">Reference Number</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900 font-mono">{{ visitor.reference_number || visitor.uuid }}</dd>
          </div>
          <div v-if="visitor.checked_in_at">
            <dt class="text-sm text-gray-500">Checked In</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900">{{ formatDateTime(visitor.checked_in_at) }}</dd>
          </div>
          <div v-if="visitor.checked_out_at">
            <dt class="text-sm text-gray-500">Checked Out</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900">{{ formatDateTime(visitor.checked_out_at) }}</dd>
          </div>
          <div v-if="visitor.cancellation_reason" class="sm:col-span-2">
            <dt class="text-sm text-gray-500">Cancellation Reason</dt>
            <dd class="mt-1 text-sm font-medium text-red-700">{{ visitor.cancellation_reason }}</dd>
          </div>
        </dl>
      </div>

      <!-- Cancel Button -->
      <div v-if="canCancel" class="mt-4">
        <AppButton variant="danger" @click="showCancelDialog = true" class="w-full">
          Cancel Visitor Pass
        </AppButton>
      </div>

      <!-- Cancel Dialog -->
      <AppModal :show="showCancelDialog" title="Cancel Visitor Pass" @close="showCancelDialog = false">
        <p class="text-sm text-gray-600 mb-4">Are you sure you want to cancel this visitor pass? This action cannot be undone.</p>
        <AppTextarea
          v-model="cancelReason"
          label="Reason (optional)"
          placeholder="Enter a reason for cancellation"
          :rows="3"
        />
        <template #footer>
          <AppButton variant="secondary" @click="showCancelDialog = false">Keep Pass</AppButton>
          <AppButton variant="danger" :loading="cancelling" @click="handleCancel">Cancel Pass</AppButton>
        </template>
      </AppModal>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import residentApi from '@/api/resident';
import { QRCode } from 'vue3-qrcode';
import AppButton from '@/components/common/AppButton.vue';
import AppModal from '@/components/common/AppModal.vue';
import AppTextarea from '@/components/common/AppTextarea.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import LoadingState from '@/components/common/LoadingState.vue';

const props = defineProps({
  uuid: { type: String, required: true },
});

const route = useRoute();

const visitor = ref({});
const loading = ref(true);
const error = ref('');
const showCancelDialog = ref(false);
const cancelReason = ref('');
const cancelling = ref(false);

const canCancel = computed(() => {
  return visitor.value.status === 'active' && !visitor.value.checked_in_at;
});

function formatPurpose(purpose) {
  if (!purpose) return '-';
  return purpose.replace(/_/g, ' ');
}

function formatDateLong(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-MY', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
}

function formatDateTime(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-MY', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

async function handleShare() {
  const shareData = {
    title: 'Visitor Pass',
    text: `Visitor Pass for ${visitor.value.visitor_name}\nProperty: ${visitor.value.property?.name || ''}\nBlock: ${visitor.value.block?.name || ''}, Unit: ${visitor.value.unit?.number || ''}\nDate: ${formatDateLong(visitor.value.visit_date)}`,
    url: window.location.href,
  };

  if (navigator.share) {
    try {
      await navigator.share(shareData);
    } catch {
      // user cancelled share
    }
  } else {
    try {
      await navigator.clipboard.writeText(window.location.href);
      alert('Link copied to clipboard!');
    } catch {
      alert('Unable to share. Please copy the URL from the address bar.');
    }
  }
}

async function handleCancel() {
  cancelling.value = true;
  try {
    const { data } = await residentApi.cancelVisitor(props.uuid, {
      reason: cancelReason.value || undefined,
    });
    visitor.value = data.data;
    showCancelDialog.value = false;
    cancelReason.value = '';
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to cancel visitor pass.');
  } finally {
    cancelling.value = false;
  }
}

onMounted(async () => {
  try {
    const { data } = await residentApi.getVisitor(props.uuid);
    visitor.value = data.data;
  } catch {
    error.value = 'Failed to load visitor details.';
  } finally {
    loading.value = false;
  }
});
</script>
