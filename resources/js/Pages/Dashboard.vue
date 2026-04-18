<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
    supplies: {
        type: Array,
        default: () => [],
    },
    monthlyExpense: {
        type: Number,
        default: 0,
    },
});

const today = new Date();
const currentDay = today.getDate();
const currentMonth = today.getMonth();
const currentYear = today.getFullYear();

const daysInCurrentMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

const currentMonthOrders = computed(() =>
    props.orders.filter((order) => {
        if (!order.created_at) return false;

        const orderDate = new Date(order.created_at);

        return (
            orderDate.getMonth() === currentMonth &&
            orderDate.getFullYear() === currentYear
        );
    }),
);

const pendingOrders = computed(() => {
    return currentMonthOrders.value.filter(
        (order) => order.status === "Pending",
    ).length;
});

const ongoingOrders = computed(() => {
    return currentMonthOrders.value.filter(
        (order) => order.status === "Ongoing",
    ).length;
});

const completedOrders = computed(() => {
    return currentMonthOrders.value.filter((order) => order.status === "Done")
        .length;
});

const totalOrders = computed(() => currentMonthOrders.value.length);

// SALES = MONEY ACTUALLY RECEIVED
const dailySales = computed(() =>
    currentMonthOrders.value
        .filter((order) => {
            if (!order.created_at) return false;
            return new Date(order.created_at).getDate() === currentDay;
        })
        .reduce((total, order) => total + Number(order.downpayment || 0), 0),
);

const monthlySales = computed(() =>
    currentMonthOrders.value.reduce(
        (total, order) => total + Number(order.downpayment || 0),
        0,
    ),
);

const dailySalesData = computed(() => {
    const data = Array(daysInCurrentMonth).fill(0);

    currentMonthOrders.value.forEach((order) => {
        if (!order.created_at) return;

        const date = new Date(order.created_at);
        const dayIndex = date.getDate() - 1;

        data[dayIndex] += Number(order.downpayment || 0);
    });

    return data;
});

const dayLabels = computed(() =>
    Array.from({ length: daysInCurrentMonth }, (_, i) =>
        String(i + 1).padStart(2, "0"),
    ),
);

const summary = computed(() => ({
    dailySales: dailySales.value,
    monthlySales: monthlySales.value,
    totalOrders: totalOrders.value,
    monthlyExpense: Number(props.monthlyExpense || 0),
}));

const salesOverview = computed(() =>
    dayLabels.value.map((label, index) => ({
        label,
        sales: dailySalesData.value[index] || 0,
    })),
);

const stats = computed(() => [
    {
        title: "Pending Orders",
        value: pendingOrders.value,
        color: "bg-yellow-500",
        bgLight: "bg-yellow-50",
        hoverBorder: "hover:border-yellow-200 hover:shadow-yellow-100",
        textHover: "group-hover:text-yellow-600",
    },
    {
        title: "Ongoing Orders",
        value: ongoingOrders.value,
        color: "bg-blue-500",
        bgLight: "bg-blue-50",
        hoverBorder: "hover:border-blue-200 hover:shadow-blue-100",
        textHover: "group-hover:text-blue-600",
    },
    {
        title: "Completed Orders",
        value: completedOrders.value,
        color: "bg-green-500",
        bgLight: "bg-green-50",
        hoverBorder: "hover:border-green-200 hover:shadow-green-100",
        textHover: "group-hover:text-green-600",
    },
    {
        title: "Total Orders",
        value: totalOrders.value,
        color: "bg-purple-500",
        bgLight: "bg-purple-50",
        hoverBorder: "hover:border-purple-200 hover:shadow-purple-100",
        textHover: "group-hover:text-purple-600",
    },
]);

const recentOrders = computed(() => props.orders.slice(0, 5));

const lowStocks = computed(() =>
    props.supplies.filter((supply) => Number(supply.stock) <= 3),
);

const upcomingDeadlines = computed(() => {
    const now = new Date();
    const twoDaysLater = new Date();
    twoDaysLater.setDate(now.getDate() + 2);

    return props.orders.filter((order) => {
        if (!order.deadline || order.status === "Done") return false;

        const deadline = new Date(order.deadline);
        return deadline >= now && deadline <= twoDaysLater;
    });
});

function statusClass(status) {
    if (status === "Pending") return "bg-yellow-100 text-yellow-700";
    if (status === "Ongoing") return "bg-blue-100 text-blue-700";
    if (status === "Done") return "bg-green-100 text-green-700";
    return "bg-gray-100 text-gray-700";
}

