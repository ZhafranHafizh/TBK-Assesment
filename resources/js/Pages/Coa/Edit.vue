<script setup lang="ts">
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import CustomSelect from '@/Components/CustomSelect.vue';

const props = defineProps<{
    coa: any;
    categories: any[];
}>();

const categoryOptions = computed(() => [
    { value: '', label: 'Pilih Kategori...' },
    ...props.categories.map(cat => ({ value: cat.id, label: cat.name, sublabel: cat.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }))
]);

const form = useForm({
    code: props.coa.code,
    name: props.coa.name,
    coa_category_id: props.coa.coa_category_id,
});

const submit = () => {
    form.put(route('coas.update', props.coa.id));
};
</script>

<template>
    <Head title="Edit COA" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-[#e6edf3]">
                Edit Chart of Account (COA)
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-md border border-[#30363d] bg-[#161b22] p-6 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div>
                            <label for="code" class="block text-sm font-medium text-[#e6edf3]">Kode Akun</label>
                            <input
                                id="code"
                                v-model="form.code"
                                type="text"
                                class="mt-1 block w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm font-mono"
                                required
                            />
                            <p v-if="form.errors.code" class="mt-2 text-sm text-[#ff7b72]">{{ form.errors.code }}</p>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-[#e6edf3]">Nama Akun</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm"
                                required
                            />
                            <p v-if="form.errors.name" class="mt-2 text-sm text-[#ff7b72]">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label for="coa_category_id" class="block text-sm font-medium text-[#e6edf3]">Kategori</label>
                            <div class="mt-1">
                                <CustomSelect v-model="form.coa_category_id" :options="categoryOptions" placeholder="Pilih Kategori..." :searchable="true" />
                            </div>
                            <p v-if="form.errors.coa_category_id" class="mt-2 text-sm text-[#ff7b72]">{{ form.errors.coa_category_id }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#30363d]">
                            <Link
                                :href="route('coas.index')"
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
