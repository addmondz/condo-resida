<template>
    <div class="pb-16 lg:pb-0">
        <PageHeader
            title="Visitor Details"
            subtitle="Verify and update visitor entry status."
            :breadcrumbs="[
                { label: 'Visitors', to: { name: 'guard.visitors' } },
                { label: visitor.visitor_name || 'Details' },
            ]"
        />

        <SkeletonLoader
            v-if="loading"
            variant="form"
            :rows="5"
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
            <!-- Status Banner -->
            <div
                :class="[
                    'mb-4 rounded-xl px-5 py-4 text-sm font-medium text-white',
                    visitor.status === 'Active'
                        ? 'bg-green-600'
                        : visitor.status === 'Checked In'
                          ? 'bg-blue-600'
                          : visitor.status === 'Checked Out'
                            ? 'bg-gray-600'
                            : visitor.status === 'Cancelled'
                              ? 'bg-red-600'
                              : 'bg-gray-500',
                ]"
            >
                Status: {{ visitor.status }}
            </div>

            <!-- Visitor Info -->
            <div class="mb-4 rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-base font-semibold text-gray-900">
                        Visitor Information
                    </h2>
                </div>
                <div class="px-5 py-5">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm text-gray-500">Visitor Name</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.visitor_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Contact</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.contact_number || "-" }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Purpose</dt>
                            <dd
                                class="mt-1 text-sm font-medium text-gray-900 capitalize"
                            >
                                {{ visitor.purpose }}
                            </dd>
                        </div>
                        <div v-if="visitor.vehicle_number">
                            <dt class="text-sm text-gray-500">Vehicle</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.vehicle_number }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Visit Date</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.visit_date }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Reference</dt>
                            <dd class="mt-1 text-sm font-mono text-gray-900">
                                {{ visitor.reference_number }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Property</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.property_name || "-" }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Unit</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">
                                {{ visitor.block_name || ""
                                }}{{
                                    visitor.block_name && visitor.unit_name
                                        ? ", "
                                        : ""
                                }}{{ visitor.unit_name || "-" }}
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
                    </dl>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <AppButton
                    v-if="visitor.status === 'Active'"
                    @click="handleCheckIn"
                    :loading="checkingIn"
                    class="w-full"
                    size="lg"
                >
                    Check In Visitor
                </AppButton>
                <AppButton
                    v-if="visitor.status === 'Checked In'"
                    @click="handleCheckOut"
                    :loading="checkingOut"
                    variant="secondary"
                    class="w-full"
                    size="lg"
                >
                    Check Out Visitor
                </AppButton>
            </div>

            <!-- Activity Logs -->
            <div
                v-if="visitor.activity_logs?.length"
                class="mt-6 rounded-xl border border-gray-200 bg-white"
            >
                <div class="border-b border-gray-100 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Activity Log
                    </h3>
                </div>
                <div class="space-y-2 px-5 py-5">
                    <div
                        v-for="log in visitor.activity_logs"
                        :key="log.uuid"
                        class="flex items-center justify-between border-b border-gray-100 pb-2 text-sm last:border-b-0"
                    >
                        <span class="text-gray-700 capitalize">{{
                            log.action
                        }}</span>
                        <span class="text-gray-400">{{
                            formatDateTime(log.created_at)
                        }}</span>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import guardApi from "@/api/guard";
import AppButton from "@/components/common/AppButton.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import { useToast } from "@/composables/useToast";

const props = defineProps({
    uuid: { type: String, required: true },
});

const visitor = ref({});
const loading = ref(true);
const error = ref("");
const checkingIn = ref(false);
const checkingOut = ref(false);
const toast = useToast();

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

async function handleCheckIn() {
    checkingIn.value = true;
    try {
        const { data } = await guardApi.checkIn(props.uuid);
        visitor.value = data.data;
        toast.success("Visitor checked in successfully.");
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to check in.");
    } finally {
        checkingIn.value = false;
    }
}

async function handleCheckOut() {
    checkingOut.value = true;
    try {
        const { data } = await guardApi.checkOut(props.uuid);
        visitor.value = data.data;
        toast.success("Visitor checked out successfully.");
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to check out.");
    } finally {
        checkingOut.value = false;
    }
}

onMounted(async () => {
    try {
        const { data } = await guardApi.getVisitor(props.uuid);
        visitor.value = data.data;
    } catch {
        error.value = "Failed to load visitor details.";
    } finally {
        loading.value = false;
    }
});
</script>
