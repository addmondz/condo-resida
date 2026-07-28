<template>
    <div class="pb-16 lg:pb-0">
        <!-- Greeting -->
        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">
                {{ greeting }}, {{ firstName }}
            </h1>
            <p class="mt-0.5 text-sm text-gray-500">{{ todayFormatted }}</p>
        </div>

        <template v-if="loading">
            <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <SkeletonLoader
                    variant="card"
                    container-class="rounded-xl border border-gray-200 bg-white px-5 py-5 h-64"
                />
                <SkeletonLoader
                    variant="card"
                    container-class="rounded-xl border border-gray-200 bg-white px-5 py-5 h-64"
                />
            </div>
            <div
                class="mb-6 rounded-xl border border-gray-200 bg-white px-5 py-5"
            >
                <SkeletonLoader variant="card" container-class="h-32" />
            </div>
        </template>

        <template v-else>
            <!-- Quick Actions -->
            <div class="mb-6 grid grid-cols-2 gap-3 lg:max-w-md">
                <router-link
                    :to="{ name: 'resident.visitors.create' }"
                    class="group flex flex-col items-center rounded-2xl border border-gray-100 bg-white px-4 py-6 text-center transition-all hover:border-primary-200 hover:bg-primary-50 hover:shadow-md active:scale-[0.98]"
                >
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-full bg-primary-600 shadow-md shadow-primary-600/30 transition-transform group-hover:scale-110"
                    >
                        <svg
                            class="h-6 w-6 text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"
                            />
                        </svg>
                    </div>
                    <span class="mt-3 text-sm font-bold text-gray-900">Register Visitor</span>
                    <span class="mt-1 text-xs text-gray-500">Pre-register a guest</span>
                </router-link>
                <router-link
                    :to="{ name: 'resident.facilities' }"
                    class="group flex flex-col items-center rounded-2xl border border-gray-100 bg-white px-4 py-6 text-center transition-all hover:border-emerald-200 hover:bg-emerald-50 hover:shadow-md active:scale-[0.98]"
                >
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 shadow-md shadow-emerald-600/30 transition-transform group-hover:scale-110"
                    >
                        <svg
                            class="h-6 w-6 text-white"
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
                    </div>
                    <span class="mt-3 text-sm font-bold text-gray-900">Book Facility</span>
                    <span class="mt-1 text-xs text-gray-500">Reserve an amenity</span>
                </router-link>
            </div>
            <!-- Charts -->
            <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <!-- Visitors - Bar Chart (7 days) -->
                <div class="rounded-xl border border-gray-200 bg-white">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h2 class="text-base font-semibold text-gray-900">
                            Visitors — Last 7 Days
                        </h2>
                        <p class="mt-0.5 text-sm text-gray-500">
                            Your visitor registrations
                        </p>
                    </div>
                    <div class="px-5 py-5">
                        <div
                            v-if="visitorSparkline.length"
                            class="flex items-end gap-1.5 sm:gap-2"
                            style="height: 140px"
                        >
                            <div
                                v-for="(day, i) in stats.visitors_daily"
                                :key="i"
                                class="group relative flex flex-1 flex-col items-center justify-end"
                                style="height: 100%"
                            >
                                <div
                                    class="w-full rounded-t-md bg-blue-400 transition-all duration-300 group-hover:bg-blue-500"
                                    :style="{
                                        height:
                                            barHeight(day.count, visitorMax) +
                                            '%',
                                        minHeight: '4px',
                                    }"
                                ></div>
                                <span
                                    class="mt-2 text-[10px] sm:text-xs text-gray-400"
                                    >{{ shortDay(day.date) }}</span
                                >
                                <div
                                    class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 rounded-lg bg-gray-900 px-2.5 py-1 text-xs font-medium text-white opacity-0 transition-opacity group-hover:opacity-100 whitespace-nowrap"
                                >
                                    {{ day.count }}
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex items-center justify-center h-[140px] text-sm text-gray-400"
                        >
                            No data
                        </div>
                    </div>
                </div>

                <!-- Visitor Status - Donut Chart -->
                <div class="rounded-xl border border-gray-200 bg-white">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h2 class="text-base font-semibold text-gray-900">
                            Visitor Status
                        </h2>
                        <p class="mt-0.5 text-sm text-gray-500">
                            All-time status breakdown
                        </p>
                    </div>
                    <div class="px-5 py-5">
                        <div
                            class="flex flex-col items-center gap-6 sm:flex-row sm:justify-center"
                        >
                            <div
                                class="relative h-32 w-32 shrink-0 sm:h-36 sm:w-36"
                            >
                                <svg
                                    viewBox="0 0 36 36"
                                    class="h-full w-full -rotate-90"
                                >
                                    <circle
                                        v-for="(seg, i) in donutSegments"
                                        :key="i"
                                        cx="18"
                                        cy="18"
                                        r="14"
                                        fill="none"
                                        :stroke="seg.strokeColor"
                                        stroke-width="4"
                                        :stroke-dasharray="`${seg.length} ${88 - seg.length}`"
                                        :stroke-dashoffset="`${-seg.offset}`"
                                        stroke-linecap="round"
                                        class="transition-all duration-500"
                                    />
                                    <circle
                                        v-if="!visitorTotal"
                                        cx="18"
                                        cy="18"
                                        r="14"
                                        fill="none"
                                        stroke="#e5e7eb"
                                        stroke-width="4"
                                    />
                                </svg>
                                <div
                                    class="absolute inset-0 flex flex-col items-center justify-center"
                                >
                                    <span
                                        class="text-2xl font-bold text-gray-900"
                                        >{{ visitorTotal }}</span
                                    >
                                    <span class="text-xs text-gray-500"
                                        >total</span
                                    >
                                </div>
                            </div>
                            <div
                                class="grid grid-cols-2 gap-x-6 gap-y-2.5 sm:grid-cols-1 sm:gap-y-3"
                            >
                                <div
                                    v-for="seg in donutLegend"
                                    :key="seg.label"
                                    class="flex items-center gap-2"
                                >
                                    <span
                                        class="h-2.5 w-2.5 shrink-0 rounded-full"
                                        :style="{ backgroundColor: seg.color }"
                                    ></span>
                                    <span class="text-sm text-gray-600">{{
                                        seg.label
                                    }}</span>
                                    <span
                                        class="ml-auto text-sm font-semibold text-gray-900"
                                        >{{ seg.value }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-Column Content -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Recent Visitors -->
                <div class="rounded-xl border border-gray-200 bg-white">
                    <div
                        class="flex items-center justify-between border-b border-gray-100 px-5 py-4"
                    >
                        <h2 class="text-base font-semibold text-gray-900">
                            Recent Visitors
                        </h2>
                        <router-link
                            :to="{ name: 'resident.visitors' }"
                            class="text-[13px] font-medium leading-5 text-primary-600 hover:text-primary-500 transition-colors"
                            >View all</router-link
                        >
                    </div>

                    <div v-if="loadingLists" class="p-5">
                        <SkeletonLoader variant="list" :rows="3" />
                    </div>
                    <div
                        v-else-if="recentVisitors.length === 0"
                        class="px-5 py-10 text-center"
                    >
                        <div
                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100"
                        >
                            <svg
                                class="h-5 w-5 text-gray-400"
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
                        </div>
                        <p class="mt-3 text-sm text-gray-500">
                            No visitors registered yet.
                        </p>
                        <router-link
                            :to="{ name: 'resident.visitors.create' }"
                            class="mt-3 inline-flex items-center gap-1 text-[13px] font-medium leading-5 text-primary-600 hover:text-primary-500"
                        >
                            Register your first visitor
                            <svg
                                class="h-3.5 w-3.5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                                />
                            </svg>
                        </router-link>
                    </div>
                    <div v-else class="divide-y divide-gray-50">
                        <router-link
                            v-for="visitor in recentVisitors"
                            :key="visitor.uuid"
                            :to="{
                                name: 'resident.visitors.show',
                                params: { uuid: visitor.uuid },
                            }"
                            class="flex items-center gap-3 px-5 py-3.5 transition-colors hover:bg-gray-50/60"
                        >
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-600"
                            >
                                {{
                                    visitor.visitor_name
                                        ?.charAt(0)
                                        ?.toUpperCase() || "?"
                                }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm font-medium text-gray-900"
                                >
                                    {{ visitor.visitor_name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ formatDate(visitor.visit_date) }}
                                </p>
                            </div>
                            <StatusBadge :status="visitor.status" />
                        </router-link>
                    </div>
                </div>

                <!-- Upcoming Bookings -->
                <div class="rounded-xl border border-gray-200 bg-white">
                    <div
                        class="flex items-center justify-between border-b border-gray-100 px-5 py-4"
                    >
                        <h2 class="text-base font-semibold text-gray-900">
                            Upcoming Bookings
                        </h2>
                        <router-link
                            :to="{ name: 'resident.bookings' }"
                            class="text-[13px] font-medium leading-5 text-primary-600 hover:text-primary-500 transition-colors"
                            >View all</router-link
                        >
                    </div>

                    <div v-if="loadingLists" class="p-5">
                        <SkeletonLoader variant="list" :rows="3" />
                    </div>
                    <div
                        v-else-if="upcomingBookings.length === 0"
                        class="px-5 py-10 text-center"
                    >
                        <div
                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100"
                        >
                            <svg
                                class="h-5 w-5 text-gray-400"
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
                        </div>
                        <p class="mt-3 text-sm text-gray-500">
                            No upcoming bookings.
                        </p>
                        <router-link
                            :to="{ name: 'resident.facilities' }"
                            class="mt-3 inline-flex items-center gap-1 text-[13px] font-medium leading-5 text-primary-600 hover:text-primary-500"
                        >
                            Browse facilities
                            <svg
                                class="h-3.5 w-3.5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                                />
                            </svg>
                        </router-link>
                    </div>
                    <div v-else class="divide-y divide-gray-50">
                        <div
                            v-for="booking in upcomingBookings"
                            :key="booking.uuid"
                            class="flex items-center gap-3 px-5 py-3.5"
                        >
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-50"
                            >
                                <svg
                                    class="h-4 w-4 text-blue-600"
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
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm font-medium text-gray-900"
                                >
                                    {{ booking.facility?.name || "Facility" }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{
                                        formatDate(booking.booking_date)
                                    }}
                                    &middot; {{ booking.start_time }} -
                                    {{ booking.end_time }}
                                </p>
                            </div>
                            <StatusBadge :status="booking.status" />
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useNotificationStore } from "@/stores/notification";
import residentApi from "@/api/resident";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";

