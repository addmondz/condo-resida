<template>
    <div class="animate-fade-in-up">
        <div class="mb-8 hidden lg:block">
            <h2 class="text-[22px] font-semibold tracking-tight text-zinc-900">Forgot your password?</h2>
            <p class="mt-1.5 text-[14px] text-zinc-500">
                Enter your email and we'll send you a reset link.
            </p>
        </div>

        <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.04]">
            <div class="p-6 sm:p-7">
                <Transition name="page-fade">
                    <div
                        v-if="successMessage"
                        class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-100 bg-emerald-50/80 px-4 py-3.5"
                    >
                        <svg
                            class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                            />
                        </svg>
                        <p class="text-[13px] leading-snug text-emerald-700">{{ successMessage }}</p>
                    </div>
                </Transition>

                <Transition name="page-fade">
                    <div
                        v-if="errorMessage"
                        class="mb-6 flex items-start gap-3 rounded-xl border border-red-100 bg-red-50/80 px-4 py-3.5"
                    >
                        <svg
                            class="mt-0.5 h-4 w-4 shrink-0 text-red-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"
                            />
                        </svg>
                        <p class="text-[13px] leading-snug text-red-700">{{ errorMessage }}</p>
                    </div>
                </Transition>

                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <AppInput
                        v-model="email"
                        label="Email"
                        type="email"
                        placeholder="name@company.com"
                        :error="emailError"
                        required
                    />

                    <AppButton type="submit" :loading="loading" size="lg" class="!mt-7 w-full">
                        Send reset link
                    </AppButton>
                </form>

                <p class="mt-7 text-center">
                    <router-link
                        :to="{ name: 'login' }"
                        class="text-[13px] font-medium text-primary-600 transition-colors hover:text-primary-700"
                    >
                        Back to sign in
                    </router-link>
                </p>
            </div>
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
