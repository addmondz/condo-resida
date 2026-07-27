<template>
    <div class="animate-fade-in-up">
        <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.04]">
            <div class="p-6 text-center sm:p-8">
                <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-red-50 ring-1 ring-red-100">
                    <svg
                        class="h-7 w-7 text-red-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"
                        />
                    </svg>
                </div>

                <h2 class="mb-2 text-[16px] font-semibold text-zinc-900">
                    Registration Rejected
                </h2>
                <p class="mb-5 text-[13px] leading-relaxed text-zinc-500">
                    Unfortunately, your registration has been rejected by the management.
                </p>

                <div
                    v-if="auth.user?.rejection_reason"
                    class="mb-6 rounded-xl border border-red-100 bg-red-50/80 p-4 text-left"
                >
                    <p class="mb-1 text-[12px] font-medium text-red-800">Reason:</p>
                    <p class="text-[13px] leading-relaxed text-red-700">{{ auth.user.rejection_reason }}</p>
                </div>

                <p class="mb-7 text-[13px] text-zinc-500">
                    If you believe this is an error, please contact the property management office.
                </p>

                <AppButton
                    variant="secondary"
                    :loading="loggingOut"
                    @click="handleLogout"
                    class="w-full"
                >
                    Logout
                </AppButton>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import AppButton from "@/components/common/AppButton.vue";

const router = useRouter();
const auth = useAuthStore();
const loggingOut = ref(false);

async function handleLogout() {
    loggingOut.value = true;
    try {
        await auth.logout();
        router.push({ name: "login" });
    } finally {
        loggingOut.value = false;
    }
}
</script>
