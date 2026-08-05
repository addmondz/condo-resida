<template>
    <div class="pb-16 lg:pb-0">
        <PageHeader
            title="QR Scanner"
            subtitle="Validate visitor passes by manual token or QR scan."
            :breadcrumbs="[{ label: 'Scanner' }]"
        />

        <div class="max-w-xl">
            <section
                class="overflow-hidden rounded-xl border border-gray-200 bg-white"
            >
                <div class="border-b border-gray-100 px-5 py-4">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">
                                {{ panelTitle }}
                            </h2>
                            <p v-if="panelSubtitle" class="mt-0.5 text-sm text-gray-500">
                                {{ panelSubtitle }}
                            </p>
                        </div>
                        <span
                            v-if="scannerMode !== 'result'"
                            class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1 text-xs font-medium"
                            :class="scannerStatusClass"
                        >
                            <span
                                class="h-2 w-2 rounded-full"
                                :class="scannerDotClass"
                            ></span>
                            {{ scannerStatusLabel }}
                        </span>
                    </div>
                </div>

                <form
                    v-if="scannerMode === 'manual'"
                    @submit.prevent="handleValidate"
                    class="space-y-5 px-5 py-5"
                >
                    <div
                        class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-4"
                    >
                        <AppInput
                            v-model="qrToken"
                            label="QR Token"
                            placeholder="Paste or type QR token"
                            :error="errorMessage"
                        />
                    </div>

                    <div class="space-y-4">
                        <AppButton
                            type="submit"
                            size="xl"
                            :loading="validating"
                            class="w-full"
                        >
                            Validate Entry
                        </AppButton>

                        <div class="flex items-center gap-3">
                            <div class="h-px flex-1 bg-gray-200"></div>
                            <span
                                class="text-xs font-semibold uppercase tracking-wide text-gray-400"
                            >
                                - or -
                            </span>
                            <div class="h-px flex-1 bg-gray-200"></div>
                        </div>

                        <button
                            type="button"
                            class="group flex w-full flex-col items-center rounded-xl border-2 border-gray-300 bg-white px-4 py-4 text-center transition-all hover:border-primary-300 hover:bg-primary-50 hover:shadow-md active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="startingCamera"
                            @click="startCamera"
                        >
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-full bg-primary-600 shadow-md shadow-primary-600/30 transition-transform group-hover:scale-105"
                            >
                                <svg
                                    v-if="!startingCamera"
                                    class="h-5 w-5 text-white"
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
                                <svg
                                    v-else
                                    class="h-5 w-5 animate-spin text-white"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    />
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    />
                                </svg>
                            </div>
                            <span class="mt-2 text-sm font-bold text-gray-900">
                                Scan QR Code
                            </span>
                            <span class="mt-1 text-xs text-gray-500">
                                Verify visitor entry
                            </span>
                        </button>
                    </div>

                    <p
                        v-if="cameraError"
                        class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                    >
                        {{ cameraError }}
                    </p>
                </form>

                <div v-else-if="scannerMode === 'camera'" class="p-4 sm:p-5">
                    <div
                        class="relative mx-auto aspect-[4/3] w-full max-w-3xl overflow-hidden rounded-xl bg-gray-950 shadow-inner"
                    >
                        <div
                            :id="scannerElementId"
                            :class="[
                                'h-full w-full scanner-reader transition-opacity',
                                isCameraActive ? 'opacity-100' : 'opacity-0',
                            ]"
                        ></div>

                        <div
                            v-if="!isCameraActive"
                            class="absolute inset-0 flex flex-col items-center justify-center px-6 text-center"
                        >
                            <div
                                class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white/10 text-white"
                            >
                                <svg
                                    class="h-8 w-8"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 15.75h2.25m2.25 0h2.25M15.75 13.5v4.5"
                                    />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-white">
                                Starting camera
                            </h3>
                            <p
                                class="mt-2 max-w-sm text-sm leading-6 text-gray-300"
                            >
                                Allow camera access, then place the visitor QR
                                code inside the frame.
                            </p>
                        </div>

                        <div
                            v-if="isCameraActive"
                            class="pointer-events-none absolute inset-0 flex items-center justify-center"
                        >
                            <div
                                class="relative h-[62%] w-[72%] max-w-sm rounded-2xl border-2 border-white/80 shadow-[0_0_0_999px_rgba(0,0,0,0.38)]"
                            >
                                <span
                                    class="absolute -left-0.5 -top-0.5 h-8 w-8 rounded-tl-2xl border-l-4 border-t-4 border-emerald-400"
                                ></span>
                                <span
                                    class="absolute -right-0.5 -top-0.5 h-8 w-8 rounded-tr-2xl border-r-4 border-t-4 border-emerald-400"
                                ></span>
                                <span
                                    class="absolute -bottom-0.5 -left-0.5 h-8 w-8 rounded-bl-2xl border-b-4 border-l-4 border-emerald-400"
                                ></span>
                                <span
                                    class="absolute -bottom-0.5 -right-0.5 h-8 w-8 rounded-br-2xl border-b-4 border-r-4 border-emerald-400"
                                ></span>
                            </div>
                        </div>

                        <div
                            v-if="cameraMessage"
                            class="absolute inset-x-4 bottom-4 rounded-lg bg-gray-950/85 px-4 py-3 text-sm text-white backdrop-blur"
                        >
                            {{ cameraMessage }}
                        </div>

                        <div
                            v-if="scanStatusBadge"
                            class="absolute inset-x-4 top-4 flex justify-center"
                        >
                            <span
                                class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold shadow-lg backdrop-blur"
                                :class="scanStatusBadgeClass"
                            >
                                <span
                                    v-if="scanStatusBadge === 'detecting'"
                                    class="h-2.5 w-2.5 animate-pulse rounded-full bg-amber-400"
                                ></span>
                                <span
                                    v-else-if="scanStatusBadge === 'validating'"
                                    class="h-2.5 w-2.5 animate-spin rounded-full border-2 border-white border-t-transparent"
                                ></span>
                                <span
                                    v-else-if="scanStatusBadge === 'success'"
                                    class="h-2.5 w-2.5 rounded-full bg-emerald-400"
                                ></span>
                                <span
                                    v-else
                                    class="h-2.5 w-2.5 rounded-full bg-red-400"
                                ></span>
                                {{ scanStatusText }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="scanLog.length"
                        class="mt-3 max-h-36 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 font-mono text-xs"
                    >
                        <div
                            v-for="(entry, i) in scanLog"
                            :key="i"
                            class="flex gap-2 py-0.5"
                            :class="{
                                'text-emerald-700': entry.type === 'success',
                                'text-red-600': entry.type === 'error',
                                'text-amber-600': entry.type === 'warn',
                                'text-gray-500': entry.type === 'info',
                            }"
                        >
                            <span class="shrink-0 text-gray-400">{{
                                entry.time
                            }}</span>
                            <span>{{ entry.msg }}</span>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <AppButton
                            type="button"
                            size="xl"
                            variant="secondary"
                            class="w-full"
                            @click="cancelCameraScan"
                        >
                            Enter Manually
                        </AppButton>
                        <AppButton
                            type="button"
                            size="xl"
                            variant="secondary"
                            class="w-full"
                            @click="restartCamera"
                        >
                            Restart Camera
                        </AppButton>
                    </div>

                    <p
                        v-if="cameraError"
                        class="mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                    >
                        {{ cameraError }}
                    </p>
                </div>

                <div v-else-if="scannerResult" class="px-5 py-6">
                    <div class="flex flex-col items-center">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full"
                            :class="resultIconBg"
                        >
                            <svg
                                v-if="resultIconType === 'check'"
                                class="h-6 w-6 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4.5 12.75l6 6 9-13.5"
                                />
                            </svg>
                            <svg
                                v-else-if="resultIconType === 'warning'"
                                class="h-6 w-6 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
                                />
                            </svg>
                            <svg
                                v-else
                                class="h-6 w-6 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </div>
                        <p
                            class="mt-3 text-sm font-semibold"
                            :class="resultLabelColor"
                        >
                            {{ resultStatusLabel }}
                        </p>
                    </div>

                    <template v-if="visitor">
                        <div class="mt-4 text-center">
                            <h3
                                class="text-xl font-bold text-gray-900"
                            >
                                {{ visitor.visitor_name }}
                            </h3>
                            <p class="mt-0.5 text-sm text-gray-500">
                                {{ formatResult(visitor.purpose) }}
                            </p>
                        </div>

                        <div class="mt-5 rounded-lg bg-gray-50 px-4 py-3">
                            <p
                                class="text-[11px] font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Destination
                            </p>
                            <p
                                class="mt-1 text-sm font-semibold text-gray-900"
                            >
                                {{ visitor.property_name }}
                            </p>
                            <p
                                v-if="unitLabel"
                                class="text-sm text-gray-600"
                            >
                                {{ unitLabel }}
                            </p>
                        </div>

                        <div
                            class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3"
                        >
                            <div
                                v-for="item in compactDetails"
                                :key="item.label"
                            >
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-wider text-gray-400"
                                >
                                    {{ item.label }}
                                </p>
                                <p
                                    class="mt-0.5 text-sm"
                                    :class="
                                        item.mono
                                            ? 'font-mono text-gray-500'
                                            : 'font-medium text-gray-900'
                                    "
                                >
                                    {{ item.value }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="timestampDisplay"
                            class="mt-4 flex items-center justify-center gap-1.5 rounded-lg border border-gray-100 py-2 text-sm text-gray-500"
                        >
                            <svg
                                class="h-3.5 w-3.5"
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
                            {{ timestampDisplay }}
                        </div>

                        <div class="mt-5">
                            <AppButton
                                v-if="scannerResult.can_check_in"
                                @click="showCheckInDialog = true"
                                :loading="checkingIn"
                                size="xl"
                                class="w-full"
                            >
                                Check In Visitor
                            </AppButton>
                            <AppButton
                                v-if="scannerResult.can_check_out"
                                @click="showCheckOutDialog = true"
                                :loading="checkingOut"
                                size="xl"
                                class="w-full"
                            >
                                Check Out Visitor
                            </AppButton>
                        </div>
                    </template>

                    <p
                        v-else
                        class="mt-3 text-center text-sm text-red-600"
                    >
                        {{ scannerResult.message }}
                    </p>

                    <div
                        class="mt-6 grid grid-cols-2 gap-3"
                    >
                        <button
                            type="button"
                            class="cursor-pointer rounded-[10px] border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40"
                            :disabled="startingCamera"
                            @click="startCamera"
                        >
                            Scan Another
                        </button>
                        <button
                            type="button"
                            class="cursor-pointer rounded-[10px] border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 active:scale-[0.98]"
                            @click="resetScan"
                        >
                            Enter Manually
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <ConfirmationDialog
            :show="showCheckInDialog"
            title="Check In Visitor"
            :message="`Confirm check-in for ${visitor?.visitor_name || 'this visitor'}? This will record their entry.`"
            confirm-label="Check In"
            confirm-variant="success"
            :loading="checkingIn"
            @confirm="handleCheckIn"
            @cancel="showCheckInDialog = false"
        />

        <ConfirmationDialog
            :show="showCheckOutDialog"
            title="Check Out Visitor"
            :message="`Confirm check-out for ${visitor?.visitor_name || 'this visitor'}? This will record their departure.`"
            confirm-label="Check Out"
            confirm-variant="primary"
            :loading="checkingOut"
            @confirm="handleCheckOut"
            @cancel="showCheckOutDialog = false"
        />
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref } from "vue";
import { Html5Qrcode, Html5QrcodeSupportedFormats } from "html5-qrcode";
import guardApi from "@/api/guard";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppButton from "@/components/common/AppButton.vue";
import ConfirmationDialog from "@/components/common/ConfirmationDialog.vue";
import { useToast } from "@/composables/useToast";

