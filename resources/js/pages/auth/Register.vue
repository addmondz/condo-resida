<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-6">Create an account</h2>

    <div v-if="errorMessage" class="mb-4 rounded-md bg-red-50 p-4">
      <p class="text-sm text-red-700">{{ errorMessage }}</p>
    </div>

    <form @submit.prevent="handleRegister" class="space-y-4">
      <AppInput
        v-model="form.name"
        label="Full Name"
        placeholder="John Doe"
        :error="errors.name"
        required
      />

      <AppInput
        v-model="form.email"
        label="Email"
        type="email"
        placeholder="you@example.com"
        :error="errors.email"
        required
      />

      <AppInput
        v-model="form.phone"
        label="Phone"
        type="tel"
        placeholder="+60123456789"
        :error="errors.phone"
        required
      />

      <AppSelect
        v-model="form.property_uuid"
        label="Property"
        :options="propertyOptions"
        placeholder="Select a property"
        :error="errors.property_uuid"
        required
      />

      <AppSelect
        v-model="form.block_uuid"
        label="Block"
        :options="blockOptions"
        placeholder="Select a block"
        :disabled="!form.property_uuid || loadingBlocks"
        :error="errors.block_uuid"
        required
      />

      <AppSelect
        v-model="form.unit_uuid"
        label="Unit"
        :options="unitOptions"
        placeholder="Select a unit"
        :disabled="!form.block_uuid || loadingUnits"
        :error="errors.unit_uuid"
        required
      />

      <AppSelect
        v-model="form.resident_type"
        label="Resident Type"
        :options="residentTypeOptions"
        placeholder="Select resident type"
        :error="errors.resident_type"
        required
      />

      <AppInput
        v-model="form.password"
        label="Password"
        type="password"
        placeholder="Create a password"
        :error="errors.password"
        required
      />

      <AppInput
        v-model="form.password_confirmation"
        label="Confirm Password"
        type="password"
        placeholder="Confirm your password"
        :error="errors.password_confirmation"
        required
      />

      <AppButton type="submit" :loading="loading" class="w-full">
        Register
      </AppButton>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
      Already have an account?
      <router-link :to="{ name: 'login' }" class="font-medium text-primary-600 hover:text-primary-500">
        Sign in
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import authApi from '@/api/auth';
import AppInput from '@/components/common/AppInput.vue';
import AppSelect from '@/components/common/AppSelect.vue';
import AppButton from '@/components/common/AppButton.vue';

const router = useRouter();
const auth = useAuthStore();

const form = reactive({
  name: '',
  email: '',
  phone: '',
  property_uuid: '',
  block_uuid: '',
  unit_uuid: '',
  resident_type: '',
  password: '',
  password_confirmation: '',
});

const errors = reactive({
  name: '',
  email: '',
  phone: '',
  property_uuid: '',
  block_uuid: '',
  unit_uuid: '',
  resident_type: '',
  password: '',
  password_confirmation: '',
});

const loading = ref(false);
const errorMessage = ref('');

const properties = ref([]);
const blocks = ref([]);
const units = ref([]);
const loadingBlocks = ref(false);
const loadingUnits = ref(false);

const residentTypeOptions = [
  { value: 'owner', label: 'Owner' },
  { value: 'tenant', label: 'Tenant' },
];

const propertyOptions = ref([]);
const blockOptions = ref([]);
const unitOptions = ref([]);

onMounted(async () => {
  try {
    const { data } = await authApi.getProperties();
    properties.value = data.data;
    propertyOptions.value = properties.value.map((p) => ({ value: p.uuid, label: p.name }));
  } catch {
    errorMessage.value = 'Failed to load properties.';
  }
});

watch(() => form.property_uuid, async (newVal) => {
  form.block_uuid = '';
  form.unit_uuid = '';
  blocks.value = [];
  units.value = [];
  blockOptions.value = [];
  unitOptions.value = [];

  if (!newVal) return;

  loadingBlocks.value = true;
  try {
    const { data } = await authApi.getBlocks(newVal);
    blocks.value = data.data;
    blockOptions.value = blocks.value.map((b) => ({ value: b.uuid, label: b.name }));
  } catch {
    errorMessage.value = 'Failed to load blocks.';
  } finally {
    loadingBlocks.value = false;
  }
});

watch(() => form.block_uuid, async (newVal) => {
  form.unit_uuid = '';
  units.value = [];
  unitOptions.value = [];

  if (!newVal) return;

  loadingUnits.value = true;
  try {
    const { data } = await authApi.getUnits(newVal);
    units.value = data.data;
    unitOptions.value = units.value.map((u) => ({ value: u.uuid, label: u.number }));
  } catch {
    errorMessage.value = 'Failed to load units.';
  } finally {
    loadingUnits.value = false;
  }
});

function clearErrors() {
  Object.keys(errors).forEach((key) => { errors[key] = ''; });
  errorMessage.value = '';
}

async function handleRegister() {
  clearErrors();
  loading.value = true;

  try {
    await auth.register(form);
    router.push({ name: 'pending-approval' });
  } catch (error) {
    const response = error.response;
    if (response?.status === 422) {
      const data = response.data;
      if (data.errors) {
        Object.keys(data.errors).forEach((key) => {
          if (errors.hasOwnProperty(key)) {
            errors[key] = data.errors[key][0];
          }
        });
      }
      errorMessage.value = data.message || '';
    } else {
      errorMessage.value = 'An unexpected error occurred. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
