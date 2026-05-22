<script setup lang="ts">
import { ref, watch, onMounted, reactive, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';

const props = defineProps<{
    transactions: any;
    coas: any[];
    categories: any[];
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

const formatRupiah = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
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
});

const submit = () => {
    form.post(route('transactions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateForm.value = false;
            // Hanya mereset field tertentu, membiarkan tanggal tetap hari ini
            form.reset('coa_id', 'description', 'debit', 'credit'); 
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
                <button 
                    @click="showCreateForm = !showCreateForm"
                    class="rounded-md bg-[#238636] px-3 py-2 text-sm font-medium text-white transition-colors duration-150 hover:bg-[#2ea043]"
                >
                    {{ showCreateForm ? 'Tutup Form' : '+ Catat Transaksi' }}
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Session Messages -->
                <div v-if="$page.props.flash?.success" class="mb-4 rounded-md border border-[#238636]/30 bg-[#238636]/15 p-4 text-[#3fb950] text-sm">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4 rounded-md border border-[#f85149]/30 bg-[#f85149]/15 p-4 text-[#ff7b72] text-sm">
                    {{ $page.props.flash.error }}
                </div>

                <div class="flex flex-col lg:flex-row gap-6 items-start">
                    <!-- Table Section -->
                    <div :class="showCreateForm ? 'lg:w-2/3' : 'w-full'" class="transition-all duration-300 ease-in-out">
                        
                        <!-- Search & Filter Bar -->
                        <div class="mb-4 bg-[#161b22] border border-[#30363d] rounded-md p-4 shadow-sm">
                            <!-- Search -->
                            <div class="mb-4 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-[#8b949e]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input type="text" v-model="filterParams.search" placeholder="Cari catatan deskripsi, nama akun, atau kode COA..." class="pl-10 w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm placeholder-[#8b949e]">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                <!-- Start Date -->
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase text-[#8b949e] mb-1">Mulai Tgl</label>
                                    <input type="date" v-model="filterParams.start_date" class="w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm color-scheme-dark">
                                </div>
                                <!-- End Date -->
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase text-[#8b949e] mb-1">Sampai Tgl</label>
                                    <input type="date" v-model="filterParams.end_date" class="w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm color-scheme-dark">
                                </div>
                                <!-- Category -->
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase text-[#8b949e] mb-1">Kategori</label>
                                    <CustomSelect v-model="filterParams.category_id" :options="categoryOptions" placeholder="Semua Kategori" :searchable="true" />
                                </div>
                                <!-- COA -->
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase text-[#8b949e] mb-1">Akun COA</label>
                                    <CustomSelect v-model="filterParams.coa_id" :options="coaOptions" placeholder="Semua Akun" :searchable="true" />
                                </div>
                                <!-- Sort -->
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase text-[#8b949e] mb-1">Urutkan</label>
                                    <CustomSelect v-model="filterParams.sort_by" :options="sortOptions" placeholder="Urutkan..." />
                                </div>
                            </div>
                            <div class="mt-3 flex justify-end border-t border-[#30363d] pt-3">
                                <button @click="resetFilters" class="text-xs text-[#58a6ff] hover:text-[#79c0ff] flex items-center gap-1 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Reset Pencarian
                                </button>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-md border border-[#30363d] bg-[#161b22]">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm text-[#e6edf3]">
                                    <thead class="bg-[#21262d] text-[#8b949e] text-xs font-semibold uppercase border-b border-[#30363d]">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Tanggal</th>
                                            <th scope="col" class="px-4 py-3">COA / Kategori</th>
                                            <th scope="col" class="px-4 py-3 text-right">Debit</th>
                                            <th scope="col" class="px-4 py-3 text-right">Credit</th>
                                            <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#30363d]">
                                        <tr v-for="tx in transactions.data" :key="tx.id" 
                                            :class="[
                                                'transition-colors duration-150',
                                                tx.coa?.coa_category?.type === 'income' ? 'bg-[#238636]/5 hover:bg-[#238636]/15' : 'bg-[#f85149]/5 hover:bg-[#f85149]/15'
                                            ]">
                                            <td class="px-4 py-3 whitespace-nowrap text-[#8b949e]">{{ tx.transaction_date }}</td>
                                            <td class="px-4 py-3">
                                                <div class="font-medium flex items-center gap-2 flex-wrap">
                                                    {{ tx.coa?.name }}
                                                    <span v-if="tx.is_restored" class="px-1.5 py-0.5 rounded-full bg-[#58a6ff]/20 text-[#58a6ff] border border-[#58a6ff]/30 text-[10px] font-semibold tracking-wide" title="Dipulihkan dari arsip">Restored</span>
                                                    <span v-if="tx.is_edited" class="px-1.5 py-0.5 rounded-full bg-[#d29922]/20 text-[#d29922] border border-[#d29922]/30 text-[10px] font-semibold tracking-wide" title="Telah diedit">Edited</span>
                                                </div>
                                                <div class="text-xs text-[#58a6ff] font-mono mt-0.5">{{ tx.coa?.code }} 
                                                    <span class="text-[#8b949e]">({{ tx.coa?.coa_category?.type }})</span>
                                                </div>
                                                <div v-if="tx.description" class="text-xs text-[#8b949e] mt-1">{{ tx.description }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-right font-medium text-[#f85149]">{{ tx.debit > 0 ? formatRupiah(tx.debit) : '-' }}</td>
                                            <td class="px-4 py-3 text-right font-medium text-[#3fb950]">{{ tx.credit > 0 ? formatRupiah(tx.credit) : '-' }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-end gap-2">
                                                    <Link :href="route('transactions.edit', tx.id)" class="inline-flex items-center rounded bg-[#21262d] px-2.5 py-1.5 text-xs font-medium text-[#c9d1d9] border border-[#30363d] hover:bg-[#30363d] hover:text-[#e6edf3] transition-colors">Edit</Link>
                                                    <button @click="confirmDeletion(tx.id)" class="inline-flex items-center rounded bg-[#21262d] px-2.5 py-1.5 text-xs font-medium text-[#f85149] border border-[#30363d] hover:bg-[#f85149] hover:text-white transition-colors">Hapus</button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="transactions.data.length === 0">
                                            <td colspan="5" class="px-4 py-8 text-center text-[#8b949e]">Belum ada data transaksi keuangan.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Advanced Pagination -->
                        <div class="mt-4 flex flex-col sm:flex-row justify-between items-center text-sm text-[#8b949e] gap-4" v-if="transactions.links">
                            <div class="flex items-center gap-2">
                                <span>Tampilkan</span>
                                <div class="w-20">
                                    <CustomSelect v-model="filterParams.per_page" :options="perPageOptions" :dropUp="true" />
                                </div>
                                <span>data per halaman. (Total: {{ transactions.total }})</span>
                            </div>
                            
                            <div class="flex gap-1 flex-wrap justify-center">
                                <template v-for="(link, index) in transactions.links" :key="index">
                                    <div 
                                        v-if="link.url === null" 
                                        class="rounded border border-[#30363d] bg-[#161b22] px-3 py-1 text-[#8b949e] opacity-50 cursor-not-allowed"
                                        v-html="link.label"
                                    ></div>
                                    <Link 
                                        v-else
                                        :href="link.url" 
                                        class="rounded border px-3 py-1 transition-colors"
                                        :class="link.active ? 'bg-[#58a6ff] text-white border-[#58a6ff]' : 'border-[#30363d] bg-[#161b22] text-[#c9d1d9] hover:bg-[#1f2937] hover:text-[#e6edf3]'"
                                        v-html="link.label"
                                    ></Link>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Create Form Sidebar -->
                    <div v-show="showCreateForm" class="lg:w-1/3 w-full transition-all duration-300 ease-in-out">
                        <div class="rounded-md border border-[#30363d] bg-[#161b22] p-5 shadow-sm sticky top-6">
                            <h3 class="text-lg font-medium text-[#e6edf3] mb-4 border-b border-[#30363d] pb-2">Catat Transaksi Baru</h3>
                            <form @submit.prevent="submit" class="space-y-4">
                                <div>
                                    <label for="transaction_date" class="block text-sm font-medium text-[#e6edf3]">Tanggal</label>
                                    <input
                                        id="transaction_date"
                                        v-model="form.transaction_date"
                                        type="date"
                                        class="mt-1 block w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm color-scheme-dark"
                                        required
                                    />
                                    <p v-if="form.errors.transaction_date" class="mt-1 text-xs text-[#ff7b72]">{{ form.errors.transaction_date }}</p>
                                </div>

                                <div>
                                    <label for="coa_id" class="block text-sm font-medium text-[#e6edf3]">Pilih COA</label>
                                    <div class="mt-1 flex items-center gap-2">
                                        <CustomSelect v-model="form.coa_id" :options="coaFormOptions" placeholder="Pilih Akun..." :searchable="true" />
                                        <Link
                                            :href="route('coas.index', { create: 'true' })"
                                            class="flex items-center justify-center min-w-[38px] h-[38px] bg-[#21262d] border border-[#30363d] text-[#c9d1d9] rounded-md hover:bg-[#30363d] hover:text-[#e6edf3] transition-colors"
                                            title="Tambah COA Baru"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </Link>
                                    </div>
                                    <p v-if="form.errors.coa_id" class="mt-1 text-xs text-[#ff7b72]">{{ form.errors.coa_id }}</p>
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-[#e6edf3]">Deskripsi <span class="text-[#8b949e] font-normal">(opsional)</span></label>
                                    <input
                                        id="description"
                                        v-model="form.description"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm"
                                        placeholder="Catatan..."
                                    />
                                    <p v-if="form.errors.description" class="mt-1 text-xs text-[#ff7b72]">{{ form.errors.description }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4 pt-2">
                                    <div>
                                        <label for="debit" class="block text-sm font-medium text-[#f85149]">Debit</label>
                                        <input
                                            id="debit"
                                            v-model="form.debit"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#f85149] font-mono shadow-sm focus:border-[#f85149] focus:ring-[#f85149] sm:text-sm"
                                            required
                                        />
                                        <p v-if="form.errors.debit" class="mt-1 text-xs text-[#ff7b72]">{{ form.errors.debit }}</p>
                                    </div>

                                    <div>
                                        <label for="credit" class="block text-sm font-medium text-[#3fb950]">Credit</label>
                                        <input
                                            id="credit"
                                            v-model="form.credit"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#3fb950] font-mono shadow-sm focus:border-[#3fb950] focus:ring-[#3fb950] sm:text-sm"
                                            required
                                        />
                                        <p v-if="form.errors.credit" class="mt-1 text-xs text-[#ff7b72]">{{ form.errors.credit }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-2 pt-4 border-t border-[#30363d] mt-4">
                                    <button
                                        type="button"
                                        @click="showCreateForm = false; form.reset('coa_id', 'description', 'debit', 'credit')"
                                        class="rounded-md border border-[#30363d] bg-[#21262d] px-3 py-1.5 text-sm font-medium text-[#c9d1d9] transition-colors hover:bg-[#30363d] hover:text-[#e6edf3]"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="rounded-md bg-[#238636] px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-[#2ea043] disabled:opacity-50"
                                    >
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
                    Setelah transaksi ini dihapus, pencatatan keuangan dan laporan Profit/Loss akan secara otomatis menyesuaikan nilai barunya. Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal" class="bg-[#21262d] text-[#c9d1d9] border-[#30363d] hover:bg-[#30363d] hover:text-[#e6edf3]">
                        Batal
                    </SecondaryButton>

                    <DangerButton
                        class="bg-[#da3633] hover:bg-[#f85149] text-white border-transparent"
                        @click="deleteItem"
                    >
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
