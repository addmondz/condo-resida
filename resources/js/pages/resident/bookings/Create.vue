<template>
  <div>
    <PageHeader title="Book Facility" subtitle="Reserve a facility time slot." />

    <div class="max-w-2xl">
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div v-if="errorMessage" class="mb-4 rounded-md bg-red-50 p-4">
          <p class="text-sm text-red-700">{{ errorMessage }}</p>
        </div>

        <div v-if="successMessage" class="mb-4 rounded-md bg-green-50 p-4">
          <p class="text-sm text-green-700">{{ successMessage }}</p>
        </div>

        <LoadingState v-if="loadingFacilities" message="Loading facilities..." />

        <form v-else @submit.prevent="handleSubmit" class="space-y-4">
          <AppSelect
            v-model="form.facility_uuid"
            label="Facility"
            :options="facilityOptions"
            placeholder="Select a facility"
            :error="errors.facility_uuid"
            required
          />

          <AppInput
            v-model="form.booking_date"
            label="Date"
            type="date"
            :min="today"
            :max="maxDate"
            :error="errors.booking_date"
            :disabled="!form.facility_uuid"
            required
          />

          <div v-if="loadingSlots">
            <LoadingState message="Loading available slots..." />
          </div>

          <div v-else-if="form.facility_uuid && form.booking_date && availableSlots.length > 0">
            <label class="block text-sm font-medium text-gray-700 mb-2">Available Time Slots</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
              <button
                v-for="slot in availableSlots"
                :key="slot.start_time"
                type="button"
                :disabled="!slot.available"
                @click="selectSlot(slot)"
                :class="[
                  'rounded-lg border px-3 py-2 text-sm font-medium transition-colors text-center',
                  selectedSlot?.start_time === slot.start_time
                    ? 'border-primary-500 bg-primary-50 text-primary-700 ring-2 ring-primary-500'
                    : slot.available
                      ? 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-300'
                      : 'border-gray-100 bg-gray-50 text-gray-400 cursor-not-allowed',
                ]"
              >
                {{ slot.start_time }} - {{ slot.end_time }}
              </button>
            </div>
            <p v-if="errors.start_time" class="mt-1 text-sm text-red-600">{{ errors.start_time }}</p>
          </div>

          <div v-else-if="form.facility_uuid && form.booking_date && !loadingSlots" class="rounded-md bg-yellow-50 p-4">
            <p class="text-sm text-yellow-700">No available time slots for the selected date.</p>
          </div>

          <div class="flex gap-3 pt-2">
            <AppButton type="submit" :loading="submitting" :disabled="!selectedSlot">
              Book Now
            </AppButton>
            <AppButton variant="secondary" :to="{ name: 'resident.bookings' }">
              Cancel
            </AppButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import residentApi from '@/api/resident';
import PageHeader from '@/components/common/PageHeader.vue';
import AppInput from '@/components/common/AppInput.vue';
import AppSelect from '@/components/common/AppSelect.vue';
import AppButton from '@/components/common/AppButton.vue';
import LoadingState from '@/components/common/LoadingState.vue';

const props = defineProps({
  facilityUuid: { type: String, default: '' },
});

const router = useRouter();

const form = reactive({
  facility_uuid: '',
  booking_date: '',
});

const errors = reactive({
  facility_uuid: '',
  booking_date: '',
  start_time: '',
});

const facilities = ref([]);
const facilityOptions = ref([]);
const availableSlots = ref([]);
const selectedSlot = ref(null);
const loadingFacilities = ref(true);
const loadingSlots = ref(false);
const submitting = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const today = computed(() => new Date().toISOString().split('T')[0]);

const maxDate = computed(() => {
  const selected = facilities.value.find((f) => f.uuid === form.facility_uuid);
  if (selected?.advance_booking_days) {
    const d = new Date();
    d.setDate(d.getDate() + selected.advance_booking_days);
    return d.toISOString().split('T')[0];
  }
  return '';
});

onMounted(async () => {
  try {
    const { data } = await residentApi.getFacilities();
    facilities.value = data.data;
    facilityOptions.value = facilities.value
      .filter((f) => !f.is_under_maintenance)
      .map((f) => ({ value: f.uuid, label: f.name }));

    if (props.facilityUuid) {
      form.facility_uuid = props.facilityUuid;
    }
  } catch {
    errorMessage.value = 'Failed to load facilities.';
  } finally {
    loadingFacilities.value = false;
  }
});

watch(() => form.facility_uuid, () => {
  form.booking_date = '';
  availableSlots.value = [];
  selectedSlot.value = null;
});

watch(() => form.booking_date, async (newVal) => {
  availableSlots.value = [];
  selectedSlot.value = null;

  if (!form.facility_uuid || !newVal) return;

  loadingSlots.value = true;
  try {
    const { data } = await residentApi.getFacilityAvailability(form.facility_uuid, { date: newVal });
    availableSlots.value = data.data;
  } catch {
    errorMessage.value = 'Failed to load available time slots.';
  } finally {
    loadingSlots.value = false;
  }
});

function selectSlot(slot) {
  if (!slot.available) return;
  selectedSlot.value = slot;
  errors.start_time = '';
}

async function handleSubmit() {
  Object.keys(errors).forEach((k) => { errors[k] = ''; });
  errorMessage.value = '';

  if (!selectedSlot.value) {
    errors.start_time = 'Please select a time slot.';
    return;
  }

  submitting.value = true;

  try {
    await residentApi.createBooking({
      facility_uuid: form.facility_uuid,
      booking_date: form.booking_date,
      start_time: selectedSlot.value.start_time,
      end_time: selectedSlot.value.end_time,
    });
    router.push({ name: 'resident.bookings' });
  } catch (error) {
    const response = error.response;
    if (response?.status === 422) {
      if (response.data.errors) {
        Object.keys(response.data.errors).forEach((key) => {
          if (errors.hasOwnProperty(key)) {
            errors[key] = response.data.errors[key][0];
          }
        });
      }
      errorMessage.value = response.data.message || '';
    } else {
      errorMessage.value = response?.data?.message || 'Failed to create booking.';
    }
  } finally {
    submitting.value = false;
  }
}
</script>
