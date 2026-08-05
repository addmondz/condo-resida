<template>
    <div class="flex h-screen overflow-hidden bg-gray-50">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden"
                @click="sidebarOpen = false"
            />
        </Transition>

        <aside
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                'fixed inset-y-0 left-0 z-50 flex w-[260px] flex-col border-r border-gray-200 bg-white transition-transform duration-200 lg:static lg:translate-x-0',
            ]"
        >
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
                        Admin Portal
                    </p>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <p
                    class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-widest text-gray-400"
                >
                    Navigation
                </p>
                <div class="space-y-1">
                    <SidebarLink
                        :to="{ name: 'admin.dashboard' }"
                        icon="dashboard"
                        @click="sidebarOpen = false"
                        >Dashboard</SidebarLink
                    >
                    <SidebarLink
                        v-if="auth.isSuperAdmin"
                        :to="{ name: 'admin.properties' }"
                        icon="properties"
                        @click="sidebarOpen = false"
                        >Properties</SidebarLink
                    >
                    <SidebarLink
                        :to="{ name: 'admin.users' }"
                        icon="users"
                        @click="sidebarOpen = false"
                        >Users</SidebarLink
                    >
                    <SidebarLink
                        :to="{ name: 'admin.visitors' }"
                        icon="visitors"
                        @click="sidebarOpen = false"
                        >Visitors</SidebarLink
                    >
                    <SidebarLink
                        :to="{ name: 'admin.facilities' }"
                        icon="facilities"
                        @click="sidebarOpen = false"
                        >Facilities</SidebarLink
                    >
                    <SidebarLink
                        :to="{ name: 'admin.bookings' }"
                        icon="bookings"
                        @click="sidebarOpen = false"
                        >Bookings</SidebarLink
                    >
                    <SidebarLink
                        :to="{ name: 'admin.notifications' }"
                        icon="notifications"
                        @click="sidebarOpen = false"
                        >Notifications</SidebarLink
                    >
                    <SidebarLink
                        :to="{ name: 'admin.settings' }"
                        icon="settings"
                        @click="sidebarOpen = false"
                        >Settings</SidebarLink
                    >
                </div>
            </nav>

            <div class="shrink-0 border-t border-gray-100 p-3">
                <div class="flex items-center gap-3 rounded-xl px-3 py-2.5">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700"
                        >{{ initials }}</span
                    >
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

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <header
                class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 bg-white/80 px-4 backdrop-blur-md sm:px-6"
            >
                <button
                    @click="sidebarOpen = true"
                    class="flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 transition-colors hover:bg-gray-100 lg:hidden"
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
                <div class="flex items-center gap-1">
                    <div class="relative" ref="userMenuRef">
                        <button
                            @click="showUserMenu = !showUserMenu"
                            class="flex h-9 items-center gap-2 rounded-xl pl-1 pr-2 text-[13px] leading-5 text-gray-700 transition-colors hover:bg-gray-100"
                        >
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700"
                                >{{ initials }}</span
                            >
                            <span class="hidden sm:block">{{
                                auth.user?.name
                            }}</span>
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
                </div>
            </header>

            <main class="min-w-0 flex-1 overflow-y-auto">
                <div
                    class="mx-auto w-full max-w-7xl px-4 py-6 pb-16 sm:px-6 lg:px-8 lg:pb-0"
                >
                    <router-view v-slot="{ Component }">
                        <Transition name="page-fade" mode="out-in" appear>
                            <component :is="Component" />
                        </Transition>
                    </router-view>
                </div>
            </main>
        </div>

        <nav
            class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-200 bg-white/95 backdrop-blur-sm lg:hidden"
            style="padding-bottom: env(safe-area-inset-bottom)"
        >
            <div class="grid h-16 grid-cols-4">
                <router-link
                    :to="{ name: 'admin.dashboard' }"
                    :class="tabClass('admin.dashboard')"
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
                            d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"
                        />
                    </svg>
                    <span>Home</span>
                </router-link>
                <router-link
                    :to="{ name: 'admin.users' }"
                    :class="tabClass('admin.users')"
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
                            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"
                        />
                    </svg>
                    <span>Users</span>
                </router-link>
                <router-link
                    :to="{ name: 'admin.visitors' }"
                    :class="tabClass('admin.visitors')"
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
                            d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.75l.001-.031m11.998 0A5.971 5.971 0 0 0 12 13.5a5.971 5.971 0 0 0-5.999 5.219m11.998 0a8.955 8.955 0 0 0-.919-3.468m-10.16 0a8.955 8.955 0 0 0-.919 3.468M15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                        />
                    </svg>
                    <span>Visitors</span>
                </router-link>
                <router-link
                    :to="{ name: 'admin.bookings' }"
                    :class="tabClass('admin.bookings')"
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
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75Z"
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
import { computed, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import SidebarLink from "@/components/common/SidebarLink.vue";
import ToastContainer from "@/components/common/ToastContainer.vue";
import { onClickOutside } from "@vueuse/core";

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const sidebarOpen = ref(false);
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
