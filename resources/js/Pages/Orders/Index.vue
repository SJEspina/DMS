<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    orders: {
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
});

const search = ref(props.filters.search || "");

const formatPrice = (value) => {
    return "₱" + Number(value || 0).toFixed(2);
};

const getTotal = (order) => {
    return (Number(order.price) || 0) * (Number(order.qty) || 0);
};

const getBalance = (order) => {
    return getTotal(order) - (Number(order.downpayment) || 0);
};

const searchOrders = () => {
    router.get(
        route("orders.index"),
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const deleteOrder = (id) => {
    if (confirm("Are you sure you want to delete this order?")) {
        router.delete(route("orders.destroy", id));
    }
};

const payBalance = (order) => {
    if (
        !confirm(
            `Mark this order as fully paid? Remaining balance: ${formatPrice(getBalance(order))}`,
        )
    ) {
        return;
    }

    router.post(route("orders.payBalance", order.id));
};

const statusClass = (status) => {
    if (status === "Pending") return "bg-yellow-100 text-yellow-700";
    if (status === "Ongoing") return "bg-blue-100 text-blue-700";
    if (status === "Done") return "bg-green-100 text-green-700";
    return "bg-gray-100 text-gray-700";
};

const paymentStatusClass = (status) => {
    if (status === "Unpaid") return "bg-red-100 text-red-700";
    if (status === "Partially Paid") return "bg-orange-100 text-orange-700";
    if (status === "Paid") return "bg-green-100 text-green-700";
    return "bg-gray-100 text-gray-700";
};
</script>

<template>
    <Head title="Orders" />

    <AuthenticatedLayout>
        <template #header>
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div
                    class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between"
                >
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            Orders Overview
                        </h2>
                    </div>

                    <div
                        class="flex w-full flex-col gap-2 sm:flex-row xl:w-auto"
                    >
                        <input
                            v-model="search"
                            @input="searchOrders"
                            type="text"
                            placeholder="Search customer..."
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 sm:w-72"
                        />

                        <Link
                            :href="route('orders.create')"
                            class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
                        >
                            Add Order
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="min-h-screen bg-slate-50 py-8">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div
                    class="w-full overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm"
                >
                    <div class="border-b border-gray-100 px-6 py-4">
                        <div
                            class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <h3 class="text-lg font-semibold text-gray-800">
                                Order List
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{ props.orders.data.length }} order(s) shown
                            </p>
                        </div>
                    </div>

                    <div class="w-full overflow-x-auto">
                        <table class="w-full min-w-[1500px]">
                            <thead class="bg-gray-50">
                                <tr
                                    class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                >
                                    <th class="px-4 py-3">Date Created</th>
                                    <th class="px-4 py-3">Deadline</th>
                                    <th class="px-4 py-3">Customer</th>
                                    <th class="px-4 py-3">Product</th>
                                    <th class="px-4 py-3">Price</th>
                                    <th class="px-4 py-3">Qty</th>
                                    <th class="px-4 py-3">Total</th>
                                    <th class="px-4 py-3">Downpayment</th>
                                    <th class="px-4 py-3">Balance</th>
                                    <th class="px-4 py-3">Payment Status</th>
                                    <th class="px-4 py-3">Order Status</th>
                                    <th class="px-4 py-3 text-center">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-gray-100 text-sm text-gray-700"
                            >
                                <tr
                                    v-for="order in props.orders.data"
                                    :key="order.id"
                                    class="transition hover:bg-gray-50"
                                >
                                    <td class="whitespace-nowrap px-4 py-4">
                                        {{
                                            new Date(
                                                order.created_at,
                                            ).toLocaleDateString()
                                        }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">
                                        {{ order.deadline ?? "No deadline" }}
                                    </td>

                                    <td
                                        class="px-4 py-4 font-medium text-gray-900"
                                    >
                                        {{ order.customer }}
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap">
                                        {{ order.product }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">
                                        {{ formatPrice(order.price) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">
                                        {{ order.qty }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-4 py-4 font-medium text-gray-900"
                                    >
                                        {{ formatPrice(getTotal(order)) }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-4 py-4 font-medium text-blue-700"
                                    >
                                        {{ formatPrice(order.downpayment) }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-4 py-4 font-semibold"
                                        :class="
                                            getBalance(order) > 0
                                                ? 'text-red-600'
                                                : 'text-green-600'
                                        "
                                    >
                                        {{ formatPrice(getBalance(order)) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">
                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                            :class="
                                                paymentStatusClass(
                                                    order.payment_status,
                                                )
                                            "
                                        >
                                            {{ order.payment_status }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">
                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                            :class="statusClass(order.status)"
                                        >
                                            {{ order.status }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4">
                                        <div
                                            class="flex items-center justify-center gap-2 min-w-[220px]"
                                        >
                                            <button
                                                v-if="getBalance(order) > 0"
                                                @click="payBalance(order)"
                                                class="w-[70px] rounded-lg bg-green-600 py-1.5 text-xs font-medium text-white hover:bg-green-700"
                                            >
                                                Pay
                                            </button>

                                            <span
                                                v-else
                                                class="w-[70px] text-center rounded-lg bg-green-50 py-1.5 text-xs font-medium text-green-700"
                                            >
                                                Paid
                                            </span>

                                            <Link
                                                :href="
                                                    route(
                                                        'orders.edit',
                                                        order.id,
                                                    )
                                                "
                                                class="w-[70px] text-center rounded-lg bg-blue-500 py-1.5 text-xs font-medium text-white hover:bg-blue-600"
                                            >
                                                Edit
                                            </Link>

                                            <button
                                                @click="deleteOrder(order.id)"
                                                class="w-[70px] rounded-lg bg-red-500 py-1.5 text-xs font-medium text-white hover:bg-red-600"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="props.orders.data.length === 0">
                                    <td
                                        colspan="12"
                                        class="px-6 py-12 text-center"
                                    >
                                        <div
                                            class="flex flex-col items-center justify-center"
                                        >
                                            <div
                                                class="mb-3 rounded-full bg-gray-100 p-4 text-gray-400"
                                            >
                                                📦
                                            </div>
                                            <h4
                                                class="text-base font-semibold text-gray-700"
                                            >
                                                No orders found
                                            </h4>
                                            <p
                                                class="mt-1 text-sm text-gray-500"
                                            >
                                                Try searching a different
                                                customer or add a new order.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        v-if="
                            props.orders.links && props.orders.links.length > 3
                        "
                        class="border-t border-gray-100 px-6 py-4"
                    >
                        <div class="flex flex-wrap gap-2">
                            <Link
                                v-for="(link, index) in props.orders.links"
                                :key="index"
                                :href="link.url || '#'"
                                v-html="link.label"
                                class="rounded-lg border px-3 py-1.5 text-sm transition"
                                :class="[
                                    link.active
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50',
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
    </AuthenticatedLayout>
</template>
