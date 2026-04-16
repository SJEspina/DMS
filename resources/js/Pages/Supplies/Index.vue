<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
    supplies: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
        }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    totalExpenses: {
        type: Number,
        default: 0,
    },
    monthlyExpenses: {
        type: Number,
        default: 0,
    },
});

const search = ref(props.filters.search || "");

const showAddStockModal = ref(false);
const showUseStockModal = ref(false);
const selectedSupply = ref(null);

const addStockForm = useForm({
    quantity: "",
    price: "",
    notes: "",
});

const useStockForm = useForm({
    quantity: "",
    notes: "",
});

const totalCost = computed(() => {
    const qty = Number(addStockForm.quantity) || 0;
    const price = Number(addStockForm.price) || 0;
    return (qty * price).toFixed(2);
});

const searchSupplies = () => {
    router.get(
        route("supplies.index"),
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const deleteSupply = (id) => {
    if (confirm("Are you sure you want to delete this supply?")) {
        router.delete(route("supplies.destroy", id));
    }
};

const openAddStockModal = (supply) => {
    selectedSupply.value = supply;
    addStockForm.reset();
    addStockForm.clearErrors();
    showAddStockModal.value = true;
};

const openUseStockModal = (supply) => {
    selectedSupply.value = supply;
    useStockForm.reset();
    useStockForm.clearErrors();
    showUseStockModal.value = true;
};

const submitAddStock = () => {
    if (!selectedSupply.value) return;

    addStockForm.post(route("supplies.addStock", selectedSupply.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showAddStockModal.value = false;
            selectedSupply.value = null;
            addStockForm.reset();
        },
    });
};

const submitUseStock = () => {
    if (!selectedSupply.value) return;

    useStockForm.post(route("supplies.useStock", selectedSupply.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showUseStockModal.value = false;
            selectedSupply.value = null;
            useStockForm.reset();
        },
    });
};

const closeAddStockModal = () => {
    showAddStockModal.value = false;
    selectedSupply.value = null;
    addStockForm.reset();
    addStockForm.clearErrors();
};

const closeUseStockModal = () => {
    showUseStockModal.value = false;
    selectedSupply.value = null;
    useStockForm.reset();
    useStockForm.clearErrors();
};

const statusClass = (status) => {
    if (status === "In Stock") return "bg-green-100 text-green-700";
    if (status === "Low Stock") return "bg-yellow-100 text-yellow-700";
    if (status === "Out of Stock") return "bg-red-100 text-red-700";
    return "bg-gray-100 text-gray-700";
};
</script>

