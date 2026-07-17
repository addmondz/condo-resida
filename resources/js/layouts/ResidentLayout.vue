<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white border-b border-gray-200">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
          <div class="flex">
            <div class="flex shrink-0 items-center">
              <router-link :to="{ name: 'resident.dashboard' }" class="text-lg font-bold text-primary-600">
                CondoManager
              </router-link>
            </div>
            <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
              <NavLink :to="{ name: 'resident.dashboard' }">Dashboard</NavLink>
              <NavLink :to="{ name: 'resident.visitors' }">Visitors</NavLink>
              <NavLink :to="{ name: 'resident.facilities' }">Facilities</NavLink>
              <NavLink :to="{ name: 'resident.bookings' }">Bookings</NavLink>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <router-link :to="{ name: 'resident.notifications' }" class="relative p-2 text-gray-500 hover:text-gray-700">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
              <span v-if="notificationStore.hasUnread" class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-medium text-white">
                {{ notificationStore.unreadCount > 9 ? '9+' : notificationStore.unreadCount }}
              </span>
            </router-link>
            <div class="relative" ref="userMenuRef">
              <button @click="showUserMenu = !showUserMenu" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <span class="hidden sm:block">{{ auth.user?.name }}</span>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
              </button>
              <div v-if="showUserMenu" class="absolute right-0 z-50 mt-1 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5">
                <router-link :to="{ name: 'resident.profile' }" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click="showUserMenu = false">Profile</router-link>
                <button @click="handleLogout" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Logout</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Mobile nav -->
      <div class="sm:hidden border-t border-gray-200 px-2 py-2 flex gap-1 overflow-x-auto">
        <NavLink :to="{ name: 'resident.dashboard' }" mobile>Dashboard</NavLink>
        <NavLink :to="{ name: 'resident.visitors' }" mobile>Visitors</NavLink>
        <NavLink :to="{ name: 'resident.facilities' }" mobile>Facilities</NavLink>
        <NavLink :to="{ name: 'resident.bookings' }" mobile>Bookings</NavLink>
      </div>
    </nav>
    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useNotificationStore } from '@/stores/notification';
import NavLink from '@/components/common/NavLink.vue';
import { onClickOutside } from '@vueuse/core';

const auth = useAuthStore();
const notificationStore = useNotificationStore();
const router = useRouter();
const showUserMenu = ref(false);
const userMenuRef = ref(null);

onClickOutside(userMenuRef, () => { showUserMenu.value = false; });

onMounted(() => {
    notificationStore.fetchNotifications();
});

async function handleLogout() {
    await auth.logout();
    router.push({ name: 'login' });
}
</script>
