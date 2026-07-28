<template>
    <div>
        <PageHeader
            title="Edit User"
            subtitle="Update user details."
            :breadcrumbs="[
                { label: 'Users', to: { name: 'admin.users' } },
                { label: user.name || 'Edit', to: { name: 'admin.users.show', params: { uuid } } },
                { label: 'Edit' },
            ]"
        />

        <SkeletonLoader v-if="loading" variant="form" :rows="4" container-class="rounded-xl border border-gray-200 bg-white px-5 py-5" />

        <div v-else-if="loadError" class="flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-5 py-4">
            <p class="text-sm text-red-700">{{ loadError }}</p>
        </div>

        <div v-else class="rounded-xl border border-gray-200 bg-white">
            <div class="px-5 py-5 sm:px-6 sm:py-6">
                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <AppInput v-model="form.name" label="Full name" :error="errors.name" required />
                    <AppInput v-model="form.phone" label="Phone number" :error="errors.phone" />
                    <AppSelect
                        v-if="isResident"
                        v-model="form.resident_type"
                        label="Resident type"
                        :options="residentTypeOptions"
                        placeholder="Select type"
                        :error="errors.resident_type"
                    />

                    <div class="flex items-center gap-3 pt-2">
                        <AppButton type="submit" :loading="saving">Save Changes</AppButton>
                        <AppButton variant="secondary" :to="{ name: 'admin.users.show', params: { uuid } }">Cancel</AppButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppSelect from "@/components/common/AppSelect.vue";
import AppButton from "@/components/common/AppButton.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import { useToast } from "@/composables/useToast";

const props = defineProps({ uuid: { type: String, required: true } });
const router = useRouter();
const toast = useToast();

const user = ref({});
const loading = ref(true);
const saving = ref(false);
const loadError = ref("");

const residentTypeOptions = [
    { value: "owner", label: "Owner" },
    { value: "tenant", label: "Tenant" },
];

const form = reactive({ name: "", phone: "", resident_type: "" });
const errors = reactive({ name: "", phone: "", resident_type: "" });

const isResident = computed(() => (user.value.roles || []).includes("resident"));

async function loadUser() {
    try {
        const { data } = await adminApi.getUser(props.uuid);
        user.value = data.data;
        form.name = data.data.name || "";
        form.phone = data.data.phone || "";
        form.resident_type = data.data.resident_type || "";
    } catch {
        loadError.value = "Failed to load user.";
    } finally {
        loading.value = false;
    }
}

async function handleSubmit() {
    Object.keys(errors).forEach((k) => (errors[k] = ""));
    saving.value = true;
    try {
        const payload = { name: form.name, phone: form.phone || null };
        if (isResident.value) {
            payload.resident_type = form.resident_type || undefined;
        }
        await adminApi.updateUser(props.uuid, payload);
        toast.success("User updated successfully.");
        router.push({ name: "admin.users.show", params: { uuid: props.uuid } });
    } catch (err) {
        const resp = err.response;
        if (resp?.status === 422 && resp.data.errors) {
            Object.keys(resp.data.errors).forEach((k) => {
                if (k in errors) errors[k] = resp.data.errors[k][0];
            });
        } else {
            toast.error(resp?.data?.message || "Failed to update user.");
        }
    } finally {
        saving.value = false;
    }
}

onMounted(loadUser);
</script>
