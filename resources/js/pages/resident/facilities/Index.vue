<template>
  <div>
    <PageHeader title="Facilities" subtitle="Browse and book available facilities." />

    <LoadingState v-if="loading" message="Loading facilities..." />

    <div v-else-if="error" class="rounded-md bg-red-50 p-4">
      <p class="text-sm text-red-700">{{ error }}</p>
    </div>

    <EmptyState
      v-else-if="facilities.length === 0"
      title="No facilities"
      message="There are no facilities available at the moment."
    />

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <router-link
        v-for="facility in facilities"
        :key="facility.uuid"
        :to="{ name: 'resident.facilities.show', params: { uuid: facility.uuid } }"
        class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
      >
        <div class="h-40 bg-gray-100 flex items-center justify-center">
          <img
            v-if="facility.image_url"
            :src="facility.image_url"
            :alt="facility.name"
            class="h-full w-full object-cover"
          />
          <svg v-else class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
          </svg>
        </div>
        <div class="p-4">
          <h3 class="text-sm font-semibold text-gray-900">{{ facility.name }}</h3>
          <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
            <span v-if="facility.capacity" class="flex items-center gap-1">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
              {{ facility.capacity }}
            </span>
            <StatusBadge
              :status="facility.is_under_maintenance ? 'suspended' : 'active'"
              :label="facility.is_under_maintenance ? 'Maintenance' : 'Available'"
            />
          </div>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import residentApi from '@/api/resident';
import PageHeader from '@/components/common/PageHeader.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import LoadingState from '@/components/common/LoadingState.vue';
import EmptyState from '@/components/common/EmptyState.vue';

const facilities = ref([]);
const loading = ref(true);
const error = ref('');

onMounted(async () => {
  try {
    const { data } = await residentApi.getFacilities();
    facilities.value = data.data;
  } catch {
    error.value = 'Failed to load facilities.';
  } finally {
    loading.value = false;
  }
});
</script>
