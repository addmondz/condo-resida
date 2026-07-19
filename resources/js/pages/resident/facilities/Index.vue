<template>
    <div class="pb-16 lg:pb-0">
        <PageHeader
            title="Facilities"
            subtitle="Browse and book available facilities."
            :breadcrumbs="[{ label: 'Facilities' }]"
        />

        <!-- Loading -->
        <div
            v-if="loading"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
        >
            <div
                v-for="i in 6"
                :key="i"
                class="rounded-xl bg-white border border-gray-200 overflow-hidden"
            >
                <div class="h-36 sm:h-44 bg-gray-100 animate-pulse"></div>
                <div class="p-4">
                    <SkeletonLoader variant="text" :rows="2" />
                </div>
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
            <div>
                <p class="text-sm font-medium text-red-800">
                    Something went wrong
                </p>
                <p class="mt-0.5 text-sm text-red-700">{{ error }}</p>
            </div>
        </div>

        <!-- Empty -->
        <EmptyState
            v-else-if="facilities.length === 0"
            title="No facilities"
            message="There are no facilities available at the moment. Check back later."
        />

        <!-- Facilities Grid -->
        <div
            v-else
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
        >
            <router-link
                v-for="facility in facilities"
                :key="facility.uuid"
                :to="{
                    name: 'resident.facilities.show',
                    params: { uuid: facility.uuid },
                }"
                class="group rounded-xl bg-white border border-gray-200 overflow-hidden hover:shadow-md hover:border-gray-300 transition-all"
            >
                <!-- Image -->
                <div
                    class="h-36 sm:h-44 bg-gray-50 flex items-center justify-center overflow-hidden"
                >
                    <img
                        v-if="facility.image_url"
                        :src="facility.image_url"
                        :alt="facility.name"
                        class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    <div
                        v-else
                        class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100"
                    >
                        <svg
                            class="h-6 w-6 text-gray-400"
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

                <!-- Content -->
                <div class="px-5 py-4">
                    <h3
                        class="text-sm font-semibold text-gray-900 group-hover:text-primary-600 transition-colors"
                    >
                        {{ facility.name }}
                    </h3>
                    <div class="mt-2.5 flex items-center gap-3">
                        <span
                            v-if="facility.capacity"
                            class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600"
                        >
                            <svg
                                class="h-3.5 w-3.5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
                                />
                            </svg>
                            {{ facility.capacity }} pax
                        </span>
                        <StatusBadge
                            :status="
                                facility.is_under_maintenance
                                    ? 'suspended'
                                    : 'active'
                            "
                            :label="
                                facility.is_under_maintenance
                                    ? 'Maintenance'
                                    : 'Available'
                            "
                        />
                    </div>
                </div>
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import residentApi from "@/api/resident";
import PageHeader from "@/components/common/PageHeader.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import EmptyState from "@/components/common/EmptyState.vue";

const facilities = ref([]);
const loading = ref(true);
const error = ref("");

onMounted(async () => {
    try {
        const { data } = await residentApi.getFacilities();
        facilities.value = data.data;
    } catch {
        error.value = "Failed to load facilities. Please try again later.";
    } finally {
        loading.value = false;
    }
});
</script>
