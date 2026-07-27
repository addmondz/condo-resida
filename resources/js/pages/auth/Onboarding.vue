<template>
    <div class="animate-fade-in-up">
        <div class="mb-8 hidden lg:block">
            <h2 class="text-[22px] font-semibold tracking-tight text-zinc-900">
                Complete your profile
            </h2>
            <p class="mt-1.5 text-[14px] text-zinc-500">
                Tell us where you live so we can set up your account
            </p>
        </div>

        <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.04]">
            <!-- Mobile header -->
            <div class="border-b border-zinc-100 p-6 lg:hidden">
                <h2 class="text-[16px] font-semibold text-zinc-900">
                    Complete your profile
                </h2>
                <p class="mt-1 text-[13px] text-zinc-500">
                    Tell us where you live so we can set up your account
                </p>
            </div>

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

                <form @submit.prevent="handleOnboarding" class="space-y-5">
                    <AppSelect
                        v-model="form.property_uuid"
                        label="Property"
                        :options="propertyOptions"
                        placeholder="Select your property"
                        :error="errors.property_uuid"
                        required
                    />

                    <AppSelect
                        v-model="form.block_uuid"
                        label="Block"
                        :options="blockOptions"
                        placeholder="Select your block"
                        :disabled="!form.property_uuid || loadingBlocks"
                        :error="errors.block_uuid"
                        required
                    />

                    <AppSelect
                        v-model="form.unit_uuid"
                        label="Unit"
                        :options="unitOptions"
                        placeholder="Select your unit"
                        :disabled="!form.block_uuid || loadingUnits"
                        :error="errors.unit_uuid"
                        required
                    />

                    <AppSelect
                        v-model="form.resident_type"
                        label="I am a"
                        :options="residentTypeOptions"
                        placeholder="Select resident type"
                        :error="errors.resident_type"
                        required
                    />

                    <AppButton type="submit" :loading="loading" size="lg" class="!mt-7 w-full">
                        Continue
                    </AppButton>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import authApi from "@/api/auth";
import AppSelect from "@/components/common/AppSelect.vue";
import AppButton from "@/components/common/AppButton.vue";

const router = useRouter();
const auth = useAuthStore();

const form = reactive({
    property_uuid: "",
    block_uuid: "",
    unit_uuid: "",
    resident_type: "",
});

const errors = reactive({
    property_uuid: "",
    block_uuid: "",
    unit_uuid: "",
    resident_type: "",
});

const loading = ref(false);
const errorMessage = ref("");

const loadingBlocks = ref(false);
const loadingUnits = ref(false);

const residentTypeOptions = [
    { value: "owner", label: "Owner" },
    { value: "tenant", label: "Tenant" },
];

const propertyOptions = ref([]);
const blockOptions = ref([]);
const unitOptions = ref([]);

onMounted(async () => {
    try {
        const { data } = await authApi.getProperties();
        propertyOptions.value = data.data.map((p) => ({
            value: p.uuid,
            label: p.name,
        }));
    } catch {
        errorMessage.value = "Failed to load properties.";
    }
});

watch(
    () => form.property_uuid,
    async (newVal) => {
        form.block_uuid = "";
        form.unit_uuid = "";
        blockOptions.value = [];
        unitOptions.value = [];

        if (!newVal) return;

        loadingBlocks.value = true;
        try {
            const { data } = await authApi.getBlocks(newVal);
            blockOptions.value = data.data.map((b) => ({
                value: b.uuid,
                label: b.name,
            }));
        } catch {
            errorMessage.value = "Failed to load blocks.";
        } finally {
            loadingBlocks.value = false;
        }
    },
);

watch(
    () => form.block_uuid,
    async (newVal) => {
        form.unit_uuid = "";
        unitOptions.value = [];

        if (!newVal) return;

        loadingUnits.value = true;
        try {
            const { data } = await authApi.getUnits(newVal);
            unitOptions.value = data.data.map((u) => ({
                value: u.uuid,
                label: u.name,
            }));
        } catch {
            errorMessage.value = "Failed to load units.";
        } finally {
            loadingUnits.value = false;
        }
    },
);

function clearErrors() {
    Object.keys(errors).forEach((key) => {
        errors[key] = "";
    });
    errorMessage.value = "";
}

async function handleOnboarding() {
    clearErrors();
    loading.value = true;

    try {
        const { data } = await authApi.completeOnboarding(form);
        auth.user = data.data;
        router.push({ name: "pending-approval" });
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
        } else if (response?.status === 409) {
            router.push({ name: "pending-approval" });
        } else {
            errorMessage.value =
                "An unexpected error occurred. Please try again.";
        }
    } finally {
        loading.value = false;
    }
}
</script>
