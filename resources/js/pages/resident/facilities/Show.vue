<template>
    <div class="pb-16 lg:pb-0">
        <!-- Back Button -->
        <div class="mb-4">
            <AppButton
                variant="ghost"
                :to="{ name: 'resident.facilities' }"
                size="sm"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"
                    />
                </svg>
                Back to Facilities
            </AppButton>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="space-y-4">
            <SkeletonLoader variant="text" :rows="2" />
            <div
                class="h-48 sm:h-64 rounded-xl bg-gray-100 animate-pulse"
            ></div>
            <SkeletonLoader variant="card" />
            <div class="grid grid-cols-2 gap-4">
                <SkeletonLoader variant="card" />
                <SkeletonLoader variant="card" />
            </div>
        </div>

        <!-- Error -->
        <div
            v-else-if="error"
            class="flex items-start gap-3 rounded-xl bg-red-50 border border-red-200 px-5 py-4"
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
            <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <template v-else>
            <PageHeader
                :title="facility.name || ''"
                :breadcrumbs="[
                    {
                        label: 'Facilities',
                        to: { name: 'resident.facilities' },
                    },
                    { label: facility.name || '' },
                ]"
            >
                <template #actions>
                    <StatusBadge
                        v-if="facility.is_under_maintenance"
                        status="suspended"
                        label="Under Maintenance"
                    />
                </template>
            </PageHeader>

            <!-- Hero Image -->
            <div
                v-if="facility.image_url"
                class="mb-6 rounded-xl overflow-hidden"
            >
                <img
                    :src="facility.image_url"
                    :alt="facility.name"
                    class="h-48 sm:h-64 w-full object-cover"
                />
            </div>
            <div
                v-else
                class="mb-6 h-48 sm:h-64 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center"
            >
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100"
                >
                    <svg
                        class="h-8 w-8 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z"
                        />
                    </svg>
                </div>
            </div>

            <!-- Description Card -->
            <div
                v-if="facility.description"
                class="mb-6 rounded-xl bg-white border border-gray-200 overflow-hidden"
            >
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">
                        About this Facility
                    </h2>
                </div>
                <div class="px-5 py-4">
                    <p
                        class="text-sm text-gray-700 leading-relaxed whitespace-pre-line"
                    >
                        {{ facility.description }}
                    </p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4">
                <div
                    v-if="facility.capacity"
                    class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3.5"
                >
                    <p class="text-sm text-gray-500">Capacity</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">
                        {{ facility.capacity }} pax
                    </p>
                </div>
                <div
                    v-if="facility.opening_time && facility.closing_time"
                    class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3.5"
                >
                    <p class="text-sm text-gray-500">Operating Hours</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">
                        {{ facility.opening_time }} -
                        {{ facility.closing_time }}
                    </p>
                </div>
                <div
                    v-if="facility.slot_duration"
                    class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3.5"
                >
                    <p class="text-sm text-gray-500">Slot Duration</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">
                        {{ facility.slot_duration }} min
                    </p>
                </div>
                <div
                    v-if="facility.advance_booking_days"
                    class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3.5"
                >
                    <p class="text-sm text-gray-500">Advance Booking</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">
                        Up to {{ facility.advance_booking_days }} days
                    </p>
                </div>
            </div>

            <!-- Rules Section -->
            <div
                v-if="facility.rules"
                class="mb-6 rounded-xl bg-amber-50 border border-amber-200 overflow-hidden"
            >
                <div
                    class="px-5 py-4 border-b border-amber-100 flex items-center gap-2"
                >
                    <svg
                        class="h-5 w-5 text-amber-600 shrink-0"
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
                    <h2 class="text-base font-semibold text-amber-900">
                        Rules & Regulations
                    </h2>
                </div>
                <div class="px-5 py-4">
                    <p
                        class="text-sm text-amber-800 leading-relaxed whitespace-pre-line"
                    >
                        {{ facility.rules }}
                    </p>
                </div>
            </div>

            <!-- Book Button -->
            <div v-if="!facility.is_under_maintenance" class="mt-6">
                <AppButton
                    :to="{
                        name: 'resident.bookings.create',
                        params: { facilityUuid: facility.uuid },
                    }"
                    size="lg"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"
                        />
                    </svg>
                    Book This Facility
                </AppButton>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import residentApi from "@/api/resident";
import PageHeader from "@/components/common/PageHeader.vue";
import AppButton from "@/components/common/AppButton.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";

const props = defineProps({
    uuid: { type: String, required: true },
});

const facility = ref({});
const loading = ref(true);
const error = ref("");

onMounted(async () => {
    try {
        const { data } = await residentApi.getFacility(props.uuid);
        facility.value = data.data;
    } catch {
        error.value = "Failed to load facility details.";
    } finally {
        loading.value = false;
    }
});
</script>
