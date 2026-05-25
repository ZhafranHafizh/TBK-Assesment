<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import CustomSelect from '@/Components/CustomSelect.vue';
import { use } from 'echarts/core';
import { SankeyChart } from 'echarts/charts';
import { TooltipComponent } from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';
import VChart from 'vue-echarts';

use([SankeyChart, TooltipComponent, CanvasRenderer]);
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';
import { Line } from 'vue-chartjs';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const isFabOpen = ref(false);

const props = defineProps<{
    totalIncome: number;
    totalExpense: number;
    netIncome: number;
    recentTransactions: any[];
    trendData: {
        label: string;
        income: number;
        expense: number;
        net: number;
    }[];
    sankeyData: {
        nodes: any[];
        links: any[];
        period: string;
    };
}>();

const sankeyPeriod = ref(props.sankeyData.period);
const activeTab = ref('trend');
const openFaq = ref<number | null>(null);

const toggleFaq = (index: number) => {
    openFaq.value = openFaq.value === index ? null : index;
};

watch(sankeyPeriod, (newPeriod) => {
    router.get(route('dashboard'), { sankey_period: newPeriod }, {
        preserveState: true,
        preserveScroll: true,
        only: ['sankeyData'],
    });
});

const formatRupiah = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const chartData = computed(() => ({
    labels: props.trendData.map(d => d.label),
    datasets: [
        {
            label: 'Net Income',
            backgroundColor: 'rgba(88, 166, 255, 0.15)',
            borderColor: '#58a6ff',
            pointBackgroundColor: '#58a6ff',
            pointBorderColor: '#161b22',
            pointHoverBackgroundColor: '#161b22',
            pointHoverBorderColor: '#58a6ff',
            pointBorderWidth: 2,
            pointHoverBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            fill: true,
            data: props.trendData.map(d => d.net),
            tension: 0.4
        }
    ]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            backgroundColor: 'rgba(22, 27, 34, 0.95)',
            titleColor: '#e6edf3',
            bodyColor: '#c9d1d9',
            borderColor: '#30363d',
            borderWidth: 1,
            padding: 10,
            callbacks: {
                label: function (context: any) {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== null) {
                        label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.parsed.y);
                    }
                    return label;
                }
            }
        }
    },
    scales: {
        y: {
            grid: {
                color: '#21262d',
            },
            ticks: {
                color: '#8b949e',
                callback: function (value: any) {
                    return new Intl.NumberFormat('id-ID', { notation: "compact", compactDisplay: "short" }).format(value);
                }
            }
        },
        x: {
            grid: {
                display: false
            },
            ticks: {
                color: '#8b949e'
            }
        }
    }
};

