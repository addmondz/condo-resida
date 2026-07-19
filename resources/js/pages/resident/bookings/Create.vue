<template>
    <div class="pb-16 lg:pb-0">
        <PageHeader
            title="Book Facility"
            :breadcrumbs="[
                { label: 'Bookings', to: { name: 'resident.bookings' } },
                { label: 'Book' },
            ]"
        />

        <div class="max-w-2xl">
            <div
                class="rounded-xl bg-white border border-gray-200 overflow-hidden"
            >
                <!-- Card Header -->
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">
                        Booking Details
                    </h2>
                    <p class="mt-0.5 text-sm text-gray-500">
                        Select a facility, date, and time slot to reserve.
                    </p>
                </div>

                <!-- Card Body -->
                <div class="px-5 py-5">
                    <!-- Error Alert -->
                    <div
                        v-if="errorMessage"
                        class="mb-5 flex items-start gap-3 rounded-xl bg-red-50 border border-red-200 px-4 py-3"
                    >
                        <svg
                            class="h-5 w-5 text-red-500 mt-0.5 shrink-0"
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

                    <!-- Loading Facilities -->
                    <SkeletonLoader
                        v-if="loadingFacilities"
                        variant="form"
                        :rows="3"
                    />

                    <form
                        v-else
                        @submit.prevent="handleSubmit"
                        class="space-y-5"
                    >
                        <AppSelect
                            v-model="form.facility_uuid"
                            label="Facility"
                            :options="facilityOptions"
                            placeholder="Select a facility"
                            :error="errors.facility_uuid"
                            required
                        />

                        <AppInput
                            v-model="form.booking_date"
                            label="Date"
                            type="date"
                            :min="today"
                            :max="maxDate"
                            :error="errors.booking_date"
                            :disabled="!form.facility_uuid"
                            required
                        />

                        <!-- Loading Slots -->
                        <div v-if="loadingSlots" class="py-4">
                            <SkeletonLoader variant="list" :rows="3" />
                        </div>

                        <!-- Time Slot Grid -->
                        <div
                            v-else-if="
                                form.facility_uuid &&
                                form.booking_date &&
                                availableSlots.length > 0
                            "
                        >
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2.5"
                                >Available Time Slots</label
                            >
                            <div
                                class="grid grid-cols-2 sm:grid-cols-3 gap-2.5"
                            >
                                <button
                                    v-for="slot in availableSlots"
                                    :key="slot.start_time"
                                    type="button"
                                    :disabled="!slot.available"
                                    @click="selectSlot(slot)"
                                    :class="[
                                        'rounded-xl border px-3 py-2.5 text-sm font-medium transition-all text-center',
                                        selectedSlot?.start_time ===
                                        slot.start_time
                                            ? 'border-primary-600 bg-primary-50 text-primary-700 ring-2 ring-primary-600'
                                            : slot.available
                                              ? 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-300'
                                              : 'border-gray-100 bg-gray-50 text-gray-400 cursor-not-allowed line-through',
                                    ]"
                                >
                                    {{ slot.start_time }} - {{ slot.end_time }}
                                </button>
                            </div>
                            <p
                                v-if="errors.start_time"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ errors.start_time }}
                            </p>
                        </div>

                        <!-- No Slots Warning -->
                        <div
                            v-else-if="
                                form.facility_uuid &&
                                form.booking_date &&
                                !loadingSlots
                            "
                            class="flex items-start gap-3 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3"
                        >
                            <svg
                                class="h-5 w-5 text-amber-500 mt-0.5 shrink-0"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
                                />
                            </svg>
                            <p class="text-sm text-amber-700">
                                No available time slots for the selected date.
                                Please try a different date.
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3 pt-2">
                            <AppButton
                                type="submit"
                                :loading="submitting"
                                :disabled="!selectedSlot"
                            >
                                Book Now
                            </AppButton>
                            <AppButton
                                variant="secondary"
                                :to="{ name: 'resident.bookings' }"
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
import { ref, reactive, computed, watch, onMounted } from "vue";
import { useRouter } from "vue-router";
import residentApi from "@/api/resident";
import { useToast } from "@/composables/useToast";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppSelect from "@/components/common/AppSelect.vue";
import AppButton from "@/components/common/AppButton.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";

const props = defineProps({
    facilityUuid: { type: String, default: "" },
});

const router = useRouter();
const toast = useToast();

const form = reactive({
    facility_uuid: "",
    booking_date: "",
});

const errors = reactive({
    facility_uuid: "",
    booking_date: "",
    start_time: "",
});

const facilities = ref([]);
const facilityOptions = ref([]);
const availableSlots = ref([]);
const selectedSlot = ref(null);
const loadingFacilities = ref(true);
const loadingSlots = ref(false);
const submitting = ref(false);
const errorMessage = ref("");

const today = computed(() => new Date().toISOString().split("T")[0]);

const maxDate = computed(() => {
    const selected = facilities.value.find(
        (f) => f.uuid === form.facility_uuid,
    );
    if (selected?.advance_booking_days) {
        const d = new Date();
        d.setDate(d.getDate() + selected.advance_booking_days);
        return d.toISOString().split("T")[0];
    }
    return "";
});

onMounted(async () => {
    try {
        const { data } = await residentApi.getFacilities();
        facilities.value = data.data;
        facilityOptions.value = facilities.value
            .filter((f) => !f.is_under_maintenance)
            .map((f) => ({ value: f.uuid, label: f.name }));

        if (props.facilityUuid) {
            form.facility_uuid = props.facilityUuid;
        }
    } catch {
        errorMessage.value = "Failed to load facilities.";
    } finally {
        loadingFacilities.value = false;
    }
});

watch(
    () => form.facility_uuid,
    () => {
        form.booking_date = "";
        availableSlots.value = [];
        selectedSlot.value = null;
    },
);

watch(
    () => form.booking_date,
    async (newVal) => {
        availableSlots.value = [];
        selectedSlot.value = null;

        if (!form.facility_uuid || !newVal) return;

        loadingSlots.value = true;
        try {
            const { data } = await residentApi.getFacilityAvailability(
                form.facility_uuid,
                { date: newVal },
            );
            availableSlots.value = data.data;
        } catch {
            errorMessage.value = "Failed to load available time slots.";
        } finally {
            loadingSlots.value = false;
        }
    },
);

function selectSlot(slot) {
    if (!slot.available) return;
    selectedSlot.value = slot;
    errors.start_time = "";
}

async function handleSubmit() {
    Object.keys(errors).forEach((k) => {
        errors[k] = "";
    });
    errorMessage.value = "";

    if (!selectedSlot.value) {
        errors.start_time = "Please select a time slot.";
        return;
    }

    submitting.value = true;

    try {
        await residentApi.createBooking({
            facility_uuid: form.facility_uuid,
            booking_date: form.booking_date,
            start_time: selectedSlot.value.start_time,
            end_time: selectedSlot.value.end_time,
        });
        toast.success("Booking created successfully.");
        router.push({ name: "resident.bookings" });
    } catch (error) {
        const response = error.response;
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
                response?.data?.message || "Failed to create booking.";
        }
    } finally {
        submitting.value = false;
    }
}
</script>
