<script setup lang="ts">
import { ref, watch, onMounted, reactive, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';

const props = defineProps<{
    transactions: any;
    coas: any[];
    categories: any[];
    allowedCurrencies: any[];
    exchangeRates: Record<string, { rate_to_idr: number; fetched_at: string }>;
    isRateStale: boolean;
    filters: any;
}>();

// Custom Debounce Function
function debounce(func: Function, wait: number) {
    let timeout: ReturnType<typeof setTimeout>;
    return function (...args: any[]) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

const filterParams = reactive({
    search: props.filters?.search || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    coa_id: props.filters?.coa_id || '',
    category_id: props.filters?.category_id || '',
    sort_by: props.filters?.sort_by || 'date_desc',
    per_page: props.filters?.per_page || 15,
});

const applyFilters = debounce(() => {
    // Minimal 3 karakter untuk pencarian, atau kosong (reset)
    const searchVal = filterParams.search.trim();
    if (searchVal.length > 0 && searchVal.length < 3) return;

    const query = Object.fromEntries(
        Object.entries(filterParams).filter(([_, v]) => v !== '' && v !== null)
    );
    router.get(route('transactions.index'), query, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
}, 500);

watch(filterParams, () => {
    applyFilters();
}, { deep: true });

const resetFilters = () => {
    filterParams.search = '';
    filterParams.start_date = '';
    filterParams.end_date = '';
    filterParams.coa_id = '';
    filterParams.category_id = '';
    filterParams.sort_by = 'date_desc';
    // per_page tetap dibiarkan agar tidak mengganggu pagination
};

// Computed options for CustomSelect
const categoryOptions = computed(() => [
    { value: '', label: 'Semua Kategori' },
    ...props.categories.map(cat => ({ value: cat.id, label: `${cat.name}`, sublabel: cat.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }))
]);

const coaOptions = computed(() => [
    { value: '', label: 'Semua Akun' },
    ...props.coas.map(coa => ({ value: coa.id, label: `[${coa.code}] ${coa.name}`, sublabel: coa.coa_category?.name }))
]);

const sortOptions = [
    { value: 'date_desc', label: 'Tgl. Terbaru' },
    { value: 'date_asc', label: 'Tgl. Terlama' },
    { value: 'expense_highest', label: 'Pengeluaran Tertinggi' },
    { value: 'expense_lowest', label: 'Pengeluaran Terendah' },
    { value: 'income_highest', label: 'Pemasukan Tertinggi' },
    { value: 'income_lowest', label: 'Pemasukan Terendah' },
];

const perPageOptions = [
    { value: 5, label: '5' },
    { value: 10, label: '10' },
    { value: 15, label: '15' },
    { value: 25, label: '25' },
    { value: 50, label: '50' },
];

const coaFormOptions = computed(() => [
    { value: '', label: 'Pilih Akun...' },
    ...props.coas.map(coa => ({ value: coa.id, label: `[${coa.code}] ${coa.name}`, sublabel: coa.coa_category?.name }))
]);

// Currency options for the form
const currencyOptions = computed(() => [
    { value: 'IDR', label: 'IDR', sublabel: 'Rupiah (Base)' },
    ...props.allowedCurrencies.map(c => ({
        value: c.code,
        label: c.code,
        sublabel: c.name
    }))
]);

const formatRupiah = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatForeign = (value: number, code: string) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value) + ' ' + code;
};

const showCreateForm = ref(false);
const confirmingDeletion = ref(false);
const itemToDelete = ref<number | null>(null);

const confirmDeletion = (id: number) => {
    itemToDelete.value = id;
    confirmingDeletion.value = true;
};

const closeModal = () => {
    confirmingDeletion.value = false;
    itemToDelete.value = null;
};

const deleteItem = () => {
    if (itemToDelete.value) {
        router.delete(route('transactions.destroy', itemToDelete.value), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onFinish: () => closeModal(),
        });
    }
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('create') === 'true') {
        setTimeout(() => {
            showCreateForm.value = true;
        }, 300);
    }
});

const form = useForm({
    transaction_date: new Date().toISOString().split('T')[0],
    coa_id: '',
    description: '',
    debit: 0,
    credit: 0,
    original_currency: 'IDR',
    original_amount: 0,
    exchange_rate: 0,
});

