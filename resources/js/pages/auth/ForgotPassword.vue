<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-2">Forgot your password?</h2>
    <p class="text-sm text-gray-500 mb-6">Enter your email and we'll send you a reset link.</p>

    <div v-if="successMessage" class="mb-4 rounded-md bg-green-50 p-4">
      <p class="text-sm text-green-700">{{ successMessage }}</p>
    </div>

    <div v-if="errorMessage" class="mb-4 rounded-md bg-red-50 p-4">
      <p class="text-sm text-red-700">{{ errorMessage }}</p>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <AppInput
        v-model="email"
        label="Email"
        type="email"
        placeholder="you@example.com"
        :error="emailError"
        required
      />

      <AppButton type="submit" :loading="loading" class="w-full">
        Send Reset Link
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
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import AppInput from '@/components/common/AppInput.vue';
import AppButton from '@/components/common/AppButton.vue';

const auth = useAuthStore();

const email = ref('');
const emailError = ref('');
const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

async function handleSubmit() {
  emailError.value = '';
  errorMessage.value = '';
  successMessage.value = '';
  loading.value = true;

  try {
    await auth.forgotPassword({ email: email.value });
    successMessage.value = 'We have emailed your password reset link. Please check your inbox.';
    email.value = '';
  } catch (error) {
    const response = error.response;
    if (response?.status === 422) {
      emailError.value = response.data.errors?.email?.[0] || '';
      errorMessage.value = response.data.message || '';
    } else {
      errorMessage.value = 'An unexpected error occurred. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
