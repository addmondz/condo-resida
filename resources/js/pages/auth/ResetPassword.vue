<template>
    <div class="animate-fade-in-up">
        <div class="mb-8 hidden lg:block">
            <h2 class="text-[22px] font-semibold tracking-tight text-zinc-900">Reset your password</h2>
            <p class="mt-1.5 text-[14px] text-zinc-500">
                Choose a new password for your account.
            </p>
        </div>

        <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.04]">
            <div class="p-6 sm:p-7">
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
                        label="New password"
                        type="password"
                        placeholder="Enter new password"
                        :error="errors.password"
                        required
                    />

                    <AppInput
                        v-model="form.password_confirmation"
                        label="Confirm password"
                        type="password"
                        placeholder="Confirm new password"
                        :error="errors.password_confirmation"
                        required
                    />

                    <AppButton type="submit" :loading="loading" size="lg" class="!mt-7 w-full">
                        Reset password
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
