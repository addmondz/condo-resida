<template>
    <div>
        <PageHeader
            title="Create Notification"
            subtitle="Send a notification or announcement to residents."
            :breadcrumbs="[
                { label: 'Notifications', to: { name: 'admin.notifications' } },
                { label: 'Create' },
            ]"
        />

        <div class="max-w-2xl">
            <div class="rounded-xl border border-gray-200 bg-white px-5 py-5">
                <div
                    v-if="errorMessage"
                    class="mb-4 rounded-xl border border-red-100 bg-red-50 px-5 py-4"
                >
                    <p class="text-sm text-red-700">{{ errorMessage }}</p>
                </div>
                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <AppInput
                        v-model="form.title"
                        label="Title"
                        placeholder="Notification title"
                        :error="errors.title"
                        required
                    />

                    <AppTextarea
                        v-model="form.body"
                        label="Body"
                        placeholder="Notification message"
                        :error="errors.body"
                        :rows="5"
                        required
                    />

                    <AppSelect
                        v-model="form.type"
                        label="Type"
                        :options="typeOptions"
                        placeholder="Select type"
                        :error="errors.type"
                        required
                    />

                    <AppSelect
                        v-model="form.target_type"
                        label="Target Audience"
                        :options="targetOptions"
                        placeholder="Select target"
                        :error="errors.target_type"
                        required
                    />

                    <AppSelect
                        v-if="
                            form.target_type === 'property' ||
                            form.target_type === 'block'
                        "
                        v-model="form.property_uuid"
                        label="Property"
                        :options="propertyOptions"
                        placeholder="Select property"
                        :error="errors.property_uuid"
                        required
                    />

                    <AppSelect
                        v-if="form.target_type === 'block'"
                        v-model="form.block_uuid"
                        label="Block"
                        :options="blockOptions"
                        placeholder="Select block"
                        :disabled="!form.property_uuid"
                        :error="errors.block_uuid"
                        required
                    />

                    <AppSelect
                        v-if="form.target_type === 'selected_residents'"
                        v-model="selectedResidentUuid"
                        label="Add Resident"
                        :options="residentOptions"
                        placeholder="Select resident"
                    />

                    <div
                        v-if="form.target_type === 'selected_residents'"
                        class="rounded-xl border border-gray-200 p-3"
                    >
                        <p class="text-sm font-medium text-gray-700">
                            Selected Residents
                        </p>
                        <div
                            v-if="!form.resident_uuids.length"
                            class="mt-2 text-sm text-gray-500"
                        >
                            No residents selected.
                        </div>
                        <div v-else class="mt-2 flex flex-wrap gap-2">
                            <button
                                v-for="uuid in form.resident_uuids"
                                :key="uuid"
                                type="button"
                                class="cursor-pointer rounded-full bg-gray-100 px-2.5 py-1.5 text-[13px] font-medium leading-5 text-gray-700 transition-colors hover:bg-gray-200"
                                @click="removeResident(uuid)"
                            >
                                {{ residentLabel(uuid) }} ×
                            </button>
                        </div>
                        <p
                            v-if="errors.resident_uuids"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ errors.resident_uuids }}
                        </p>
                    </div>

                    <AppSelect
                        v-model="form.status"
                        label="Status"
                        :options="statusOptions"
                        :error="errors.status"
                        required
                    />

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                        <AppButton type="submit" :loading="submitting">{{
                            form.status === "draft"
                                ? "Save Draft"
                                : "Send Notification"
                        }}</AppButton>
                        <AppButton
                            variant="secondary"
                            :to="{ name: 'admin.notifications' }"
                            >Cancel</AppButton
                        >
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from "vue";
import { useRouter } from "vue-router";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppSelect from "@/components/common/AppSelect.vue";
import AppTextarea from "@/components/common/AppTextarea.vue";
import AppButton from "@/components/common/AppButton.vue";
import { useToast } from "@/composables/useToast";

