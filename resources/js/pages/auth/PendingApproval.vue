<template>
    <div class="animate-fade-in-up">
        <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.04]">
            <div class="p-6 text-center sm:p-8">
                <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 ring-1 ring-amber-100">
                    <svg
                        class="h-7 w-7 text-amber-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                        />
                    </svg>
                </div>

                <h2 class="mb-2 text-[16px] font-semibold text-zinc-900">
                    Account Pending Approval
                </h2>
                <p class="mb-7 text-[13px] leading-relaxed text-zinc-500">
                    Your account is currently being reviewed by the management. You will
                    be notified once your account has been approved.
                </p>

                <div
                    v-if="auth.user"
                    class="mb-7 rounded-xl border border-zinc-100 bg-zinc-50/80 p-4 text-left"
                >
                    <dl class="space-y-2.5">
                        <div class="flex justify-between">
                            <dt class="text-[13px] text-zinc-500">Name</dt>
                            <dd class="text-[13px] font-medium text-zinc-900">
                                {{ auth.user.name }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-[13px] text-zinc-500">Email</dt>
                            <dd class="text-[13px] font-medium text-zinc-900">
                                {{ auth.user.email }}
                            </dd>
                        </div>
                    </dl>
                </div>

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
