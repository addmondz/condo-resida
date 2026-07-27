<template>
    <div>
        <PageHeader
            title="Add User"
            subtitle="Create a new admin or guard account."
            :breadcrumbs="[
                { label: 'Users', to: { name: 'admin.users' } },
                { label: 'Add User' },
            ]"
        />

        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="px-5 py-5 sm:px-6 sm:py-6">
                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <AppInput v-model="form.name" label="Full name" placeholder="John Doe" :error="errors.name" required />
                    <AppInput v-model="form.email" label="Email" type="email" placeholder="user@company.com" :error="errors.email" required />
                    <AppInput v-model="form.phone" label="Phone number" placeholder="+60123456789" :error="errors.phone" />
                    <AppInput v-model="form.password" label="Password" type="password" placeholder="Minimum 8 characters" :error="errors.password" required />
                    <AppSelect v-model="form.role" label="Role" :options="roleOptions" placeholder="Select role" :error="errors.role" required />
                    <AppSelect
                        v-model="form.property_uuid"
                        label="Assigned Property"
                        :options="propertyOptions"
                        placeholder="Select property"
                        :error="errors.property_uuid"
                        required
                    />

                    <div class="flex items-center gap-3 pt-2">
                        <AppButton type="submit" :loading="loading">Create User</AppButton>
                        <AppButton variant="secondary" :to="{ name: 'admin.users' }">Cancel</AppButton>
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
import AppSelect from "@/components/common/AppSelect.vue";
import AppButton from "@/components/common/AppButton.vue";
import { useToast } from "@/composables/useToast";

const router = useRouter();
const toast = useToast();

const roleOptions = [
    { value: "property_admin", label: "Property Admin" },
    { value: "guard", label: "Guard" },
];

const propertyOptions = ref([]);
const form = reactive({ name: "", email: "", phone: "", password: "", role: "", property_uuid: "" });
const errors = reactive({ name: "", email: "", phone: "", password: "", role: "", property_uuid: "" });
const loading = ref(false);

async function loadProperties() {
    try {
        const { data } = await adminApi.getProperties({ per_page: 100 });
        propertyOptions.value = (data.data || []).map((p) => ({
            value: p.uuid,
            label: p.name,
        }));
    } catch {
        toast.error("Failed to load properties.");
    }
}

async function handleSubmit() {
    Object.keys(errors).forEach((k) => (errors[k] = ""));
    loading.value = true;
    try {
        const { data } = await adminApi.createUser(form);
        toast.success("User created successfully.");
        router.push({ name: "admin.users.show", params: { uuid: data.data.uuid } });
    } catch (error) {
        const resp = error.response;
        if (resp?.status === 422 && resp.data.errors) {
            Object.keys(resp.data.errors).forEach((k) => {
                if (k in errors) errors[k] = resp.data.errors[k][0];
            });
        } else {
            toast.error(resp?.data?.message || "Failed to create user.");
        }
    } finally {
        loading.value = false;
    }
}

onMounted(loadProperties);
</script>
