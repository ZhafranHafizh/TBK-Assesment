<script setup lang="ts">
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import CustomSelect from '@/Components/CustomSelect.vue';

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

const form = useForm({
    transaction_date: formattedDate,
    coa_id: props.transaction.coa_id,
    description: props.transaction.description || '',
    debit: props.transaction.debit,
    credit: props.transaction.credit,
});

const submit = () => {
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
                                <div class="bg-[#0d1117] border border-[#30363d] rounded-md p-4 space-y-4">
                                    <h3 class="text-sm font-medium text-[#8b949e] border-b border-[#30363d] pb-2 mb-4">Nilai Transaksi (Rp)</h3>
                                    
                                    <div>
                                        <label for="debit" class="block text-sm font-medium text-[#e6edf3]">Debit</label>
                                        <input
                                            id="debit"
                                            v-model="form.debit"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            :disabled="!transaction.is_editable_full"
                                            class="mt-1 block w-full rounded-md border-[#30363d] bg-[#161b22] text-[#f85149] font-mono shadow-sm focus:border-[#f85149] focus:ring-[#f85149] sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                            required
                                        />
                                        <p v-if="form.errors.debit" class="mt-2 text-sm text-[#ff7b72]">{{ form.errors.debit }}</p>
                                    </div>

                                    <div>
                                        <label for="credit" class="block text-sm font-medium text-[#e6edf3]">Credit</label>
                                        <input
                                            id="credit"
                                            v-model="form.credit"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            :disabled="!transaction.is_editable_full"
                                            class="mt-1 block w-full rounded-md border-[#30363d] bg-[#161b22] text-[#3fb950] font-mono shadow-sm focus:border-[#3fb950] focus:ring-[#3fb950] sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                            required
                                        />
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
