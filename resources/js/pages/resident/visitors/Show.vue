<template>
    <div class="pb-16 lg:pb-0">
        <!-- Back Button -->
        <div class="mb-4">
            <AppButton
                variant="ghost"
                :to="{ name: 'resident.visitors' }"
                size="sm"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"
                    />
                </svg>
                Back to Visitors
            </AppButton>
        </div>

        <!-- Loading -->
        <SkeletonLoader v-if="loading" variant="card" />

        <!-- Error -->
        <div
            v-else-if="error"
            class="flex items-start gap-3 rounded-xl bg-red-50 border border-red-100 px-5 py-4"
        >
            <svg
                class="mt-0.5 h-5 w-5 shrink-0 text-red-500"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"
                />
            </svg>
            <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <template v-else>
            <PageHeader
                :title="visitor.visitor_name || 'Visitor'"
                :breadcrumbs="[
                    { label: 'Visitors', to: { name: 'resident.visitors' } },
                    { label: visitor.visitor_name || 'Details' },
                ]"
            />

            <!-- QR Code Card -->
            <div
                v-if="qrToken && visitor.status === 'Active'"
                ref="qrCardRef"
                class="bg-gray-900 rounded-2xl p-6 text-white mb-4"
            >
                <div class="text-center mb-4">
                    <p
                        class="text-xs font-semibold uppercase tracking-widest text-gray-400"
                    >
                        Visitor Pass
                    </p>
                    <p class="text-sm text-gray-400 mt-1">
                        {{ visitor.property_name || "Property" }}
                    </p>
                    <p class="text-xl font-bold mt-0.5">
                        {{ visitor.block_name || ""
                        }}{{
                            visitor.block_name && visitor.unit_name ? ", " : ""
                        }}{{ visitor.unit_name || "" }}
                    </p>
                </div>

                <div class="flex justify-center mb-4">
                    <div class="rounded-xl bg-white p-4">
                        <canvas
                            ref="qrCanvasRef"
                            width="220"
                            height="220"
                            aria-label="Visitor QR code"
                        ></canvas>
                    </div>
                </div>

                <p class="text-center text-xs text-gray-400">
                    Scan this QR code at the guard house
                </p>
            </div>

            <!-- QR Error -->
            <div
                v-else-if="qrError"
                class="mb-4 flex items-start gap-3 rounded-xl bg-yellow-50 border border-yellow-100 px-5 py-4"
            >
                <svg
                    class="mt-0.5 h-5 w-5 shrink-0 text-yellow-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
                    />
                </svg>
                <p class="text-sm text-yellow-800">{{ qrError }}</p>
            </div>

            <!-- Share / Download Buttons -->
            <div
                v-if="qrToken && visitor.status === 'Active'"
                class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2"
            >
                <AppButton @click="handleShare" class="w-full" size="lg">
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
                            d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"
                        />
                    </svg>
                    Share QR Code
                </AppButton>
                <AppButton
                    variant="secondary"
                    @click="handleDownload"
                    class="w-full"
                    size="lg"
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
                            d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 12 12 16.5m0 0 4.5-4.5M12 16.5V3"
                        />
                    </svg>
                    Download QR Code
                </AppButton>
            </div>

            <!-- Visitor Information Card -->
            <div class="rounded-xl bg-white border border-gray-200 mb-4">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">
                        Visitor Information
                    </h2>
                </div>

                <div class="px-5 py-5">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        <div>
                            <dt class="text-sm text-gray-500">
                                Purpose of Visit
                            </dt>
                            <dd
                                class="mt-1 text-sm font-medium text-gray-900 capitalize"
                            >
                                {{ formatPurpose(visitor.purpose) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Visitor Name</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.visitor_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">
                                Contact Number
                            </dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.contact_number || "-" }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">
                                Vehicle Number
                            </dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.vehicle_number || "-" }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Visit Date</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatDateLong(visitor.visit_date) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Validity</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                Single entry only
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <StatusBadge :status="visitor.status" />
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">
                                Reference Number
                            </dt>
                            <dd
                                class="mt-1 text-sm font-medium text-gray-900 font-mono"
                            >
                                {{ visitor.reference_number || visitor.uuid }}
                            </dd>
                        </div>
                        <div v-if="visitor.checked_in_at">
                            <dt class="text-sm text-gray-500">Checked In</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatDateTime(visitor.checked_in_at) }}
                            </dd>
                        </div>
                        <div v-if="visitor.checked_out_at">
                            <dt class="text-sm text-gray-500">Checked Out</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatDateTime(visitor.checked_out_at) }}
                            </dd>
                        </div>
                        <div
                            v-if="visitor.cancellation_reason"
                            class="sm:col-span-2"
                        >
                            <dt class="text-sm text-gray-500">
                                Cancellation Reason
                            </dt>
                            <dd class="mt-1 text-sm font-medium text-red-700">
                                {{ visitor.cancellation_reason }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Cancel Button -->
            <div v-if="canCancel" class="mt-4">
                <AppButton
                    variant="danger"
                    @click="showCancelDialog = true"
                    class="w-full sm:w-auto"
                >
                    Cancel Visitor Pass
                </AppButton>
            </div>

            <!-- Cancel Dialog -->
            <AppModal
                :show="showCancelDialog"
                title="Cancel Visitor Pass"
                @close="showCancelDialog = false"
            >
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to cancel this visitor pass? This
                    action cannot be undone.
                </p>
                <AppTextarea
                    v-model="cancelReason"
                    label="Reason (optional)"
                    placeholder="Enter a reason for cancellation"
                    :rows="3"
                />
                <template #footer>
                    <AppButton
                        variant="secondary"
                        @click="showCancelDialog = false"
                        >Keep Pass</AppButton
                    >
                    <AppButton
                        variant="danger"
                        :loading="cancelling"
                        @click="handleCancel"
                        >Cancel Pass</AppButton
                    >
                </template>
            </AppModal>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, watch } from "vue";
