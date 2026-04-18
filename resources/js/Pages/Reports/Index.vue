<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({
            todaySale: 0,
            todayTotalOrders: 0,
            monthlySales: 0,
            monthlyExpense: 0,
        }),
    },
    orderStatistics: {
        type: Array,
        default: () => [],
    },
    salesOverview: {
        type: Array,
        default: () => [],
    },
    expenseOverview: {
        type: Array,
        default: () => [],
    },
    expenseDetails: {
        type: Array,
        default: () => [],
    },
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 2,
    }).format(Number(value || 0));
};

const formatCompactCurrency = (value) => {
    const number = Number(value || 0);
    if (number >= 1000000) return `₱${(number / 1000000).toFixed(1)}M`;
    if (number >= 1000) return `₱${(number / 1000).toFixed(1)}k`;
    return `₱${number.toFixed(0)}`;
};

const normalizedOrderStatistics = computed(() => {
    const orderMap = new Map(
        props.orderStatistics.map((item) => [
            item.label,
            Number(item.orders || 0),
        ]),
    );

    if (props.salesOverview.length) {
        return props.salesOverview.map((item) => ({
            label: item.label,
            orders: orderMap.get(item.label) ?? 0,
        }));
    }

    return props.orderStatistics.map((item) => ({
        label: item.label,
        orders: Number(item.orders || 0),
    }));
});

const totalOrders = computed(() =>
    normalizedOrderStatistics.value.reduce(
        (total, item) => total + Number(item.orders || 0),
        0,
    ),
);

const maxOrders = computed(() =>
    Math.max(
        ...normalizedOrderStatistics.value.map((o) => Number(o.orders || 0)),
        1,
    ),
);

const maxSales = computed(() =>
    Math.max(...props.salesOverview.map((s) => Number(s.sales || 0)), 1),
);

const maxExpenses = computed(() =>
    Math.max(...props.expenseOverview.map((e) => Number(e.expense || 0)), 1),
);

const chartWidth = 1200;
const chartHeight = 300;

const getSalesLinePoints = () => {
    if (!props.salesOverview.length) return "";
    if (props.salesOverview.length === 1) {
        const item = props.salesOverview[0];
        const y =
            chartHeight -
            (Number(item.sales || 0) / maxSales.value) * chartHeight;
        return `${chartWidth / 2},${y}`;
    }

    return props.salesOverview
        .map((item, index) => {
            const x = (index * chartWidth) / (props.salesOverview.length - 1);
            const y =
                chartHeight -
                (Number(item.sales || 0) / maxSales.value) * chartHeight;
            return `${x},${y}`;
        })
        .join(" ");
};

const getSalesAreaPoints = () => {
    if (!props.salesOverview.length) return "";
    const line = getSalesLinePoints();
    return `0,${chartHeight} ${line} ${chartWidth},${chartHeight}`;
};

const getExpenseLinePoints = () => {
    if (!props.expenseOverview.length) return "";
    if (props.expenseOverview.length === 1) {
        const item = props.expenseOverview[0];
        const y =
            chartHeight -
            (Number(item.expense || 0) / maxExpenses.value) * chartHeight;
        return `${chartWidth / 2},${y}`;
    }

    return props.expenseOverview
        .map((item, index) => {
            const x = (index * chartWidth) / (props.expenseOverview.length - 1);
            const y =
                chartHeight -
                (Number(item.expense || 0) / maxExpenses.value) * chartHeight;
            return `${x},${y}`;
        })
        .join(" ");
};

const getExpenseAreaPoints = () => {
    if (!props.expenseOverview.length) return "";
    const line = getExpenseLinePoints();
    return `0,${chartHeight} ${line} ${chartWidth},${chartHeight}`;
};

const salesYAxis = computed(() => {
    const max = maxSales.value;
    return [max, max * 0.75, max * 0.5, max * 0.25, 0];
});

