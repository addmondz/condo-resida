<template>
    <div class="pb-16 lg:pb-0">
        <PageHeader
            title="QR Scanner"
            subtitle="Scan a visitor's QR code to verify their pass."
            :breadcrumbs="[{ label: 'Scanner' }]"
        />

        <div class="max-w-lg mx-auto">
            <!-- Manual Entry -->
            <div class="rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Enter QR Token
                    </h3>
                    <p class="mt-0.5 text-sm text-gray-500">
                        Paste a QR token when camera scanning is unavailable.
                    </p>
                </div>
                <form
                    @submit.prevent="handleValidate"
                    class="space-y-5 px-5 py-5"
                >
                    <AppInput
                        v-model="qrToken"
                        placeholder="Paste or type QR token here"
                        :error="errorMessage"
                    />
                    <AppButton
                        type="submit"
                        :loading="validating"
                        class="w-full"
                    >
                        Validate QR Code
                    </AppButton>
                </form>
            </div>

            <!-- Result Card -->
            <div
                v-if="scannerResult"
                class="mt-6 overflow-hidden rounded-xl border"
                :class="
                    scannerResult.visitor
                        ? 'bg-white border-gray-200'
                        : 'bg-red-50 border-red-200'
                "
            >
                <div
                    :class="[
                        'px-4 py-3 text-sm font-medium text-white',
                        scannerResult.can_check_in
                            ? 'bg-green-600'
                            : scannerResult.can_check_out
                              ? 'bg-blue-600'
                              : scannerResult.visitor
                                ? 'bg-gray-600'
                                : 'bg-red-600',
                    ]"
                >
                    {{ formatResult(scannerResult.result) }}
                </div>

                <div class="px-5 py-5">
                    <p
                        class="mb-4 text-sm"
                        :class="
                            scannerResult.visitor
                                ? 'text-gray-700'
                                : 'text-red-700'
                        "
                    >
                        {{ scannerResult.message }}
                    </p>

                    <dl class="space-y-3">
                        <template v-if="visitor">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Visitor</dt>
                                <dd class="text-sm font-medium text-gray-900">
                                    {{ visitor.visitor_name }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Contact</dt>
                                <dd class="text-sm font-medium text-gray-900">
                                    {{ visitor.contact_number || "-" }}
                                </dd>
                            </div>
                            <div
                                v-if="visitor.vehicle_number"
                                class="flex justify-between"
                            >
                                <dt class="text-sm text-gray-500">Vehicle</dt>
                                <dd class="text-sm font-medium text-gray-900">
                                    {{ visitor.vehicle_number }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Purpose</dt>
                                <dd
                                    class="text-sm font-medium text-gray-900 capitalize"
                                >
                                    {{ visitor.purpose }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">
                                    Visit Date
                                </dt>
                                <dd class="text-sm font-medium text-gray-900">
                                    {{ visitor.visit_date }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Property</dt>
                                <dd class="text-sm font-medium text-gray-900">
                                    {{ visitor.property_name || "-" }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Unit</dt>
                                <dd class="text-sm font-medium text-gray-900">
                                    {{ visitor.block_name || ""
                                    }}{{
                                        visitor.block_name && visitor.unit_name
                                            ? ", "
                                            : ""
                                    }}{{ visitor.unit_name || "-" }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Reference</dt>
                                <dd class="text-sm font-mono text-gray-900">
                                    {{ visitor.reference_number }}
                                </dd>
                            </div>
                        </template>
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

            <!-- Success/Error Messages -->
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import guardApi from "@/api/guard";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppButton from "@/components/common/AppButton.vue";
import { useToast } from "@/composables/useToast";

const qrToken = ref("");
const visitor = ref(null);
const scannerResult = ref(null);
const validating = ref(false);
const checkingIn = ref(false);
const checkingOut = ref(false);
const errorMessage = ref("");
const toast = useToast();

async function handleValidate() {
    if (!qrToken.value.trim()) {
        errorMessage.value = "Please enter a QR token.";
        return;
    }

    errorMessage.value = "";
    visitor.value = null;
    scannerResult.value = null;
    validating.value = true;

    try {
        const { data } = await guardApi.validateQr({
            qr_token: qrToken.value.trim(),
        });
        scannerResult.value = data.data;
        visitor.value = data.data.visitor;
    } catch (err) {
        errorMessage.value = err.response?.data?.message || "Invalid QR code.";
    } finally {
        validating.value = false;
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

function formatResult(result) {
    if (!result) return "Scanner Result";
    return result
        .replace(/_/g, " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}
</script>
