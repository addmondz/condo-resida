<template>
    <div class="flex h-screen overflow-hidden bg-gray-50">
        <!-- Sidebar Overlay (mobile/tablet) -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebar.isOpen.value"
                class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden"
                @click="sidebar.close()"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-[260px] flex-col border-r border-gray-200 bg-white transition-transform duration-200 lg:static lg:translate-x-0',
                sidebar.isOpen.value ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Logo -->
            <div
                class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-100 px-5"
            >
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-600 text-white"
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
                            d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"
                        />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-lg font-bold text-gray-900">Rumi</p>
                    <p class="text-xs font-medium text-primary-600">
                        Guard Portal
                    </p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <p
                    class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-widest text-gray-400"
                >
                    Navigation
                </p>
                <div class="space-y-1">
                    <SidebarLink
                        :to="{ name: 'guard.dashboard' }"
                        icon="dashboard"
                        @click="sidebar.close()"
                        >Dashboard</SidebarLink
                    >
                    <SidebarLink
                        :to="{ name: 'guard.scanner' }"
                        icon="scanner"
                        @click="sidebar.close()"
                        >QR Scanner</SidebarLink
                    >
                    <SidebarLink
                        :to="{ name: 'guard.visitors' }"
                        icon="visitors"
                        @click="sidebar.close()"
                        >Visitors</SidebarLink
                    >
                    <SidebarLink
                        :to="{ name: 'guard.activity-log' }"
                        icon="activity"
                        @click="sidebar.close()"
                        >Activity Log</SidebarLink
                    >
                </div>
            </nav>

            <!-- User Section -->
            <div class="shrink-0 border-t border-gray-100 p-3">
                <div class="flex items-center gap-3 rounded-xl px-3 py-2.5">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700"
                    >
                        {{ initials }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-gray-900">
                            {{ auth.user?.name }}
                        </p>
                        <p class="truncate text-xs text-gray-500">
                            {{ auth.user?.email }}
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Top Bar -->
            <header
                class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 bg-white px-4 sm:px-6"
            >
                <!-- Hamburger -->
                <button
                    @click="sidebar.toggle()"
                    class="flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 hover:bg-gray-100 transition-colors lg:hidden"
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
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                        />
                    </svg>
                </button>

                <div class="flex-1"></div>

                <!-- User Dropdown -->
                <div class="relative" ref="userMenuRef">
                    <button
                        @click="showUserMenu = !showUserMenu"
                        class="flex h-9 items-center gap-2 rounded-xl pl-1 pr-2 text-[13px] leading-5 text-gray-700 hover:bg-gray-100 transition-colors"
                    >
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700"
                        >
                            {{ initials }}
                        </span>
                        <svg
                            class="h-3.5 w-3.5 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m19.5 8.25-7.5 7.5-7.5-7.5"
                            />
                        </svg>
                    </button>
                    <Transition
                        enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95"
                    >
                        <div
                            v-if="showUserMenu"
                            class="absolute right-0 z-50 mt-1 w-52 rounded-xl bg-white py-1 shadow-lg ring-1 ring-black/5"
                        >
                            <div class="border-b border-gray-100 px-4 py-2.5">
                                <p
                                    class="truncate text-sm font-semibold text-gray-900"
                                >
                                    {{ auth.user?.name }}
                                </p>
                                <p class="truncate text-xs text-gray-500">
                                    {{ auth.user?.email }}
                                </p>
                            </div>
                            <button
                                @click="handleLogout"
                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-[13px] leading-5 text-gray-700 transition-colors hover:bg-gray-50"
                            >
                                <svg
                                    class="h-4 w-4 text-gray-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"
                                    />
                                </svg>
                                Logout
                            </button>
                        </div>
                    </Transition>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <router-view v-slot="{ Component }">
                        <Transition name="page-fade" mode="out-in" appear>
                            <component :is="Component" />
                        </Transition>
                    </router-view>
                </div>
            </main>
        </div>

        <!-- Mobile Bottom Tab Bar -->
        <nav
            class="fixed bottom-0 inset-x-0 z-30 border-t border-gray-200 bg-white/95 backdrop-blur-sm lg:hidden"
            style="padding-bottom: env(safe-area-inset-bottom)"
        >
            <div class="grid grid-cols-4 h-16">
                <router-link
                    :to="{ name: 'guard.dashboard' }"
                    :class="tabClass('guard.dashboard')"
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
                            d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
                        />
                    </svg>
                    <span>Home</span>
                </router-link>
                <router-link
                    :to="{ name: 'guard.scanner' }"
                    :class="tabClass('guard.scanner')"
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
                            d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Z"
                        />
                    </svg>
                    <span>Scanner</span>
                </router-link>
                <router-link
                    :to="{ name: 'guard.visitors' }"
                    :class="tabClass('guard.visitors')"
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
                            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
                        />
                    </svg>
                    <span>Visitors</span>
                </router-link>
                <router-link
                    :to="{ name: 'guard.activity-log' }"
                    :class="tabClass('guard.activity-log')"
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
                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                        />
                    </svg>
                    <span>Activity</span>
                </router-link>
            </div>
        </nav>

        <ToastContainer />
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useSidebar } from "@/composables/useSidebar";
import { onClickOutside } from "@vueuse/core";
import SidebarLink from "@/components/common/SidebarLink.vue";
import ToastContainer from "@/components/common/ToastContainer.vue";

const auth = useAuthStore();
const sidebar = useSidebar();
const router = useRouter();
const route = useRoute();
const showUserMenu = ref(false);
const userMenuRef = ref(null);

const initials = computed(() => {
    const name = auth.user?.name || "";
    return name
        .split(" ")
        .map((w) => w[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
});

function isTabActive(routeName) {
    return route.name === routeName || route.name?.startsWith(routeName + ".");
}

function tabClass(routeName) {
    return [
        "flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition-colors",
        isTabActive(routeName) ? "text-primary-600" : "text-gray-400",
    ];
}

onClickOutside(userMenuRef, () => {
    showUserMenu.value = false;
});

async function handleLogout() {
    await auth.logout();
    router.push({ name: "login" });
}
</script>
