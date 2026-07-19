<template>
    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-base font-semibold text-gray-900">
                Reset your password
            </h2>
            <p class="mt-0.5 text-sm text-gray-500">
                Choose a new password for your account.
            </p>
        </div>

        <div class="px-5 py-5">
            <div
                v-if="errorMessage"
                class="mb-5 flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-5 py-4"
            >
                <svg
                    class="mt-0.5 h-5 w-5 shrink-0 text-red-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"
                    />
                </svg>
                <p class="text-sm text-red-700">{{ errorMessage }}</p>
            </div>

            <form @submit.prevent="handleReset" class="space-y-5">
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
                <router-link
                    :to="{ name: 'login' }"
                    class="font-medium text-primary-600 transition-colors hover:text-primary-500"
                >
                    Back to sign in
                </router-link>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import AppInput from "@/components/common/AppInput.vue";
import AppButton from "@/components/common/AppButton.vue";

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();

const form = reactive({
    token: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const errors = reactive({
    email: "",
    password: "",
    password_confirmation: "",
});

const loading = ref(false);
const errorMessage = ref("");

onMounted(() => {
    form.token = route.query.token || "";
    form.email = route.query.email || "";

    if (!form.token || !form.email) {
        errorMessage.value = "Invalid reset link. Please request a new one.";
    }
});

async function handleReset() {
    errors.email = "";
    errors.password = "";
    errors.password_confirmation = "";
    errorMessage.value = "";
    loading.value = true;

    try {
        await auth.resetPassword(form);
        router.push({ name: "login", query: { reset: "success" } });
    } catch (error) {
        const response = error.response;
        if (response?.status === 422) {
            const data = response.data;
            if (data.errors) {
                errors.email = data.errors.email?.[0] || "";
                errors.password = data.errors.password?.[0] || "";
                errors.password_confirmation =
                    data.errors.password_confirmation?.[0] || "";
            }
            errorMessage.value = data.message || "";
        } else {
            errorMessage.value =
                "An unexpected error occurred. Please try again.";
        }
    } finally {
        loading.value = false;
    }
}
</script>
