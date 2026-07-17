<template>
  <div>
    <PageHeader :title="`Welcome, ${auth.user?.name || 'Resident'}`" subtitle="Here's what's happening today." />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
      <AppButton :to="{ name: 'resident.visitors.create' }" size="lg" class="w-full">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
        Register Visitor
      </AppButton>
      <AppButton :to="{ name: 'resident.facilities' }" variant="secondary" size="lg" class="w-full">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
        Book Facility
      </AppButton>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Visitors -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Recent Visitors</h2>
          <router-link :to="{ name: 'resident.visitors' }" class="text-sm text-primary-600 hover:text-primary-500">View all</router-link>
        </div>

        <LoadingState v-if="loadingVisitors" message="Loading visitors..." />
        <div v-else-if="recentVisitors.length === 0" class="text-center py-6">
          <p class="text-sm text-gray-500">No visitors registered yet.</p>
        </div>
        <ul v-else class="divide-y divide-gray-100">
          <li v-for="visitor in recentVisitors" :key="visitor.uuid" class="py-3">
            <router-link :to="{ name: 'resident.visitors.show', params: { uuid: visitor.uuid } }" class="flex items-center justify-between hover:bg-gray-50 -mx-2 px-2 py-1 rounded">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ visitor.visitor_name }}</p>
                <p class="text-xs text-gray-500">{{ formatDate(visitor.visit_date) }}</p>
              </div>
              <StatusBadge :status="visitor.status" />
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Upcoming Bookings -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Upcoming Bookings</h2>
          <router-link :to="{ name: 'resident.bookings' }" class="text-sm text-primary-600 hover:text-primary-500">View all</router-link>
        </div>

        <LoadingState v-if="loadingBookings" message="Loading bookings..." />
        <div v-else-if="upcomingBookings.length === 0" class="text-center py-6">
          <p class="text-sm text-gray-500">No upcoming bookings.</p>
        </div>
        <ul v-else class="divide-y divide-gray-100">
          <li v-for="booking in upcomingBookings" :key="booking.uuid" class="py-3">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ booking.facility?.name || 'Facility' }}</p>
                <p class="text-xs text-gray-500">{{ formatDate(booking.booking_date) }} &middot; {{ booking.start_time }} - {{ booking.end_time }}</p>
              </div>
              <StatusBadge :status="booking.status" />
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import residentApi from '@/api/resident';
import PageHeader from '@/components/common/PageHeader.vue';
import AppButton from '@/components/common/AppButton.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import LoadingState from '@/components/common/LoadingState.vue';

const auth = useAuthStore();

const recentVisitors = ref([]);
const upcomingBookings = ref([]);
const loadingVisitors = ref(true);
const loadingBookings = ref(true);

function formatDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-MY', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
}

onMounted(async () => {
  try {
    const { data } = await residentApi.getVisitors({ per_page: 5, sort: '-created_at' });
    recentVisitors.value = data.data;
  } catch {
    // silently fail for dashboard widget
  } finally {
    loadingVisitors.value = false;
  }

  try {
    const { data } = await residentApi.getBookings({ filter: 'upcoming', per_page: 5 });
    upcomingBookings.value = data.data;
  } catch {
    // silently fail for dashboard widget
  } finally {
    loadingBookings.value = false;
  }
});
</script>
