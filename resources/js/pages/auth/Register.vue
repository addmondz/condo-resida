<template>
    <div class="animate-fade-in-up">
        <div class="mb-8 hidden lg:block">
            <h2 class="text-[22px] font-semibold tracking-tight text-zinc-900">Create an account</h2>
            <p class="mt-1.5 text-[14px] text-zinc-500">
                Get started with Rumi in seconds
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

                <form @submit.prevent="handleRegister" class="space-y-5">
                    <AppInput
                        v-model="form.name"
                        label="Full name"
                        placeholder="John Doe"
                        :error="errors.name"
                        required
                    />

                    <AppInput
                        v-model="form.email"
                        label="Email"
                        type="email"
                        placeholder="name@company.com"
                        :error="errors.email"
                        required
                    />

                    <AppInput
                        v-model="form.phone"
                        label="Phone number"
                        type="tel"
                        placeholder="+60123456789"
                        :error="errors.phone"
                        required
                    />

                    <AppInput
                        v-model="form.password"
                        label="Password"
                        type="password"
                        placeholder="Create a password"
                        :error="errors.password"
                        required
                    />

                    <AppInput
                        v-model="form.password_confirmation"
                        label="Confirm password"
                        type="password"
                        placeholder="Confirm your password"
                        :error="errors.password_confirmation"
                        required
                    />

                    <AppButton type="submit" :loading="loading" size="lg" class="!mt-7 w-full">
                        Create account
                    </AppButton>
                </form>

                <!-- Divider -->
                <div class="relative my-7">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-zinc-100"></div>
                    </div>
                    <div class="relative flex justify-center text-[12px]">
                        <span class="bg-white px-3 text-zinc-400">Already have an account?</span>
                    </div>
                </div>

                <router-link
                    :to="{ name: 'login' }"
                    class="flex w-full items-center justify-center rounded-[10px] border border-zinc-200 bg-white px-4 py-2.5 text-[13px] font-medium text-zinc-700 shadow-sm shadow-zinc-900/[0.04] transition-all duration-200 hover:border-zinc-300 hover:bg-zinc-50 active:scale-[0.98]"
                >
                    Sign in instead
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import AppInput from "@/components/common/AppInput.vue";
import AppButton from "@/components/common/AppButton.vue";

const router = useRouter();
const auth = useAuthStore();

const form = reactive({
    name: "",
    email: "",
    phone: "",
    password: "",
    password_confirmation: "",
});

const errors = reactive({
    name: "",
    email: "",
    phone: "",
    password: "",
    password_confirmation: "",
});

const loading = ref(false);
const errorMessage = ref("");

function clearErrors() {
    Object.keys(errors).forEach((key) => {
        errors[key] = "";
    });
    errorMessage.value = "";
}

async function handleRegister() {
    clearErrors();
    loading.value = true;

    try {
        await auth.register(form);
        router.push({ name: "onboarding" });
    } catch (error) {
        const response = error.response;
        if (response?.status === 422) {
            const data = response.data;
            if (data.errors) {
                Object.keys(data.errors).forEach((key) => {
                    if (Object.prototype.hasOwnProperty.call(errors, key)) {
                        errors[key] = data.errors[key][0];
                    }
                });
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