<template>
    <Head title="Supplies" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <h2 class="text-2xl font-bold tracking-tight text-slate-800">
                    Supply
                </h2>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <input
                        v-model="search"
                        @input="searchSupplies"
                        type="text"
                        placeholder="Search supply..."
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 sm:w-72"
                    />

                    <Link
                        :href="route('supplies.create')"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        Add Supply
                    </Link>
                </div>
            </div>
        </template>

        <div class="min-h-screen bg-slate-50 py-8">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div class="w-full overflow-x-auto rounded-xl bg-white shadow">
                    <div class="p-6">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr
                                    class="border-b text-left text-sm text-gray-500"
                                >
                                    <th class="px-4 py-3 whitespace-nowrap">
                                        Date Created
                                    </th>
                                    <th class="px-4 py-3 whitespace-nowrap">
                                        Name
                                    </th>
                                    <th class="px-4 py-3 whitespace-nowrap">
                                        Category
                                    </th>
                                    <th class="px-4 py-3 whitespace-nowrap">
                                        Price
                                    </th>
                                    <th class="px-4 py-3 whitespace-nowrap">
                                        Stock
                                    </th>
                                    <th class="px-4 py-3 whitespace-nowrap">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 whitespace-nowrap">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="supply in props.supplies.data"
                                    :key="supply.id"
                                    class="border-b text-sm"
                                >
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{
                                            new Date(
                                                supply.created_at,
                                            ).toLocaleDateString()
                                        }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ supply.name }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ supply.category }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        ₱{{ Number(supply.price).toFixed(2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ supply.stock }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-medium"
                                            :class="statusClass(supply.status)"
                                        >
                                            {{ supply.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <Link
                                                :href="
                                                    route(
                                                        'supplies.edit',
                                                        supply.id,
                                                    )
                                                "
                                                class="rounded bg-blue-500 px-3 py-1 text-white hover:bg-blue-600"
                                            >
                                                Edit
                                            </Link>

                                            <button
                                                @click="
                                                    openAddStockModal(supply)
                                                "
                                                class="rounded bg-green-500 px-3 py-1 text-white hover:bg-green-600"
                                            >
                                                Add Stock
                                            </button>

                                            <button
                                                @click="
                                                    openUseStockModal(supply)
                                                "
                                                class="rounded bg-yellow-500 px-3 py-1 text-white hover:bg-yellow-600"
                                            >
                                                Use Stock
                                            </button>

                                            <button
                                                @click="deleteSupply(supply.id)"
                                                class="rounded bg-red-500 px-3 py-1 text-white hover:bg-red-600"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="props.supplies.data.length === 0">
                                    <td
                                        colspan="7"
                                        class="px-4 py-6 text-center text-gray-500"
                                    >
                                        No supplies found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div
                            v-if="
                                props.supplies.links &&
                                props.supplies.links.length > 3
                            "
                            class="mt-6 flex flex-wrap gap-2"
                        >
                            <Link
                                v-for="(link, index) in props.supplies.links"
                                :key="index"
                                :href="link.url || '#'"
                                v-html="link.label"
                                class="rounded border px-3 py-1 text-sm"
                                :class="[
                                    link.active
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-100',
                                    !link.url
                                        ? 'pointer-events-none opacity-50'
                                        : '',
                                ]"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Stock Modal -->
        <div
            v-if="showAddStockModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-1 text-lg font-semibold text-gray-800">
                    Add Stock
                </h3>
                <p class="mb-4 text-sm text-gray-500">
                    {{ selectedSupply?.name }}
                </p>

                <div class="space-y-4">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Quantity
                        </label>
                        <input
                            v-model="addStockForm.quantity"
                            type="number"
                            min="1"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-200"
                        />
                        <p
                            v-if="addStockForm.errors.quantity"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ addStockForm.errors.quantity }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Price
                        </label>
                        <input
                            v-model="addStockForm.price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-200"
                        />
                        <p
                            v-if="addStockForm.errors.price"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ addStockForm.errors.price }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Total Cost
                        </label>
                        <input
                            :value="`₱${totalCost}`"
                            type="text"
                            readonly
                            class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-gray-700"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Notes
                        </label>
                        <input
                            v-model="addStockForm.notes"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-200"
                            placeholder="Optional"
                        />
                        <p
                            v-if="addStockForm.errors.notes"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ addStockForm.errors.notes }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        @click="closeAddStockModal"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitAddStock"
                        :disabled="addStockForm.processing"
                        class="rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:opacity-50"
                    >
                        Save
                    </button>
                </div>
            </div>
        </div>

        <!-- Use Stock Modal -->
        <div
            v-if="showUseStockModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-1 text-lg font-semibold text-gray-800">
                    Use Stock
                </h3>
                <p class="mb-1 text-sm text-gray-500">
                    {{ selectedSupply?.name }}
                </p>
                <p class="mb-4 text-sm text-gray-500">
                    Current Stock: {{ selectedSupply?.stock ?? 0 }}
                </p>

                <div class="space-y-4">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Quantity
                        </label>
                        <input
                            v-model="useStockForm.quantity"
                            type="number"
                            min="1"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-200"
                        />
                        <p
                            v-if="useStockForm.errors.quantity"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ useStockForm.errors.quantity }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Notes
                        </label>
                        <input
                            v-model="useStockForm.notes"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-200"
                            placeholder="Optional"
                        />
                        <p
                            v-if="useStockForm.errors.notes"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ useStockForm.errors.notes }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        @click="closeUseStockModal"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitUseStock"
                        :disabled="useStockForm.processing"
                        class="rounded-lg bg-yellow-500 px-4 py-2 text-white hover:bg-yellow-600 disabled:opacity-50"
                    >
                        Use
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
