<template>
    <div>
        <div class="mb-8 hidden lg:block">
            <h2 class="text-2xl font-bold text-gray-900">
                Complete your profile
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Tell us where you live so we can set up your account
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-5 py-4 lg:hidden">
                <h2 class="text-base font-semibold text-gray-900">
                    Complete your profile
                </h2>
                <p class="mt-0.5 text-sm text-gray-500">
                    Tell us where you live so we can set up your account
                </p>
            </div>

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

                    <AppButton type="submit" :loading="loading" class="w-full">
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
