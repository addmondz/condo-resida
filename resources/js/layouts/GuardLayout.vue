<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white border-b border-gray-200">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
          <div class="flex">
            <div class="flex shrink-0 items-center">
              <router-link :to="{ name: 'guard.dashboard' }" class="text-lg font-bold text-primary-600">
                CondoManager
              </router-link>
              <span class="ml-2 rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">Guard</span>
            </div>
            <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
              <NavLink :to="{ name: 'guard.dashboard' }">Dashboard</NavLink>
              <NavLink :to="{ name: 'guard.scanner' }">Scanner</NavLink>
              <NavLink :to="{ name: 'guard.visitors' }">Visitors</NavLink>
              <NavLink :to="{ name: 'guard.activity-log' }">Activity Log</NavLink>
            </div>
          </div>
          <div class="flex items-center">
            <div class="relative" ref="userMenuRef">
              <button @click="showUserMenu = !showUserMenu" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <span class="hidden sm:block">{{ auth.user?.name }}</span>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
              </button>
              <div v-if="showUserMenu" class="absolute right-0 z-50 mt-1 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5">
                <button @click="handleLogout" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Logout</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="sm:hidden border-t border-gray-200 px-2 py-2 flex gap-1 overflow-x-auto">
        <NavLink :to="{ name: 'guard.dashboard' }" mobile>Dashboard</NavLink>
        <NavLink :to="{ name: 'guard.scanner' }" mobile>Scanner</NavLink>
        <NavLink :to="{ name: 'guard.visitors' }" mobile>Visitors</NavLink>
        <NavLink :to="{ name: 'guard.activity-log' }" mobile>Log</NavLink>
      </div>
    </nav>
    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import NavLink from '@/components/common/NavLink.vue';
import { onClickOutside } from '@vueuse/core';

const auth = useAuthStore();
const router = useRouter();
const showUserMenu = ref(false);
const userMenuRef = ref(null);

onClickOutside(userMenuRef, () => { showUserMenu.value = false; });

async function handleLogout() {
    await auth.logout();
    router.push({ name: 'login' });
}
</script>
