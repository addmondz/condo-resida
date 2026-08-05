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
                            d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"
                        />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-lg font-bold text-gray-900">Rumi</p>
                    <p class="text-xs font-medium text-primary-600">
                        Resident Portal
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
                        :to="{ name: 'resident.dashboard' }"
                        icon="dashboard"
                        @click="sidebar.close()"
                        >Dashboard</SidebarLink
                    >
                    <SidebarLink
                        :to="{ name: 'resident.visitors' }"
                        icon="visitors"
                        @click="sidebar.close()"
                        >Visitors</SidebarLink
                    >
                    <SidebarLink
                        :to="{ name: 'resident.facilities' }"
                        icon="facilities"
                        @click="sidebar.close()"
                        >Facilities</SidebarLink
                    >
                    <SidebarLink
                        :to="{ name: 'resident.bookings' }"
                        icon="bookings"
                        @click="sidebar.close()"
                        >Bookings</SidebarLink
                    >
                    <SidebarLink
                        :to="{ name: 'resident.notifications' }"
                        icon="notifications"
                        @click="sidebar.close()"
                    >
                        Notifications
                        <span
                            v-if="notificationStore.hasUnread"
                            class="ml-auto flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-[10px] font-bold text-white"
                        >
                            {{
                                notificationStore.unreadCount > 99
                                    ? "99+"
                                    : notificationStore.unreadCount
                            }}
                        </span>
                    </SidebarLink>
                </div>
            </nav>

            <!-- User Section -->
            <div class="shrink-0 border-t border-gray-100 p-3">
                <router-link
                    :to="{ name: 'resident.profile' }"
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors hover:bg-gray-50"
                    @click="sidebar.close()"
                >
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
                </router-link>
            </div>
        </aside>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <!-- Top Bar -->
            <header
                class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 bg-white/95 px-4 shadow-sm shadow-gray-900/[0.03] backdrop-blur-md sm:px-6"
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

                <!-- Right Actions -->
                <div class="flex items-center gap-1">
                    <!-- Notifications -->
                    <router-link
                        :to="{ name: 'resident.notifications' }"
                        class="relative flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 hover:bg-gray-100 transition-colors"
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
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
                            />
                        </svg>
                        <span
                            v-if="notificationStore.hasUnread"
                            class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white"
                        >
                            {{
                                notificationStore.unreadCount > 9
                                    ? "9+"
                                    : notificationStore.unreadCount
                            }}
                        </span>
                    </router-link>

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
                                <div
                                    class="border-b border-gray-100 px-4 py-2.5"
                                >
                                    <p
                                        class="truncate text-sm font-semibold text-gray-900"
                                    >
                                        {{ auth.user?.name }}
                                    </p>
                                    <p class="truncate text-xs text-gray-500">
                                        {{ auth.user?.email }}
                                    </p>
                                </div>
                                <router-link
                                    :to="{ name: 'resident.profile' }"
                                    class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] leading-5 text-gray-700 hover:bg-gray-50 transition-colors"
                                    @click="showUserMenu = false"
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
                                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                                        />
                                    </svg>
                                    Profile
                                </router-link>
                                <button
                                    @click="handleLogout"
                                    class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-[13px] leading-5 text-gray-700 hover:bg-gray-50 transition-colors"
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
                </div>
            </header>

            <!-- Content -->
            <main class="min-w-0 flex-1 overflow-y-auto">
                <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
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
                    :to="{ name: 'resident.dashboard' }"
                    :class="tabClass('resident.dashboard')"
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
                    :to="{ name: 'resident.visitors' }"
                    :class="tabClass('resident.visitors')"
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
                    :to="{ name: 'resident.facilities' }"
                    :class="tabClass('resident.facilities')"
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
                    <span>Facilities</span>
                </router-link>
                <router-link
                    :to="{ name: 'resident.bookings' }"
                    :class="tabClass('resident.bookings')"
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
                    <span>Bookings</span>
                </router-link>
            </div>
        </nav>

        <ToastContainer />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useNotificationStore } from "@/stores/notification";
import { useSidebar } from "@/composables/useSidebar";
import { onClickOutside } from "@vueuse/core";
import SidebarLink from "@/components/common/SidebarLink.vue";
import ToastContainer from "@/components/common/ToastContainer.vue";

const auth = useAuthStore();
const notificationStore = useNotificationStore();
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

onMounted(() => {
    notificationStore.fetchNotifications();
});

async function handleLogout() {
    await auth.logout();
    router.push({ name: "login" });
}
</script>
