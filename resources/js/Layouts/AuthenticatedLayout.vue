<script setup>
import { ref } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/vue3";

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <!-- Fixed Sidebar -->
            <aside
                class="fixed left-0 top-0 hidden h-screen w-64 flex-col border-r border-gray-200 bg-white sm:flex"
            >
                <!-- Logo -->
                <div
                    class="flex h-16 shrink-0 items-center border-b border-gray-200 px-6"
                >
                    <Link :href="route('dashboard')">
                        <ApplicationLogo
                            class="block h-10 w-auto fill-current text-gray-800"
                        />
                    </Link>
                </div>

                <!-- Navigation -->
                <div class="flex h-full flex-col px-4 py-6">
                    <!-- Top Links -->
                    <div class="space-y-1">
                        <Link
                            :href="route('dashboard')"
                            class="block rounded-md px-3 py-2 text-sm font-medium transition"
                            :class="
                                route().current('dashboard')
                                    ? 'bg-gray-100 text-indigo-700'
                                    : 'text-gray-700 hover:bg-gray-50'
                            "
                        >
                            Dashboard
                        </Link>

                        <Link
                            :href="route('orders.index')"
                            class="block rounded-md px-3 py-2 text-sm font-medium transition"
                            :class="
                                route().current('orders.index')
                                    ? 'bg-gray-100 text-indigo-700'
                                    : 'text-gray-700 hover:bg-gray-50'
                            "
                        >
                            Orders
                        </Link>

                        <Link
                            :href="route('supplies.index')"
                            class="block rounded-md px-3 py-2 text-sm font-medium transition"
                            :class="
                                route().current('supplies.index')
                                    ? 'bg-gray-100 text-indigo-700'
                                    : 'text-gray-700 hover:bg-gray-50'
                            "
                        >
                            Supplies
                        </Link>

                        <Link
                            :href="route('reports.index')"
                            class="block rounded-md px-3 py-2 text-sm font-medium transition"
                            :class="
                                route().current('reports.index')
                                    ? 'bg-gray-100 text-indigo-700'
                                    : 'text-gray-700 hover:bg-gray-50'
                            "
                        >
                            Reports
                        </Link>
                    </div>

                    <!-- Bottom Links -->
                    <div
                        class="mt-auto space-y-1 border-t border-gray-200 pt-4"
                    >
                        <Link
                            :href="route('profile.edit')"
                            class="block rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                            :class="
                                route().current('profile.edit')
                                    ? 'bg-gray-100 text-indigo-700'
                                    : 'text-gray-700 hover:bg-gray-50'
                            "
                        >
                            Profile
                        </Link>

                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="block w-full rounded-md px-3 py-2 text-left text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            Log Out
                        </Link>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="sm:ml-64">
                <!-- Mobile Top Bar -->
                <nav class="border-b border-gray-100 bg-white sm:hidden">
                    <div class="mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex h-16 justify-between">
                            <div class="flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-10 w-auto fill-current text-gray-800"
                                    />
                                </Link>
                            </div>

                            <div class="-me-2 flex items-center">
                                <button
                                    @click="
                                        showingNavigationDropdown =
                                            !showingNavigationDropdown
                                    "
                                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                                >
                                    <svg
                                        class="h-6 w-6"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            :class="{
                                                hidden: showingNavigationDropdown,
                                                'inline-flex':
                                                    !showingNavigationDropdown,
                                            }"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16"
                                        />
                                        <path
                                            :class="{
                                                hidden: !showingNavigationDropdown,
                                                'inline-flex':
                                                    showingNavigationDropdown,
                                            }"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Menu -->
                    <div
                        :class="{
                            block: showingNavigationDropdown,
                            hidden: !showingNavigationDropdown,
                        }"
                    >
                        <div class="space-y-1 pb-3 pt-2">
                            <ResponsiveNavLink
                                :href="route('dashboard')"
                                :active="route().current('dashboard')"
                            >
                                Dashboard
                            </ResponsiveNavLink>

                            <ResponsiveNavLink
                                :href="route('orders.index')"
                                :active="route().current('orders.index')"
                            >
                                Orders
                            </ResponsiveNavLink>

                            <ResponsiveNavLink
                                :href="route('supplies.index')"
                                :active="route().current('supplies.index')"
                            >
                                Supplies
                            </ResponsiveNavLink>

                            <ResponsiveNavLink
                                :href="route('reports.index')"
                                :active="route().current('reports.index')"
                            >
                                Reports
                            </ResponsiveNavLink>

                            <ResponsiveNavLink
                                :href="route('profile.edit')"
                                :active="route().current('profile.edit')"
                            >
                                Profile
                            </ResponsiveNavLink>

                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </nav>

                <!-- Header -->
                <header class="bg-white shadow" v-if="$slots.header">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