const scannerElementId = "guard-qr-reader";
const qrToken = ref("");
const visitor = ref(null);
const scannerResult = ref(null);
const scannerMode = ref("manual");
const validating = ref(false);
const checkingIn = ref(false);
const checkingOut = ref(false);
const showCheckInDialog = ref(false);
const showCheckOutDialog = ref(false);
const startingCamera = ref(false);
const isCameraActive = ref(false);
const scanLocked = ref(false);
const errorMessage = ref("");
const cameraError = ref("");
const cameraMessage = ref("");
const scanStatusBadge = ref("");
const scanStatusText = ref("");
const scanLog = ref([]);
const toast = useToast();

let qrScanner = null;
let lastDecodedAt = 0;

const scanStatusBadgeClass = computed(() => {
    const map = {
        detecting: "bg-amber-900/80 text-amber-100",
        validating: "bg-blue-900/80 text-blue-100",
        success: "bg-emerald-900/80 text-emerald-100",
        error: "bg-red-900/80 text-red-100",
    };
    return map[scanStatusBadge.value] || "bg-gray-900/80 text-gray-100";
});

function addScanLog(msg, type = "info") {
    const now = new Date();
    const time = now.toLocaleTimeString("en-GB", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
    scanLog.value.unshift({ time, msg, type });
    if (scanLog.value.length > 30) scanLog.value.pop();
}

function setScanBadge(badge, text) {
    scanStatusBadge.value = badge;
    scanStatusText.value = text;
}

const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

const scannerStatusLabel = computed(() => {
    if (validating.value) return "Validating";
    if (isCameraActive.value) return "Scanning";
    if (scannerResult.value) return "Result ready";
    return "Manual entry";
});

const scannerStatusClass = computed(() => {
    if (validating.value) return "bg-amber-50 text-amber-700";
    if (isCameraActive.value) return "bg-emerald-50 text-emerald-700";
    if (scannerResult.value) return "bg-blue-50 text-blue-700";
    return "bg-gray-100 text-gray-600";
});

const scannerDotClass = computed(() => {
    if (validating.value) return "bg-amber-500";
    if (isCameraActive.value) return "bg-emerald-500";
    if (scannerResult.value) return "bg-blue-500";
    return "bg-gray-400";
});

const panelTitle = computed(() => {
    if (scannerMode.value === "camera") return "Scan QR Code";
    if (scannerMode.value === "result") return "Scan Result";
    return "Manual Entry";
});

const panelSubtitle = computed(() => {
    if (scannerMode.value === "camera") {
        return "Center the visitor QR code in the camera frame.";
    }
    if (scannerMode.value === "result") return "";
    return "Enter the QR token, or switch to camera scanning.";
});

const resultIconType = computed(() => {
    if (!scannerResult.value) return "error";
    if (scannerResult.value.can_check_in) return "check";
    if (scannerResult.value.can_check_out) return "check";
    const r = scannerResult.value.result;
    if (r === "already_checked_out") return "check";
    if (["expired", "not_valid_for_today"].includes(r)) return "warning";
    return "error";
});

const resultIconBg = computed(() => {
    if (!scannerResult.value) return "bg-red-500";
    if (scannerResult.value.can_check_in) return "bg-emerald-500";
    if (scannerResult.value.can_check_out) return "bg-blue-500";
    const r = scannerResult.value.result;
    if (r === "already_checked_out") return "bg-gray-400";
    if (["expired", "not_valid_for_today"].includes(r)) return "bg-amber-500";
    return "bg-red-500";
});

const resultLabelColor = computed(() => {
    if (!scannerResult.value) return "text-red-600";
    if (scannerResult.value.can_check_in) return "text-emerald-600";
    if (scannerResult.value.can_check_out) return "text-blue-600";
    const r = scannerResult.value.result;
    if (r === "already_checked_out") return "text-gray-500";
    if (["expired", "not_valid_for_today"].includes(r)) return "text-amber-600";
    return "text-red-600";
});

const resultStatusLabel = computed(() => {
    if (!scannerResult.value) return "Error";
    if (scannerResult.value.can_check_in) return "Ready to Check In";
    if (scannerResult.value.can_check_out) return "Checked In";
    const r = scannerResult.value.result;
    const labels = {
        already_checked_out: "Checked Out",
        expired: "Pass Expired",
        cancelled: "Pass Cancelled",
        not_valid_for_today: "Not Valid Today",
        suspended_resident: "Resident Suspended",
        invalid_qr: "Invalid QR Code",
    };
    return labels[r] || "Invalid";
});

const compactDetails = computed(() => {
    if (!visitor.value) return [];
    return [
        { label: "Contact", value: visitor.value.contact_number },
        { label: "Vehicle", value: visitor.value.vehicle_number },
        { label: "Visit Date", value: formattedVisitDate.value },
        {
            label: "Reference",
            value: visitor.value.reference_number,
            mono: true,
        },
    ].filter((item) => item.value);
});

const formattedVisitDate = computed(() => {
    if (!visitor.value?.visit_date) return "";
    const today = new Date().toISOString().slice(0, 10);
    if (visitor.value.visit_date === today) return "Today";
    const d = new Date(visitor.value.visit_date + "T00:00:00");
    return d.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
});

const timestampDisplay = computed(() => {
    if (!visitor.value) return "";
    if (visitor.value.checked_out_at) {
        return `Checked out at ${formatTime(visitor.value.checked_out_at)}`;
    }
    if (visitor.value.checked_in_at) {
        return `Checked in at ${formatTime(visitor.value.checked_in_at)}`;
    }
    return "";
});

const unitLabel = computed(() => {
    if (!visitor.value) return "";
    const block = visitor.value.block_name || "";
    const unit = visitor.value.unit_name || "";
    if (block && unit) return `${block}, ${unit}`;
    return block || unit;
});

async function startCamera() {
    await resetScan({ mode: "camera" });
    cameraError.value = "";
    errorMessage.value = "";
    cameraMessage.value = "Requesting camera access...";
    startingCamera.value = true;
    addScanLog("Starting camera...", "info");

    try {
        await nextTick();

        if (qrScanner) {
            try {
                await qrScanner.clear();
            } catch (_) {}
            qrScanner = null;
        }

        qrScanner = new Html5Qrcode(scannerElementId, {
            formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
            verbose: false,
        });

        addScanLog("Enumerating cameras...", "info");
        const cameras = await Html5Qrcode.getCameras();
        addScanLog(`Found ${cameras.length} camera(s)`, "info");

        const cameraId = preferredCameraId(cameras);
        const cameraConfig = cameraId
            ? { deviceId: { exact: cameraId } }
            : { facingMode: "environment" };

        await qrScanner.start(
            cameraConfig,
            {
                fps: isMobile ? 5 : 10,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    const size = Math.floor(minEdge * 0.72);
                    return { width: size, height: size };
                },
                aspectRatio: 1.333,
                disableFlip: false,
            },
            handleScanSuccess,
            () => {},
        );

        isCameraActive.value = true;
        scanLocked.value = false;
        lastDecodedAt = 0;
        cameraMessage.value = "Hold the QR code inside the frame.";
        addScanLog("Camera active — ready to scan", "success");
    } catch (err) {
        cameraError.value = cameraErrorMessage(err);
        cameraMessage.value = "";
        isCameraActive.value = false;
        scannerMode.value = "manual";
        addScanLog(`Camera error: ${cameraError.value}`, "error");
    } finally {
        startingCamera.value = false;
    }
}

