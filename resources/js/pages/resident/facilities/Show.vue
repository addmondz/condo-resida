<template>
  <div>
    <div class="mb-4">
      <AppButton variant="ghost" :to="{ name: 'resident.facilities' }" size="sm">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Back to Facilities
      </AppButton>
    </div>

    <LoadingState v-if="loading" message="Loading facility details..." />

    <div v-else-if="error" class="rounded-md bg-red-50 p-4">
      <p class="text-sm text-red-700">{{ error }}</p>
    </div>

    <template v-else>
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div v-if="facility.image_url" class="h-56 sm:h-72">
          <img :src="facility.image_url" :alt="facility.name" class="h-full w-full object-cover" />
        </div>

        <div class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">{{ facility.name }}</h1>
              <StatusBadge
                v-if="facility.is_under_maintenance"
                status="suspended"
                label="Under Maintenance"
                class="mt-2"
              />
            </div>
            <AppButton
              v-if="!facility.is_under_maintenance"
              :to="{ name: 'resident.bookings.create', params: { facilityUuid: facility.uuid } }"
            >
              Book This Facility
            </AppButton>
          </div>

          <div v-if="facility.description" class="mb-6">
            <h3 class="text-sm font-medium text-gray-500 mb-1">Description</h3>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ facility.description }}</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div v-if="facility.capacity" class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm text-gray-500">Capacity</p>
              <p class="text-lg font-semibold text-gray-900">{{ facility.capacity }} pax</p>
            </div>
            <div v-if="facility.opening_time && facility.closing_time" class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm text-gray-500">Operating Hours</p>
              <p class="text-lg font-semibold text-gray-900">{{ facility.opening_time }} - {{ facility.closing_time }}</p>
            </div>
            <div v-if="facility.slot_duration" class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm text-gray-500">Slot Duration</p>
              <p class="text-lg font-semibold text-gray-900">{{ facility.slot_duration }} minutes</p>
            </div>
            <div v-if="facility.advance_booking_days" class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm text-gray-500">Advance Booking</p>
              <p class="text-lg font-semibold text-gray-900">Up to {{ facility.advance_booking_days }} days</p>
            </div>
          </div>

          <div v-if="facility.rules" class="mb-6">
            <h3 class="text-sm font-medium text-gray-500 mb-1">Rules & Regulations</h3>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
              <p class="text-sm text-yellow-800 whitespace-pre-line">{{ facility.rules }}</p>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import residentApi from '@/api/resident';
import AppButton from '@/components/common/AppButton.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import LoadingState from '@/components/common/LoadingState.vue';

const props = defineProps({
  uuid: { type: String, required: true },
});

const facility = ref({});
const loading = ref(true);
const error = ref('');

onMounted(async () => {
  try {
    const { data } = await residentApi.getFacility(props.uuid);
    facility.value = data.data;
  } catch {
    error.value = 'Failed to load facility details.';
  } finally {
    loading.value = false;
  }
});
</script>
