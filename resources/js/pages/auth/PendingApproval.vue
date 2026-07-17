<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100 mb-4">
      <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
    </div>

    <h2 class="text-xl font-semibold text-gray-900 mb-2">Account Pending Approval</h2>
    <p class="text-sm text-gray-500 mb-6">
      Your account is currently being reviewed by the management. You will be notified once your account has been approved.
    </p>

    <div v-if="auth.user" class="mb-6 rounded-md bg-gray-50 p-4 text-left">
      <dl class="space-y-2 text-sm">
        <div class="flex justify-between">
          <dt class="text-gray-500">Name</dt>
          <dd class="font-medium text-gray-900">{{ auth.user.name }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-gray-500">Email</dt>
          <dd class="font-medium text-gray-900">{{ auth.user.email }}</dd>
        </div>
      </dl>
    </div>

    <AppButton variant="secondary" :loading="loggingOut" @click="handleLogout" class="w-full">
      Logout
    </AppButton>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import AppButton from '@/components/common/AppButton.vue';

const router = useRouter();
const auth = useAuthStore();
const loggingOut = ref(false);

async function handleLogout() {
  loggingOut.value = true;
  try {
    await auth.logout();
    router.push({ name: 'login' });
  } finally {
    loggingOut.value = false;
  }
}
</script>