async function restartCamera() {
    await stopCamera();
    await startCamera();
}

async function cancelCameraScan() {
    await stopCamera();
    scannerMode.value = "manual";
    cameraError.value = "";
    cameraMessage.value = "";
    scanLocked.value = false;
}

async function stopCamera() {
    if (!qrScanner || !isCameraActive.value) {
        isCameraActive.value = false;
        return;
    }

    try {
        const stopPromise = qrScanner.stop();
        const timeout = new Promise((_, reject) =>
            setTimeout(() => reject(new Error("Camera stop timed out")), 3000),
        );
        await Promise.race([stopPromise, timeout]);
        addScanLog("Camera stopped", "info");
    } catch (err) {
        addScanLog(`Camera stop: ${err.message || "forced"}`, "warn");
    } finally {
        isCameraActive.value = false;
        cameraMessage.value = "";
    }
}

async function handleScanSuccess(decodedText) {
    if (scanLocked.value || !decodedText) return;

    const now = Date.now();
    if (now - lastDecodedAt < 1500) return;
    lastDecodedAt = now;

    scanLocked.value = true;
    qrToken.value = decodedText.trim();

    setScanBadge("detecting", "QR Code Detected");
    addScanLog(`QR detected (${decodedText.trim().substring(0, 12)}...)`, "success");
    cameraMessage.value = "QR found — stopping camera...";

    await stopCamera();

    setScanBadge("validating", "Validating Pass...");
    addScanLog("Sending to server for validation...", "info");
    await validateToken(qrToken.value, { fromCamera: true });
}

