<script setup lang="ts">
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps<{
    settings: { app_name: string; base_currency: string };
    currencies: any[];
    lastFetchTime: string | null;
    isRateStale: boolean;
}>();

const appNameForm = useForm({ app_name: props.settings.app_name });
const currencyForm = useForm({ code: '', name: '' });

const submitAppName = () => {
    appNameForm.put(route('settings.update-app-name'), { preserveScroll: true });
};

const submitCurrency = () => {
    currencyForm.post(route('settings.add-currency'), {
        preserveScroll: true,
        onSuccess: () => currencyForm.reset(),
    });
};

const confirmingRemoval = ref(false);
const currencyToRemove = ref<any>(null);

const confirmRemoval = (currency: any) => {
    currencyToRemove.value = currency;
    confirmingRemoval.value = true;
};

const removeCurrency = () => {
    if (currencyToRemove.value) {
        router.delete(route('settings.remove-currency', currencyToRemove.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                confirmingRemoval.value = false;
                currencyToRemove.value = null;
            },
        });
    }
};

const isFetching = ref(false);
const fetchRates = () => {
    isFetching.value = true;
    router.post(route('settings.fetch-rates'), {}, {
        preserveScroll: true,
        onFinish: () => isFetching.value = false,
    });
};

const formatDate = (isoString: string | null) => {
    if (!isoString) return 'Belum pernah di-fetch';
    const d = new Date(isoString);
    return d.toLocaleString('id-ID', {
        day: '2-digit', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
    });
};

const formatRate = (rate: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 2,
    }).format(rate);
};

// Common currency suggestions
const popularCurrencies = [
    { code: 'USD', name: 'US Dollar' },
    { code: 'EUR', name: 'Euro' },
    { code: 'GBP', name: 'British Pound' },
    { code: 'JPY', name: 'Japanese Yen' },
    { code: 'SGD', name: 'Singapore Dollar' },
    { code: 'AUD', name: 'Australian Dollar' },
    { code: 'CNY', name: 'Chinese Yuan' },
    { code: 'MYR', name: 'Malaysian Ringgit' },
    { code: 'KRW', name: 'South Korean Won' },
    { code: 'THB', name: 'Thai Baht' },
];

const availableSuggestions = computed(() => {
    const existingCodes = props.currencies.map(c => c.code);
    return popularCurrencies.filter(c => !existingCodes.includes(c.code));
});

