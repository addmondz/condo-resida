<template>
    <div class="pb-16 lg:pb-0">
        <PageHeader
            title="Register Visitor"
            :breadcrumbs="[
                { label: 'Visitors', to: { name: 'resident.visitors' } },
                { label: 'Register' },
            ]"
        />

        <div class="max-w-2xl">
            <div class="rounded-xl bg-white border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">
                        Visitor Details
                    </h2>
                    <p class="mt-0.5 text-sm text-gray-500">
                        Fill in the details below to generate a visitor pass.
                    </p>
                </div>

                <div class="px-5 py-5">
                    <div
                        v-if="errorMessage"
                        class="mb-5 flex items-start gap-3 rounded-xl bg-red-50 border border-red-100 px-4 py-3"
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

                    <form @submit.prevent="handleSubmit" class="space-y-5">
                        <AppSelect
                            v-model="form.purpose"
                            label="Purpose of Visit"
                            :options="purposeOptions"
                            placeholder="Select purpose"
                            :error="errors.purpose"
                            required
                        />

                        <AppInput
                            v-model="form.visitor_name"
                            label="Visitor Name"
                            placeholder="Enter visitor's full name"
                            :error="errors.visitor_name"
                            required
                        />

                        <AppInput
                            v-model="form.contact_number"
                            label="Contact Number"
                            type="tel"
                            placeholder="+60123456789"
                            :error="errors.contact_number"
                            required
                        />

                        <AppInput
                            v-model="form.vehicle_number"
                            label="Vehicle Number"
                            placeholder="e.g. ABC 1234 (optional)"
                            :error="errors.vehicle_number"
                            hint="Leave blank if the visitor is not driving."
                        />

                        <AppInput
                            v-model="form.visit_date"
                            label="Visit Date"
                            type="date"
                            :min="today"
                            :error="errors.visit_date"
                            required
                        />

                        <AppTextarea
                            v-model="form.notes"
                            label="Notes"
                            placeholder="Any additional notes (optional)"
                            :error="errors.notes"
                            :rows="3"
                        />

                        <div class="flex items-center gap-3 pt-2">
                            <AppButton type="submit" :loading="loading">
                                Register Visitor
                            </AppButton>
                            <AppButton
                                variant="ghost"
                                :to="{ name: 'resident.visitors' }"
                            >
                                Cancel
                            </AppButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import { useRouter } from "vue-router";
import residentApi from "@/api/resident";
import { useToast } from "@/composables/useToast";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppSelect from "@/components/common/AppSelect.vue";
import AppTextarea from "@/components/common/AppTextarea.vue";
import AppButton from "@/components/common/AppButton.vue";

const router = useRouter();
const toast = useToast();

const purposeOptions = [
    { value: "visitor", label: "Visitor" },
    { value: "delivery", label: "Delivery" },
    { value: "contractor", label: "Contractor" },
    { value: "service_provider", label: "Service Provider" },
    { value: "family", label: "Family" },
    { value: "other", label: "Other" },
];

const today = computed(() => {
    const d = new Date();
    return d.toISOString().split("T")[0];
});

const form = reactive({
    purpose: "",
    visitor_name: "",
    contact_number: "",
    vehicle_number: "",
    visit_date: "",
    notes: "",
});

const errors = reactive({
    purpose: "",
    visitor_name: "",
    contact_number: "",
    vehicle_number: "",
    visit_date: "",
    notes: "",
});

const loading = ref(false);
const errorMessage = ref("");

async function handleSubmit() {
    Object.keys(errors).forEach((k) => {
        errors[k] = "";
    });
    errorMessage.value = "";
    loading.value = true;

    try {
        const { data } = await residentApi.createVisitor(form);
        const created = data.data;
        router.push({
            name: "resident.visitors.show",
            params: { uuid: created.visitor.uuid },
            query: { qr_token: created.qr_token },
        });
    } catch (err) {
        const response = err.response;
        if (response?.status === 422) {
            if (response.data.errors) {
                Object.keys(response.data.errors).forEach((key) => {
                    if (errors.hasOwnProperty(key)) {
                        errors[key] = response.data.errors[key][0];
                    }
                });
            }
            errorMessage.value = response.data.message || "";
        } else {
            errorMessage.value =
                "An unexpected error occurred. Please try again.";
            toast.error("Failed to register visitor. Please try again.");
        }
    } finally {
        loading.value = false;
    }
}
</script>
