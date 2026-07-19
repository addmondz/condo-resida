<template>
    <div>
        <PageHeader
            title="Add Facility"
            subtitle="Create a new bookable facility."
            :breadcrumbs="[
                { label: 'Facilities', to: { name: 'admin.facilities' } },
                { label: 'Add Facility' },
            ]"
        />

        <div class="max-w-2xl">
            <div class="rounded-xl border border-gray-200 bg-white px-5 py-5">
                <div
                    v-if="errorMessage"
                    class="mb-4 rounded-xl border border-red-100 bg-red-50 px-5 py-4"
                >
                    <p class="text-sm text-red-700">{{ errorMessage }}</p>
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <AppSelect
                        v-model="form.property_uuid"
                        label="Property"
                        :options="propertyOptions"
                        placeholder="Select a property"
                        :error="errors.property_uuid"
                        required
                    />

                    <AppInput
                        v-model="form.name"
                        label="Name"
                        placeholder="e.g. Swimming Pool"
                        :error="errors.name"
                        required
                    />

                    <AppTextarea
                        v-model="form.description"
                        label="Description"
                        placeholder="Describe the facility"
                        :error="errors.description"
                        :rows="3"
                    />

                    <AppTextarea
                        v-model="form.rules"
                        label="Rules & Regulations"
                        placeholder="Usage rules"
                        :error="errors.rules"
                        :rows="3"
                    />

                    <div class="grid grid-cols-2 gap-4">
                        <AppInput
                            v-model="form.capacity"
                            label="Capacity"
                            type="number"
                            min="1"
                            :error="errors.capacity"
                        />
                        <AppInput
                            v-model="form.slot_duration"
                            label="Slot Duration (min)"
                            type="number"
                            min="15"
                            :error="errors.slot_duration"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <AppInput
                            v-model="form.opening_time"
                            label="Opening Time"
                            type="time"
                            :error="errors.opening_time"
                        />
                        <AppInput
                            v-model="form.closing_time"
                            label="Closing Time"
                            type="time"
                            :error="errors.closing_time"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <AppInput
                            v-model="form.max_bookings_per_resident"
                            label="Max Bookings/Resident"
                            type="number"
                            min="1"
                            :error="errors.max_bookings_per_resident"
                        />
                        <AppInput
                            v-model="form.advance_booking_days"
                            label="Advance Booking (days)"
                            type="number"
                            min="1"
                            :error="errors.advance_booking_days"
                        />
                    </div>

                    <AppInput
                        v-model="form.cancellation_hours"
                        label="Cancellation Hours"
                        type="number"
                        min="0"
                        :error="errors.cancellation_hours"
                    />

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Image</label
                        >
                        <input
                            type="file"
                            accept="image/*"
                            @change="onFileChange"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                        />
                    </div>

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                        <AppButton type="submit" :loading="submitting"
                            >Create Facility</AppButton
                        >
                        <AppButton
                            variant="secondary"
                            :to="{ name: 'admin.facilities' }"
                            >Cancel</AppButton
                        >
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppSelect from "@/components/common/AppSelect.vue";
import AppTextarea from "@/components/common/AppTextarea.vue";
import AppButton from "@/components/common/AppButton.vue";

const router = useRouter();

const form = reactive({
    property_uuid: "",
    name: "",
    description: "",
    rules: "",
    capacity: "",
    slot_duration: "60",
    opening_time: "08:00",
    closing_time: "22:00",
    max_bookings_per_resident: "2",
    advance_booking_days: "7",
    cancellation_hours: "24",
});

const errors = reactive({});
const errorMessage = ref("");
const submitting = ref(false);
const propertyOptions = ref([]);
let imageFile = null;

function onFileChange(e) {
    imageFile = e.target.files[0] || null;
}

onMounted(async () => {
    try {
        const { data } = await adminApi.getProperties();
        propertyOptions.value = (data.data || []).map((p) => ({
            value: p.uuid,
            label: p.name,
        }));
    } catch {
        errorMessage.value = "Failed to load properties.";
    }
});

async function handleSubmit() {
    Object.keys(errors).forEach((k) => delete errors[k]);
    errorMessage.value = "";
    submitting.value = true;

    const formData = new FormData();
    Object.entries(form).forEach(([key, val]) => {
        if (val !== "" && val !== null) formData.append(key, val);
    });
    if (imageFile) formData.append("image", imageFile);

    try {
        await adminApi.createFacility(formData);
        router.push({ name: "admin.facilities" });
    } catch (error) {
        const response = error.response;
        if (response?.status === 422 && response.data.errors) {
            Object.entries(response.data.errors).forEach(([key, msgs]) => {
                errors[key] = msgs[0];
            });
        }
        errorMessage.value =
            response?.data?.message || "Failed to create facility.";
    } finally {
        submitting.value = false;
    }
}
</script>