const applySuggestion = (sug: { code: string; name: string }) => {
    currencyForm.code = sug.code;
    currencyForm.name = sug.name;
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Settings" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-[#e6edf3]">
                Settings
            </h2>
            <p class="mt-1 text-sm text-[#8b949e]">Pengaturan umum aplikasi.</p>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success"
                    class="rounded-md border border-[#238636]/30 bg-[#238636]/15 p-4 text-[#3fb950] text-sm">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error"
                    class="rounded-md border border-[#f85149]/30 bg-[#f85149]/15 p-4 text-[#ff7b72] text-sm">
                    {{ $page.props.flash.error }}
                </div>

                <!-- Section 1: App Info -->
                <div class="rounded-md border border-[#30363d] bg-[#161b22] p-6 shadow-sm">
                    <h3 class="text-lg font-medium text-[#e6edf3] mb-4 border-b border-[#30363d] pb-2">Informasi
                        Aplikasi</h3>
                    <form @submit.prevent="submitAppName" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-[#e6edf3] mb-1">Nama Aplikasi</label>
                                <input v-model="appNameForm.app_name" type="text"
                                    class="w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm"
                                    required />
                                <p v-if="appNameForm.errors.app_name" class="mt-1 text-xs text-[#ff7b72]">{{
                                    appNameForm.errors.app_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#e6edf3] mb-1">Base Currency</label>
                                <div
                                    class="flex items-center h-[38px] px-3 rounded-md border border-[#30363d] bg-[#0d1117] text-[#8b949e] text-sm">
                                    <span class="font-mono font-bold text-[#58a6ff] mr-2">{{ settings.base_currency
                                    }}</span>
                                    <span>Indonesian Rupiah</span>
                                    <span
                                        class="ml-auto text-[10px] bg-[#30363d] px-2 py-0.5 rounded-full text-[#8b949e]">READ
                                        ONLY</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="appNameForm.processing"
                                class="rounded-md bg-[#238636] px-4 py-2 text-sm font-medium text-white hover:bg-[#2ea043] disabled:opacity-50 transition-colors">
                                Simpan Nama
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Section 2: Allowed Currencies -->
                <div class="rounded-md border border-[#30363d] bg-[#161b22] p-6 shadow-sm">
                    <h3 class="text-lg font-medium text-[#e6edf3] mb-4 border-b border-[#30363d] pb-2">Mata Uang Asing
                        yang
                        Diizinkan</h3>

                    <!-- Add Currency Form -->
                    <form @submit.prevent="submitCurrency" class="mb-5">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="w-full sm:w-24">
                                <input v-model="currencyForm.code" type="text" placeholder="USD" maxlength="3"
                                    class="w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm font-mono uppercase"
                                    required />
                            </div>
                            <div class="flex-1">
                                <input v-model="currencyForm.name" type="text"
                                    placeholder="Nama mata uang (e.g. US Dollar)"
                                    class="w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm"
                                    required />
                            </div>
                            <button type="submit" :disabled="currencyForm.processing"
                                class="rounded-md bg-[#238636] px-4 py-2 text-sm font-medium text-white hover:bg-[#2ea043] disabled:opacity-50 transition-colors whitespace-nowrap">
                                + Tambah
                            </button>
                        </div>
                        <p v-if="currencyForm.errors.code" class="mt-1 text-xs text-[#ff7b72]">{{
                            currencyForm.errors.code }}
                        </p>
                    </form>

                    <!-- Quick Suggestions -->
                    <div v-if="availableSuggestions.length > 0" class="mb-5">
                        <p class="text-xs text-[#8b949e] mb-2">Saran cepat:</p>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="sug in availableSuggestions" :key="sug.code" @click="applySuggestion(sug)"
                                class="text-xs px-2.5 py-1 rounded-full border border-[#30363d] text-[#c9d1d9] hover:border-[#58a6ff] hover:text-[#58a6ff] transition-colors bg-[#0d1117]">
                                {{ sug.code }} — {{ sug.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Currency List Table -->
                    <div class="overflow-hidden rounded-md border border-[#30363d]">
                        <table class="w-full text-left text-sm text-[#e6edf3]">
                            <thead
                                class="bg-[#21262d] text-[#8b949e] text-xs font-semibold uppercase border-b border-[#30363d]">
                                <tr>
                                    <th class="px-4 py-3 w-20">Kode</th>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3 text-right">Rate ke IDR</th>
                                    <th class="px-4 py-3 text-right w-24">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#30363d]">
                                <tr v-for="cur in currencies" :key="cur.id"
                                    class="hover:bg-[#1f2937] transition-colors">
                                    <td class="px-4 py-3 font-mono font-bold text-[#58a6ff]">{{ cur.code }}</td>
                                    <td class="px-4 py-3">{{ cur.name }}</td>
                                    <td class="px-4 py-3 text-right font-mono">
                                        <span v-if="cur.latest_rate">{{ formatRate(cur.latest_rate.rate_to_idr)
                                        }}</span>
                                        <span v-else class="text-[#8b949e] italic">—</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button @click="confirmRemoval(cur)"
                                            class="inline-flex items-center rounded bg-[#21262d] px-2.5 py-1.5 text-xs font-medium text-[#f85149] border border-[#30363d] hover:bg-[#f85149] hover:text-white transition-colors">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="currencies.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-[#8b949e]">
                                        Belum ada mata uang asing yang ditambahkan. Gunakan form di atas untuk
                                        menambahkan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section 3: Exchange Rates -->
                <div class="rounded-md border border-[#30363d] bg-[#161b22] p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4 border-b border-[#30363d] pb-2">
                        <h3 class="text-lg font-medium text-[#e6edf3]">Nilai Tukar</h3>
                        <button @click="fetchRates" :disabled="isFetching || currencies.length === 0"
                            class="flex items-center gap-2 rounded-md border border-[#30363d] bg-[#21262d] px-4 py-2 text-sm font-medium text-[#c9d1d9] hover:bg-[#30363d] hover:text-[#e6edf3] transition-colors disabled:opacity-50">
                            <svg :class="{ 'animate-spin': isFetching }" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            {{ isFetching ? 'Mengambil...' : 'Fetch Rate Terbaru' }}
                        </button>
                    </div>

                    <!-- Stale Rate Warning -->
                    <div v-if="isRateStale && currencies.length > 0"
                        class="mb-4 rounded-md border border-[#d29922]/30 bg-[#d29922]/10 p-4 text-[#d29922] text-sm flex items-start gap-3">
                        <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p><strong>Rate kedaluwarsa!</strong> Data nilai tukar belum di-fetch hari ini. Silakan tekan
                            tombol
                            "Fetch Rate Terbaru" untuk memperbarui.</p>
                    </div>

                    <div class="flex items-center gap-2 text-sm text-[#8b949e]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Terakhir di-fetch: <strong class="text-[#e6edf3]">{{ formatDate(lastFetchTime)
                        }}</strong></span>
                    </div>

                    <p class="mt-3 text-xs text-[#8b949e]">
                        Data bersumber dari <a href="https://frankfurter.dev" target="_blank"
                            class="text-[#58a6ff] hover:underline">Frankfurter API</a> (data bank sentral, gratis &
                        open-source).
                    </p>
                </div>

            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmingRemoval" @close="confirmingRemoval = false">
            <div class="p-6 bg-[#161b22] border border-[#30363d] rounded-lg">
                <h2 class="text-lg font-medium text-[#e6edf3]">
                    Hapus Mata Uang {{ currencyToRemove?.code }}?
                </h2>
                <p class="mt-1 text-sm text-[#8b949e]">
                    Mata uang ini akan dihapus dari daftar yang diizinkan beserta seluruh data rate-nya. Transaksi yang
                    sudah
                    menggunakan mata uang ini tidak akan terpengaruh.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingRemoval = false"
                        class="bg-[#21262d] text-[#c9d1d9] border-[#30363d] hover:bg-[#30363d]">
                        Batal
                    </SecondaryButton>
                    <DangerButton class="bg-[#da3633] hover:bg-[#f85149] text-white" @click="removeCurrency">
                        Ya, Hapus
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
