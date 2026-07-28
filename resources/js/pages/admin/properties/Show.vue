<template>
    <div>
        <PageHeader
            title="Property Details"
            subtitle="View and manage property configuration."
            :breadcrumbs="[
                { label: 'Properties', to: { name: 'admin.properties' } },
                { label: property.name || 'Details' },
            ]"
        />

        <SkeletonLoader v-if="loading" variant="form" :rows="5" container-class="rounded-xl border border-gray-200 bg-white px-5 py-5" />

        <div v-else-if="error" class="flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 px-5 py-4">
            <p class="text-sm text-red-700">{{ error }}</p>
        </div>

        <template v-else>
            <!-- Property Info -->
            <div class="mb-4 rounded-xl border border-gray-200 bg-white">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">{{ property.name }}</h2>
                        <p class="mt-0.5 text-sm text-gray-500">{{ property.address }}</p>
                    </div>
                    <AppButton
                        :to="{ name: 'admin.properties.edit', params: { uuid: property.uuid } }"
                        size="xs"
                        class="!px-5 !py-2"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                        </svg>
                        Edit
                    </AppButton>
                </div>
                <div class="px-5 py-5">
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
                        <div>
                            <dt class="text-sm text-gray-500">Contact Email</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ property.contact_email || "-" }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Contact Phone</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ property.contact_phone || "-" }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Timezone</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ property.timezone || "-" }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Blocks -->
            <div class="mb-4 rounded-xl border border-gray-200 bg-white">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Blocks</h3>
                        <p class="mt-0.5 text-xs text-gray-500">{{ blocks.length }} block(s) in this property</p>
                    </div>
                    <AppButton
                        size="xs"
                        class="!px-5 !py-2"
                        @click="showAddBlock = true"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Block
                    </AppButton>
                </div>

                <div v-if="loadingBlocks" class="px-5 py-8 text-center text-sm text-gray-400">Loading blocks...</div>
                <div v-else-if="!blocks.length" class="px-5 py-8 text-center text-sm text-gray-400">No blocks yet. Add one to get started.</div>
                <div v-else class="divide-y divide-gray-100">
                    <div v-for="block in blocks" :key="block.uuid" class="px-5 py-3.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3 cursor-pointer" @click="toggleBlock(block.uuid)">
                                <svg :class="['h-4 w-4 text-gray-400 transition-transform', expandedBlocks.has(block.uuid) ? 'rotate-90' : '']" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ block.name }}</p>
                                    <p class="text-xs text-gray-500">{{ block.units_count ?? 0 }} unit(s)</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    class="rounded-lg p-1.5 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                                    aria-label="Edit block"
                                    title="Edit block"
                                    @click="startEditBlock(block)"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg p-1.5 text-gray-500 transition-colors hover:bg-red-50 hover:text-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"
                                    aria-label="Delete block"
                                    title="Delete block"
                                    @click="confirmDeleteBlock(block)"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Expanded Units -->
                        <div v-if="expandedBlocks.has(block.uuid)" class="mt-3 ml-7 rounded-lg border border-gray-100 bg-gray-50">
                            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2.5">
                                <p class="text-xs font-medium text-gray-500">Units</p>
                                <button @click="startAddUnit(block)" class="flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-[13px] font-medium leading-5 bg-primary-600 text-white shadow-sm transition-colors hover:bg-primary-700">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    Add Unit
                                </button>
                            </div>
                            <div v-if="loadingUnits[block.uuid]" class="px-4 py-4 text-center text-xs text-gray-400">Loading...</div>
                            <div v-else-if="!(blockUnits[block.uuid] || []).length" class="px-4 py-4 text-center text-xs text-gray-400">No units in this block.</div>
                            <div v-else class="divide-y divide-gray-100">
                                <div v-for="unit in blockUnits[block.uuid]" :key="unit.uuid" class="flex items-center justify-between px-4 py-2.5">
                                    <div>
                                        <span class="text-sm text-gray-900">{{ unit.name }}</span>
                                        <span v-if="unit.floor" class="ml-2 text-xs text-gray-400">Floor {{ unit.floor }}</span>
                                        <span v-if="unit.residents?.length" class="ml-2 text-xs text-gray-500">&middot; {{ unit.residents.map(r => r.name).join(', ') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button
                                            type="button"
                                            class="rounded-lg p-1.5 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                                            aria-label="Edit unit"
                                            title="Edit unit"
                                            @click="startEditUnit(block, unit)"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-lg p-1.5 text-gray-500 transition-colors hover:bg-red-50 hover:text-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"
                                            aria-label="Delete unit"
                                            title="Delete unit"
                                            @click="confirmDeleteUnit(block, unit)"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Add Block Modal -->
        <AppModal :show="showAddBlock" title="Add Block" @close="showAddBlock = false">
            <AppInput v-model="blockForm.name" label="Block name" placeholder="e.g. Block A" required />
            <template #footer>
                <AppButton variant="secondary" @click="showAddBlock = false">Cancel</AppButton>
                <AppButton :loading="savingBlock" @click="addBlock">Add Block</AppButton>
            </template>
        </AppModal>

        <!-- Edit Block Modal -->
        <AppModal :show="showEditBlock" title="Edit Block" @close="showEditBlock = false">
            <AppInput v-model="blockForm.name" label="Block name" placeholder="e.g. Block A" required />
            <template #footer>
                <AppButton variant="secondary" @click="showEditBlock = false">Cancel</AppButton>
                <AppButton :loading="savingBlock" @click="updateBlock">Save</AppButton>
            </template>
        </AppModal>

        <!-- Add Unit Modal -->
        <AppModal :show="showAddUnit" title="Add Unit" @close="showAddUnit = false">
            <div class="space-y-4">
                <AppInput v-model="unitForm.name" label="Unit name" placeholder="e.g. A-12-01" required />
                <AppInput v-model="unitForm.floor" label="Floor" placeholder="e.g. 12" />
            </div>
            <template #footer>
                <AppButton variant="secondary" @click="showAddUnit = false">Cancel</AppButton>
                <AppButton :loading="savingUnit" @click="addUnit">Add Unit</AppButton>
            </template>
        </AppModal>

        <!-- Edit Unit Modal -->
        <AppModal :show="showEditUnit" title="Edit Unit" @close="showEditUnit = false">
            <div class="space-y-4">
                <AppInput v-model="unitForm.name" label="Unit name" placeholder="e.g. A-12-01" required />
                <AppInput v-model="unitForm.floor" label="Floor" placeholder="e.g. 12" />
            </div>
            <template #footer>
                <AppButton variant="secondary" @click="showEditUnit = false">Cancel</AppButton>
                <AppButton :loading="savingUnit" @click="updateUnit">Save</AppButton>
            </template>
        </AppModal>

        <!-- Delete Confirmation -->
        <ConfirmationDialog
            :show="showDeleteConfirm"
            :title="deleteTarget.type === 'block' ? 'Delete Block' : 'Delete Unit'"
            :message="`Are you sure you want to delete '${deleteTarget.name}'? This action cannot be undone.`"
            confirm-label="Delete"
            variant="danger"
            :loading="deleting"
            @confirm="executeDelete"
            @cancel="showDeleteConfirm = false"
        />
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { RouterLink } from "vue-router";
import adminApi from "@/api/admin";
import PageHeader from "@/components/common/PageHeader.vue";
import AppButton from "@/components/common/AppButton.vue";
import AppInput from "@/components/common/AppInput.vue";
import AppModal from "@/components/common/AppModal.vue";
import ConfirmationDialog from "@/components/common/ConfirmationDialog.vue";
import SkeletonLoader from "@/components/common/SkeletonLoader.vue";
import { useToast } from "@/composables/useToast";

const props = defineProps({ uuid: { type: String, required: true } });
const toast = useToast();

const property = ref({});
const loading = ref(true);
const error = ref("");

const blocks = ref([]);
const loadingBlocks = ref(true);
const expandedBlocks = ref(new Set());
const blockUnits = ref({});
const loadingUnits = ref({});

const showAddBlock = ref(false);
const showEditBlock = ref(false);
const showAddUnit = ref(false);
const showEditUnit = ref(false);
const showDeleteConfirm = ref(false);

const blockForm = reactive({ name: "", uuid: "" });
const unitForm = reactive({ name: "", floor: "", uuid: "", blockUuid: "" });
const deleteTarget = reactive({ type: "", uuid: "", blockUuid: "", name: "" });

const savingBlock = ref(false);
const savingUnit = ref(false);
const deleting = ref(false);

async function loadProperty() {
    try {
        const { data } = await adminApi.getProperty(props.uuid);
        property.value = data.data;
    } catch {
        error.value = "Failed to load property.";
    } finally {
        loading.value = false;
    }
}

async function loadBlocks() {
    loadingBlocks.value = true;
    try {
        const { data } = await adminApi.getBlocks(props.uuid, { per_page: 100 });
        blocks.value = data.data.data;
    } catch {
        blocks.value = [];
    } finally {
        loadingBlocks.value = false;
    }
}

async function toggleBlock(blockUuid) {
    if (expandedBlocks.value.has(blockUuid)) {
        expandedBlocks.value.delete(blockUuid);
        expandedBlocks.value = new Set(expandedBlocks.value);
        return;
    }
    expandedBlocks.value.add(blockUuid);
    expandedBlocks.value = new Set(expandedBlocks.value);
    await loadUnits(blockUuid);
}

async function loadUnits(blockUuid) {
    loadingUnits.value[blockUuid] = true;
    try {
        const { data } = await adminApi.getUnits(blockUuid, { per_page: 200 });
        blockUnits.value[blockUuid] = data.data.data;
    } catch {
        blockUnits.value[blockUuid] = [];
    } finally {
        loadingUnits.value[blockUuid] = false;
    }
}

async function addBlock() {
    if (!blockForm.name.trim()) return;
    savingBlock.value = true;
    try {
        await adminApi.createBlock(props.uuid, { name: blockForm.name });
        toast.success("Block added.");
        showAddBlock.value = false;
        blockForm.name = "";
        await loadBlocks();
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to add block.");
    } finally {
        savingBlock.value = false;
    }
}

function startEditBlock(block) {
    blockForm.name = block.name;
    blockForm.uuid = block.uuid;
    showEditBlock.value = true;
}

async function updateBlock() {
    if (!blockForm.name.trim()) return;
    savingBlock.value = true;
    try {
        await adminApi.updateBlock(props.uuid, blockForm.uuid, { name: blockForm.name });
        toast.success("Block updated.");
        showEditBlock.value = false;
        await loadBlocks();
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to update block.");
    } finally {
        savingBlock.value = false;
    }
}

function startAddUnit(block) {
    unitForm.name = "";
    unitForm.floor = "";
    unitForm.blockUuid = block.uuid;
    showAddUnit.value = true;
}

async function addUnit() {
    if (!unitForm.name.trim()) return;
    savingUnit.value = true;
    try {
        await adminApi.createUnit(unitForm.blockUuid, { name: unitForm.name, floor: unitForm.floor || null });
        toast.success("Unit added.");
        showAddUnit.value = false;
        await loadUnits(unitForm.blockUuid);
        await loadBlocks();
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to add unit.");
    } finally {
        savingUnit.value = false;
    }
}

function startEditUnit(block, unit) {
    unitForm.name = unit.name;
    unitForm.floor = unit.floor || "";
    unitForm.uuid = unit.uuid;
    unitForm.blockUuid = block.uuid;
    showEditUnit.value = true;
}

async function updateUnit() {
    if (!unitForm.name.trim()) return;
    savingUnit.value = true;
    try {
        await adminApi.updateUnit(unitForm.blockUuid, unitForm.uuid, { name: unitForm.name, floor: unitForm.floor || null });
        toast.success("Unit updated.");
        showEditUnit.value = false;
        await loadUnits(unitForm.blockUuid);
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to update unit.");
    } finally {
        savingUnit.value = false;
    }
}

function confirmDeleteBlock(block) {
    deleteTarget.type = "block";
    deleteTarget.uuid = block.uuid;
    deleteTarget.name = block.name;
    showDeleteConfirm.value = true;
}

function confirmDeleteUnit(block, unit) {
    deleteTarget.type = "unit";
    deleteTarget.uuid = unit.uuid;
    deleteTarget.blockUuid = block.uuid;
    deleteTarget.name = unit.name;
    showDeleteConfirm.value = true;
}

async function executeDelete() {
    deleting.value = true;
    try {
        if (deleteTarget.type === "block") {
            await adminApi.deleteBlock(props.uuid, deleteTarget.uuid);
            toast.success("Block deleted.");
            await loadBlocks();
        } else {
            await adminApi.deleteUnit(deleteTarget.blockUuid, deleteTarget.uuid);
            toast.success("Unit deleted.");
            await loadUnits(deleteTarget.blockUuid);
            await loadBlocks();
        }
        showDeleteConfirm.value = false;
    } catch (err) {
        toast.error(err.response?.data?.message || "Failed to delete.");
    } finally {
        deleting.value = false;
    }
}

onMounted(() => {
    loadProperty();
    loadBlocks();
});
</script>
