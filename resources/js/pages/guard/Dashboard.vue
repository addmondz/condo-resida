<template>
    <div class="pb-16 lg:pb-0">
        <PageHeader
            title="Guard Dashboard"
            subtitle="Today's visitor overview."
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
         <!-- Quick Actions -->
            <div class="grid grid-cols-2 gap-3 lg:max-w-md mb-3">
                <router-link
                    :to="{ name: 'guard.scanner' }"
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
                                d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Z"
                            />
                        </svg>
                    </div>
                    <span class="mt-3 text-sm font-bold text-gray-900">Scan QR Code</span>
                    <span class="mt-1 text-xs text-gray-500">Verify visitor entry</span>
                </router-link>
                <router-link
                    :to="{ name: 'guard.visitors' }"
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
                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
                            />
                        </svg>
                    </div>
                    <span class="mt-3 text-sm font-bold text-gray-900">View All Visitors</span>
                    <span class="mt-1 text-xs text-gray-500">Today's visitor list</span>
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
                            Daily visitor count
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

                <!-- Today's Status - Donut Chart -->
                <div class="rounded-xl border border-gray-200 bg-white">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h2 class="text-base font-semibold text-gray-900">
                            Today's Status
                        </h2>
                        <p class="mt-0.5 text-sm text-gray-500">
                            Visitor status breakdown
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
                                        v-if="!todayTotal"
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
                                        >{{ todayTotal }}</span
                                    >
                                    <span class="text-xs text-gray-500"
                                        >today</span
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
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import guardApi from "@/api/guard";
import PageHeader from "@/components/common/PageHeader.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";

const stats = ref({});
const loading = ref(true);

const visitorSparkline = computed(() =>
    (stats.value.visitors_daily || []).map((d) => d.count),
);

const visitorMax = computed(() => Math.max(...visitorSparkline.value, 1));

const todayTotal = computed(() => stats.value.today_total || 0);

const donutColors = {
    active: "#3b82f6",
    checked_in: "#22c55e",
    checked_out: "#6b7280",
    cancelled: "#ef4444",
};

const donutSegments = computed(() => {
    const b = stats.value.status_breakdown;
    if (!b || !todayTotal.value) return [];
    const items = [
        { key: "active", value: b.active || 0 },
        { key: "checked_in", value: b.checked_in || 0 },
        { key: "checked_out", value: b.checked_out || 0 },
        { key: "cancelled", value: b.cancelled || 0 },
    ].filter((s) => s.value > 0);

    const circumference = 2 * Math.PI * 14;
    let offset = 0;
    return items.map((s) => {
        const length = (s.value / todayTotal.value) * circumference;
        const seg = { strokeColor: donutColors[s.key], length, offset };
        offset += length;
        return seg;
    });
});

const donutLegend = computed(() => {
    const b = stats.value.status_breakdown;
    if (!b) return [];
    return [
        { label: "Expected", value: b.active || 0, color: donutColors.active },
        {
            label: "Checked In",
            value: b.checked_in || 0,
            color: donutColors.checked_in,
        },
        {
            label: "Checked Out",
            value: b.checked_out || 0,
            color: donutColors.checked_out,
        },
        {
            label: "Cancelled",
            value: b.cancelled || 0,
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

onMounted(async () => {
    try {
        const { data } = await guardApi.getDashboard();
        stats.value = data.data;
    } catch {
        // keep defaults
    } finally {
        loading.value = false;
    }
});
</script>