import { useRoute } from "vue-router";
import residentApi from "@/api/resident";
import * as QRCode from "qrcode";
import { useToast } from "@/composables/useToast";
import PageHeader from "@/components/common/PageHeader.vue";
import AppButton from "@/components/common/AppButton.vue";
import AppModal from "@/components/common/AppModal.vue";
import AppTextarea from "@/components/common/AppTextarea.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";

const props = defineProps({
    uuid: { type: String, required: true },
});

const route = useRoute();
const toast = useToast();

const visitor = ref({});
const loading = ref(true);
const error = ref("");
const qrError = ref("");
const showCancelDialog = ref(false);
const cancelReason = ref("");
const cancelling = ref(false);
const qrToken = ref("");
const qrCardRef = ref(null);
const qrCanvasRef = ref(null);

const canCancel = computed(
    () => visitor.value.status === "Active" && !visitor.value.checked_in_at,
);

function formatPurpose(purpose) {
    if (!purpose) return "-";
    return purpose.replace(/_/g, " ");
}

function formatDateLong(dateStr) {
    if (!dateStr) return "-";
    const date = new Date(dateStr);
    return date.toLocaleDateString("en-MY", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
}

function formatDateTime(dateStr) {
    if (!dateStr) return "-";
    const date = new Date(dateStr);
    return date.toLocaleDateString("en-MY", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

async function handleShare() {
    const file = await getQrFile();
    const shareData = {
        title: "Visitor Pass",
        text: `Visitor Pass for ${visitor.value.visitor_name}\nProperty: ${visitor.value.property_name || ""}\nBlock: ${visitor.value.block_name || ""}, Unit: ${visitor.value.unit_name || ""}\nDate: ${formatDateLong(visitor.value.visit_date)}`,
        url: `${window.location.origin}${window.location.pathname}?qr_token=${encodeURIComponent(qrToken.value)}`,
    };

    if (file && navigator.canShare?.({ files: [file] })) {
        try {
            await navigator.share({ ...shareData, files: [file] });
        } catch {
            // user cancelled share
        }
    } else if (navigator.share) {
        try {
            await navigator.share(shareData);
        } catch {
            // user cancelled share
        }
    } else {
        try {
            await navigator.clipboard.writeText(window.location.href);
            toast.success("Link copied to clipboard.");
        } catch {
            toast.error(
                "Unable to share. Please copy the URL from the address bar.",
            );
        }
    }
}

async function handleDownload() {
    const file = await getQrFile();
    if (!file) {
        toast.error("Unable to download this QR code.");
        return;
    }

    const url = URL.createObjectURL(file);
    const link = document.createElement("a");
    link.href = url;
    link.download = `visitor-${visitor.value.reference_number || visitor.value.uuid}.png`;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}

function getQrFile() {
    return new Promise((resolve) => {
        const canvas =
            qrCanvasRef.value || qrCardRef.value?.querySelector("canvas");
        if (!canvas) {
            resolve(null);
            return;
        }

        canvas.toBlob((blob) => {
            if (!blob) {
                resolve(null);
                return;
            }

            resolve(
                new File(
                    [blob],
                    `visitor-${visitor.value.reference_number || visitor.value.uuid}.png`,
                    { type: "image/png" },
                ),
            );
        }, "image/png");
    });
}

async function renderQrCode() {
    if (!qrToken.value || !qrCanvasRef.value) {
        return;
    }

    await QRCode.toCanvas(qrCanvasRef.value, qrToken.value, {
        width: 220,
        margin: 1,
        errorCorrectionLevel: "M",
    });
}

async function handleCancel() {
    cancelling.value = true;
    try {
        const { data } = await residentApi.cancelVisitor(props.uuid, {
            reason: cancelReason.value || undefined,
        });
        visitor.value = data.data;
        showCancelDialog.value = false;
        cancelReason.value = "";
        sessionStorage.removeItem(`qr_${props.uuid}`);
        qrToken.value = "";
        toast.success("Visitor pass has been cancelled.");
    } catch (err) {
        toast.error(
            err.response?.data?.message || "Failed to cancel visitor pass.",
        );
    } finally {
        cancelling.value = false;
    }
}

onMounted(async () => {
    const tokenFromQuery = route.query.qr_token;
    if (tokenFromQuery) {
        sessionStorage.setItem(`qr_${props.uuid}`, tokenFromQuery);
        qrToken.value = tokenFromQuery;
    } else {
        qrToken.value = sessionStorage.getItem(`qr_${props.uuid}`) || "";
    }

    try {
        const { data } = await residentApi.getVisitor(props.uuid);
        visitor.value = data.data;

        if (!qrToken.value && visitor.value.status === "Active") {
            try {
                const qrResponse = await residentApi.getVisitorQr(props.uuid);
                qrToken.value = qrResponse.data.data.qr_token;
                sessionStorage.setItem(`qr_${props.uuid}`, qrToken.value);
            } catch (err) {
                qrError.value =
                    err.response?.data?.message ||
                    "QR code is unavailable for this visitor pass.";
            }
        }
    } catch {
        error.value = "Failed to load visitor details.";
    } finally {
        loading.value = false;
        await nextTick();
        renderQrCode();
    }
});

watch(qrToken, async () => {
    await nextTick();
    renderQrCode();
});
</script>