const isForeignCurrency = computed(() => form.original_currency !== 'IDR');

const currentRate = computed(() => {
    if (!isForeignCurrency.value) return null;
    const rateData = props.exchangeRates[form.original_currency];
    return rateData ? Number(rateData.rate_to_idr) : null;
});

// Auto-fill exchange_rate when currency or date changes
const fetchHistoricalRate = async (currency: string, date: string) => {
    isFetchingRate.value = true;
    try {
        const response = await axios.post(route('settings.fetch-historical-rate'), {
            currency,
            date
        });
        if (response.data.success && response.data.rate) {
            form.exchange_rate = response.data.rate;
        } else {
            form.exchange_rate = 0;
        }
    } catch (error) {
        console.error('Failed to fetch historical rate:', error);
        form.exchange_rate = 0;
    } finally {
        isFetchingRate.value = false;
    }
};

watch([() => form.original_currency, () => form.transaction_date], ([code, date]) => {
    if (code === 'IDR' || !code) {
        form.original_amount = 0;
        form.exchange_rate = 0;
        return;
    }

    const today = new Date().toISOString().split('T')[0];

    if (date === today || !date) {
        // Use cached latest rate
        const rateData = props.exchangeRates[code];
        form.exchange_rate = rateData ? Number(rateData.rate_to_idr) : 0;
    } else {
        // Fetch historical rate for the backdated transaction
        fetchHistoricalRate(code, date);
    }
});

const convertedPreview = computed(() => {
    if (!isForeignCurrency.value || !form.original_amount || !form.exchange_rate) return null;
    return Math.round(form.original_amount * form.exchange_rate);
});

const isFetchingRate = ref(false);

const fetchRatesFromForm = () => {
    const date = form.transaction_date;
    const today = new Date().toISOString().split('T')[0];

    if (date && date !== today && form.original_currency !== 'IDR') {
        fetchHistoricalRate(form.original_currency, date);
    } else {
        isFetchingRate.value = true;
        router.post(route('settings.fetch-rates'), {}, {
            preserveScroll: true,
            onFinish: () => isFetchingRate.value = false,
        });
    }
};

const nominalIdr = ref(0);

const selectedCoaType = computed(() => {
    if (!form.coa_id) return null;
    const coa = props.coas.find(c => c.id === form.coa_id);
    return coa?.coa_category?.type || null;
});

const coaTypeLabel = computed(() => {
    if (selectedCoaType.value === 'expense') return { text: 'Pengeluaran (Debit)', color: 'text-[#f85149]', bg: 'bg-[#f85149]/10', border: 'border-[#f85149]/30' };
    if (selectedCoaType.value === 'income') return { text: 'Pemasukan (Credit)', color: 'text-[#3fb950]', bg: 'bg-[#3fb950]/10', border: 'border-[#3fb950]/30' };
    return null;
});

const submit = () => {
    let finalValue = 0;
    if (isForeignCurrency.value && convertedPreview.value) {
        finalValue = convertedPreview.value;
    } else {
        finalValue = nominalIdr.value;
    }

    if (selectedCoaType.value === 'expense') {
        form.debit = finalValue;
        form.credit = 0;
    } else if (selectedCoaType.value === 'income') {
        form.credit = finalValue;
        form.debit = 0;
    } else {
        form.debit = finalValue;
    }

    form.post(route('transactions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateForm.value = false;
            form.reset('coa_id', 'description', 'debit', 'credit', 'original_currency', 'original_amount', 'exchange_rate');
            nominalIdr.value = 0;
        },
    });
};
</script>

