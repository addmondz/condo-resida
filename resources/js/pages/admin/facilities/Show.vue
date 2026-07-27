<template>
    <div>
        <PageHeader
            title="Facility Details"
            subtitle="View facility information and configuration."
            :breadcrumbs="[
                { label: 'Facilities', to: { name: 'admin.facilities' } },
                { label: facility.name || 'Details' },
            ]"
        >
            <template #actions>
                <div class="flex items-center gap-2">
                    <AppButton variant="secondary" size="sm" :to="{ name: 'admin.facilities.edit', params: { uuid } }">Edit</AppButton>
                    <AppButton variant="secondary" :to="{ name: 'admin.facilities' }" size="sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Back
                    </AppButton>
                </div>
            </template>
        </PageHeader>

        <SkeletonLoader v-if="loading" variant="form" :rows="5" container-class="rounded-xl border border-gray-200 bg-white px-5 py-5" />

        <div v-else-if="error" class="flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-5 py-4">
            <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <template v-else>
            <div class="mb-4 rounded-xl border border-gray-200 bg-white">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">{{ facility.name }}</h2>
                        <p v-if="facility.property?.name" class="mt-0.5 text-sm text-gray-500">{{ facility.property.name }}</p>
                    </div>
                    <StatusBadge
                        :status="facility.is_under_maintenance ? 'suspended' : facility.is_active ? 'active' : 'draft'"
                        :label="facility.is_under_maintenance ? 'Maintenance' : facility.is_active ? 'Active' : 'Inactive'"
                    />
                </div>

                <div class="px-5 py-5">
                    <div v-if="facility.image_url" class="mb-5">
                        <img :src="facility.image_url" :alt="facility.name" class="h-48 w-full rounded-lg object-cover" />
                    </div>

                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <dt class="text-sm text-gray-500">Capacity</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ facility.capacity || "-" }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Operating Hours</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ facility.opening_time && facility.closing_time ? `${facility.opening_time} - ${facility.closing_time}` : "-" }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Slot Duration</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ facility.slot_duration ? `${facility.slot_duration} min` : "-" }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Max Bookings / Resident</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ facility.max_bookings_per_resident || "-" }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Advance Booking</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ facility.advance_booking_days ? `${facility.advance_booking_days} days` : "-" }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Cancellation Window</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ facility.cancellation_hours != null ? `${facility.cancellation_hours} hours` : "-" }}</dd>
                        </div>
                    </dl>

                    <div v-if="facility.description" class="mt-5 border-t border-gray-100 pt-5">
                        <dt class="text-sm text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ facility.description }}</dd>
                    </div>

                    <div v-if="facility.rules" class="mt-5 border-t border-gray-100 pt-5">
                        <dt class="text-sm text-gray-500">Rules</dt>
                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ facility.rules }}</dd>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppButton from "@/components/common/AppButton.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";

const props = defineProps({ uuid: { type: String, required: true } });

const facility = ref({});
const loading = ref(true);
const error = ref("");

async function loadFacility() {
    try {
        const { data } = await adminApi.getFacility(props.uuid);
        facility.value = data.data;
    } catch {
        error.value = "Failed to load facility details.";
    } finally {
        loading.value = false;
    }
}

onMounted(loadFacility);
</script>