const router = useRouter();
const toast = useToast();

const typeOptions = [
    { value: "announcement", label: "Announcement" },
    { value: "visitor_cancelled", label: "Visitor Pass Cancelled" },
    { value: "visitor_expired", label: "Visitor Pass Expired" },
    { value: "booking_cancelled", label: "Facility Booking Cancelled" },
];

const targetOptions = [
    { value: "all", label: "All Residents" },
    { value: "residents", label: "Residents Only" },
    { value: "property", label: "Selected Property" },
    { value: "block", label: "Selected Block" },
    { value: "selected_residents", label: "Selected Residents" },
];

const statusOptions = [
    { value: "published", label: "Publish Now" },
    { value: "draft", label: "Save Draft" },
];

const form = reactive({
    title: "",
    body: "",
    type: "announcement",
    target_type: "all",
    property_uuid: "",
    block_uuid: "",
    resident_uuids: [],
    status: "published",
});

const errors = reactive({
    title: "",
    body: "",
    type: "",
    target_type: "",
    property_uuid: "",
    block_uuid: "",
    resident_uuids: "",
    status: "",
});

const submitting = ref(false);
const errorMessage = ref("");
const properties = ref([]);
const blocks = ref([]);
const residents = ref([]);
const selectedResidentUuid = ref("");

const propertyOptions = ref([]);
const blockOptions = ref([]);
const residentOptions = ref([]);

onMounted(async () => {
    const [{ data: propertyResponse }, { data: userResponse }] =
        await Promise.all([
            adminApi.getProperties(),
            adminApi.getUsers({
                role: "resident",
                status: "approved",
                per_page: 100,
            }),
        ]);

    properties.value = propertyResponse.data || [];
    propertyOptions.value = properties.value.map((property) => ({
        value: property.uuid,
        label: property.name,
    }));
    residents.value = userResponse.data?.data || [];
    residentOptions.value = residents.value.map((resident) => ({
        value: resident.uuid,
        label: `${resident.name} (${resident.email})`,
    }));
});

watch(
    () => form.property_uuid,
    async (propertyUuid) => {
        form.block_uuid = "";
        blockOptions.value = [];

        if (!propertyUuid) return;

        const { data } = await adminApi.getBlocks(propertyUuid);
        blocks.value = data.data || [];
        blockOptions.value = blocks.value.map((block) => ({
            value: block.uuid,
            label: block.name,
        }));
    },
);

watch(selectedResidentUuid, (uuid) => {
    if (!uuid || form.resident_uuids.includes(uuid)) {
        selectedResidentUuid.value = "";
        return;
    }

    form.resident_uuids.push(uuid);
    selectedResidentUuid.value = "";
});

watch(
    () => form.target_type,
    () => {
        form.property_uuid = "";
        form.block_uuid = "";
        form.resident_uuids = [];
    },
);

function residentLabel(uuid) {
    const resident = residents.value.find((item) => item.uuid === uuid);
    return resident ? resident.name : uuid;
}

function removeResident(uuid) {
    form.resident_uuids = form.resident_uuids.filter((item) => item !== uuid);
}

async function handleSubmit() {
    Object.keys(errors).forEach((k) => {
        errors[k] = "";
    });
    errorMessage.value = "";
    submitting.value = true;

    try {
        await adminApi.createNotification(form);
        toast.success(
            form.status === "draft"
                ? "Notification draft saved."
                : "Notification sent successfully.",
        );
        router.push({ name: "admin.notifications" });
    } catch (error) {
        const response = error.response;
        if (response?.status === 422 && response.data.errors) {
            Object.keys(response.data.errors).forEach((key) => {
                if (errors.hasOwnProperty(key)) {
                    errors[key] = response.data.errors[key][0];
                }
            });
        }
        errorMessage.value =
            response?.data?.message || "Failed to send notification.";
    } finally {
        submitting.value = false;
    }
}
</script>
