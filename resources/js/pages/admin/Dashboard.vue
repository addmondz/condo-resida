<template>
    <div>
        <PageHeader
            title="Admin Dashboard"
            subtitle="System overview and key metrics."
            :breadcrumbs="[{ label: 'Dashboard' }]"
        />

        <template v-if="loading">
            <div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                <SkeletonLoader
                    v-for="i in 4"
                    :key="i"
                    variant="card"
                    container-class="rounded-xl border border-gray-200 bg-white px-5 py-5"
                />
            </div>
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <SkeletonLoader
                    variant="card"
                    container-class="rounded-xl border border-gray-200 bg-white px-5 py-5 h-64"
                />
                <SkeletonLoader
                    variant="card"
                    container-class="rounded-xl border border-gray-200 bg-white px-5 py-5 h-64"
                />
            </div>
        </template>

        <template v-else>
            <!-- Charts -->
            <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <!-- Visitors - Bar Chart (7 days) -->
                <div class="rounded-xl border border-gray-200 bg-white">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h2 class="text-base font-semibold text-gray-900">
                            Visitors — Last 7 Days
                        </h2>
                        <p class="mt-0.5 text-sm text-gray-500">
                            Daily visitor registrations
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
                                <!-- Tooltip -->
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

                <!-- Users - Donut Chart -->
                <div class="rounded-xl border border-gray-200 bg-white">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h2 class="text-base font-semibold text-gray-900">
                            User Breakdown
                        </h2>
                        <p class="mt-0.5 text-sm text-gray-500">
                            Distribution by account status
                        </p>
                    </div>
                    <div class="px-5 py-5">
                        <div
                            class="flex flex-col items-center gap-6 sm:flex-row sm:justify-center"
                        >
                            <!-- Donut SVG -->
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
                                        v-if="!userTotal"
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
                                        >{{ userTotal }}</span
                                    >
                                    <span class="text-xs text-gray-500"
                                        >total</span
                                    >
                                </div>
                            </div>
                            <!-- Legend -->
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

            <!-- Bookings bar chart -->
            <div class="mb-6 rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-base font-semibold text-gray-900">
                        Bookings — Last 7 Days
                    </h2>
                    <p class="mt-0.5 text-sm text-gray-500">
                        Daily facility bookings
                    </p>
                </div>
                <div class="px-5 py-5">
                    <div
                        v-if="bookingSparkline.length"
                        class="flex items-end gap-1.5 sm:gap-2"
                        style="height: 120px"
                    >
                        <div
                            v-for="(day, i) in stats.bookings_daily"
                            :key="i"
                            class="group relative flex flex-1 flex-col items-center justify-end"
                            style="height: 100%"
                        >
                            <div
                                class="w-full rounded-t-md bg-purple-400 transition-all duration-300 group-hover:bg-purple-500"
                                :style="{
                                    height:
                                        barHeight(day.count, bookingMax) + '%',
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
                        class="flex items-center justify-center h-[120px] text-sm text-gray-400"
                    >
                        No data
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppButton from "@/components/common/AppButton.vue";
import StatCard from "@/components/common/StatCard.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";

const stats = ref({});
const loading = ref(true);

const visitorSparkline = computed(() =>
    (stats.value.visitors_daily || []).map((d) => d.count),
);
const bookingSparkline = computed(() =>
    (stats.value.bookings_daily || []).map((d) => d.count),
);

const visitorMax = computed(() => Math.max(...visitorSparkline.value, 1));
const bookingMax = computed(() => Math.max(...bookingSparkline.value, 1));

const userTotal = computed(() => stats.value.users?.total || 0);

const donutColors = {
    approved: "#22c55e",
    pending: "#eab308",
    rejected: "#ef4444",
    suspended: "#6b7280",
};

const donutSegments = computed(() => {
    const u = stats.value.users;
    if (!u || !userTotal.value) return [];
    const items = [
        { key: "approved", value: u.approved || 0 },
        { key: "pending", value: u.pending || 0 },
        { key: "rejected", value: u.rejected || 0 },
        { key: "suspended", value: u.suspended || 0 },
    ].filter((s) => s.value > 0);

    const circumference = 2 * Math.PI * 14;
    let offset = 0;
    return items.map((s) => {
        const length = (s.value / userTotal.value) * circumference;
        const seg = { strokeColor: donutColors[s.key], length, offset };
        offset += length;
        return seg;
    });
});

const donutLegend = computed(() => {
    const u = stats.value.users;
    if (!u) return [];
    return [
        {
            label: "Approved",
            value: u.approved || 0,
            color: donutColors.approved,
        },
        { label: "Pending", value: u.pending || 0, color: donutColors.pending },
        {
            label: "Rejected",
            value: u.rejected || 0,
            color: donutColors.rejected,
        },
        {
            label: "Suspended",
            value: u.suspended || 0,
            color: donutColors.suspended,
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

onMounted(async () => {
    try {
        const { data } = await adminApi.getDashboard();
        stats.value = data.data;
    } catch {
        // keep defaults
    } finally {
        loading.value = false;
    }
});
</script>
