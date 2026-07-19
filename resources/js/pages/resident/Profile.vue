<template>
    <div class="pb-16 lg:pb-0">
        <PageHeader
            title="My Profile"
            subtitle="View and manage your account details."
            :breadcrumbs="[{ label: 'Profile' }]"
        />

        <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-xl border border-gray-200 bg-white px-5 py-5">
                <SkeletonLoader variant="form" :rows="5" />
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-5 py-5">
                <SkeletonLoader variant="form" :rows="3" />
            </div>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Profile Information -->
            <div class="rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-gray-900">
                        Profile Information
                    </h2>
                </div>
                <div class="px-5 py-5">
                    <dl>
                        <div
                            class="flex justify-between border-b border-gray-100 py-2.5"
                        >
                            <dt class="text-sm text-gray-500">Email</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ profile.email }}
                            </dd>
                        </div>
                        <div
                            class="flex justify-between border-b border-gray-100 py-2.5"
                        >
                            <dt class="text-sm text-gray-500">Property</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ profile.property?.name || "-" }}
                            </dd>
                        </div>
                        <div
                            class="flex justify-between border-b border-gray-100 py-2.5"
                        >
                            <dt class="text-sm text-gray-500">
                                Block &amp; Unit
                            </dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ profile.block?.name || "-" }},
                                {{ profile.unit?.number || "-" }}
                            </dd>
                        </div>
                        <div
                            class="flex justify-between border-b border-gray-100 py-2.5"
                        >
                            <dt class="text-sm text-gray-500">Resident Type</dt>
                            <dd
                                class="text-sm font-medium text-gray-900 capitalize"
                            >
                                {{ profile.resident_type || "-" }}
                            </dd>
                        </div>
                        <div
                            class="flex justify-between border-b border-gray-100 py-2.5"
                        >
                            <dt class="text-sm text-gray-500">Status</dt>
                            <dd>
                                <StatusBadge
                                    :status="profile.status || 'pending'"
                                />
                            </dd>
                        </div>
                    </dl>

                    <form
                        class="mt-6 space-y-4"
                        @submit.prevent="handleUpdateProfile"
                    >
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
                </div>
            </div>

            <!-- Change Password -->
            <div class="rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-gray-900">
                        Change Password
                    </h2>
                </div>
                <div class="px-5 py-5">
                    <form
                        class="space-y-4"
                        @submit.prevent="handleChangePassword"
                    >
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
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useToast } from "@/composables/useToast";
import residentApi from "@/api/resident";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppButton from "@/components/common/AppButton.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";

const auth = useAuthStore();
const toast = useToast();

const loading = ref(true);
const profile = ref({});

const profileForm = reactive({ name: "", phone: "" });
const profileErrors = reactive({ name: "", phone: "" });
const savingProfile = ref(false);

const passwordForm = reactive({
    current_password: "",
    password: "",
    password_confirmation: "",
});
const passwordErrors = reactive({
    current_password: "",
    password: "",
    password_confirmation: "",
});
const savingPassword = ref(false);

onMounted(async () => {
    try {
        const { data } = await residentApi.getProfile();
        profile.value = data.data;
        profileForm.name = profile.value.name || "";
        profileForm.phone = profile.value.phone || "";
    } catch {
        toast.error("Failed to load profile.");
    } finally {
        loading.value = false;
    }
});

async function handleUpdateProfile() {
    profileErrors.name = "";
    profileErrors.phone = "";
    savingProfile.value = true;

    try {
        const { data } = await residentApi.updateProfile(profileForm);
        profile.value = data.data;
        toast.success("Profile updated successfully.");
    } catch (error) {
        const response = error.response;
        if (response?.status === 422 && response.data.errors) {
            profileErrors.name = response.data.errors.name?.[0] || "";
            profileErrors.phone = response.data.errors.phone?.[0] || "";
        }
        toast.error(response?.data?.message || "Failed to update profile.");
    } finally {
        savingProfile.value = false;
    }
}

async function handleChangePassword() {
    Object.keys(passwordErrors).forEach((k) => {
        passwordErrors[k] = "";
    });
    savingPassword.value = true;

    try {
        await auth.changePassword(passwordForm);
        toast.success("Password changed successfully.");
        passwordForm.current_password = "";
        passwordForm.password = "";
        passwordForm.password_confirmation = "";
    } catch (error) {
        const response = error.response;
        if (response?.status === 422 && response.data.errors) {
            Object.keys(response.data.errors).forEach((key) => {
                if (passwordErrors.hasOwnProperty(key)) {
                    passwordErrors[key] = response.data.errors[key][0];
                }
            });
        }
        toast.error(response?.data?.message || "Failed to change password.");
    } finally {
        savingPassword.value = false;
    }
}
</script>
