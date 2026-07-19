<template>
    <div
        class="rounded-xl border border-gray-200 bg-white px-5 py-8 text-center"
    >
        <div
            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100"
        >
            <svg
                class="h-8 w-8 text-red-600"
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

        <h2 class="mb-2 text-base font-semibold text-gray-900">
            Registration Rejected
        </h2>
        <p class="mb-4 text-sm text-gray-500">
            Unfortunately, your registration has been rejected by the
            management.
        </p>

        <div
            v-if="auth.user?.rejection_reason"
            class="mb-6 rounded-xl border border-red-100 bg-red-50 p-4 text-left"
        >
            <p class="mb-1 text-sm font-medium text-red-800">Reason:</p>
            <p class="text-sm text-red-700">{{ auth.user.rejection_reason }}</p>
        </div>

        <p class="mb-6 text-sm text-gray-500">
            If you believe this is an error, please contact the property
            management office.
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
