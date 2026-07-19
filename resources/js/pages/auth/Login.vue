<template>
    <div>
        <!-- Desktop heading - only shown when split panel is visible -->
        <div class="mb-8 hidden lg:block">
            <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
            <p class="mt-1 text-sm text-gray-500">
                Sign in to your account to continue
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="px-5 py-5 sm:px-6 sm:py-6">
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

                <form @submit.prevent="handleLogin" class="space-y-5">
                    <AppInput
                        v-model="form.email"
                        label="Email address"
                        type="email"
                        placeholder="Enter your email"
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

                    <div class="flex items-center justify-between">
                        <label
                            class="flex cursor-pointer items-center gap-2 text-sm text-gray-600"
                        >
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 cursor-pointer rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            />
                            Remember me
                        </label>
                        <router-link
                            :to="{ name: 'forgot-password' }"
                            class="text-sm font-medium text-primary-600 transition-colors hover:text-primary-500"
                        >
                            Forgot password?
                        </router-link>
                    </div>

                    <AppButton type="submit" :loading="loading" class="w-full">
                        Sign in
                    </AppButton>
                </form>

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="bg-white px-3 text-gray-400"></span
                        >
                    </div>
                </div>

                <router-link
                    :to="{ name: 'register' }"
                    class="flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-all hover:bg-gray-50 active:scale-[0.98]"
                >
                    Create an account
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import AppInput from "@/components/common/AppInput.vue";
import AppButton from "@/components/common/AppButton.vue";

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();

const form = reactive({
    email: "",
    password: "",
    remember: false,
});

const errors = reactive({
    email: "",
    password: "",
});

const loading = ref(false);
const errorMessage = ref("");

function clearErrors() {
    errors.email = "";
    errors.password = "";
    errorMessage.value = "";
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
                errors.email = data.errors.email?.[0] || "";
                errors.password = data.errors.password?.[0] || "";
            }
            errorMessage.value = data.message || "";
        } else if (response?.status === 401) {
            errorMessage.value =
                response.data?.message || "Invalid credentials.";
        } else {
            errorMessage.value =
                "An unexpected error occurred. Please try again.";
        }
    } finally {
        loading.value = false;
    }
}
</script>
