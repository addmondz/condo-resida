<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-6">Sign in to your account</h2>

    <div v-if="errorMessage" class="mb-4 rounded-md bg-red-50 p-4">
      <p class="text-sm text-red-700">{{ errorMessage }}</p>
    </div>

    <form @submit.prevent="handleLogin" class="space-y-4">
      <AppInput
        v-model="form.email"
        label="Email"
        type="email"
        placeholder="you@example.com"
        :error="errors.email"
        required
      />

      <AppInput
        v-model="form.password"
        label="Password"
        type="password"
        placeholder="Enter your password"
        :error="errors.password"
        required
      />

      <div class="flex items-center justify-end">
        <router-link :to="{ name: 'forgot-password' }" class="text-sm text-primary-600 hover:text-primary-500">
          Forgot your password?
        </router-link>
      </div>

      <AppButton type="submit" :loading="loading" class="w-full">
        Sign in
      </AppButton>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
      Don't have an account?
      <router-link :to="{ name: 'register' }" class="font-medium text-primary-600 hover:text-primary-500">
        Register here
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import AppInput from '@/components/common/AppInput.vue';
import AppButton from '@/components/common/AppButton.vue';

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();

const form = reactive({
  email: '',
  password: '',
});

const errors = reactive({
  email: '',
  password: '',
});

const loading = ref(false);
const errorMessage = ref('');

function clearErrors() {
  errors.email = '';
  errors.password = '';
  errorMessage.value = '';
}

async function handleLogin() {
  clearErrors();
  loading.value = true;

  try {
    await auth.login(form);
    const redirect = route.query.redirect || auth.getHomePath();
    router.push(redirect);
  } catch (error) {
    const response = error.response;
    if (response?.status === 422) {
      const data = response.data;
      if (data.errors) {
        errors.email = data.errors.email?.[0] || '';
        errors.password = data.errors.password?.[0] || '';
      }
      errorMessage.value = data.message || '';
    } else if (response?.status === 401) {
      errorMessage.value = response.data?.message || 'Invalid credentials.';
    } else {
      errorMessage.value = 'An unexpected error occurred. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
