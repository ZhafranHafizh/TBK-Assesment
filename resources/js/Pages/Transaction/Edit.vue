<script setup lang="ts">
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import CustomSelect from '@/Components/CustomSelect.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';

const props = defineProps<{
    transaction: any;
    coas: any[];
}>();

const coaOptions = computed(() => [
    { value: '', label: 'Pilih Akun...' },
    ...props.coas.map(coa => ({ value: coa.id, label: `[${coa.code}] ${coa.name}`, sublabel: coa.coa_category?.name }))
]);

// Simple format to keep the HTML date input happy (YYYY-MM-DD)
const formattedDate = props.transaction.transaction_date.split(' ')[0];

const nominalIdr = ref(props.transaction.debit > 0 ? props.transaction.debit : props.transaction.credit);

const form = useForm({
    transaction_date: formattedDate,
    coa_id: props.transaction.coa_id,
    description: props.transaction.description || '',
    debit: props.transaction.debit,
    credit: props.transaction.credit,
});

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
    if (selectedCoaType.value === 'expense') {
        form.debit = nominalIdr.value;
        form.credit = 0;
    } else if (selectedCoaType.value === 'income') {
        form.credit = nominalIdr.value;
        form.debit = 0;
    } else {
        form.debit = nominalIdr.value;
    }

    form.put(route('transactions.update', props.transaction.id));
};
</script>

<template>
    <Head title="Edit Transaksi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-[#e6edf3]">
                Edit Transaksi Keuangan
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-md border border-[#30363d] bg-[#161b22] p-6 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div v-if="!transaction.is_editable_full" class="mb-4 rounded-md border border-[#d29922]/30 bg-[#d29922]/10 p-4 text-[#d29922] text-sm flex items-start gap-3">
                            <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p>Transaksi ini dicatat lebih dari 24 jam yang lalu. Untuk menjaga integritas data akuntansi, Anda hanya diizinkan untuk memperbarui bagian deskripsi/catatan saja.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div class="space-y-6">
                                <div>
                                    <label for="transaction_date" class="block text-sm font-medium text-[#e6edf3]">Tanggal Transaksi</label>
                                    <input
                                        id="transaction_date"
                                        v-model="form.transaction_date"
                                        type="date"
                                        :disabled="!transaction.is_editable_full"
                                        class="mt-1 block w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm color-scheme-dark disabled:opacity-50 disabled:cursor-not-allowed"
                                        required
                                    />
                                    <p v-if="form.errors.transaction_date" class="mt-2 text-sm text-[#ff7b72]">{{ form.errors.transaction_date }}</p>
                                </div>

                                <div>
                                    <label for="coa_id" class="block text-sm font-medium text-[#e6edf3]">Pilih COA</label>
                                    <div class="mt-1">
                                        <CustomSelect v-model="form.coa_id" :options="coaOptions" placeholder="Pilih Akun..." :searchable="true" :disabled="!transaction.is_editable_full" />
                                    </div>
                                    <div v-if="coaTypeLabel" class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border" :class="[coaTypeLabel.bg, coaTypeLabel.color, coaTypeLabel.border]">
                                        {{ coaTypeLabel.text }}
                                    </div>
                                    <p v-if="form.errors.coa_id" class="mt-2 text-sm text-[#ff7b72]">{{ form.errors.coa_id }}</p>
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-[#e6edf3]">Deskripsi <span class="text-[#8b949e] font-normal">(opsional)</span></label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm"
                                        placeholder="Catatan tambahan transaksi..."
                                    ></textarea>
                                    <p v-if="form.errors.description" class="mt-2 text-sm text-[#ff7b72]">{{ form.errors.description }}</p>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-6">
                                <!-- Currency Conversion Info (Read-only) -->
                                <div v-if="transaction.original_currency && transaction.original_currency !== 'IDR'"
                                    class="rounded-md border border-[#d29922]/30 bg-[#d29922]/5 p-4 space-y-2">
                                    <h3 class="text-sm font-medium text-[#d29922] flex items-center gap-1.5">
                                        💱 Konversi Mata Uang
                                    </h3>
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <span class="text-[#8b949e] text-xs">Mata Uang Asli</span>
                                            <p class="font-mono font-bold text-[#e6edf3]">{{ transaction.original_currency }}</p>
                                        </div>
                                        <div>
                                            <span class="text-[#8b949e] text-xs">Nominal Asli</span>
                                            <p class="font-mono font-bold text-[#e6edf3]">{{ new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(transaction.original_amount) }}</p>
                                        </div>
                                        <div class="col-span-2">
                                            <span class="text-[#8b949e] text-xs">Rate saat dicatat</span>
                                            <p class="font-mono text-[#e6edf3]">1 {{ transaction.original_currency }} = {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(transaction.exchange_rate) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-[#0d1117] border border-[#30363d] rounded-md p-4 space-y-4">
                                    <h3 class="text-sm font-medium text-[#8b949e] border-b border-[#30363d] pb-2 mb-4">Nilai Transaksi (Rp)</h3>
                                    
                                    <div>
                                        <label for="nominal_idr" class="block text-sm font-medium text-[#e6edf3]">Nominal (IDR)</label>
                                        <CurrencyInput
                                            id="nominal_idr"
                                            v-model="nominalIdr"
                                            :disabled="!transaction.is_editable_full"
                                            class="mt-1 block w-full rounded-md border-[#30363d] bg-[#161b22] font-mono shadow-sm focus:ring-1 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                            :class="selectedCoaType === 'expense' ? 'text-[#f85149] focus:border-[#f85149] focus:ring-[#f85149]' : (selectedCoaType === 'income' ? 'text-[#3fb950] focus:border-[#3fb950] focus:ring-[#3fb950]' : 'text-[#e6edf3] focus:border-[#58a6ff] focus:ring-[#58a6ff]')"
                                            required
                                        />
                                        <p v-if="form.errors.debit" class="mt-2 text-sm text-[#ff7b72]">{{ form.errors.debit }}</p>
                                        <p v-if="form.errors.credit" class="mt-2 text-sm text-[#ff7b72]">{{ form.errors.credit }}</p>
                                    </div>

                                    <p class="text-xs text-[#8b949e] mt-4 italic">
                                        * Isi 0 jika kolom tidak digunakan. Minimal salah satu (Debit atau Credit) harus lebih besar dari 0.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#30363d]">
                            <Link
                                :href="route('transactions.index')"
                                class="rounded-md border border-[#30363d] bg-[#21262d] px-4 py-2 text-sm font-medium text-[#c9d1d9] transition-colors hover:bg-[#30363d] hover:text-[#e6edf3]"
                            >
                                Batal
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-md bg-[#238636] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[#2ea043] disabled:opacity-50"
                            >
                                Simpan Perubahan
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Force dark scheme for date picker widget across browsers */
.color-scheme-dark {
    color-scheme: dark;
}
</style>
