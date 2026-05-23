<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    laravelVersion: string;
    phpVersion: string;
}>();
</script>

<template>

    <Head title="Aplikasi Pencatatan Keuangan" />

    <div class="min-h-screen bg-[#0d1117] text-[#e6edf3] font-sans flex flex-col">
        <!-- Top Navigation -->
        <nav class="bg-[#161b22] border-b border-[#30363d] px-4 py-3 sm:px-6 lg:px-8 flex items-center justify-between animate-fade-in-up">
            <div class="flex items-center gap-3">
                <svg class="h-6 w-6 text-[#e6edf3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-lg font-semibold text-[#e6edf3]">{{ $page.props.app_settings?.app_name || 'ArthaLedger' }}</span>
            </div>
            <div class="flex items-center gap-3">
                <div v-if="canLogin">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                        class="text-sm font-medium text-[#e6edf3] hover:text-[#58a6ff] transition-colors duration-150">
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="route('login')"
                            class="text-sm font-medium text-[#e6edf3] hover:text-[#58a6ff] transition-colors duration-150 mr-4">
                            Masuk
                        </Link>
                        <Link v-if="canRegister" :href="route('register')"
                            class="inline-flex items-center justify-center rounded-md border border-[#30363d] bg-[#21262d] px-3 py-1.5 text-sm font-medium text-[#e6edf3] transition-colors duration-150 hover:bg-[#30363d]">
                            Daftar
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main
            class="flex-1 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-12 lg:py-24 flex flex-col lg:flex-row items-center gap-12 lg:gap-16">

            <!-- Left Side: Copy & Actions -->
            <div class="flex-1 text-left animate-fade-in-up delay-100">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-[#e6edf3] mb-4">
                    Pencatatan Keuangan & <br /> Profit/Loss Report
                </h1>

                <p class="text-[#8b949e] text-base lg:text-lg mb-8 max-w-xl leading-relaxed">
                    Kelola kategori COA, transaksi debit/kredit, dan laporan laba rugi bulanan dalam satu dashboard
                    sederhana.
                </p>

                <!-- Bullet Points -->
                <ul class="space-y-3 mb-8 text-[#8b949e] text-sm lg:text-base">
                    <li class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-[#238636] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Master COA berbasis kategori income dan expense</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-[#238636] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Validasi debit/credit otomatis</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-[#238636] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Laporan Profit/Loss bulanan dengan export Excel</span>
                    </li>
                </ul>

                <div v-if="canLogin" class="flex flex-wrap items-center gap-4">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                        class="inline-flex items-center justify-center rounded-md bg-[#238636] px-4 py-2 text-sm font-medium text-white transition-colors duration-150 hover:bg-[#2ea043]">
                        Buka Dashboard
                    </Link>

                    <template v-else>
                        <Link :href="route('login')"
                            class="inline-flex items-center justify-center rounded-md bg-[#238636] px-4 py-2 text-sm font-medium text-white transition-colors duration-150 hover:bg-[#2ea043]">
                            Buka Dashboard
                        </Link>

                        <Link :href="route('login')"
                            class="inline-flex items-center justify-center rounded-md border border-[#30363d] bg-[#21262d] px-4 py-2 text-sm font-medium text-[#e6edf3] transition-colors duration-150 hover:bg-[#30363d]">
                            Lihat Laporan
                        </Link>
                    </template>
                </div>
            </div>

            <!-- Right Side: Preview Card -->
            <div class="flex-1 w-full max-w-xl animate-fade-in-up delay-200">
                <div class="rounded-md border border-[#30363d] bg-[#161b22] p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-[#e6edf3]">Profit/Loss Summary</h2>
                        <span class="text-xs text-[#8b949e]">Tahun 2022</span>
                    </div>

                    <!-- 3 Stat Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <!-- Income -->
                        <div class="rounded-md border border-[#30363d] bg-[#0d1117] p-3">
                            <div class="text-xs text-[#8b949e] mb-1">Total Income</div>
                            <div class="text-sm font-bold text-[#3fb950]">Rp 17.500.000</div>
                        </div>
                        <!-- Expense -->
                        <div class="rounded-md border border-[#30363d] bg-[#0d1117] p-3">
                            <div class="text-xs text-[#8b949e] mb-1">Total Expense</div>
                            <div class="text-sm font-bold text-[#ff7b72]">Rp 850.000</div>
                        </div>
                        <!-- Net -->
                        <div class="rounded-md border border-[#30363d] bg-[#0d1117] p-3">
                            <div class="text-xs text-[#8b949e] mb-1">Net Income</div>
                            <div class="text-sm font-bold text-[#e6edf3]">Rp 16.650.000</div>
                        </div>
                    </div>

                    <!-- Mini Table -->
                    <div class="overflow-hidden rounded-md border border-[#30363d] bg-[#0d1117]">
                        <table class="w-full text-left text-sm text-[#e6edf3]">
                            <thead class="bg-[#21262d] text-[#8b949e] text-xs font-semibold uppercase">
                                <tr>
                                    <th scope="col" class="px-4 py-2 border-b border-[#30363d]">Category</th>
                                    <th scope="col" class="px-4 py-2 border-b border-[#30363d] text-right">Jan 2022</th>
                                    <th scope="col" class="px-4 py-2 border-b border-[#30363d] text-right">Feb 2022</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#30363d]">
                                <tr class="hover:bg-[#161b22] transition-colors duration-150">
                                    <td class="px-4 py-2">Salary</td>
                                    <td class="px-4 py-2 text-right">12.000.000</td>
                                    <td class="px-4 py-2 text-right">12.000.000</td>
                                </tr>
                                <tr class="hover:bg-[#161b22] transition-colors duration-150">
                                    <td class="px-4 py-2">Other Income</td>
                                    <td class="px-4 py-2 text-right">5.500.000</td>
                                    <td class="px-4 py-2 text-right">6.000.000</td>
                                </tr>
                                <tr class="bg-[#238636]/10 text-[#3fb950] font-medium">
                                    <td class="px-4 py-2">Total Income</td>
                                    <td class="px-4 py-2 text-right">17.500.000</td>
                                    <td class="px-4 py-2 text-right">18.000.000</td>
                                </tr>
                                <tr class="bg-[#f85149]/10 text-[#ff7b72] font-medium">
                                    <td class="px-4 py-2">Total Expense</td>
                                    <td class="px-4 py-2 text-right">850.000</td>
                                    <td class="px-4 py-2 text-right">450.000</td>
                                </tr>
                                <tr class="font-bold border-t border-[#30363d] bg-[#161b22]">
                                    <td class="px-4 py-2">Net Income</td>
                                    <td class="px-4 py-2 text-right">16.650.000</td>
                                    <td class="px-4 py-2 text-right">13.950.000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </main>

        <!-- Footer -->
        <footer class="border-t border-[#30363d] py-6 text-center text-xs text-[#6e7681] animate-fade-in-up delay-300">
            <p>Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})</p>
        </footer>
    </div>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
    opacity: 0;
}
.delay-100 { animation-delay: 100ms; }
.delay-200 { animation-delay: 200ms; }
.delay-300 { animation-delay: 300ms; }
</style>
