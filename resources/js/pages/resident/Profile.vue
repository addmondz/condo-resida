<template>
  <div>
    <PageHeader title="My Profile" subtitle="View and manage your account details." />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Profile Details -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Information</h2>

        <div v-if="successMessage" class="mb-4 rounded-md bg-green-50 p-4">
          <p class="text-sm text-green-700">{{ successMessage }}</p>
        </div>
        <div v-if="profileError" class="mb-4 rounded-md bg-red-50 p-4">
          <p class="text-sm text-red-700">{{ profileError }}</p>
        </div>

        <LoadingState v-if="loading" message="Loading profile..." />

        <template v-else>
          <dl class="space-y-3 mb-6">
            <div class="flex justify-between py-2 border-b border-gray-100">
              <dt class="text-sm text-gray-500">Email</dt>
              <dd class="text-sm font-medium text-gray-900">{{ profile.email }}</dd>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
              <dt class="text-sm text-gray-500">Property</dt>
              <dd class="text-sm font-medium text-gray-900">{{ profile.property?.name || '-' }}</dd>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
              <dt class="text-sm text-gray-500">Block &amp; Unit</dt>
              <dd class="text-sm font-medium text-gray-900">{{ profile.block?.name || '-' }}, {{ profile.unit?.number || '-' }}</dd>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
              <dt class="text-sm text-gray-500">Resident Type</dt>
              <dd class="text-sm font-medium text-gray-900 capitalize">{{ profile.resident_type || '-' }}</dd>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
              <dt class="text-sm text-gray-500">Status</dt>
              <dd><StatusBadge :status="profile.status || 'pending'" /></dd>
            </div>
          </dl>

          <form @submit.prevent="handleUpdateProfile" class="space-y-4">
            <AppInput
              v-model="profileForm.name"
              label="Full Name"
              :error="profileErrors.name"
              required
            />
            <AppInput
              v-model="profileForm.phone"
              label="Phone"
              type="tel"
              :error="profileErrors.phone"
              required
            />
            <AppButton type="submit" :loading="savingProfile">
              Update Profile
            </AppButton>
          </form>
        </template>
      </div>

      <!-- Change Password -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h2>

        <div v-if="passwordSuccess" class="mb-4 rounded-md bg-green-50 p-4">
          <p class="text-sm text-green-700">{{ passwordSuccess }}</p>
        </div>
        <div v-if="passwordError" class="mb-4 rounded-md bg-red-50 p-4">
          <p class="text-sm text-red-700">{{ passwordError }}</p>
        </div>

        <form @submit.prevent="handleChangePassword" class="space-y-4">
          <AppInput
            v-model="passwordForm.current_password"
            label="Current Password"
            type="password"
            :error="passwordErrors.current_password"
            required
          />
          <AppInput
            v-model="passwordForm.password"
            label="New Password"
            type="password"
            :error="passwordErrors.password"
            required
          />
          <AppInput
            v-model="passwordForm.password_confirmation"
            label="Confirm New Password"
            type="password"
            :error="passwordErrors.password_confirmation"
            required
          />
          <AppButton type="submit" :loading="savingPassword">
            Change Password
          </AppButton>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import residentApi from '@/api/resident';
import PageHeader from '@/components/common/PageHeader.vue';
import AppInput from '@/components/common/AppInput.vue';
import AppButton from '@/components/common/AppButton.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import LoadingState from '@/components/common/LoadingState.vue';

const auth = useAuthStore();

const loading = ref(true);
const profile = ref({});
const successMessage = ref('');
const profileError = ref('');

const profileForm = reactive({ name: '', phone: '' });
const profileErrors = reactive({ name: '', phone: '' });
const savingProfile = ref(false);

const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' });
const passwordErrors = reactive({ current_password: '', password: '', password_confirmation: '' });
const savingPassword = ref(false);
const passwordSuccess = ref('');
const passwordError = ref('');

onMounted(async () => {
  try {
    const { data } = await residentApi.getProfile();
    profile.value = data.data;
    profileForm.name = profile.value.name || '';
    profileForm.phone = profile.value.phone || '';
  } catch {
    profileError.value = 'Failed to load profile.';
  } finally {
    loading.value = false;
  }
});

async function handleUpdateProfile() {
  profileErrors.name = '';
  profileErrors.phone = '';
  profileError.value = '';
  successMessage.value = '';
  savingProfile.value = true;

  try {
    const { data } = await residentApi.updateProfile(profileForm);
    profile.value = data.data;
    successMessage.value = 'Profile updated successfully.';
  } catch (error) {
    const response = error.response;
    if (response?.status === 422 && response.data.errors) {
      profileErrors.name = response.data.errors.name?.[0] || '';
      profileErrors.phone = response.data.errors.phone?.[0] || '';
    }
    profileError.value = response?.data?.message || 'Failed to update profile.';
  } finally {
    savingProfile.value = false;
  }
}

async function handleChangePassword() {
  Object.keys(passwordErrors).forEach((k) => { passwordErrors[k] = ''; });
  passwordError.value = '';
  passwordSuccess.value = '';
  savingPassword.value = true;

  try {
    await auth.changePassword(passwordForm);
    passwordSuccess.value = 'Password changed successfully.';
    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';
  } catch (error) {
    const response = error.response;
    if (response?.status === 422 && response.data.errors) {
      Object.keys(response.data.errors).forEach((key) => {
        if (passwordErrors.hasOwnProperty(key)) {
          passwordErrors[key] = response.data.errors[key][0];
        }
      });
    }
    passwordError.value = response?.data?.message || 'Failed to change password.';
  } finally {
    savingPassword.value = false;
  }
}
</script>