async function handleValidate() {
    await validateToken(qrToken.value);
}

async function validateToken(token, options = {}) {
    if (!token.trim()) {
        errorMessage.value = "Please enter a QR token.";
        scannerMode.value = "manual";
        return;
    }

    errorMessage.value = "";
    cameraError.value = "";
    visitor.value = null;
    scannerResult.value = null;
    validating.value = true;

    setScanBadge("validating", "Validating Pass...");

    try {
        const { data } = await guardApi.validateQr({
            qr_token: token.trim(),
        });
        scannerResult.value = data.data;
        visitor.value = data.data.visitor;
        scannerMode.value = "result";

        const r = data.data.result;
        if (data.data.can_check_in) {
            setScanBadge("success", "Valid — Ready for Check-In");
            addScanLog(`Result: ${r} — can check in`, "success");
        } else if (data.data.can_check_out) {
            setScanBadge("success", "Checked In — Ready for Check-Out");
            addScanLog(`Result: ${r} — can check out`, "success");
        } else {
            setScanBadge("error", formatResult(r));
            addScanLog(`Result: ${r} — ${data.data.message}`, "warn");
        }

        if (options.fromCamera) {
            toast.success("QR code scanned successfully.");
        }
    } catch (err) {
        const message = err.response?.data?.message || "Invalid QR code.";
        errorMessage.value = message;
        scannerResult.value = err.response?.data?.data || {
            result: "invalid_qr",
            message,
            visitor: null,
            can_check_in: false,
            can_check_out: false,
        };
        scannerMode.value = "result";

        setScanBadge("error", "Scan Failed");
        addScanLog(`Validation failed: ${message}`, "error");
    } finally {
        validating.value = false;
        cameraMessage.value = "";
    }
}