<template>

    <Head title="Transaksi Keuangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-[#e6edf3]">
                        Transaksi Keuangan
                    </h2>
                    <p class="mt-1 text-sm text-[#8b949e]">
                        Catat dan kelola histori debit & kredit harian.
                    </p>
                </div>
                <button @click="showCreateForm = !showCreateForm"
                    class="rounded-md bg-[#238636] px-3 py-2 text-sm font-medium text-white transition-colors duration-150 hover:bg-[#2ea043]">
                    {{ showCreateForm ? 'Tutup Form' : '+ Catat Transaksi' }}
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Session Messages -->
                <div v-if="$page.props.flash?.success"
                    class="mb-4 rounded-md border border-[#238636]/30 bg-[#238636]/15 p-4 text-[#3fb950] text-sm">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error"
                    class="mb-4 rounded-md border border-[#f85149]/30 bg-[#f85149]/15 p-4 text-[#ff7b72] text-sm">
                    {{ $page.props.flash.error }}
                </div>

                <div class="flex flex-col lg:flex-row gap-6 items-start">
                    <!-- Table Section -->
                    <div :class="showCreateForm ? 'lg:w-2/3' : 'w-full'"
                        class="transition-all duration-300 ease-in-out">

                        <!-- Search & Filter Bar -->
                        <div class="mb-4 bg-[#161b22] border border-[#30363d] rounded-md p-4 shadow-sm">
                            <!-- Search -->
                            <div class="mb-4 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-[#8b949e]" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input type="text" v-model="filterParams.search"
                                    placeholder="Cari catatan deskripsi, nama akun, atau kode COA..."
                                    class="pl-10 w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm placeholder-[#8b949e]">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                <!-- Start Date -->
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase text-[#8b949e] mb-1">Mulai
                                        Tgl</label>
                                    <input type="date" v-model="filterParams.start_date"
                                        class="w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm color-scheme-dark">
                                </div>
                                <!-- End Date -->
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase text-[#8b949e] mb-1">Sampai
                                        Tgl</label>
                                    <input type="date" v-model="filterParams.end_date"
                                        class="w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm color-scheme-dark">
                                </div>
                                <!-- Category -->
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase text-[#8b949e] mb-1">Kategori</label>
                                    <CustomSelect v-model="filterParams.category_id" :options="categoryOptions"
                                        placeholder="Semua Kategori" :searchable="true" />
                                </div>
                                <!-- COA -->
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase text-[#8b949e] mb-1">Akun
                                        COA</label>
                                    <CustomSelect v-model="filterParams.coa_id" :options="coaOptions"
                                        placeholder="Semua Akun" :searchable="true" />
                                </div>
                                <!-- Sort -->
                                <div>
                                    <label
                                        class="block text-[11px] font-semibold uppercase text-[#8b949e] mb-1">Urutkan</label>
                                    <CustomSelect v-model="filterParams.sort_by" :options="sortOptions"
                                        placeholder="Urutkan..." />
                                </div>
                            </div>
                            <div class="mt-3 flex justify-end border-t border-[#30363d] pt-3">
                                <button @click="resetFilters"
                                    class="text-xs text-[#58a6ff] hover:text-[#79c0ff] flex items-center gap-1 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Reset Pencarian
                                </button>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-md border border-[#30363d] bg-[#161b22]">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm text-[#e6edf3]">
                                    <thead
                                        class="bg-[#21262d] text-[#8b949e] text-xs font-semibold uppercase border-b border-[#30363d]">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Tanggal</th>
                                            <th scope="col" class="px-4 py-3">COA / Kategori</th>
                                            <th scope="col" class="px-4 py-3 text-right">Debit</th>
                                            <th scope="col" class="px-4 py-3 text-right">Credit</th>
                                            <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#30363d]">
                                        <tr v-for="tx in transactions.data" :key="tx.id" :class="[
                                            'transition-colors duration-150',
                                            tx.coa?.coa_category?.type === 'income' ? 'bg-[#238636]/5 hover:bg-[#238636]/15' : 'bg-[#f85149]/5 hover:bg-[#f85149]/15'
                                        ]">
                                            <td class="px-4 py-3 whitespace-nowrap text-[#8b949e]">{{
                                                tx.transaction_date }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="font-medium flex items-center gap-2 flex-wrap">
                                                    {{ tx.coa?.name }}
                                                    <span v-if="tx.original_currency && tx.original_currency !== 'IDR'"
                                                        class="px-1.5 py-0.5 rounded-full bg-[#22d2c6]/20 text-[#22d2c6] border border-[#22d2c6]/30 text-[10px] font-semibold tracking-wide"
                                                        title="Transaksi Valas">Valas</span>
                                                    <span v-if="tx.is_restored"
                                                        class="px-1.5 py-0.5 rounded-full bg-[#58a6ff]/20 text-[#58a6ff] border border-[#58a6ff]/30 text-[10px] font-semibold tracking-wide"
                                                        title="Dipulihkan dari arsip">Restored</span>
                                                    <span v-if="tx.is_edited"
                                                        class="px-1.5 py-0.5 rounded-full bg-[#d29922]/20 text-[#d29922] border border-[#d29922]/30 text-[10px] font-semibold tracking-wide"
                                                        title="Telah diedit">Edited</span>
                                                </div>
                                                <div class="text-xs text-[#58a6ff] font-mono mt-0.5">{{ tx.coa?.code }}
                                                    <span class="text-[#8b949e]">({{ tx.coa?.coa_category?.type
                                                    }})</span>
                                                </div>
                                                <div v-if="tx.description" class="text-xs text-[#8b949e] mt-1">{{
                                                    tx.description
                                                }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <div class="font-medium text-[#f85149]">{{ tx.debit > 0 ?
                                                    formatRupiah(tx.debit)
                                                    : '-' }}</div>
                                                <div v-if="tx.original_currency && tx.original_currency !== 'IDR' && tx.debit > 0"
                                                    class="text-[10px] text-[#8b949e] mt-0.5 font-mono">
                                                    {{ formatForeign(tx.original_amount, tx.original_currency) }} × {{
                                                        formatRupiah(tx.exchange_rate) }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <div class="font-medium text-[#3fb950]">{{ tx.credit > 0 ?
                                                    formatRupiah(tx.credit) : '-' }}</div>
                                                <div v-if="tx.original_currency && tx.original_currency !== 'IDR' && tx.credit > 0"
                                                    class="text-[10px] text-[#8b949e] mt-0.5 font-mono">
                                                    {{ formatForeign(tx.original_amount, tx.original_currency) }} × {{
                                                        formatRupiah(tx.exchange_rate) }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-end gap-2">
                                                    <Link :href="route('transactions.edit', tx.id)"
                                                        class="inline-flex items-center rounded bg-[#21262d] px-2.5 py-1.5 text-xs font-medium text-[#c9d1d9] border border-[#30363d] hover:bg-[#30363d] hover:text-[#e6edf3] transition-colors">
                                                        Edit</Link>
                                                    <button @click="confirmDeletion(tx.id)"
                                                        class="inline-flex items-center rounded bg-[#21262d] px-2.5 py-1.5 text-xs font-medium text-[#f85149] border border-[#30363d] hover:bg-[#f85149] hover:text-white transition-colors">Hapus</button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="transactions.data.length === 0">
                                            <td colspan="5" class="px-4 py-8 text-center text-[#8b949e]">Belum ada data
                                                transaksi keuangan.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Advanced Pagination -->
                        <div class="mt-4 flex flex-col sm:flex-row justify-between items-center text-sm text-[#8b949e] gap-4"
                            v-if="transactions.links">
                            <div class="flex items-center gap-2">
                                <span>Tampilkan</span>
                                <div class="w-20">
                                    <CustomSelect v-model="filterParams.per_page" :options="perPageOptions"
                                        :dropUp="true" />
                                </div>
                                <span>data per halaman. (Total: {{ transactions.total }})</span>
                            </div>

                            <div class="flex gap-1 flex-wrap justify-center">
                                <template v-for="(link, index) in transactions.links" :key="index">
                                    <div v-if="link.url === null"
                                        class="rounded border border-[#30363d] bg-[#161b22] px-3 py-1 text-[#8b949e] opacity-50 cursor-not-allowed"
                                        v-html="link.label"></div>
                                    <Link v-else :href="link.url" class="rounded border px-3 py-1 transition-colors"
                                        :class="link.active ? 'bg-[#58a6ff] text-white border-[#58a6ff]' : 'border-[#30363d] bg-[#161b22] text-[#c9d1d9] hover:bg-[#1f2937] hover:text-[#e6edf3]'"
                                        v-html="link.label"></Link>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Create Form Sidebar -->
                    <div v-show="showCreateForm" class="lg:w-1/3 w-full transition-all duration-300 ease-in-out sticky top-6 self-start">
                        <div class="rounded-md border border-[#30363d] bg-[#161b22] p-5 shadow-sm">
                            <h3 class="text-lg font-medium text-[#e6edf3] mb-4 border-b border-[#30363d] pb-2">Catat
                                Transaksi
                                Baru</h3>
                            <form @submit.prevent="submit" class="space-y-4">
                                <div>
                                    <label for="transaction_date"
                                        class="block text-sm font-medium text-[#e6edf3]">Tanggal</label>
                                    <input id="transaction_date" v-model="form.transaction_date" type="date"
                                        class="mt-1 block w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm color-scheme-dark"
                                        required />
                                    <p v-if="form.errors.transaction_date" class="mt-1 text-xs text-[#ff7b72]">{{
                                        form.errors.transaction_date }}</p>
                                </div>

                                <div>
                                    <label for="coa_id" class="block text-sm font-medium text-[#e6edf3]">Pilih
                                        COA</label>
                                    <div class="mt-1 flex items-center gap-2">
                                        <CustomSelect v-model="form.coa_id" :options="coaFormOptions"
                                            placeholder="Pilih Akun..." :searchable="true" />
                                        <Link :href="route('coas.index', { create: 'true' })"
                                            class="flex items-center justify-center min-w-[38px] h-[38px] bg-[#21262d] border border-[#30363d] text-[#c9d1d9] rounded-md hover:bg-[#30363d] hover:text-[#e6edf3] transition-colors"
                                            title="Tambah COA Baru">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                        </Link>
                                    </div>
                                    <div v-if="coaTypeLabel"
                                        class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                                        :class="[coaTypeLabel.bg, coaTypeLabel.color, coaTypeLabel.border]">
                                        {{ coaTypeLabel.text }}
                                    </div>
                                    <p v-if="form.errors.coa_id" class="mt-1 text-xs text-[#ff7b72]">{{
                                        form.errors.coa_id }}
                                    </p>
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-[#e6edf3]">Deskripsi
                                        <span class="text-[#8b949e] font-normal">(opsional)</span></label>
                                    <input id="description" v-model="form.description" type="text"
                                        class="mt-1 block w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm"
                                        placeholder="Catatan..." />
                                    <p v-if="form.errors.description" class="mt-1 text-xs text-[#ff7b72]">{{
                                        form.errors.description }}</p>
                                </div>

                                <!-- Currency Picker -->
                                <div v-if="allowedCurrencies.length > 0">
                                    <label class="block text-sm font-medium text-[#e6edf3]">Mata Uang</label>
                                    <div class="mt-1">
                                        <CustomSelect v-model="form.original_currency" :options="currencyOptions" />
                                    </div>
                                </div>

                                <!-- Foreign Currency Input -->
                                <div v-if="isForeignCurrency"
                                    class="rounded-md border border-[#d29922]/30 bg-[#d29922]/5 p-3 space-y-3">
                                    <!-- Stale rate warning -->
                                    <div v-if="isRateStale" class="flex items-center gap-2 text-xs text-[#d29922]">
                                        <span>⚠️ Rate kedaluwarsa!</span>
                                        <button type="button" @click="fetchRatesFromForm" :disabled="isFetchingRate"
                                            class="px-2 py-0.5 rounded bg-[#d29922]/20 hover:bg-[#d29922]/30 text-[#d29922] border border-[#d29922]/30 transition-colors text-xs">
                                            {{ isFetchingRate ? '⏳' : '🔄' }} Fetch
                                        </button>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-[#d29922]">Nominal Asli ({{
                                            form.original_currency }})</label>
                                        <CurrencyInput v-model="form.original_amount"
                                            class="mt-1 block w-full rounded-md border-[#d29922]/30 bg-[#0d1117] text-[#e6edf3] font-mono shadow-sm focus:border-[#d29922] focus:ring-[#d29922] sm:text-sm" />
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <label class="block text-xs font-medium text-[#d29922]">Rate (1 {{
                                                form.original_currency }} = ? IDR)</label>
                                            <button type="button" @click="fetchRatesFromForm" :disabled="isFetchingRate"
                                                title="Refresh rate"
                                                class="text-[#8b949e] hover:text-[#58a6ff] transition-colors">
                                                <svg :class="{ 'animate-spin': isFetchingRate }" class="h-3.5 w-3.5"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </button>
                                        </div>
                                        <CurrencyInput v-model="form.exchange_rate" readonly
                                            class="mt-1 block w-full rounded-md border-[#d29922]/30 bg-[#0d1117] text-[#e6edf3] font-mono shadow-sm focus:border-[#d29922] focus:ring-[#d29922] sm:text-sm opacity-70 cursor-not-allowed" />
                                    </div>
                                    <!-- Conversion Preview -->
                                    <div v-if="convertedPreview" class="pt-2 border-t border-[#d29922]/20 text-center">
                                        <p class="text-xs text-[#8b949e]">Konversi ke IDR:</p>
                                        <p class="text-lg font-bold font-mono text-[#e6edf3]"
                                            :class="selectedCoaType === 'expense' ? 'text-[#f85149]' : (selectedCoaType === 'income' ? 'text-[#3fb950]' : '')">
                                            {{ formatRupiah(convertedPreview) }}</p>
                                        <p class="text-[10px] text-[#8b949e] mt-0.5">{{ form.original_amount }} {{
                                            form.original_currency }} × {{ formatRupiah(form.exchange_rate) }}</p>
                                    </div>
                                </div>

                                <div v-if="!isForeignCurrency" class="pt-2">
                                    <label for="nominal_idr" class="block text-sm font-medium text-[#e6edf3]">Nominal
                                        (IDR)</label>
                                    <CurrencyInput id="nominal_idr" v-model="nominalIdr"
                                        class="mt-1 block w-full rounded-md border-[#30363d] bg-[#0d1117] font-mono shadow-sm focus:ring-1 sm:text-sm"
                                        :class="selectedCoaType === 'expense' ? 'text-[#f85149] focus:border-[#f85149] focus:ring-[#f85149]' : (selectedCoaType === 'income' ? 'text-[#3fb950] focus:border-[#3fb950] focus:ring-[#3fb950]' : 'text-[#e6edf3] focus:border-[#58a6ff] focus:ring-[#58a6ff]')"
                                        required />
                                    <p v-if="form.errors.debit" class="mt-1 text-xs text-[#ff7b72]">{{ form.errors.debit
                                    }}</p>
                                    <p v-if="form.errors.credit" class="mt-1 text-xs text-[#ff7b72]">{{
                                        form.errors.credit }}
                                    </p>
                                </div>

                                <div class="flex items-center justify-end gap-2 pt-4 border-t border-[#30363d] mt-4">
                                    <button type="button"
                                        @click="showCreateForm = false; form.reset('coa_id', 'description', 'debit', 'credit')"
                                        class="rounded-md border border-[#30363d] bg-[#21262d] px-3 py-1.5 text-sm font-medium text-[#c9d1d9] transition-colors hover:bg-[#30363d] hover:text-[#e6edf3]">
                                        Batal
                                    </button>
                                    <button type="submit" :disabled="form.processing"
                                        class="rounded-md bg-[#238636] px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-[#2ea043] disabled:opacity-50">
                                        Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <Modal :show="confirmingDeletion" @close="closeModal">
            <div class="p-6 bg-[#161b22] border border-[#30363d] rounded-lg">
                <h2 class="text-lg font-medium text-[#e6edf3]">
                    Apakah Anda yakin ingin menghapus transaksi ini?
                </h2>

                <p class="mt-1 text-sm text-[#8b949e]">
                    Setelah transaksi ini dihapus, pencatatan keuangan dan laporan Profit/Loss akan secara otomatis
                    menyesuaikan
                    nilai barunya. Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal"
                        class="bg-[#21262d] text-[#c9d1d9] border-[#30363d] hover:bg-[#30363d] hover:text-[#e6edf3]">
                        Batal
                    </SecondaryButton>

                    <DangerButton class="bg-[#da3633] hover:bg-[#f85149] text-white border-transparent"
                        @click="deleteItem">
                        Ya, Hapus
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
.color-scheme-dark {
    color-scheme: dark;
}
</style>
