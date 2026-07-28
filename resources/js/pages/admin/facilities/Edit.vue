<template>
    <div>
        <PageHeader
            :title="facility.name ? `Edit: ${facility.name}` : 'Edit Facility'"
            subtitle="Update facility details and settings."
            :breadcrumbs="[
                { label: 'Facilities', to: { name: 'admin.facilities' } },
                { label: facility.name || 'Edit' },
            ]"
        />

        <SkeletonLoader
            v-if="loadingFacility"
            variant="form"
            :rows="6"
            container-class="rounded-xl border border-gray-200 bg-white px-5 py-5"
        />

        <div
            v-else-if="loadError"
            class="flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-5 py-4"
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
            <p class="text-sm text-red-700">{{ loadError }}</p>
        </div>

        <template v-else>
            <div class="max-w-2xl space-y-6">
                <div
                    class="rounded-xl border border-gray-200 bg-white px-5 py-5"
                >
                    <div
                        v-if="errorMessage"
                        class="mb-4 rounded-xl border border-red-100 bg-red-50 px-5 py-4"
                    >
                        <p class="text-sm text-red-700">{{ errorMessage }}</p>
                    </div>
                    <form @submit.prevent="handleSubmit" class="space-y-5">
                        <AppInput
                            v-model="form.name"
                            label="Name"
                            :error="errors.name"
                            required
                        />

                        <AppTextarea
                            v-model="form.description"
                            label="Description"
                            :error="errors.description"
                            :rows="3"
                        />

                        <AppTextarea
                            v-model="form.rules"
                            label="Rules & Regulations"
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

                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center gap-2">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                />
                                <label
                                    for="is_active"
                                    class="text-sm font-medium text-gray-700"
                                    >Active</label
                                >
                            </div>
                            <div class="flex items-center gap-2">
                                <input
                                    id="is_under_maintenance"
                                    v-model="form.is_under_maintenance"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                />
                                <label
                                    for="is_under_maintenance"
                                    class="text-sm font-medium text-gray-700"
                                    >Under Maintenance</label
                                >
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Image</label
                            >
                            <div v-if="facility.image_url" class="mb-2">
                                <img
                                    :src="facility.image_url"
                                    alt="Current image"
                                    class="h-24 w-auto rounded-xl object-cover"
                                />
                            </div>
                            <input
                                type="file"
                                accept="image/*"
                                @change="onFileChange"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                            />
                        </div>

                        <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                            <AppButton type="submit" :loading="submitting"
                                >Save Changes</AppButton
                            >
                            <AppButton
                                variant="secondary"
                                :to="{ name: 'admin.facilities' }"
                                >Cancel</AppButton
                            >
                        </div>
                    </form>
                </div>

                <div
                    class="rounded-xl border border-gray-200 bg-white px-5 py-5"
                >
                    <h2 class="text-lg font-semibold text-gray-900">
                        Blocked Dates & Time Slots
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Block a full date by leaving the time fields empty, or
                        block a specific time range.
                    </p>

                    <form
                        @submit.prevent="handleCreateBlockedSlot"
                        class="mt-4 space-y-5"
                    >
                        <div
                            v-if="blockedSlotError"
                            class="rounded-xl border border-red-100 bg-red-50 px-5 py-4"
                        >
                            <p class="text-sm text-red-700">
                                {{ blockedSlotError }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <AppInput
                                v-model="blockedSlotForm.blocked_date"
                                label="Date"
                                type="date"
                                :min="today"
                                :error="blockedSlotErrors.blocked_date"
                                required
                            />
                            <AppInput
                                v-model="blockedSlotForm.start_time"
                                label="Start Time"
                                type="time"
                                :error="blockedSlotErrors.start_time"
                            />
                            <AppInput
                                v-model="blockedSlotForm.end_time"
                                label="End Time"
                                type="time"
                                :error="blockedSlotErrors.end_time"
                            />
                        </div>

                        <AppInput
                            v-model="blockedSlotForm.reason"
                            label="Reason"
                            placeholder="Maintenance, private event, cleaning"
                            :error="blockedSlotErrors.reason"
                        />

                        <AppButton type="submit" :loading="creatingBlockedSlot"
                            >Add Blocked Slot</AppButton
                        >
                    </form>

                    <div
                        class="mt-6 divide-y divide-gray-200 rounded-xl border border-gray-200"
                    >
                        <div
                            v-if="loadingBlockedSlots"
                            class="p-4 text-sm text-gray-500"
                        >
                            Loading blocked slots...
                        </div>
                        <div
                            v-else-if="!blockedSlots.length"
                            class="p-4 text-sm text-gray-500"
                        >
                            No blocked slots configured.
                        </div>
                        <div
                            v-for="slot in blockedSlots"
                            :key="slot.id"
                            class="flex items-center justify-between gap-4 p-4"
                        >
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ formatDate(slot.blocked_date) }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{
                                        slot.start_time && slot.end_time
                                            ? `${slot.start_time} - ${slot.end_time}`
                                            : "Full day"
                                    }}
                                    <span v-if="slot.reason">
                                        · {{ slot.reason }}</span
                                    >
                                </p>
                            </div>
                            <AppButton
                                variant="ghost"
                                size="sm"
                                @click="handleDeleteBlockedSlot(slot.id)"
                                >Remove</AppButton
                            >
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, ref, reactive, onMounted } from "vue";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppTextarea from "@/components/common/AppTextarea.vue";
import AppButton from "@/components/common/AppButton.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import { useToast } from "@/composables/useToast";