async function handleCheckIn() {
    checkingIn.value = true;
    try {
        const { data } = await guardApi.checkIn(visitor.value.uuid);
        visitor.value = data.data;
        scannerResult.value = {
            ...scannerResult.value,
            result: "already_checked_in",
            message: "Visitor is already checked in and can be checked out.",
            visitor: visitor.value,
            can_check_in: false,
            can_check_out: true,
        };
        toast.success("Visitor checked in successfully.");
    } catch (err) {
        errorMessage.value =
            err.response?.data?.message || "Failed to check in visitor.";
    } finally {
        checkingIn.value = false;
        showCheckInDialog.value = false;
    }
}

async function handleCheckOut() {
    checkingOut.value = true;
    try {
        const { data } = await guardApi.checkOut(visitor.value.uuid);
        visitor.value = data.data;
        scannerResult.value = {
            ...scannerResult.value,
            result: "already_checked_out",
            message:
                "This single-entry visitor pass has already been checked out.",
            visitor: visitor.value,
            can_check_in: false,
            can_check_out: false,
        };
        toast.success("Visitor checked out successfully.");
    } catch (err) {
        errorMessage.value =
            err.response?.data?.message || "Failed to check out visitor.";
    } finally {
        checkingOut.value = false;
        showCheckOutDialog.value = false;
    }
}

