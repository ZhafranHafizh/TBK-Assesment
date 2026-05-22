<script setup lang="ts">
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineProps<{
    categories: any[];
    coas: any[];
    transactions: any[];
    reports: any[];
}>();

const activeTab = ref('transactions');
const confirmingRestore = ref(false);
const itemToRestore = ref<{ type: string, id: number } | null>(null);

const confirmRestore = (type: string, id: number) => {
    itemToRestore.value = { type, id };
    confirmingRestore.value = true;
};

const closeRestoreModal = () => {
    confirmingRestore.value = false;
    itemToRestore.value = null;
};

const restoreItem = () => {
    if (itemToRestore.value) {
        router.post(route('archive.restore', { type: itemToRestore.value.type, id: itemToRestore.value.id }), {}, {
            preserveScroll: true,
            onSuccess: () => closeRestoreModal(),
            onFinish: () => closeRestoreModal(),
        });
    }
};

const formatRupiah = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <Head title="Arsip Data" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-[#e6edf3]">
                Arsip Data (Soft Deletes)
            </h2>
            <p class="mt-1 text-sm text-[#8b949e]">
                Lihat dan pulihkan data yang telah dihapus. Data di sini tersimpan dengan aman.
            </p>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Session Messages -->
                <div v-if="$page.props.flash?.success" class="mb-4 rounded-md border border-[#238636]/30 bg-[#238636]/15 p-4 text-[#3fb950] text-sm">
                    {{ $page.props.flash.success }}
                </div>
                
                <!-- Tabs Navigation -->
                <div class="mb-6 flex space-x-1 rounded-lg bg-[#161b22] p-1 shadow border border-[#30363d] overflow-x-auto">
                    <button @click="activeTab = 'transactions'" :class="[
                        'w-full rounded-md py-2.5 text-sm font-medium leading-5 transition-colors focus:outline-none whitespace-nowrap',
                        activeTab === 'transactions'
                            ? 'bg-[#21262d] text-[#e6edf3] shadow border border-[#30363d]'
                            : 'text-[#8b949e] hover:bg-[#21262d]/50 hover:text-[#c9d1d9]'
                    ]">
                        Transaksi ({{ transactions.length }})
                    </button>
                    <button @click="activeTab = 'coas'" :class="[
                        'w-full rounded-md py-2.5 text-sm font-medium leading-5 transition-colors focus:outline-none whitespace-nowrap',
                        activeTab === 'coas'
                            ? 'bg-[#21262d] text-[#e6edf3] shadow border border-[#30363d]'
                            : 'text-[#8b949e] hover:bg-[#21262d]/50 hover:text-[#c9d1d9]'
                    ]">
                        COA ({{ coas.length }})
                    </button>
                    <button @click="activeTab = 'categories'" :class="[
                        'w-full rounded-md py-2.5 text-sm font-medium leading-5 transition-colors focus:outline-none whitespace-nowrap',
                        activeTab === 'categories'
                            ? 'bg-[#21262d] text-[#e6edf3] shadow border border-[#30363d]'
                            : 'text-[#8b949e] hover:bg-[#21262d]/50 hover:text-[#c9d1d9]'
                    ]">
                        Kategori ({{ categories.length }})
                    </button>
                    <button @click="activeTab = 'reports'" :class="[
                        'w-full rounded-md py-2.5 text-sm font-medium leading-5 transition-colors focus:outline-none whitespace-nowrap',
                        activeTab === 'reports'
                            ? 'bg-[#21262d] text-[#e6edf3] shadow border border-[#30363d]'
                            : 'text-[#8b949e] hover:bg-[#21262d]/50 hover:text-[#c9d1d9]'
                    ]">
                        Laporan Cadangan ({{ reports.length }})
                    </button>
                </div>

                <!-- Transaction Archive -->
                <div v-show="activeTab === 'transactions'" class="overflow-hidden rounded-md border border-[#30363d] bg-[#161b22]">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-[#e6edf3]">
                            <thead class="bg-[#21262d] text-[#8b949e] text-xs font-semibold uppercase border-b border-[#30363d]">
                                <tr>
                                    <th class="px-4 py-3">Tgl. Dihapus</th>
                                    <th class="px-4 py-3">Deskripsi</th>
                                    <th class="px-4 py-3 text-right">Debit</th>
                                    <th class="px-4 py-3 text-right">Credit</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#30363d]">
                                <tr v-for="tx in transactions" :key="tx.id" class="hover:bg-[#1f2937]">
                                    <td class="px-4 py-3 text-[#ff7b72] font-mono text-xs">{{ new Date(tx.deleted_at).toLocaleString() }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">{{ tx.description || 'Tanpa Deskripsi' }}</div>
                                        <div class="text-xs text-[#8b949e] mt-1">{{ tx.coa?.name }} ({{ tx.coa?.code }})</div>
                                    </td>
                                    <td class="px-4 py-3 text-right text-[#f85149]">{{ formatRupiah(tx.debit) }}</td>
                                    <td class="px-4 py-3 text-right text-[#3fb950]">{{ formatRupiah(tx.credit) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <button @click="confirmRestore('transaction', tx.id)" class="inline-flex items-center rounded bg-[#21262d] px-2.5 py-1.5 text-xs font-medium text-[#58a6ff] border border-[#30363d] hover:bg-[#58a6ff] hover:text-white transition-colors">Pulihkan</button>
                                    </td>
                                </tr>
                                <tr v-if="transactions.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-[#8b949e]">Arsip transaksi kosong.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- COA Archive -->
                <div v-show="activeTab === 'coas'" class="overflow-hidden rounded-md border border-[#30363d] bg-[#161b22]">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-[#e6edf3]">
                            <thead class="bg-[#21262d] text-[#8b949e] text-xs font-semibold uppercase border-b border-[#30363d]">
                                <tr>
                                    <th class="px-4 py-3">Tgl. Dihapus</th>
                                    <th class="px-4 py-3">Kode Akun</th>
                                    <th class="px-4 py-3">Nama Akun</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#30363d]">
                                <tr v-for="coa in coas" :key="coa.id" class="hover:bg-[#1f2937]">
                                    <td class="px-4 py-3 text-[#ff7b72] font-mono text-xs">{{ new Date(coa.deleted_at).toLocaleString() }}</td>
                                    <td class="px-4 py-3 font-mono text-[#58a6ff]">{{ coa.code }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">{{ coa.name }}</div>
                                        <div class="text-xs text-[#8b949e] mt-1">{{ coa.coa_category?.name }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button @click="confirmRestore('coa', coa.id)" class="inline-flex items-center rounded bg-[#21262d] px-2.5 py-1.5 text-xs font-medium text-[#58a6ff] border border-[#30363d] hover:bg-[#58a6ff] hover:text-white transition-colors">Pulihkan</button>
                                    </td>
                                </tr>
                                <tr v-if="coas.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-[#8b949e]">Arsip COA kosong.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Category Archive -->
                <div v-show="activeTab === 'categories'" class="overflow-hidden rounded-md border border-[#30363d] bg-[#161b22]">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-[#e6edf3]">
                            <thead class="bg-[#21262d] text-[#8b949e] text-xs font-semibold uppercase border-b border-[#30363d]">
                                <tr>
                                    <th class="px-4 py-3">Tgl. Dihapus</th>
                                    <th class="px-4 py-3">Nama Kategori</th>
                                    <th class="px-4 py-3">Tipe</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#30363d]">
                                <tr v-for="cat in categories" :key="cat.id" class="hover:bg-[#1f2937]">
                                    <td class="px-4 py-3 text-[#ff7b72] font-mono text-xs">{{ new Date(cat.deleted_at).toLocaleString() }}</td>
                                    <td class="px-4 py-3 font-medium">{{ cat.name }}</td>
                                    <td class="px-4 py-3 uppercase text-xs">{{ cat.type }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <button @click="confirmRestore('category', cat.id)" class="inline-flex items-center rounded bg-[#21262d] px-2.5 py-1.5 text-xs font-medium text-[#58a6ff] border border-[#30363d] hover:bg-[#58a6ff] hover:text-white transition-colors">Pulihkan</button>
                                    </td>
                                </tr>
                                <tr v-if="categories.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-[#8b949e]">Arsip kategori kosong.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Reports Archive -->
                <div v-show="activeTab === 'reports'" class="overflow-hidden rounded-md border border-[#30363d] bg-[#161b22]">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-[#e6edf3]">
                            <thead class="bg-[#21262d] text-[#8b949e] text-xs font-semibold uppercase border-b border-[#30363d]">
                                <tr>
                                    <th class="px-4 py-3">Waktu Dibuat</th>
                                    <th class="px-4 py-3">Nama Berkas</th>
                                    <th class="px-4 py-3">Ukuran</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#30363d]">
                                <tr v-for="(report, index) in reports" :key="index" class="hover:bg-[#1f2937]">
                                    <td class="px-4 py-3 font-mono text-xs text-[#8b949e]">{{ report.last_modified }}</td>
                                    <td class="px-4 py-3 font-medium text-[#58a6ff]">{{ report.name }}</td>
                                    <td class="px-4 py-3 text-xs">{{ report.size }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a :href="report.url" target="_blank" download class="inline-flex items-center rounded bg-[#238636] px-2.5 py-1.5 text-xs font-medium text-white border border-[#2ea043] hover:bg-[#2ea043] transition-colors">Unduh PDF</a>
                                    </td>
                                </tr>
                                <tr v-if="reports.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-[#8b949e]">Belum ada laporan cadangan yang digenerate.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <Modal :show="confirmingRestore" @close="closeRestoreModal">
            <div class="p-6 bg-[#161b22] border border-[#30363d] rounded-lg">
                <h2 class="text-lg font-medium text-[#e6edf3]">
                    Konfirmasi Pemulihan
                </h2>

                <p class="mt-1 text-sm text-[#8b949e]">
                    Apakah Anda yakin ingin memulihkan data ini? Data yang terhubung juga akan dipulihkan agar tidak terjadi error relasi.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeRestoreModal" class="bg-[#21262d] text-[#c9d1d9] border-[#30363d] hover:bg-[#30363d] hover:text-[#e6edf3]">
                        Batal
                    </SecondaryButton>

                    <button
                        class="inline-flex items-center rounded-md bg-[#238636] px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-[#2ea043] focus:outline-none"
                        @click="restoreItem"
                    >
                        Ya, Pulihkan
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
