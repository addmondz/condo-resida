<template>
    <div>
        <PageHeader
            title="Edit Property"
            subtitle="Update property details."
            :breadcrumbs="[
                { label: 'Properties', to: { name: 'admin.properties' } },
                { label: property.name || 'Edit' },
            ]"
        />

        <SkeletonLoader v-if="loading" variant="form" :rows="5" container-class="rounded-xl border border-gray-200 bg-white px-5 py-5" />

        <div v-else-if="error" class="flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-5 py-4">
            <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <div v-else class="rounded-xl border border-gray-200 bg-white">
            <div class="px-5 py-5 sm:px-6 sm:py-6">
                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <AppInput v-model="form.name" label="Property name" :error="errors.name" required />
                    <AppInput v-model="form.address" label="Address" :error="errors.address" required />
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <AppInput v-model="form.contact_email" label="Contact email" type="email" :error="errors.contact_email" />
                        <AppInput v-model="form.contact_phone" label="Contact phone" :error="errors.contact_phone" />
                    </div>
                    <AppInput v-model="form.timezone" label="Timezone" :error="errors.timezone" />

                    <div class="flex items-center gap-3 pt-2">
                        <AppButton type="submit" :loading="saving">Save Changes</AppButton>
                        <AppButton variant="secondary" :to="{ name: 'admin.properties.show', params: { uuid } }">Cancel</AppButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppButton from "@/components/common/AppButton.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import { useToast } from "@/composables/useToast";

const props = defineProps({ uuid: { type: String, required: true } });
const router = useRouter();
const toast = useToast();

const property = ref({});
const loading = ref(true);
const saving = ref(false);
const error = ref("");

const form = reactive({ name: "", address: "", contact_email: "", contact_phone: "", timezone: "" });
const errors = reactive({ name: "", address: "", contact_email: "", contact_phone: "", timezone: "" });

async function loadProperty() {
    try {
        const { data } = await adminApi.getProperty(props.uuid);
        property.value = data.data;
        Object.keys(form).forEach((k) => { form[k] = data.data[k] || ""; });
    } catch {
        error.value = "Failed to load property.";
    } finally {
        loading.value = false;
    }
}

async function handleSubmit() {
    Object.keys(errors).forEach((k) => (errors[k] = ""));
    saving.value = true;
    try {
        await adminApi.updateProperty(props.uuid, form);
        toast.success("Property updated successfully.");
        router.push({ name: "admin.properties.show", params: { uuid: props.uuid } });
    } catch (err) {
        const resp = err.response;
        if (resp?.status === 422 && resp.data.errors) {
            Object.keys(resp.data.errors).forEach((k) => {
                if (k in errors) errors[k] = resp.data.errors[k][0];
            });
        } else {
            toast.error(resp?.data?.message || "Failed to update property.");
        }
    } finally {
        saving.value = false;
    }
}

onMounted(loadProperty);
</script>
