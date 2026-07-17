<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar overlay for mobile -->
    <div v-if="sidebarOpen" class="fixed inset-0 z-40 lg:hidden" @click="sidebarOpen = false">
      <div class="fixed inset-0 bg-gray-600/75"></div>
    </div>

    <!-- Sidebar -->
    <aside :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', 'fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transition-transform duration-200 lg:translate-x-0']">
      <div class="flex h-16 items-center justify-between px-4 border-b border-gray-200">
        <router-link :to="{ name: 'admin.dashboard' }" class="text-lg font-bold text-primary-600">
          CondoManager
        </router-link>
        <button @click="sidebarOpen = false" class="lg:hidden p-1 text-gray-500">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </button>
      </div>
      <nav class="flex-1 overflow-y-auto px-3 py-4">
        <div class="space-y-1">
          <SidebarLink :to="{ name: 'admin.dashboard' }" icon="dashboard">Dashboard</SidebarLink>
          <SidebarLink :to="{ name: 'admin.users' }" icon="users">Users</SidebarLink>
          <SidebarLink :to="{ name: 'admin.visitors' }" icon="visitors">Visitors</SidebarLink>
          <SidebarLink :to="{ name: 'admin.facilities' }" icon="facilities">Facilities</SidebarLink>
          <SidebarLink :to="{ name: 'admin.bookings' }" icon="bookings">Bookings</SidebarLink>
          <SidebarLink :to="{ name: 'admin.notifications' }" icon="notifications">Notifications</SidebarLink>
          <SidebarLink :to="{ name: 'admin.settings' }" icon="settings">Settings</SidebarLink>
        </div>
      </nav>
    </aside>

    <!-- Main content -->
    <div class="lg:pl-64">
      <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8">
        <button @click="sidebarOpen = true" class="lg:hidden p-1 text-gray-500">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
        </button>
        <div class="flex-1"></div>
        <div class="flex items-center gap-3">
          <div class="relative" ref="userMenuRef">
            <button @click="showUserMenu = !showUserMenu" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <span>{{ auth.user?.name }}</span>
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
            </button>
            <div v-if="showUserMenu" class="absolute right-0 z-50 mt-1 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5">
              <button @click="handleLogout" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Logout</button>
            </div>
          </div>
        </div>
      </header>
      <main class="px-4 py-6 sm:px-6 lg:px-8">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import SidebarLink from '@/components/common/SidebarLink.vue';
import { onClickOutside } from '@vueuse/core';

const auth = useAuthStore();
const router = useRouter();
const sidebarOpen = ref(false);
const showUserMenu = ref(false);
const userMenuRef = ref(null);

onClickOutside(userMenuRef, () => { showUserMenu.value = false; });

async function handleLogout() {
    await auth.logout();
    router.push({ name: 'login' });
}
</script>
