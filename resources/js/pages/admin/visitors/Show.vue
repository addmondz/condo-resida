<template>
    <div>
        <SkeletonLoader
            v-if="loading"
            variant="form"
            :rows="6"
            container-class="rounded-xl border border-gray-200 bg-white px-5 py-5"
        />

        <div
            v-else-if="error"
            class="flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-5 py-4"
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
                title="Visitor Pass"
                :subtitle="visitor.visitor_name || 'Visitor details'"
                :breadcrumbs="[
                    { label: 'Visitors', to: { name: 'admin.visitors' } },
                    { label: visitor.visitor_name || 'Details' },
                ]"
            >
                <template #actions>
                    <AppButton
                        v-if="canCancel"
                        variant="danger"
                        size="sm"
                        @click="showCancelDialog = true"
                    >
                        Cancel Pass
                    </AppButton>
                </template>
            </PageHeader>

            <div class="grid gap-4 lg:grid-cols-[360px_minmax(0,1fr)]">
                <aside class="order-1 lg:sticky lg:top-6 lg:self-start">
                    <div
                        v-if="qrToken"
                        ref="qrCardRef"
                        class="overflow-hidden rounded-xl border border-gray-200 bg-white"
                    >
                        <div class="bg-gray-900 px-5 py-5 text-white">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase text-gray-400">
                                        Entry QR
                                    </p>
                                    <p class="mt-1 text-base font-semibold">
                                        {{ visitor.visitor_name || "Visitor" }}
                                    </p>
                                </div>
                                <StatusBadge :status="visitor.status" />
                            </div>
                            <p class="mt-3 text-sm text-gray-300">
                                Present this pass at the guard house.
                            </p>
                        </div>

                        <div class="px-5 py-6">
                            <div class="flex justify-center">
                                <div class="rounded-xl border border-gray-200 bg-white p-3">
                                    <canvas
                                        ref="qrCanvasRef"
                                        width="220"
                                        height="220"
                                        aria-label="Visitor QR code"
                                    ></canvas>
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-2">
                                <AppButton @click="handleShare" class="w-full" size="sm">
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
                                            d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"
                                        />
                                    </svg>
                                    Share
                                </AppButton>
                                <AppButton
                                    variant="secondary"
                                    @click="handleDownload"
                                    class="w-full"
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
                                            d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 12 12 16.5m0 0 4.5-4.5M12 16.5V3"
                                        />
                                    </svg>
                                    Save
                                </AppButton>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else-if="qrError"
                        class="flex items-start gap-3 rounded-xl border border-yellow-100 bg-yellow-50 px-5 py-4"
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

                    <div
                        v-else
                        class="rounded-xl border border-gray-200 bg-white px-5 py-5"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-500"
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
                                    d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5Zm0 9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Zm9.75-9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"
                                />
                            </svg>
                        </div>
                        <h2 class="mt-3 text-base font-semibold text-gray-900">
                            QR unavailable
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            This visitor pass does not have a QR token.
                        </p>
                    </div>
                </aside>

                <section class="order-2 space-y-4">
                    <div class="rounded-xl border border-gray-200 bg-white">
                        <div
                            class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary-50 text-lg font-semibold text-primary-600"
                                >
                                    {{ visitorInitial }}
                                </div>
                                <div class="min-w-0">
                                    <h2 class="truncate text-lg font-semibold text-gray-900">
                                        {{ visitor.visitor_name || "Visitor" }}
                                    </h2>
                                    <p class="mt-0.5 text-sm capitalize text-gray-500">
                                        {{ formatPurpose(visitor.purpose) }}
                                    </p>
                                </div>
                            </div>
                            <StatusBadge :status="visitor.status" />
                        </div>

                        <div class="grid gap-px bg-gray-100 sm:grid-cols-3">
                            <div class="bg-white px-5 py-4">
                                <p class="text-xs font-medium text-gray-500">
                                    Visit Date
                                </p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">
                                    {{ formatDateLong(visitor.visit_date) }}
                                </p>
                            </div>
                            <div class="bg-white px-5 py-4">
                                <p class="text-xs font-medium text-gray-500">
                                    Unit
                                </p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">
                                    {{ unitLabel }}
                                </p>
                            </div>
                            <div class="bg-white px-5 py-4">
                                <p class="text-xs font-medium text-gray-500">
                                    Validity
                                </p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">
                                    Single entry
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-gray-200 bg-white p-5">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-600"
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
                                            d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102A1.125 1.125 0 0 0 5.872 2.25H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"
                                        />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-gray-500">
                                        Contact Number
                                    </p>
                                    <p class="mt-1 truncate text-sm font-semibold text-gray-900">
                                        {{ visitor.contact_number || "-" }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-white p-5">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-600"
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
                                            d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-17.25 0V6.75A2.25 2.25 0 0 1 6.75 4.5h8.25a2.25 2.25 0 0 1 2.25 2.25v7.5m-12.75 0h12.75"
                                        />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-gray-500">
                                        Vehicle Number
                                    </p>
                                    <p class="mt-1 truncate text-sm font-semibold uppercase text-gray-900">
                                        {{ visitor.vehicle_number || "-" }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white">
                        <div class="border-b border-gray-100 px-5 py-4">
                            <h2 class="text-base font-semibold text-gray-900">
                                Pass Details
                            </h2>
                        </div>
                        <dl
                            class="grid grid-cols-1 gap-x-6 gap-y-5 px-5 py-5 sm:grid-cols-2"
                        >
                            <div>
                                <dt class="text-sm text-gray-500">Resident</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-900">
                                    {{ visitor.resident?.name || "-" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Property</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-900">
                                    {{ visitor.property_name || "-" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">
                                    Reference Number
                                </dt>
                                <dd
                                    class="mt-1 break-all font-mono text-sm font-medium text-gray-900"
                                >
                                    {{ visitor.reference_number || visitor.uuid }}
                                </dd>
                            </div>
                            <div v-if="visitor.checked_in_at">
                                <dt class="text-sm text-gray-500">Checked In</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-900">
                                    {{ formatDateTime(visitor.checked_in_at)
                                    }}{{
                                        visitor.checked_in_by
                                            ? ` by ${visitor.checked_in_by}`
                                            : ""
                                    }}
                                </dd>
                            </div>
                            <div v-if="visitor.checked_out_at">
                                <dt class="text-sm text-gray-500">
                                    Checked Out
                                </dt>
                                <dd class="mt-1 text-sm font-medium text-gray-900">
                                    {{ formatDateTime(visitor.checked_out_at)
                                    }}{{
                                        visitor.checked_out_by
                                            ? ` by ${visitor.checked_out_by}`
                                            : ""
                                    }}
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
                </section>
            </div>

            <AppModal
                :show="showCancelDialog"
                title="Cancel Visitor Pass"
                @close="showCancelDialog = false"
            >
                <p class="mb-4 text-sm text-gray-600">
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
import adminApi from "@/api/admin";
import * as QRCode from "qrcode";
import AppButton from "@/components/common/AppButton.vue";
import AppModal from "@/components/common/AppModal.vue";
import AppTextarea from "@/components/common/AppTextarea.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import { useToast } from "@/composables/useToast";

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

const canCancel = computed(() =>
    ["Active", "Checked In"].includes(visitor.value.status),
);

const visitorInitial = computed(
    () => visitor.value.visitor_name?.charAt(0)?.toUpperCase() || "?",
);

const unitLabel = computed(() => {
    const block = visitor.value.block_name;
    const unit = visitor.value.unit_name;

    if (block && unit) return `${block}, ${unit}`;
    return block || unit || "-";
});

function formatPurpose(purpose) {
    if (!purpose) return "-";
    return purpose.replace(/_/g, " ");
}

function formatDateLong(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("en-MY", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
}

function formatDateTime(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("en-MY", {
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
    if (!qrToken.value || !qrCanvasRef.value) return;

    await QRCode.toCanvas(qrCanvasRef.value, qrToken.value, {
        width: 220,
        margin: 1,
        errorCorrectionLevel: "M",
    });
}

async function handleCancel() {
    cancelling.value = true;
    try {
        const { data } = await adminApi.cancelVisitor(props.uuid, {
            reason: cancelReason.value || undefined,
        });
        visitor.value = data.data;
        showCancelDialog.value = false;
        cancelReason.value = "";
        sessionStorage.removeItem(`admin_qr_${props.uuid}`);
        qrToken.value = "";
        toast.success("Visitor pass cancelled.");
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to cancel.");
    } finally {
        cancelling.value = false;
    }
}

onMounted(async () => {
    const tokenFromQuery = route.query.qr_token;
    if (tokenFromQuery) {
        sessionStorage.setItem(`admin_qr_${props.uuid}`, tokenFromQuery);
        qrToken.value = tokenFromQuery;
    } else {
        qrToken.value = sessionStorage.getItem(`admin_qr_${props.uuid}`) || "";
    }

    try {
        const { data } = await adminApi.getVisitor(props.uuid);
        visitor.value = data.data;

        if (!qrToken.value) {
            try {
                const qrResponse = await adminApi.getVisitorQr(props.uuid);
                qrToken.value = qrResponse.data.data.qr_token;
                sessionStorage.setItem(`admin_qr_${props.uuid}`, qrToken.value);
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
