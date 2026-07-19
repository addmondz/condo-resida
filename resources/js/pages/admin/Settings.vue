<template>
    <div>
        <PageHeader
            title="System Settings"
            subtitle="Configure system-wide settings."
            :breadcrumbs="[{ label: 'Settings' }]"
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
            <form @submit.prevent="handleSubmit" class="max-w-2xl space-y-4">
                <div
                    v-for="(values, group) in settings"
                    :key="group"
                    class="rounded-xl border border-gray-200 bg-white"
                >
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h3
                            class="text-base font-semibold text-gray-900 capitalize"
                        >
                            {{ group }}
                        </h3>
                    </div>
                    <div class="space-y-5 px-5 py-5">
                        <div v-for="(value, key) in values" :key="key">
                            <template
                                v-if="
                                    typeof value === 'boolean' ||
                                    value === 'true' ||
                                    value === 'false'
                                "
                            >
                                <div class="flex items-center gap-2">
                                    <input
                                        :id="`${group}-${key}`"
                                        type="checkbox"
                                        :checked="
                                            value === true || value === 'true'
                                        "
                                        @change="
                                            settings[group][key] =
                                                $event.target.checked
                                        "
                                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                    />
                                    <label
                                        :for="`${group}-${key}`"
                                        class="text-sm font-medium text-gray-700"
                                        >{{ formatLabel(key) }}</label
                                    >
                                </div>
                            </template>
                            <AppInput
                                v-else
                                v-model="settings[group][key]"
                                :label="formatLabel(key)"
                            />
                        </div>
                    </div>
                </div>

                <AppButton type="submit" :loading="saving"
                    >Save Settings</AppButton
                >
            </form>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppButton from "@/components/common/AppButton.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import { useToast } from "@/composables/useToast";

const settings = ref({});
const loading = ref(true);
const error = ref("");
const saving = ref(false);
const toast = useToast();

function formatLabel(key) {
    return key.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
}

onMounted(async () => {
    try {
        const { data } = await adminApi.getSettings();
        settings.value = data.data;
    } catch {
        error.value = "Failed to load settings.";
    } finally {
        loading.value = false;
    }
});

async function handleSubmit() {
    saving.value = true;

    const payload = [];
    for (const [group, values] of Object.entries(settings.value)) {
        for (const [key, value] of Object.entries(values)) {
            payload.push({ group, key, value: String(value) });
        }
    }

    try {
        await adminApi.updateSettings({ settings: payload });
        toast.success("Settings saved successfully.");
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to save settings.");
    } finally {
        saving.value = false;
    }
}
</script>
