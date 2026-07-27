<template>
    <div>
        <PageHeader
            title="Add Property"
            subtitle="Create a new property."
            :breadcrumbs="[
                { label: 'Properties', to: { name: 'admin.properties' } },
                { label: 'Add Property' },
            ]"
        />

        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="px-5 py-5 sm:px-6 sm:py-6">
                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <AppInput v-model="form.name" label="Property name" placeholder="e.g. Residensi Harmoni" :error="errors.name" required />
                    <AppInput v-model="form.address" label="Address" placeholder="Full property address" :error="errors.address" required />
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <AppInput v-model="form.contact_email" label="Contact email" type="email" placeholder="admin@property.com" :error="errors.contact_email" />
                        <AppInput v-model="form.contact_phone" label="Contact phone" placeholder="+60123456789" :error="errors.contact_phone" />
                    </div>
                    <AppInput v-model="form.timezone" label="Timezone" placeholder="Asia/Kuala_Lumpur" :error="errors.timezone" />

                    <div class="flex items-center gap-3 pt-2">
                        <AppButton type="submit" :loading="loading">Create Property</AppButton>
                        <AppButton variant="secondary" :to="{ name: 'admin.properties' }">Cancel</AppButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import { useRouter } from "vue-router";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppButton from "@/components/common/AppButton.vue";
import { useToast } from "@/composables/useToast";

const router = useRouter();
const toast = useToast();

const form = reactive({
    name: "",
    address: "",
    contact_email: "",
    contact_phone: "",
    timezone: "Asia/Kuala_Lumpur",
});

const errors = reactive({ name: "", address: "", contact_email: "", contact_phone: "", timezone: "" });
const loading = ref(false);

async function handleSubmit() {
    Object.keys(errors).forEach((k) => (errors[k] = ""));
    loading.value = true;
    try {
        await adminApi.createProperty(form);
        toast.success("Property created successfully.");
        router.push({ name: "admin.properties" });
    } catch (error) {
        const resp = error.response;
        if (resp?.status === 422 && resp.data.errors) {
            Object.keys(resp.data.errors).forEach((k) => {
                if (k in errors) errors[k] = resp.data.errors[k][0];
            });
        } else {
            toast.error(resp?.data?.message || "Failed to create property.");
        }
    } finally {
        loading.value = false;
    }
}
</script>
