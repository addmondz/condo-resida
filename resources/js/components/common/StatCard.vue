<template>
    <div
        class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 transition-shadow hover:shadow-md"
    >
        <div class="flex items-center gap-3 sm:gap-4">
            <div
                :class="[
                    'flex h-10 w-10 sm:h-11 sm:w-11 shrink-0 items-center justify-center rounded-xl',
                    iconBg,
                ]"
            >
                <slot name="icon">
                    <svg
                        class="h-5 w-5"
                        :class="iconColor"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            :d="resolvedIconPath"
                        />
                    </svg>
                </slot>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs sm:text-sm text-gray-500 truncate">
                    {{ label }}
                </p>
                <div class="flex items-center gap-2">
                    <p
                        class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight"
                    >
                        {{ value }}
                    </p>
                    <span
                        v-if="trend"
                        :class="[
                            'inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-xs font-semibold',
                            trendColor,
                        ]"
                    >
                        <svg
                            v-if="trend > 0"
                            class="h-3 w-3"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-3 w-3"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4.5 4.5l15 15m0 0V8.25m0 11.25H8.25"
                            />
                        </svg>
                        {{ Math.abs(trend) }}%
                    </span>
                </div>
            </div>
            <!-- Sparkline -->
            <svg
                v-if="sparkline?.length"
                class="hidden sm:block h-8 w-16 shrink-0"
                :viewBox="`0 0 ${sparkline.length * 8} 32`"
                preserveAspectRatio="none"
            >
                <rect
                    v-for="(val, i) in normalizedSparkline"
                    :key="i"
                    :x="i * 8 + 1"
                    :y="32 - val"
                    :width="5"
                    :height="Math.max(val, 2)"
                    rx="1.5"
                    :class="sparkFill"
                />
            </svg>
        </div>
        <div v-if="progress !== null" class="mt-3">
            <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="text-gray-500">{{ progressLabel }}</span>
                <span class="font-medium text-gray-700">{{ progress }}%</span>
            </div>
            <div class="h-1.5 w-full rounded-full bg-gray-100">
                <div
                    class="h-1.5 rounded-full transition-all duration-500"
                    :class="progressColor"
                    :style="{ width: `${Math.min(progress, 100)}%` }"
                ></div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    icon: { type: String, default: "" },
    iconPath: { type: String, default: "" },
    color: { type: String, default: "primary" },
    trend: { type: Number, default: null },
    progress: { type: Number, default: null },
    progressLabel: { type: String, default: "" },
    sparkline: { type: Array, default: null },
});

const colorMap = {
    primary: {
        bg: "bg-primary-50",
        icon: "text-primary-600",
        bar: "bg-primary-500",
        spark: "fill-primary-300",
    },
    green: {
        bg: "bg-green-50",
        icon: "text-green-600",
        bar: "bg-green-500",
        spark: "fill-green-300",
    },
    yellow: {
        bg: "bg-yellow-50",
        icon: "text-yellow-600",
        bar: "bg-yellow-500",
        spark: "fill-yellow-300",
    },
    orange: {
        bg: "bg-orange-50",
        icon: "text-orange-600",
        bar: "bg-orange-500",
        spark: "fill-orange-300",
    },
    red: {
        bg: "bg-red-50",
        icon: "text-red-600",
        bar: "bg-red-500",
        spark: "fill-red-300",
    },
    blue: {
        bg: "bg-blue-50",
        icon: "text-blue-600",
        bar: "bg-blue-500",
        spark: "fill-blue-300",
    },
    purple: {
        bg: "bg-purple-50",
        icon: "text-purple-600",
        bar: "bg-purple-500",
        spark: "fill-purple-300",
    },
    gray: {
        bg: "bg-gray-100",
        icon: "text-gray-600",
        bar: "bg-gray-500",
        spark: "fill-gray-300",
    },
};

const icons = {
    users: "M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z",
    clock: "M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z",
    check: "M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z",
    ban: "M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636",
    login: "M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9",
    logout: "M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75",
    calendar:
        "M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75Z",
    settings:
        "M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z",
    notifications:
        "M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0",
};

const scheme = computed(() => colorMap[props.color] || colorMap.primary);
const resolvedIconPath = computed(
    () => props.iconPath || icons[props.icon] || icons.users,
);
const iconBg = computed(() => scheme.value.bg);
const iconColor = computed(() => scheme.value.icon);
const progressColor = computed(() => scheme.value.bar);
const sparkFill = computed(() => scheme.value.spark);

const normalizedSparkline = computed(() => {
    if (!props.sparkline?.length) return [];
    const max = Math.max(...props.sparkline, 1);
    return props.sparkline.map((v) => (v / max) * 28 + 2);
});

const trendColor = computed(() =>
    props.trend > 0 ? "bg-green-50 text-green-700" : "bg-red-50 text-red-700",
);
</script>
