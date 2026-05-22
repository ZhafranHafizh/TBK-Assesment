<script setup lang="ts">
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import CustomSelect from '@/Components/CustomSelect.vue';

const props = defineProps<{
    reportData: {
        period: { month: number; year: number };
        incomes: any[];
        total_income: number;
        expenses: any[];
        total_expense: number;
        net_income: number;
    };
    filters: {
        month: number;
        year: number;
    };
}>();

const selectedMonth = ref(props.filters.month);
const selectedYear = ref(props.filters.year);

const months = [
    { value: 1, label: 'Januari' }, { value: 2, label: 'Februari' },
    { value: 3, label: 'Maret' }, { value: 4, label: 'April' },
    { value: 5, label: 'Mei' }, { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' }, { value: 8, label: 'Agustus' },
    { value: 9, label: 'September' }, { value: 10, label: 'Oktober' },
    { value: 11, label: 'November' }, { value: 12, label: 'Desember' },
];

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => currentYear - i);

const yearOptions = computed(() => years.map(y => ({ value: y, label: String(y) })));

const applyFilter = () => {
    router.get(route('report.index'), {
        month: selectedMonth.value,
        year: selectedYear.value,
    }, { preserveState: true });
};

const formatRupiah = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const exportExcel = () => {
    window.location.href = route('report.export', {
        month: selectedMonth.value,
        year: selectedYear.value,
    });
};
</script>

<template>
    <Head title="Laporan Profit/Loss" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-[#e6edf3]">
                        Laporan Profit/Loss
                    </h2>
                    <p class="mt-1 text-sm text-[#8b949e]">
                        Ringkasan pendapatan dan beban per bulan.
                    </p>
                </div>
                <div class="flex gap-2">
                    <button 
                        @click="exportExcel"
                        class="rounded-md border border-[#30363d] bg-[#21262d] px-3 py-2 text-sm font-medium text-[#c9d1d9] transition-colors duration-150 hover:bg-[#30363d] hover:text-[#e6edf3] flex items-center gap-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export Excel
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                
                <!-- Filter Section -->
                <div class="mb-6 flex flex-col sm:flex-row gap-4 items-end bg-[#161b22] p-4 rounded-md border border-[#30363d]">
                    <div class="w-full sm:w-48">
                        <label class="block text-xs font-medium text-[#8b949e] mb-1">Bulan</label>
                        <CustomSelect v-model="selectedMonth" :options="months" />
                    </div>
                    <div class="w-full sm:w-32">
                        <label class="block text-xs font-medium text-[#8b949e] mb-1">Tahun</label>
                        <CustomSelect v-model="selectedYear" :options="yearOptions" />
                    </div>
                    <button 
                        @click="applyFilter"
                        class="w-full sm:w-auto rounded-md bg-[#21262d] border border-[#30363d] px-4 py-2 text-sm font-medium text-[#c9d1d9] hover:bg-[#30363d] transition-colors"
                    >
                        Terapkan
                    </button>
                </div>

                <!-- Report Card -->
                <div class="overflow-hidden rounded-md border border-[#30363d] bg-[#161b22] shadow-sm">
                    <!-- Title -->
                    <div class="border-b border-[#30363d] bg-[#21262d] px-6 py-4 text-center">
                        <h3 class="text-lg font-bold uppercase tracking-wider text-[#e6edf3]">Laporan Profit & Loss</h3>
                        <p class="text-sm text-[#8b949e] mt-1">Periode: {{ months.find(m => m.value === reportData.period.month)?.label }} {{ reportData.period.year }}</p>
                    </div>

                    <div class="p-6">
                        <table class="w-full text-left text-sm text-[#e6edf3]">
                            <tbody>
                                <!-- Income Section -->
                                <tr>
                                    <td colspan="2" class="py-3 font-bold text-[#3fb950] border-b border-[#30363d]/50">PENDAPATAN (INCOME)</td>
                                </tr>
                                <tr v-for="inc in reportData.incomes" :key="inc.name" class="group">
                                    <td class="py-2 pl-4 text-[#c9d1d9] group-hover:text-[#e6edf3] transition-colors">{{ inc.name }}</td>
                                    <td class="py-2 text-right font-mono">{{ formatRupiah(inc.total) }}</td>
                                </tr>
                                <tr v-if="reportData.incomes.length === 0">
                                    <td colspan="2" class="py-2 pl-4 text-[#8b949e] italic">Tidak ada transaksi pendapatan.</td>
                                </tr>
                                <tr>
                                    <td class="py-3 pl-4 font-bold text-[#e6edf3] border-t border-[#30363d]/50 border-b border-[#30363d]">Total Pendapatan</td>
                                    <td class="py-3 text-right font-bold font-mono text-[#3fb950] border-t border-[#30363d]/50 border-b border-[#30363d]">{{ formatRupiah(reportData.total_income) }}</td>
                                </tr>

                                <tr><td colspan="2" class="py-4"></td></tr>

                                <!-- Expense Section -->
                                <tr>
                                    <td colspan="2" class="py-3 font-bold text-[#f85149] border-b border-[#30363d]/50">BEBAN (EXPENSE)</td>
                                </tr>
                                <tr v-for="exp in reportData.expenses" :key="exp.name" class="group">
                                    <td class="py-2 pl-4 text-[#c9d1d9] group-hover:text-[#e6edf3] transition-colors">{{ exp.name }}</td>
                                    <td class="py-2 text-right font-mono">{{ formatRupiah(exp.total) }}</td>
                                </tr>
                                <tr v-if="reportData.expenses.length === 0">
                                    <td colspan="2" class="py-2 pl-4 text-[#8b949e] italic">Tidak ada transaksi beban.</td>
                                </tr>
                                <tr>
                                    <td class="py-3 pl-4 font-bold text-[#e6edf3] border-t border-[#30363d]/50 border-b border-[#30363d]">Total Beban</td>
                                    <td class="py-3 text-right font-bold font-mono text-[#f85149] border-t border-[#30363d]/50 border-b border-[#30363d]">{{ formatRupiah(reportData.total_expense) }}</td>
                                </tr>

                                <tr><td colspan="2" class="py-4"></td></tr>

                                <!-- Net Income -->
                                <tr class="bg-[#21262d]">
                                    <td class="py-4 px-4 font-bold text-[#e6edf3] text-base rounded-l-md">LABA BERSIH (NET INCOME)</td>
                                    <td class="py-4 px-4 text-right font-bold text-lg font-mono rounded-r-md" 
                                        :class="reportData.net_income < 0 ? 'text-[#f85149]' : 'text-[#3fb950]'">
                                        {{ formatRupiah(reportData.net_income) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