const sankeyOption = computed(() => {
    return {
        tooltip: {
            trigger: 'item',
            triggerOn: 'mousemove',
            backgroundColor: 'rgba(22, 27, 34, 0.95)',
            borderColor: '#30363d',
            textStyle: {
                color: '#c9d1d9'
            },
            formatter: function (params: any) {
                if (params.dataType === 'node') {
                    return params.name + ': ' + formatRupiah(params.value);
                }
                return params.data.source + ' ➔ ' + params.data.target + '<br/>' + formatRupiah(params.value);
            }
        },
        series: [
            {
                type: 'sankey',
                data: props.sankeyData.nodes,
                links: props.sankeyData.links,
                emphasis: {
                    focus: 'adjacency'
                },
                lineStyle: {
                    color: 'source',
                    curveness: 0.5
                },
                itemStyle: {
                    borderWidth: 1,
                    borderColor: '#30363d'
                },
                label: {
                    color: '#e6edf3',
                    fontFamily: 'Inter, sans-serif',
                }
            }
        ]
    };
});
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-[#e6edf3]">
                    Dashboard
                </h2>
                <p class="mt-1 text-sm text-[#8b949e]">
                    Ringkasan performa keuangan berdasarkan transaksi yang tercatat.
                </p>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- 3 Stat Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <!-- Total Income -->
                    <div
                        class="rounded-md border border-[#30363d] bg-[#161b22] p-5 shadow-sm transition-all duration-150 hover:bg-[#1f2937]">
                        <div class="text-sm font-medium text-[#8b949e] mb-2 flex items-center justify-between">
                            Total Income
                            <svg class="h-5 w-5 text-[#3fb950]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div class="text-2xl font-bold text-[#3fb950]">
                            {{ formatRupiah(totalIncome) }}
                        </div>
                    </div>

                    <!-- Total Expense -->
                    <div
                        class="rounded-md border border-[#30363d] bg-[#161b22] p-5 shadow-sm transition-all duration-150 hover:bg-[#1f2937]">
                        <div class="text-sm font-medium text-[#8b949e] mb-2 flex items-center justify-between">
                            Total Expense
                            <svg class="h-5 w-5 text-[#f85149]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                        </div>
                        <div class="text-2xl font-bold text-[#ff7b72]">
                            {{ formatRupiah(totalExpense) }}
                        </div>
                    </div>

                    <!-- Net Income -->
                    <div
                        class="rounded-md border border-[#30363d] bg-[#161b22] p-5 shadow-sm transition-all duration-150 hover:bg-[#1f2937]">
                        <div class="text-sm font-medium text-[#8b949e] mb-2 flex items-center justify-between">
                            Net Income
                            <svg class="h-5 w-5 text-[#e6edf3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-2xl font-bold text-[#e6edf3]">
                            {{ formatRupiah(netIncome) }}
                        </div>
                    </div>
                </div>

                <!-- 2 Columns Layout for Desktop (Charts + Table) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Left Column: Charts (Takes 2/3 width) -->
                    <div class="lg:col-span-2 flex flex-col">
                        <div class="rounded-md border border-[#30363d] bg-[#161b22] shadow-sm flex flex-col h-full">
                            <!-- Card Header with Tabs -->
                            <div class="flex items-center justify-between border-b border-[#30363d] px-5 py-4">
                                <div class="flex space-x-6">
                                    <button @click="activeTab = 'trend'"
                                        :class="[activeTab === 'trend' ? 'text-[#58a6ff] border-b-2 border-[#58a6ff]' : 'text-[#8b949e] hover:text-[#c9d1d9]', 'pb-4 -mb-4 text-sm font-medium transition-colors']">
                                        Tren Net Income
                                    </button>
                                    <button @click="activeTab = 'sankey'"
                                        :class="[activeTab === 'sankey' ? 'text-[#58a6ff] border-b-2 border-[#58a6ff]' : 'text-[#8b949e] hover:text-[#c9d1d9]', 'pb-4 -mb-4 text-sm font-medium transition-colors']">
                                        Arus Kas (Sankey)
                                    </button>
                                </div>

                                <!-- Sankey Period Filter (Only show when Sankey is active) -->
                                <div v-if="activeTab === 'sankey'" class="w-44">
                                    <CustomSelect v-model="sankeyPeriod" :options="[
                                        { value: '1_month', label: 'Bulan Ini' },
                                        { value: '3_months', label: '3 Bulan Terakhir' },
                                        { value: '1_year', label: 'Tahun Ini' },
                                        { value: '5_years', label: '5 Tahun Terakhir' },
                                        { value: 'all_time', label: 'Sepanjang Waktu' },
                                    ]" />
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="p-5 flex-1 flex flex-col min-h-[400px]">
                                <!-- Trend Chart -->
                                <div v-show="activeTab === 'trend'"
                                    class="flex-1 rounded-md flex items-center justify-center relative overflow-hidden pt-4"
                                    style="min-height: 400px;">
                                    <Line v-if="trendData && trendData.length > 0" :data="chartData"
                                        :options="chartOptions" />
                                    <div v-else class="text-center">
                                        <svg class="mx-auto h-8 w-8 text-[#8b949e] mb-2" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        <p class="text-sm text-[#8b949e]">Data visualisasi tren belum tersedia.</p>
                                    </div>
                                </div>

                                <!-- Sankey Chart -->
                                <div v-if="activeTab === 'sankey'" class="flex-1 w-full relative overflow-hidden"
                                    style="min-height: 400px;">
                                    <v-chart v-if="sankeyData && sankeyData.nodes && sankeyData.nodes.length > 0"
                                        class="w-full h-full min-h-[400px]" :option="sankeyOption" autoresize />
                                    <div v-else class="absolute inset-0 flex items-center justify-center">
                                        <p class="text-sm text-[#8b949e]">Data transaksi tidak tersedia untuk periode
                                            ini.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Recent Transactions (Takes 1/3 width) -->
                    <div class="lg:col-span-1">
                        <div class="rounded-md border border-[#30363d] bg-[#161b22] shadow-sm overflow-hidden h-full">
                            <div class="p-4 border-b border-[#30363d] flex items-center justify-between">
                                <h3 class="text-base font-semibold text-[#e6edf3]">Transaksi Terbaru</h3>
                            </div>

                            <div class="divide-y divide-[#30363d]">
                                <template v-if="recentTransactions && recentTransactions.length > 0">
                                    <div v-for="tx in recentTransactions" :key="tx.id"
                                        class="p-4 hover:bg-[#1f2937] transition-colors duration-150">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-sm font-medium text-[#e6edf3] truncate max-w-[150px]">
                                                {{ tx.coa?.name || 'Unknown COA' }}
                                            </span>
                                            <span class="text-sm font-medium whitespace-nowrap"
                                                :class="tx.coa?.coa_category?.type === 'income' ? 'text-[#3fb950]' : 'text-[#ff7b72]'">
                                                {{ tx.coa?.coa_category?.type === 'income' ? '+' : '-' }}
                                                {{ formatRupiah(tx.coa?.coa_category?.type === 'income' ? tx.credit :
                                                    tx.debit) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs text-[#8b949e]">
                                            <span class="truncate pr-2">{{ tx.description || 'Tidak ada deskripsi'
                                                }}</span>
                                            <span class="whitespace-nowrap">{{ tx.transaction_date }}</span>
                                        </div>
                                    </div>
                                </template>
                                <div v-else class="p-8 text-center text-sm text-[#8b949e]">
                                    Belum ada transaksi tercatat.
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 pb-12">
            <div class="bg-[#161b22] border border-[#30363d] rounded-xl p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-[#e6edf3] mb-6 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#58a6ff]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Frequently Asked Questions (FAQ)
                </h2>
                <div class="flex flex-col gap-4">
                    <!-- FAQ 1 -->
                    <div
                        class="group bg-[#0d1117] border border-[#30363d] rounded-lg hover:border-[#58a6ff] transition-colors overflow-hidden">
                        <button @click="toggleFaq(1)"
                            class="w-full text-left font-medium text-[#e6edf3] p-5 cursor-pointer flex justify-between items-center group-hover:text-[#58a6ff] transition-colors focus:outline-none">
                            1. Bagaimana alur penggunaan?
                            <span class="transition-transform duration-300" :class="{ '-rotate-180': openFaq === 1 }">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>
                        <div class="grid transition-all duration-300 ease-in-out"
                            :class="openFaq === 1 ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'">
                            <div class="overflow-hidden">
                                <div class="px-5 pb-5 pt-0">
                                    <p
                                        class="text-sm text-[#8b949e] leading-relaxed border-t border-[#30363d] pt-4 mt-1">
                                        Sistem ini berbasis Chart of Account (COA). Alur utamanya adalah: Anda membuat
                                        <strong>Kategori COA</strong> (Income/Expense) &rarr; Membuat <strong>Akun
                                            COA</strong>
                                        &rarr; Mencatat <strong>Transaksi</strong>. Laporan Laba/Rugi (Profit/Loss) akan
                                        otomatis digenerate.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div
                        class="group bg-[#0d1117] border border-[#30363d] rounded-lg hover:border-[#58a6ff] transition-colors overflow-hidden">
                        <button @click="toggleFaq(2)"
                            class="w-full text-left font-medium text-[#e6edf3] p-5 cursor-pointer flex justify-between items-center group-hover:text-[#58a6ff] transition-colors focus:outline-none">
                            2. Apakah integritas data terjaga?
                            <span class="transition-transform duration-300" :class="{ '-rotate-180': openFaq === 2 }">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>
                        <div class="grid transition-all duration-300 ease-in-out"
                            :class="openFaq === 2 ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'">
                            <div class="overflow-hidden">
                                <div class="px-5 pb-5 pt-0">
                                    <p
                                        class="text-sm text-[#8b949e] leading-relaxed border-t border-[#30363d] pt-4 mt-1">
                                        Sangat terjaga. Kami menerapkan <em>auto-routing</em> Debit/Credit, nilai tukar
                                        mata
                                        uang yang dikunci otomatis dari API, validasi server yang ketat, serta
                                        penguncian data
                                        (Lock Period) setelah 24 jam untuk mencegah manipulasi.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div
                        class="group bg-[#0d1117] border border-[#30363d] rounded-lg hover:border-[#58a6ff] transition-colors overflow-hidden">
                        <button @click="toggleFaq(3)"
                            class="w-full text-left font-medium text-[#e6edf3] p-5 cursor-pointer flex justify-between items-center group-hover:text-[#58a6ff] transition-colors focus:outline-none">
                            3. Apa yang terjadi jika saya menghapus transaksi?
                            <span class="transition-transform duration-300" :class="{ '-rotate-180': openFaq === 3 }">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>
                        <div class="grid transition-all duration-300 ease-in-out"
                            :class="openFaq === 3 ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'">
                            <div class="overflow-hidden">
                                <div class="px-5 pb-5 pt-0">
                                    <p
                                        class="text-sm text-[#8b949e] leading-relaxed border-t border-[#30363d] pt-4 mt-1">
                                        Data tidak akan benar-benar terhapus secara fisik dari database (<em>hard
                                            delete</em>).
                                        Sistem menggunakan mekanisme <em>soft-delete</em> yang menyembunyikan data
                                        tersebut demi
                                        mempertahankan <strong>jejak audit</strong> (audit trail).
                                        Laporan transaksi tersebut juga akan langsung otomatis tergenerate sebagai
                                        sebuah report
                                        dalam bentuk PDF di halaman <strong>Arsip</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div
                        class="group bg-[#0d1117] border border-[#30363d] rounded-lg hover:border-[#58a6ff] transition-colors overflow-hidden">
                        <button @click="toggleFaq(4)"
                            class="w-full text-left font-medium text-[#e6edf3] p-5 cursor-pointer flex justify-between items-center group-hover:text-[#58a6ff] transition-colors focus:outline-none">
                            4. Kenapa tidak bisa edit Kategori/COAs?
                            <span class="transition-transform duration-300" :class="{ '-rotate-180': openFaq === 4 }">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>
                        <div class="grid transition-all duration-300 ease-in-out"
                            :class="openFaq === 4 ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'">
                            <div class="overflow-hidden">
                                <div class="px-5 pb-5 pt-0">
                                    <p
                                        class="text-sm text-[#8b949e] leading-relaxed border-t border-[#30363d] pt-4 mt-1">
                                        Jika usia pembuatan data Master (Kategori atau COA) sudah melewati 24 jam,
                                        sistem akan
                                        menguncinya (<em>lock</em>) secara otomatis. Hal ini untuk mematuhi kaidah
                                        pembukuan
                                        yang sah dan mencegah terjadinya perubahan paksa pada histori data.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Action Button -->
        <div class="fixed bottom-8 right-8 z-50">
            <!-- Main Button -->
            <button @click="isFabOpen = !isFabOpen"
                class="flex items-center justify-center w-14 h-14 bg-[#238636] text-white rounded-full shadow-[0_4px_14px_0_rgba(35,134,54,0.39)] hover:bg-[#2ea043] transition-all duration-200 focus:outline-none"
                :class="{ 'scale-105': isFabOpen }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transform transition-transform duration-200"
                    :class="{ 'rotate-45': isFabOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>

            <!-- Menu Options (Appears on click) -->
            <div v-show="isFabOpen" class="absolute bottom-16 right-0 flex-col items-end gap-3 mb-2 flex">
                <!-- Transaksi -->
                <Link :href="route('transactions.index', { create: 'true' })"
                    class="flex items-center gap-2 group/item">
                    <span
                        class="bg-[#21262d] text-[#c9d1d9] text-sm py-1 px-3 rounded-md shadow-sm border border-[#30363d] whitespace-nowrap opacity-0 group-hover/item:opacity-100 transition-opacity">Tambah
                        Transaksi</span>
                    <div
                        class="flex items-center justify-center w-10 h-10 bg-[#21262d] border border-[#30363d] text-[#c9d1d9] rounded-full shadow hover:bg-[#30363d] hover:text-[#e6edf3] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </Link>

                <!-- COA -->
                <Link :href="route('coas.index', { create: 'true' })" class="flex items-center gap-2 group/item">
                    <span
                        class="bg-[#21262d] text-[#c9d1d9] text-sm py-1 px-3 rounded-md shadow-sm border border-[#30363d] whitespace-nowrap opacity-0 group-hover/item:opacity-100 transition-opacity">Tambah
                        COA</span>
                    <div
                        class="flex items-center justify-center w-10 h-10 bg-[#21262d] border border-[#30363d] text-[#c9d1d9] rounded-full shadow hover:bg-[#30363d] hover:text-[#e6edf3] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                </Link>

                <!-- Kategori COA -->
                <Link :href="route('coa-categories.index', { create: 'true' })"
                    class="flex items-center gap-2 group/item">
                    <span
                        class="bg-[#21262d] text-[#c9d1d9] text-sm py-1 px-3 rounded-md shadow-sm border border-[#30363d] whitespace-nowrap opacity-0 group-hover/item:opacity-100 transition-opacity">Tambah
                        Kategori COA</span>
                    <div
                        class="flex items-center justify-center w-10 h-10 bg-[#21262d] border border-[#30363d] text-[#c9d1d9] rounded-full shadow hover:bg-[#30363d] hover:text-[#e6edf3] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </Link>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
