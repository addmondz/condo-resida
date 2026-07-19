<template>
    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-base font-semibold text-gray-900">
                Forgot your password?
            </h2>
            <p class="mt-0.5 text-sm text-gray-500">
                Enter your email and we'll send you a reset link.
            </p>
        </div>

        <div class="px-5 py-5">
            <div
                v-if="successMessage"
                class="mb-5 flex items-start gap-3 rounded-xl border border-green-100 bg-green-50 px-5 py-4"
            >
                <svg
                    class="mt-0.5 h-5 w-5 shrink-0 text-green-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                    />
                </svg>
                <p class="text-sm text-green-700">{{ successMessage }}</p>
            </div>

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

            <form @submit.prevent="handleSubmit" class="space-y-5">
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
import { ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import AppInput from "@/components/common/AppInput.vue";
import AppButton from "@/components/common/AppButton.vue";

const auth = useAuthStore();

const email = ref("");
const emailError = ref("");
const loading = ref(false);
const successMessage = ref("");
const errorMessage = ref("");

async function handleSubmit() {
    emailError.value = "";
    errorMessage.value = "";
    successMessage.value = "";
    loading.value = true;

    try {
        await auth.forgotPassword({ email: email.value });
        successMessage.value =
            "We have emailed your password reset link. Please check your inbox.";
        email.value = "";
    } catch (error) {
        const response = error.response;
        if (response?.status === 422) {
            emailError.value = response.data.errors?.email?.[0] || "";
            errorMessage.value = response.data.message || "";
        } else {
            errorMessage.value =
                "An unexpected error occurred. Please try again.";
        }
    } finally {
        loading.value = false;
    }
}
</script>