const props = defineProps({
    uuid: { type: String, required: true },
});

const facility = ref({});
const loadingFacility = ref(true);
const loadError = ref("");

const form = reactive({
    name: "",
    description: "",
    rules: "",
    capacity: "",
    slot_duration: "",
    opening_time: "",
    closing_time: "",
    max_bookings_per_resident: "",
    advance_booking_days: "",
    cancellation_hours: "",
    is_active: true,
    is_under_maintenance: false,
});

const errors = reactive({});
const errorMessage = ref("");
const toast = useToast();
const submitting = ref(false);
let imageFile = null;
const blockedSlots = ref([]);
const loadingBlockedSlots = ref(false);
const creatingBlockedSlot = ref(false);
const blockedSlotError = ref("");
const blockedSlotForm = reactive({
    blocked_date: "",
    start_time: "",
    end_time: "",
    reason: "",
});
const blockedSlotErrors = reactive({});

const today = computed(() => new Date().toISOString().split("T")[0]);

function onFileChange(e) {
    imageFile = e.target.files[0] || null;
}

onMounted(async () => {
    try {
        const { data } = await adminApi.getFacility(props.uuid);
        facility.value = data.data;
        const f = data.data;
        Object.assign(form, {
            name: f.name || "",
            description: f.description || "",
            rules: f.rules || "",
            capacity: f.capacity ?? "",
            slot_duration: f.slot_duration ?? "",
            opening_time: f.opening_time || "",
            closing_time: f.closing_time || "",
            max_bookings_per_resident: f.max_bookings_per_resident ?? "",
            advance_booking_days: f.advance_booking_days ?? "",
            cancellation_hours: f.cancellation_hours ?? "",
            is_active: f.is_active ?? true,
            is_under_maintenance: f.is_under_maintenance ?? false,
        });
        await loadBlockedSlots();
    } catch {
        loadError.value = "Failed to load facility.";
    } finally {
        loadingFacility.value = false;
    }
});

async function handleSubmit() {
    Object.keys(errors).forEach((k) => delete errors[k]);
    errorMessage.value = "";
    submitting.value = true;

    const formData = new FormData();
    formData.append("_method", "PUT");
    Object.entries(form).forEach(([key, val]) => {
        if (typeof val === "boolean") {
            formData.append(key, val ? "1" : "0");
        } else if (val !== "" && val !== null) {
            formData.append(key, val);
        }
    });
    if (imageFile) formData.append("image", imageFile);

    try {
        const { data } = await adminApi.updateFacility(props.uuid, formData);
        facility.value = data.data;
        toast.success("Facility updated successfully.");
    } catch (error) {
        const response = error.response;
        if (response?.status === 422 && response.data.errors) {
            Object.entries(response.data.errors).forEach(([key, msgs]) => {
                errors[key] = msgs[0];
            });
        }
        errorMessage.value =
            response?.data?.message || "Failed to update facility.";
    } finally {
        submitting.value = false;
    }
}

function formatDate(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("en-MY", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

async function loadBlockedSlots() {
    loadingBlockedSlots.value = true;
    try {
        const { data } = await adminApi.getFacilityBlockedSlots(props.uuid);
        blockedSlots.value = data.data || [];
    } finally {
        loadingBlockedSlots.value = false;
    }
}

async function handleCreateBlockedSlot() {
    Object.keys(blockedSlotErrors).forEach((k) => delete blockedSlotErrors[k]);
    blockedSlotError.value = "";
    creatingBlockedSlot.value = true;

    const payload = {
        blocked_date: blockedSlotForm.blocked_date,
        start_time: blockedSlotForm.start_time || null,
        end_time: blockedSlotForm.end_time || null,
        reason: blockedSlotForm.reason || null,
    };

    try {
        const { data } = await adminApi.createFacilityBlockedSlot(
            props.uuid,
            payload,
        );
        blockedSlots.value = [data.data, ...blockedSlots.value];
        Object.assign(blockedSlotForm, {
            blocked_date: "",
            start_time: "",
            end_time: "",
            reason: "",
        });
        toast.success("Blocked slot added.");
    } catch (error) {
        const response = error.response;
        if (response?.status === 422 && response.data.errors) {
            Object.entries(response.data.errors).forEach(([key, msgs]) => {
                blockedSlotErrors[key] = msgs[0];
            });
        }
        blockedSlotError.value =
            response?.data?.message || "Failed to add blocked slot.";
    } finally {
        creatingBlockedSlot.value = false;
    }
}

async function handleDeleteBlockedSlot(slotId) {
    try {
        await adminApi.deleteFacilityBlockedSlot(props.uuid, slotId);
        blockedSlots.value = blockedSlots.value.filter(
            (slot) => slot.id !== slotId,
        );
        toast.success("Blocked slot removed.");
    } catch (error) {
        toast.error(
            error.response?.data?.message || "Failed to remove blocked slot.",
        );
    }
}
</script>
