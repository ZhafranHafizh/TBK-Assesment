<script setup lang="ts">
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-[#0d1117] text-[#e6edf3] font-sans">
            <nav class="border-b border-[#30363d] bg-[#161b22]">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')" class="flex items-center gap-3">
                                    <svg class="h-6 w-6 text-[#e6edf3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-lg font-semibold text-[#e6edf3] hidden sm:block">ArthaLedger</span>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                                <NavLink :href="route('coa-categories.index')" :active="route().current('coa-categories.*')">
                                    Kategori COA
                                </NavLink>
                                <NavLink :href="route('coas.index')" :active="route().current('coas.*')">
                                    Chart of Account
                                </NavLink>
                                <NavLink :href="route('transactions.index')" :active="route().current('transactions.*')">
                                    Transaksi
                                </NavLink>
                                <NavLink :href="route('report.index')" :active="route().current('report.*')">
                                    Laporan Profit/Loss
                                </NavLink>
                                <NavLink :href="route('archive.index')" :active="route().current('archive.*')">
                                    Arsip
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-[#161b22] px-3 py-2 text-sm font-medium leading-4 text-[#e6edf3] transition duration-150 ease-in-out hover:text-[#58a6ff] focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="bg-[#161b22] border border-[#30363d] rounded-md shadow-lg overflow-hidden w-48 py-1">
                                            <DropdownLink :href="route('profile.edit')" class="text-[#e6edf3] hover:bg-[#1f2937]">
                                                Profile
                                            </DropdownLink>
                                            <DropdownLink :href="route('logout')" method="post" as="button" class="text-[#f85149] hover:bg-[#1f2937]">
                                                Log Out
                                            </DropdownLink>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-[#8b949e] transition duration-150 ease-in-out hover:bg-[#1f2937] hover:text-[#e6edf3] focus:bg-[#1f2937] focus:text-[#e6edf3] focus:outline-none"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden border-t border-[#30363d]">
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')" class="text-[#e6edf3] hover:bg-[#1f2937]">
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('coa-categories.index')" :active="route().current('coa-categories.*')" class="text-[#8b949e] hover:text-[#e6edf3] hover:bg-[#1f2937]">
                            Kategori COA
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('coas.index')" :active="route().current('coas.*')" class="text-[#8b949e] hover:text-[#e6edf3] hover:bg-[#1f2937]">
                            Chart of Account
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('transactions.index')" :active="route().current('transactions.*')" class="text-[#8b949e] hover:text-[#e6edf3] hover:bg-[#1f2937]">
                            Transaksi
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('report.index')" :active="route().current('report.*')" class="text-[#8b949e] hover:text-[#e6edf3] hover:bg-[#1f2937]">
                            Laporan Profit/Loss
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('archive.index')" :active="route().current('archive.*')" class="text-[#8b949e] hover:text-[#e6edf3] hover:bg-[#1f2937]">
                            Arsip
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-[#30363d] pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-[#e6edf3]">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-[#8b949e]">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')" class="text-[#e6edf3] hover:bg-[#1f2937]">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="text-[#f85149] hover:bg-[#1f2937]">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-[#161b22] border-b border-[#30363d]" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <Transition name="page" mode="out-in" appear>
                    <div :key="$page.url">
                        <slot />
                    </div>
                </Transition>
            </main>
        </div>
    </div>
</template>

<style>
.page-enter-active,
.page-leave-active {
    transition: all 0.25s ease-out;
}
.page-enter-from {
    opacity: 0;
    transform: translateY(10px);
}
.page-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
