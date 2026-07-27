<template>
    <div class="animate-fade-in-up">
        <!-- Desktop heading -->
        <div class="mb-8 hidden lg:block">
            <h2 class="text-[22px] font-semibold tracking-tight text-zinc-900">Welcome back</h2>
            <p class="mt-1.5 text-[14px] text-zinc-500">
                Sign in to your account to continue
            </p>
        </div>

        <!-- Card -->
        <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.04]">
            <div class="p-6 sm:p-7">
                <!-- Error message -->
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

                <form @submit.prevent="handleLogin" class="space-y-5">
                    <AppInput
                        v-model="form.email"
                        label="Email"
                        type="email"
                        placeholder="name@company.com"
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
                        <label class="group flex cursor-pointer items-center gap-2.5">
                            <div class="relative">
                                <input
                                    v-model="form.remember"
                                    type="checkbox"
                                    class="peer h-4 w-4 cursor-pointer appearance-none rounded-[5px] border border-zinc-300 bg-white transition-all duration-200 checked:border-primary-600 checked:bg-primary-600 hover:border-zinc-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/20 focus-visible:ring-offset-1"
                                />
                                <svg
                                    class="pointer-events-none absolute left-0.5 top-0.5 h-3 w-3 text-white opacity-0 transition-opacity duration-150 peer-checked:opacity-100"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="3"
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <span class="text-[13px] text-zinc-600 transition-colors group-hover:text-zinc-800">Remember me</span>
                        </label>
                        <router-link
                            :to="{ name: 'forgot-password' }"
                            class="text-[13px] font-medium text-primary-600 transition-colors hover:text-primary-700"
                        >
                            Forgot password?
                        </router-link>
                    </div>

                    <AppButton
                        type="submit"
                        :loading="loading"
                        :disabled="!isFormValid"
                        size="lg"
                        class="!mt-7 w-full"
                    >
                        Sign in
                    </AppButton>
                </form>

                <!-- Divider -->
                <div class="relative my-7">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-zinc-100"></div>
                    </div>
                </div>

                <!-- Register link -->
                <router-link
                    :to="{ name: 'register' }"
                    class="flex w-full items-center justify-center rounded-[10px] border border-zinc-200 bg-white px-4 py-2.5 text-[13px] font-medium text-zinc-700 shadow-sm shadow-zinc-900/[0.04] transition-all duration-200 hover:border-zinc-300 hover:bg-zinc-50 active:scale-[0.98]"
                >
                    Create an account
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
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

const isFormValid = computed(() => form.email.length > 0 && form.password.length > 0);

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