const auth = useAuthStore();
const notificationStore = useNotificationStore();

const stats = ref({});
const loading = ref(true);
const loadingLists = ref(true);
const recentVisitors = ref([]);
const upcomingBookings = ref([]);

const firstName = computed(() => auth.user?.name?.split(" ")[0] || "Resident");

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return "Good morning";
    if (hour < 17) return "Good afternoon";
    return "Good evening";
});

const todayFormatted = computed(() => {
    return new Date().toLocaleDateString("en-MY", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
});

const visitorSparkline = computed(() =>
    (stats.value.visitors_daily || []).map((d) => d.count),
);
const bookingSparkline = computed(() =>
    (stats.value.bookings_daily || []).map((d) => d.count),
);

const visitorMax = computed(() => Math.max(...visitorSparkline.value, 1));
const bookingMax = computed(() => Math.max(...bookingSparkline.value, 1));

const visitorTotal = computed(() => stats.value.total_visitors || 0);

const donutColors = {
    active: "#3b82f6",
    checked_in: "#22c55e",
    checked_out: "#6b7280",
    expired: "#eab308",
    cancelled: "#ef4444",
};

const donutSegments = computed(() => {
    const s = stats.value.visitor_status;
    if (!s || !visitorTotal.value) return [];
    const items = [
        { key: "active", value: s.active || 0 },
        { key: "checked_in", value: s.checked_in || 0 },
        { key: "checked_out", value: s.checked_out || 0 },
        { key: "expired", value: s.expired || 0 },
        { key: "cancelled", value: s.cancelled || 0 },
    ].filter((i) => i.value > 0);

    const circumference = 2 * Math.PI * 14;
    let offset = 0;
    return items.map((i) => {
        const length = (i.value / visitorTotal.value) * circumference;
        const seg = { strokeColor: donutColors[i.key], length, offset };
        offset += length;
        return seg;
    });
});

const donutLegend = computed(() => {
    const s = stats.value.visitor_status;
    if (!s) return [];
    return [
        { label: "Active", value: s.active || 0, color: donutColors.active },
        {
            label: "Checked In",
            value: s.checked_in || 0,
            color: donutColors.checked_in,
        },
        {
            label: "Checked Out",
            value: s.checked_out || 0,
            color: donutColors.checked_out,
        },
        { label: "Expired", value: s.expired || 0, color: donutColors.expired },
        {
            label: "Cancelled",
            value: s.cancelled || 0,
            color: donutColors.cancelled,
        },
    ];
});

function barHeight(value, max) {
    if (!max) return 0;
    return Math.max((value / max) * 100, 3);
}

function shortDay(dateStr) {
    if (!dateStr) return "";
    const d = new Date(dateStr + "T00:00:00");
    return d.toLocaleDateString("en-US", { weekday: "short" }).slice(0, 3);
}

function collectionFromResponse(data) {
    if (Array.isArray(data?.data?.data)) return data.data.data;
    if (Array.isArray(data?.data)) return data.data;
    return [];
}

function formatDate(dateStr) {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    return date.toLocaleDateString("en-MY", {
        weekday: "short",
        month: "short",
        day: "numeric",
    });
}

onMounted(async () => {
    try {
        const { data } = await residentApi.getDashboard();
        stats.value = data.data;
    } catch {
        // keep defaults
    } finally {
        loading.value = false;
    }

    try {
        const [visitorsRes, bookingsRes] = await Promise.all([
            residentApi.getVisitors({ per_page: 5, sort: "-created_at" }),
            residentApi.getBookings({ filter: "upcoming", per_page: 5 }),
        ]);
        recentVisitors.value = collectionFromResponse(visitorsRes.data).filter(
            (v) => v.uuid,
        );
        upcomingBookings.value = collectionFromResponse(bookingsRes.data);
    } catch {
        // keep defaults
    } finally {
        loadingLists.value = false;
    }
});
</script>