async function resetScan(options = {}) {
    await stopCamera();
    qrToken.value = "";
    visitor.value = null;
    scannerResult.value = null;
    errorMessage.value = "";
    cameraError.value = "";
    cameraMessage.value = "";
    scanLocked.value = false;
    scanStatusBadge.value = "";
    scanStatusText.value = "";
    scanLog.value = [];
    lastDecodedAt = 0;
    scannerMode.value = options.mode || "manual";
}

function preferredCameraId(cameras) {
    if (!Array.isArray(cameras) || !cameras.length) return null;

    const rearCamera = cameras.find((camera) =>
        /back|rear|environment/i.test(camera.label || ""),
    );

    return (rearCamera || cameras[0]).id;
}

function cameraErrorMessage(err) {
    const rawMessage = err?.message || String(err || "");

    if (/permission|notallowed/i.test(rawMessage)) {
        return "Camera permission was denied. Allow camera access in the browser, then try again.";
    }

    if (/notfound|devicesnotfound|overconstrained/i.test(rawMessage)) {
        return "No camera was found on this device. Use manual entry instead.";
    }

    return "Unable to start the camera. Check browser permissions or use manual entry.";
}

function formatResult(result) {
    if (!result) return "Scanner Result";
    return result
        .replace(/_/g, " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function formatTime(timestamp) {
    if (!timestamp) return "";
    const d = new Date(timestamp);
    return d.toLocaleTimeString("en-US", {
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
    });
}

onBeforeUnmount(async () => {
    await stopCamera();
    if (qrScanner) {
        try {
            await qrScanner.clear();
        } catch (_) {}
        qrScanner = null;
    }
});
</script>

<style scoped>
.scanner-reader :deep(video) {
    height: 100%;
    width: 100%;
    object-fit: cover;
}

.scanner-reader :deep(#qr-shaded-region) {
    border-width: 0 !important;
}

.scanner-reader :deep(img),
.scanner-reader :deep(button),
.scanner-reader :deep(select),
.scanner-reader :deep(span) {
    display: none !important;
}
</style>