function paymentStatusClass(status) {
    if (status === "Unpaid") return "bg-red-100 text-red-700";
    if (status === "Partially Paid") return "bg-orange-100 text-orange-700";
    if (status === "Paid") return "bg-green-100 text-green-700";
    return "bg-gray-100 text-gray-700";
}

function getPaymentStatus(order) {
    if (order.payment_status) return order.payment_status;

    const total = (Number(order.price) || 0) * Number(order.qty || 0);
    const downpayment = Number(order.downpayment || 0);

    if (downpayment <= 0) return "Unpaid";
    if (downpayment >= total) return "Paid";
    return "Partially Paid";
}

function formatDate(date) {
    if (!date) return "No date";
    return new Date(date).toLocaleDateString();
}

function formatPrice(price) {
    return `₱${Number(price || 0).toFixed(2)}`;
}

function formatCurrency(value) {
    return `₱${Number(value || 0).toLocaleString("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

function formatCompactCurrency(value) {
    const number = Number(value || 0);

    if (number >= 1000000) {
        return `₱${(number / 1000000).toFixed(1)}M`;
    }

    if (number >= 1000) {
        return `₱${(number / 1000).toFixed(1)}k`;
    }

    return `₱${number.toFixed(0)}`;
}

const chartWidth = 1600;
const chartHeight = 300;

const maxSalesValue = computed(() => {
    const max = Math.max(
        ...salesOverview.value.map((item) => Number(item.sales || 0)),
        0,
    );
    return max > 0 ? max : 1;
});

const salesYAxis = computed(() => {
    const max = maxSalesValue.value;
    return [max, max * 0.75, max * 0.5, max * 0.25, 0];
});

function getChartX(index, total) {
    if (total <= 1) return chartWidth / 2;
    return (index * chartWidth) / (total - 1);
}

function getChartY(value) {
    return (
        chartHeight - (Number(value || 0) / maxSalesValue.value) * chartHeight
    );
}

function getSalesLinePoints() {
    if (!salesOverview.value.length) return "";

    return salesOverview.value
        .map((item, index) => {
            const x = getChartX(index, salesOverview.value.length);
            const y = getChartY(item.sales);
            return `${x},${y}`;
        })
        .join(" ");
}

function getSalesAreaPoints() {
    if (!salesOverview.value.length) return "";
    const line = getSalesLinePoints();
    return `0,${chartHeight} ${line} ${chartWidth},${chartHeight}`;
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-800">
                    Dashboard
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Monitor your printing orders, sales, and supplies.
                </p>
            </div>
        </template>

        <div class="min-h-screen bg-slate-50 py-8">
            <div class="w-full px-6 lg:px-8 xl:px-10">
                <main class="space-y-6">
                    <div
                        class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4"
                    >
                        <div
                            v-for="stat in stats"
                            :key="stat.title"
                            class="group relative overflow-hidden rounded-[2rem] border border-slate-100 bg-white p-7 shadow-xl shadow-slate-200/40 transition-all duration-300 hover:-translate-y-1"
                            :class="stat.hoverBorder"
                        >
                            <div
                                class="absolute -right-10 -top-10 h-32 w-32 rounded-full opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                                :class="stat.bgLight"
                            ></div>

                            <div class="relative">
                                <div
                                    class="mb-4 flex items-center justify-between"
                                >
                                    <p
                                        class="text-[13px] font-bold uppercase tracking-wider text-slate-400"
                                    >
                                        {{ stat.title }}
                                    </p>

                                    <div
                                        class="rounded-xl p-2 transition-transform duration-300 group-hover:scale-110"
                                        :class="stat.bgLight"
                                    >
                                        <div
                                            class="flex h-5 w-5 items-center justify-center"
                                        >
                                            <div
                                                class="h-2.5 w-2.5 rounded-full"
                                                :class="stat.color"
                                            ></div>
                                        </div>
                                    </div>
                                </div>

                                <h3
                                    class="text-3xl font-black tracking-tight text-slate-800 transition-colors duration-300"
                                    :class="stat.textHover"
                                >
                                    {{ stat.value }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 2xl:grid-cols-12">
                        <div
                            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm 2xl:col-span-9"
                        >
                            <div
                                class="mb-8 flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                            >
                                <div>
                                    <h2
                                        class="text-xl font-bold tracking-tight text-slate-800"
                                    >
                                        Monthly Sales Overview
                                    </h2>
                                    <p
                                        class="mt-1 text-sm text-slate-500 text-pretty"
                                    >
                                        Daily received payments for the current
                                        month
                                    </p>
                                </div>
                            </div>

                            <div
                                class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3"
                            >
                                <div
                                    class="group rounded-2xl border border-slate-100 bg-green-50/30 p-5 transition-all hover:border-green-200"
                                >
                                    <p
                                        class="text-[11px] font-bold uppercase tracking-wider text-green-600/70"
                                    >
                                        Daily Sales
                                    </p>
                                    <h3
                                        class="mt-2 text-2xl font-black text-green-600"
                                    >
                                        {{ formatCurrency(summary.dailySales) }}
                                    </h3>
                                </div>

                                <div
                                    class="group rounded-2xl border border-slate-100 bg-blue-50/30 p-5 transition-all hover:border-blue-200"
                                >
                                    <p
                                        class="text-[11px] font-bold uppercase tracking-wider text-blue-600/70"
                                    >
                                        Monthly Sales
                                    </p>
                                    <h3
                                        class="mt-2 text-2xl font-black text-blue-600"
                                    >
                                        {{
                                            formatCurrency(summary.monthlySales)
                                        }}
                                    </h3>
                                </div>

                                <div
                                    class="group rounded-2xl border border-slate-100 bg-rose-50/30 p-5 transition-all hover:border-rose-200"
                                >
                                    <p
                                        class="text-[11px] font-bold uppercase tracking-wider text-rose-600/70"
                                    >
                                        Monthly Expense
                                    </p>
                                    <h3
                                        class="mt-2 text-2xl font-black text-rose-600"
                                    >
                                        {{
                                            formatCurrency(
                                                summary.monthlyExpense,
                                            )
                                        }}
                                    </h3>
                                </div>
                            </div>

                            <div class="rounded-2xl bg-white p-2 sm:p-4">
                                <div class="relative">
                                    <div
                                        class="pointer-events-none absolute left-0 top-0 z-10 flex h-[300px] flex-col justify-between py-1"
                                    >
                                        <p
                                            v-for="(value, index) in salesYAxis"
                                            :key="`y-${index}`"
                                            class="bg-white/50 pr-1 text-[10px] font-bold text-blue-300"
                                        >
                                            {{ formatCompactCurrency(value) }}
                                        </p>
                                    </div>

                                    <div class="w-full">
                                        <div class="relative h-[300px] w-full">
                                            <div
                                                class="pointer-events-none absolute inset-0 flex flex-col justify-between"
                                            >
                                                <div
                                                    v-for="i in 5"
                                                    :key="i"
                                                    class="w-full border-t border-blue-50"
                                                ></div>
                                            </div>

                                            <svg
                                                :viewBox="`0 0 ${chartWidth} ${chartHeight}`"
                                                class="absolute inset-0 h-full w-full"
                                                preserveAspectRatio="none"
                                            >
                                                <defs>
                                                    <linearGradient
                                                        id="salesGrad"
                                                        x1="0"
                                                        y1="0"
                                                        x2="0"
                                                        y2="1"
                                                    >
                                                        <stop
                                                            offset="0%"
                                                            stop-color="#3b82f6"
                                                            stop-opacity="0.2"
                                                        />
                                                        <stop
                                                            offset="100%"
                                                            stop-color="#3b82f6"
                                                            stop-opacity="0"
                                                        />
                                                    </linearGradient>
                                                </defs>

                                                <polygon
                                                    :points="
                                                        getSalesAreaPoints()
                                                    "
                                                    fill="url(#salesGrad)"
                                                />

                                                <polyline
                                                    fill="none"
                                                    stroke="#3b82f6"
                                                    stroke-width="3"
                                                    vector-effect="non-scaling-stroke"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    :points="
                                                        getSalesLinePoints()
                                                    "
                                                />
                                            </svg>
                                        </div>

                                        <div
                                            class="mt-2 flex w-full justify-between"
                                        >
                                            <div
                                                v-for="item in salesOverview"
                                                :key="item.label"
                                                class="flex-1 text-center text-[10px] font-bold"
                                                :class="
                                                    item.sales > 0
                                                        ? 'text-blue-600'
                                                        : 'text-slate-300'
                                                "
                                            >
                                                {{ item.label }}
                                            </div>
                                        </div>

                                        <div
                                            class="mt-0.5 flex w-full justify-between"
                                        >
                                            <div
                                                v-for="item in salesOverview"
                                                :key="`v-${item.label}`"
                                                class="flex-1 text-center text-[10px] font-black"
                                                :class="
                                                    item.sales > 0
                                                        ? 'text-slate-700'
                                                        : 'text-slate-200'
                                                "
                                            >
                                                {{
                                                    item.sales > 0
                                                        ? formatCompactCurrency(
                                                              item.sales,
                                                          )
                                                        : "·"
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm 2xl:col-span-3"
                        >
                            <h2
                                class="mb-1 text-lg font-semibold text-slate-800"
                            >
                                Alerts
                            </h2>
                            <p class="mb-5 text-sm text-slate-500">
                                Upcoming deadlines and low stocks
                            </p>

                            <div v-if="upcomingDeadlines.length > 0">
                                <p
                                    class="mb-3 text-sm font-semibold text-red-600"
                                >
                                    ⚠ Upcoming Deadlines
                                </p>

                                <div
                                    class="max-h-48 space-y-3 overflow-y-auto pr-1"
                                >
                                    <div
                                        v-for="order in upcomingDeadlines"
                                        :key="order.id"
                                        class="rounded-2xl bg-red-50 p-4"
                                    >
                                        <p
                                            class="text-sm font-semibold text-slate-800"
                                        >
                                            {{ order.customer }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            Deadline:
                                            {{ formatDate(order.deadline) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="lowStocks.length > 0" class="mt-5">
                                <p
                                    class="mb-3 text-sm font-semibold text-yellow-600"
                                >
                                    ⚠ Low Stocks
                                </p>

                                <div
                                    class="max-h-48 space-y-3 overflow-y-auto pr-1"
                                >
                                    <div
                                        v-for="item in lowStocks"
                                        :key="item.id"
                                        class="rounded-2xl bg-yellow-50 p-4"
                                    >
                                        <p
                                            class="text-sm font-semibold text-slate-800"
                                        >
                                            {{ item.name }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            Stock: {{ item.stock }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <p
                                v-if="
                                    upcomingDeadlines.length === 0 &&
                                    lowStocks.length === 0
                                "
                                class="text-sm text-slate-400"
                            >
                                No alerts found
                            </p>
                        </div>
                    </div>

                    <div
                        class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
                    >
                        <div class="border-b border-slate-200 px-6 py-5">
                            <h2 class="text-lg font-semibold text-slate-800">
                                Recent Orders
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Latest order records from your system
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-slate-50">
                                    <tr
                                        class="text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500"
                                    >
                                        <th class="px-6 py-4">Date Created</th>
                                        <th class="px-6 py-4">Deadline</th>
                                        <th class="px-6 py-4">Customer</th>
                                        <th class="px-6 py-4">Product</th>
                                        <th class="px-6 py-4">Total Amount</th>
                                        <th class="px-6 py-4">
                                            Payment Status
                                        </th>
                                        <th class="px-6 py-4">Order Status</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    <tr
                                        v-for="order in recentOrders"
                                        :key="order.id"
                                        class="text-sm transition hover:bg-slate-50"
                                    >
                                        <td class="px-6 py-4 text-slate-600">
                                            {{ formatDate(order.created_at) }}
                                        </td>

                                        <td class="px-6 py-4 text-slate-600">
                                            {{
                                                order.deadline
                                                    ? formatDate(order.deadline)
                                                    : "No deadline"
                                            }}
                                        </td>

                                        <td
                                            class="px-6 py-4 font-medium text-slate-800"
                                        >
                                            {{ order.customer }}
                                        </td>

                                        <td class="px-6 py-4 text-slate-600">
                                            {{ order.product }}
                                        </td>

                                        <td
                                            class="px-6 py-4 font-semibold text-green-600"
                                        >
                                            {{
                                                formatPrice(
                                                    (Number(order.price) || 0) *
                                                        (Number(order.qty) ||
                                                            0),
                                                )
                                            }}
                                        </td>

                                        <td class="px-6 py-4">
                                            <span
                                                class="rounded-full px-3 py-1 text-xs font-medium"
                                                :class="
                                                    paymentStatusClass(
                                                        getPaymentStatus(order),
                                                    )
                                                "
                                            >
                                                {{ getPaymentStatus(order) }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4">
                                            <span
                                                class="rounded-full px-3 py-1 text-xs font-medium"
                                                :class="
                                                    statusClass(order.status)
                                                "
                                            >
                                                {{ order.status }}
                                            </span>
                                        </td>
                                    </tr>

                                    <tr v-if="recentOrders.length === 0">
                                        <td
                                            colspan="7"
                                            class="px-6 py-8 text-center text-slate-500"
                                        >
                                            No orders found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
