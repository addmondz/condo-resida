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
                            <p class="mt-0.5 text-sm text-gray-500">
                                {{ panelSubtitle }}
                            </p>
                        </div>
                        <span
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

                <div v-else-if="scannerResult" class="px-5 py-5">
                    <div
                        class="overflow-hidden rounded-xl border"
                        :class="resultPanelClass"
                    >
                    <div
                        :class="[
                            'px-5 py-4 text-sm font-semibold text-white',
                            scannerResult.can_check_in
                                ? 'bg-emerald-600'
                                : scannerResult.can_check_out
                                  ? 'bg-blue-600'
                                  : scannerResult.visitor
                                    ? 'bg-gray-700'
                                    : 'bg-red-600',
                        ]"
                    >
                        {{ formatResult(scannerResult.result) }}
                    </div>

                    <div class="px-5 py-5">
                        <p
                            class="text-sm leading-6"
                            :class="
                                scannerResult.visitor
                                    ? 'text-gray-700'
                                    : 'text-red-700'
                            "
                        >
                            {{ scannerResult.message }}
                        </p>

                        <dl
                            v-if="visitor"
                            class="mt-5 divide-y divide-gray-100"
                        >
                            <div
                                v-for="item in visitorDetails"
                                :key="item.label"
                                class="grid grid-cols-[110px_minmax(0,1fr)] gap-3 py-3"
                            >
                                <dt class="text-sm text-gray-500">
                                    {{ item.label }}
                                </dt>
                                <dd
                                    class="min-w-0 text-right text-sm font-medium text-gray-900"
                                    :class="item.mono ? 'font-mono' : ''"
                                >
                                    {{ item.value || "-" }}
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-6 space-y-3">
                            <AppButton
                                v-if="scannerResult.can_check_in"
                                @click="handleCheckIn"
                                :loading="checkingIn"
                                class="w-full"
                            >
                                Check In Visitor
                            </AppButton>
                            <AppButton
                                v-if="scannerResult.can_check_out"
                                @click="handleCheckOut"
                                :loading="checkingOut"
                                variant="secondary"
                                class="w-full"
                            >
                                Check Out Visitor
                            </AppButton>
                        </div>
                    </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <AppButton
                            type="button"
                            size="xl"
                            class="w-full"
                            :loading="startingCamera"
                            @click="startCamera"
                        >
                            Scan Another QR
                        </AppButton>
                        <AppButton
                            type="button"
                            size="xl"
                            variant="secondary"
                            class="w-full"
                            @click="resetScan"
                        >
                            Enter Manually
                        </AppButton>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref } from "vue";
import { Html5Qrcode, Html5QrcodeSupportedFormats } from "html5-qrcode";
import guardApi from "@/api/guard";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppButton from "@/components/common/AppButton.vue";
import { useToast } from "@/composables/useToast";

const scannerElementId = "guard-qr-reader";
const qrToken = ref("");
const visitor = ref(null);
const scannerResult = ref(null);
const scannerMode = ref("manual");
const validating = ref(false);
const checkingIn = ref(false);
const checkingOut = ref(false);
const startingCamera = ref(false);
const isCameraActive = ref(false);
const scanLocked = ref(false);
const errorMessage = ref("");
const cameraError = ref("");
const cameraMessage = ref("");
const toast = useToast();

let qrScanner = null;

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
    if (scannerMode.value === "result") return resultTitle.value;
    return "Manual Entry";
});

const panelSubtitle = computed(() => {
    if (scannerMode.value === "camera") {
        return "Center the visitor QR code in the camera frame.";
    }

    if (scannerMode.value === "result") {
        return scannerResult.value?.message || "Review the scan result.";
    }

    return "Enter the QR token, or switch to camera scanning.";
});

const resultTitle = computed(() => {
    if (!scannerResult.value) return "Scanner Result";
    if (scannerResult.value.can_check_in) return "Valid visitor pass";
    if (scannerResult.value.can_check_out) return "Visitor already checked in";
    if (scannerResult.value.visitor) return "Pass cannot be used";
    return "QR code not found";
});

const resultPanelClass = computed(() => {
    if (!scannerResult.value?.visitor) return "border-red-200 bg-red-50";
    if (scannerResult.value.can_check_in) return "border-emerald-200 bg-white";
    if (scannerResult.value.can_check_out) return "border-blue-200 bg-white";
    return "border-gray-200 bg-white";
});

const visitorDetails = computed(() => {
    if (!visitor.value) return [];

    return [
        { label: "Visitor", value: visitor.value.visitor_name },
        { label: "Contact", value: visitor.value.contact_number },
        { label: "Vehicle", value: visitor.value.vehicle_number },
        { label: "Purpose", value: formatResult(visitor.value.purpose) },
        { label: "Visit Date", value: visitor.value.visit_date },
        { label: "Property", value: visitor.value.property_name },
        { label: "Unit", value: unitLabel.value },
        {
            label: "Reference",
            value: visitor.value.reference_number,
            mono: true,
        },
    ].filter((item) => item.value);
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

    try {
        await nextTick();

        if (!qrScanner) {
            qrScanner = new Html5Qrcode(scannerElementId, {
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                verbose: false,
            });
        }

        const cameras = await Html5Qrcode.getCameras();
        const cameraId = preferredCameraId(cameras);
        const cameraConfig = cameraId
            ? { deviceId: { exact: cameraId } }
            : { facingMode: "environment" };

        await qrScanner.start(
            cameraConfig,
            {
                fps: 10,
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
        cameraMessage.value = "Hold the QR code inside the frame.";
    } catch (err) {
        cameraError.value = cameraErrorMessage(err);
        cameraMessage.value = "";
        isCameraActive.value = false;
        scannerMode.value = "manual";
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
        await qrScanner.stop();
    } catch (err) {
        // The scanner can already be stopped by the browser when permissions change.
    } finally {
        isCameraActive.value = false;
        cameraMessage.value = "";
    }
}

async function handleScanSuccess(decodedText) {
    if (scanLocked.value || !decodedText) return;

    scanLocked.value = true;
    qrToken.value = decodedText.trim();
    cameraMessage.value = "QR found. Validating pass...";
    await stopCamera();
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

    try {
        const { data } = await guardApi.validateQr({
            qr_token: token.trim(),
        });
        scannerResult.value = data.data;
        visitor.value = data.data.visitor;
        scannerMode.value = "result";
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

onBeforeUnmount(async () => {
    await stopCamera();
    if (qrScanner) {
        try {
            await qrScanner.clear();
        } catch (err) {
            // Clear can fail if the scanner was never fully mounted.
        }
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