const expenseYAxis = computed(() => {
    const max = maxExpenses.value;
    return [max, max * 0.75, max * 0.5, max * 0.25, 0];
});

const ordersYAxis = computed(() => {
    const max = maxOrders.value;
    return [
        max,
        Math.ceil(max * 0.75),
        Math.ceil(max * 0.5),
        Math.ceil(max * 0.25),
        0,
    ];
});
</script>

<template>
    <Head title="Reports" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-800">
                    Report Overview
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Monitor your printing orders, sales, and supplies.
                </p>
            </div>
        </template>

        <div class="min-h-screen bg-slate-50 py-8">
            <div class="mx-auto w-full max-w-[1800px] px-4 sm:px-6 lg:px-8">
                <div class="space-y-8">
                    <div
                        class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4"
                    >
                        <div
                            class="group relative overflow-hidden rounded-[2rem] border border-slate-100 bg-white p-7 shadow-xl shadow-slate-200/40 transition-all duration-300 hover:border-emerald-200 hover:shadow-emerald-100"
                        >
                            <div
                                class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-emerald-50 opacity-0 transition-opacity group-hover:opacity-100"
                            ></div>

                            <div class="relative">
                                <div
                                    class="mb-4 flex items-center justify-between"
                                >
                                    <p
                                        class="text-[13px] font-bold uppercase tracking-wider text-slate-400"
                                    >
                                        Today's Sale
                                    </p>
                                    <div
                                        class="rounded-xl bg-emerald-50 p-2 text-emerald-600"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <h3
                                    class="text-3xl font-black tracking-tight text-slate-800 transition-colors group-hover:text-emerald-600"
                                >
                                    {{
                                        formatCurrency(props.summary.todaySale)
                                    }}
                                </h3>
                            </div>
                        </div>

                        <div
                            class="group relative overflow-hidden rounded-[2rem] border border-slate-100 bg-white p-7 shadow-xl shadow-slate-200/40 transition-all duration-300 hover:border-blue-200 hover:shadow-blue-100"
                        >
                            <div
                                class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-blue-50 opacity-0 transition-opacity group-hover:opacity-100"
                            ></div>

                            <div class="relative">
                                <div
                                    class="mb-4 flex items-center justify-between"
                                >
                                    <p
                                        class="text-[13px] font-bold uppercase tracking-wider text-slate-400"
                                    >
                                        Today's Total Orders
                                    </p>
                                    <div
                                        class="rounded-xl bg-blue-50 p-2 text-blue-600"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <h3
                                    class="text-3xl font-black tracking-tight text-slate-800 transition-colors group-hover:text-blue-600"
                                >
                                    {{ props.summary.todayTotalOrders }}
                                </h3>
                            </div>
                        </div>

                        <div
                            class="group relative overflow-hidden rounded-[2rem] border border-slate-100 bg-white p-7 shadow-xl shadow-slate-200/40 transition-all duration-300 hover:border-amber-200 hover:shadow-amber-100"
                        >
                            <div
                                class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-amber-50 opacity-0 transition-opacity group-hover:opacity-100"
                            ></div>

                            <div class="relative">
                                <div
                                    class="mb-4 flex items-center justify-between"
                                >
                                    <p
                                        class="text-[13px] font-bold uppercase tracking-wider text-slate-400"
                                    >
                                        Monthly Sales
                                    </p>
                                    <div
                                        class="rounded-xl bg-amber-50 p-2 text-amber-600"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <h3
                                    class="text-3xl font-black tracking-tight text-slate-800 transition-colors group-hover:text-amber-500"
                                >
                                    {{
                                        formatCurrency(
                                            props.summary.monthlySales,
                                        )
                                    }}
                                </h3>
                            </div>
                        </div>

                        <div
                            class="group relative overflow-hidden rounded-[2rem] border border-slate-100 bg-white p-7 shadow-xl shadow-slate-200/40 transition-all duration-300 hover:border-rose-200 hover:shadow-rose-100"
                        >
                            <div
                                class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-rose-50 opacity-0 transition-opacity group-hover:opacity-100"
                            ></div>

                            <div class="relative">
                                <div
                                    class="mb-4 flex items-center justify-between"
                                >
                                    <p
                                        class="text-[13px] font-bold uppercase tracking-wider text-slate-400"
                                    >
                                        Monthly Expense
                                    </p>
                                    <div
                                        class="rounded-xl bg-rose-50 p-2 text-rose-600"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <h3
                                    class="text-3xl font-black tracking-tight text-slate-800 transition-colors group-hover:text-rose-500"
                                >
                                    {{
                                        formatCurrency(
                                            props.summary.monthlyExpense,
                                        )
                                    }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-3xl border border-slate-100 bg-white p-8 shadow-sm transition-all hover:shadow-md"
                    >
                        <div
                            class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <h3
                                    class="text-xl font-bold tracking-tight text-slate-900"
                                >
                                    Order Statistics
                                </h3>
                                <p class="text-sm font-medium text-slate-400">
                                    Daily performance overview for the current
                                    month.
                                </p>
                            </div>

                            <div
                                class="flex flex-col items-end rounded-2xl border border-indigo-100/50 bg-indigo-50/50 px-6 py-3"
                            >
                                <p
                                    class="text-[10px] font-bold uppercase tracking-widest text-indigo-400"
                                >
                                    Total Orders
                                </p>
                                <p class="text-3xl font-black text-indigo-600">
                                    {{ totalOrders }}
                                </p>
                            </div>
                        </div>

                        <div class="relative w-full">
                            <div class="flex gap-4">
                                <div
                                    class="hidden w-6 shrink-0 text-right md:flex md:h-[300px] md:flex-col md:justify-between md:pb-12"
                                >
                                    <p
                                        v-for="(value, index) in ordersYAxis"
                                        :key="`orders-y-${index}`"
                                        class="text-[10px] font-bold text-slate-300"
                                    >
                                        {{ value }}
                                    </p>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="relative h-[300px] w-full">
                                        <div
                                            class="pointer-events-none absolute inset-0 flex flex-col justify-between pb-12"
                                        >
                                            <div
                                                v-for="i in 4"
                                                :key="i"
                                                class="border-t border-slate-100"
                                            ></div>
                                            <div
                                                class="border-t-2 border-slate-200/30"
                                            ></div>
                                        </div>

                                        <div
                                            class="absolute inset-0 flex w-full items-end justify-between gap-1 px-1 pb-12"
                                        >
                                            <div
                                                v-for="item in normalizedOrderStatistics"
                                                :key="item.label"
                                                class="group relative flex h-full flex-1 items-end justify-center"
                                            >
                                                <div
                                                    class="pointer-events-none absolute -top-10 z-10 opacity-0 transition-all duration-200 group-hover:opacity-100"
                                                >
                                                    <div
                                                        class="whitespace-nowrap rounded bg-slate-900 px-2 py-1 text-[10px] font-bold text-white shadow-xl"
                                                    >
                                                        {{ item.orders }}
                                                    </div>
                                                    <div
                                                        class="mx-auto -mt-1 h-2 w-2 rotate-45 bg-slate-900"
                                                    ></div>
                                                </div>

                                                <div
                                                    class="w-full max-w-[12px] rounded-full transition-all duration-500 ease-out"
                                                    :class="
                                                        Number(item.orders) > 0
                                                            ? 'bg-gradient-to-t from-indigo-600 to-violet-400 shadow-[0_4px_10px_rgba(79,70,229,0.3)]'
                                                            : 'bg-slate-100'
                                                    "
                                                    :style="{
                                                        height: `${(Number(item.orders || 0) / (maxOrders || 1)) * 100}%`,
                                                        minHeight:
                                                            Number(
                                                                item.orders,
                                                            ) > 0
                                                                ? '6px'
                                                                : '3px',
                                                    }"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="flex w-full justify-between gap-1 px-1"
                                    >
                                        <div
                                            v-for="item in normalizedOrderStatistics"
                                            :key="item.label"
                                            class="flex-1 text-center text-[9px] font-bold tracking-tighter"
                                            :class="
                                                Number(item.orders) > 0
                                                    ? 'text-indigo-600'
                                                    : 'text-slate-300'
                                            "
                                        >
                                            {{ item.label.split(" ")[0] }}
                                        </div>
                                    </div>

                                    <div
                                        class="mt-1 flex w-full justify-between gap-1 px-1"
                                    >
                                        <div
                                            v-for="item in normalizedOrderStatistics"
                                            :key="`${item.label}-num`"
                                            class="flex-1 text-center text-[10px] font-black"
                                            :class="
                                                Number(item.orders) > 0
                                                    ? 'text-slate-700'
                                                    : 'text-slate-100'
                                            "
                                        >
                                            {{
                                                item.orders > 0
                                                    ? item.orders
                                                    : "·"
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-3xl border border-slate-100 bg-white p-8 shadow-sm"
                    >
                        <div
                            class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <h3
                                    class="text-xl font-bold tracking-tight text-slate-900"
                                >
                                    Sales Overview
                                </h3>
                                <p class="text-sm font-medium text-slate-400">
                                    Daily sales performance.
                                </p>
                            </div>
                            <div
                                class="flex flex-col items-end rounded-2xl border border-indigo-100/50 bg-indigo-50/50 px-6 py-3"
                            >
                                <p
                                    class="text-[10px] font-bold uppercase tracking-widest text-indigo-400"
                                >
                                    Total Sales
                                </p>
                                <p class="text-3xl font-black text-indigo-600">
                                    {{
                                        formatCurrency(
                                            props.summary.monthlySales,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="relative w-full">
                            <div class="flex gap-4">
                                <div
                                    class="hidden w-12 shrink-0 text-right md:flex md:h-[300px] md:flex-col md:justify-between"
                                >
                                    <p
                                        v-for="(value, index) in salesYAxis"
                                        :key="`y-${index}`"
                                        class="text-[10px] font-bold text-slate-300"
                                    >
                                        {{ formatCompactCurrency(value) }}
                                    </p>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="relative h-[300px] w-full">
                                        <div
                                            class="pointer-events-none absolute inset-0 flex flex-col justify-between"
                                        >
                                            <div
                                                v-for="i in 5"
                                                :key="i"
                                                class="w-full border-t border-slate-100"
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
                                                        stop-color="#6366f1"
                                                        stop-opacity="0.2"
                                                    />
                                                    <stop
                                                        offset="100%"
                                                        stop-color="#6366f1"
                                                        stop-opacity="0"
                                                    />
                                                </linearGradient>
                                            </defs>

                                            <polygon
                                                :points="getSalesAreaPoints()"
                                                fill="url(#salesGrad)"
                                            />

                                            <polyline
                                                fill="none"
                                                stroke="#6366f1"
                                                stroke-width="4"
                                                vector-effect="non-scaling-stroke"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                :points="getSalesLinePoints()"
                                            />
                                        </svg>
                                    </div>

                                    <div
                                        class="mt-6 flex w-full justify-between"
                                    >
                                        <div
                                            v-for="item in props.salesOverview"
                                            :key="item.label"
                                            class="flex-1 text-center text-[10px] font-bold"
                                            :class="
                                                item.sales > 0
                                                    ? 'text-indigo-600'
                                                    : 'text-slate-300'
                                            "
                                        >
                                            {{ item.label.split(" ")[0] }}
                                        </div>
                                    </div>

                                    <div
                                        class="mt-1 flex w-full justify-between"
                                    >
                                        <div
                                            v-for="item in props.salesOverview"
                                            :key="`v-${item.label}`"
                                            class="flex-1 text-center text-[10px] font-black"
                                            :class="
                                                item.sales > 0
                                                    ? 'text-slate-700'
                                                    : 'text-slate-100'
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
                        class="rounded-3xl border border-slate-100 bg-white p-8 shadow-sm"
                    >
                        <div
                            class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <h3
                                    class="text-xl font-bold tracking-tight text-slate-900"
                                >
                                    Monthly Expense Overview
                                </h3>
                                <p class="text-sm font-medium text-slate-400">
                                    Expense based on supply price × quantity.
                                </p>
                            </div>

                            <div
                                class="flex flex-col items-end rounded-2xl border border-rose-100/50 bg-rose-50/50 px-6 py-3"
                            >
                                <p
                                    class="text-[10px] font-bold uppercase tracking-widest text-rose-400"
                                >
                                    Total Expense
                                </p>
                                <p class="text-3xl font-black text-rose-600">
                                    {{
                                        formatCurrency(
                                            props.summary.monthlyExpense,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="relative w-full">
                            <div class="flex gap-4">
                                <div
                                    class="hidden w-12 shrink-0 text-right md:flex md:h-[300px] md:flex-col md:justify-between"
                                >
                                    <p
                                        v-for="(value, index) in expenseYAxis"
                                        :key="`y-${index}`"
                                        class="text-[10px] font-bold text-slate-300"
                                    >
                                        {{ formatCompactCurrency(value) }}
                                    </p>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="relative h-[300px] w-full">
                                        <div
                                            class="pointer-events-none absolute inset-0 flex flex-col justify-between"
                                        >
                                            <div
                                                v-for="i in 5"
                                                :key="i"
                                                class="w-full border-t border-slate-100"
                                            ></div>
                                        </div>

                                        <svg
                                            :viewBox="`0 0 ${chartWidth} ${chartHeight}`"
                                            class="absolute inset-0 h-full w-full overflow-visible"
                                            preserveAspectRatio="none"
                                        >
                                            <defs>
                                                <linearGradient
                                                    id="expenseGrad"
                                                    x1="0"
                                                    y1="0"
                                                    x2="0"
                                                    y2="1"
                                                >
                                                    <stop
                                                        offset="0%"
                                                        stop-color="#f43f5e"
                                                        stop-opacity="0.2"
                                                    />
                                                    <stop
                                                        offset="100%"
                                                        stop-color="#f43f5e"
                                                        stop-opacity="0"
                                                    />
                                                </linearGradient>
                                            </defs>

                                            <polygon
                                                :points="getExpenseAreaPoints()"
                                                fill="url(#expenseGrad)"
                                            />

                                            <polyline
                                                fill="none"
                                                stroke="#f43f5e"
                                                stroke-width="4"
                                                vector-effect="non-scaling-stroke"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                :points="getExpenseLinePoints()"
                                            />
                                        </svg>
                                    </div>

                                    <div
                                        class="mt-6 flex w-full justify-between"
                                    >
                                        <div
                                            v-for="item in props.expenseOverview"
                                            :key="item.label"
                                            class="flex-1 text-center text-[10px] font-bold"
                                            :class="
                                                item.expense > 0
                                                    ? 'text-rose-600'
                                                    : 'text-slate-300'
                                            "
                                        >
                                            {{ item.label.split(" ")[0] }}
                                        </div>
                                    </div>

                                    <div
                                        class="mt-1 flex w-full justify-between"
                                    >
                                        <div
                                            v-for="item in props.expenseOverview"
                                            :key="`v-${item.label}`"
                                            class="flex-1 text-center text-[10px] font-black"
                                            :class="
                                                item.expense > 0
                                                    ? 'text-slate-700'
                                                    : 'text-slate-100'
                                            "
                                        >
                                            {{
                                                item.expense > 0
                                                    ? formatCompactCurrency(
                                                          item.expense,
                                                      )
                                                    : "·"
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
