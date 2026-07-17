<template>
  <div>
    <PageHeader title="Register Visitor" subtitle="Create a new visitor pass." />

    <div class="max-w-2xl">
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div v-if="errorMessage" class="mb-4 rounded-md bg-red-50 p-4">
          <p class="text-sm text-red-700">{{ errorMessage }}</p>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <AppSelect
            v-model="form.purpose"
            label="Purpose of Visit"
            :options="purposeOptions"
            placeholder="Select purpose"
            :error="errors.purpose"
            required
          />

          <AppInput
            v-model="form.visitor_name"
            label="Visitor Name"
            placeholder="Enter visitor's full name"
            :error="errors.visitor_name"
            required
          />

          <AppInput
            v-model="form.visitor_phone"
            label="Contact Number"
            type="tel"
            placeholder="+60123456789"
            :error="errors.visitor_phone"
            required
          />

          <AppInput
            v-model="form.vehicle_number"
            label="Vehicle Number"
            placeholder="e.g. ABC 1234 (optional)"
            :error="errors.vehicle_number"
            hint="Leave blank if the visitor is not driving."
          />

          <AppInput
            v-model="form.visit_date"
            label="Visit Date"
            type="date"
            :min="today"
            :error="errors.visit_date"
            required
          />

          <AppTextarea
            v-model="form.notes"
            label="Notes"
            placeholder="Any additional notes (optional)"
            :error="errors.notes"
            :rows="3"
          />

          <div class="flex gap-3 pt-2">
            <AppButton type="submit" :loading="loading">
              Register Visitor
            </AppButton>
            <AppButton variant="secondary" :to="{ name: 'resident.visitors' }">
              Cancel
            </AppButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import residentApi from '@/api/resident';
import PageHeader from '@/components/common/PageHeader.vue';
import AppInput from '@/components/common/AppInput.vue';
import AppSelect from '@/components/common/AppSelect.vue';
import AppTextarea from '@/components/common/AppTextarea.vue';
import AppButton from '@/components/common/AppButton.vue';

const router = useRouter();

const purposeOptions = [
  { value: 'visitor', label: 'Visitor' },
  { value: 'delivery', label: 'Delivery' },
  { value: 'contractor', label: 'Contractor' },
  { value: 'service_provider', label: 'Service Provider' },
  { value: 'family', label: 'Family' },
  { value: 'other', label: 'Other' },
];

const today = computed(() => {
  const d = new Date();
  return d.toISOString().split('T')[0];
});

const form = reactive({
  purpose: '',
  visitor_name: '',
  visitor_phone: '',
  vehicle_number: '',
  visit_date: '',
  notes: '',
});

const errors = reactive({
  purpose: '',
  visitor_name: '',
  visitor_phone: '',
  vehicle_number: '',
  visit_date: '',
  notes: '',
});

const loading = ref(false);
const errorMessage = ref('');

async function handleSubmit() {
  Object.keys(errors).forEach((k) => { errors[k] = ''; });
  errorMessage.value = '';
  loading.value = true;

  try {
    const { data } = await residentApi.createVisitor(form);
    router.push({ name: 'resident.visitors.show', params: { uuid: data.data.uuid } });
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
      errorMessage.value = 'An unexpected error occurred. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
