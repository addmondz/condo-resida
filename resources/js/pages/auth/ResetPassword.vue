<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-6">Reset your password</h2>

    <div v-if="errorMessage" class="mb-4 rounded-md bg-red-50 p-4">
      <p class="text-sm text-red-700">{{ errorMessage }}</p>
    </div>

    <form @submit.prevent="handleReset" class="space-y-4">
      <AppInput
        v-model="form.email"
        label="Email"
        type="email"
        :error="errors.email"
        disabled
      />

      <AppInput
        v-model="form.password"
        label="New Password"
        type="password"
        placeholder="Enter new password"
        :error="errors.password"
        required
      />

      <AppInput
        v-model="form.password_confirmation"
        label="Confirm Password"
        type="password"
        placeholder="Confirm new password"
        :error="errors.password_confirmation"
        required
      />

      <AppButton type="submit" :loading="loading" class="w-full">
        Reset Password
      </AppButton>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
      <router-link :to="{ name: 'login' }" class="font-medium text-primary-600 hover:text-primary-500">
        Back to sign in
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import AppInput from '@/components/common/AppInput.vue';
import AppButton from '@/components/common/AppButton.vue';

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();

const form = reactive({
  token: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const errors = reactive({
  email: '',
  password: '',
  password_confirmation: '',
});

const loading = ref(false);
const errorMessage = ref('');

onMounted(() => {
  form.token = route.query.token || '';
  form.email = route.query.email || '';

  if (!form.token || !form.email) {
    errorMessage.value = 'Invalid reset link. Please request a new one.';
  }
});

async function handleReset() {
  errors.email = '';
  errors.password = '';
  errors.password_confirmation = '';
  errorMessage.value = '';
  loading.value = true;

  try {
    await auth.resetPassword(form);
    router.push({ name: 'login', query: { reset: 'success' } });
  } catch (error) {
    const response = error.response;
    if (response?.status === 422) {
      const data = response.data;
      if (data.errors) {
        errors.email = data.errors.email?.[0] || '';
        errors.password = data.errors.password?.[0] || '';
        errors.password_confirmation = data.errors.password_confirmation?.[0] || '';
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
