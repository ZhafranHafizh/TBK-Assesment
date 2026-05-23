<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';

const props = defineProps<{
    coas: any;
    categories: any[];
}>();

const categoryFormOptions = computed(() => [
    { value: '', label: 'Pilih Kategori...' },
    ...props.categories.map(cat => ({ value: cat.id, label: cat.name, sublabel: cat.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }))
]);

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
        router.delete(route('coas.destroy', itemToDelete.value), {
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
    code: '',
    name: '',
    coa_category_id: '',
});

const submit = () => {
    form.post(route('coas.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateForm.value = false;
            form.reset();
        },
    });
};
</script>

<template>

    <Head title="Chart of Account" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-[#e6edf3]">
                        Chart of Account (COA)
                    </h2>
                    <p class="mt-1 text-sm text-[#8b949e]">
                        Kelola daftar kode akun untuk pencatatan transaksi keuangan.
                    </p>
                </div>
                <button @click="showCreateForm = !showCreateForm"
                    class="rounded-md bg-[#238636] px-3 py-2 text-sm font-medium text-white transition-colors duration-150 hover:bg-[#2ea043]">
                    {{ showCreateForm ? 'Tutup Form' : '+ Tambah COA' }}
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
                        <div class="overflow-hidden rounded-md border border-[#30363d] bg-[#161b22]">
                            <table class="w-full text-left text-sm text-[#e6edf3]">
                                <thead
                                    class="bg-[#21262d] text-[#8b949e] text-xs font-semibold uppercase border-b border-[#30363d]">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 w-24">Kode</th>
                                        <th scope="col" class="px-4 py-3">Nama Akun</th>
                                        <th scope="col" class="px-4 py-3">Kategori</th>
                                        <th scope="col" class="px-4 py-3 w-32 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#30363d]">
                                    <tr v-for="coa in coas.data" :key="coa.id"
                                        class="hover:bg-[#1f2937] transition-colors duration-150">
                                        <td class="px-4 py-3 font-mono text-[#58a6ff]">{{ coa.code }}</td>
                                        <td class="px-4 py-3 font-medium">{{ coa.name }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <span>{{ coa.coa_category?.name }}</span>
                                                <span v-if="coa.coa_category?.type === 'income'"
                                                    class="inline-flex items-center rounded-full bg-[#238636]/15 px-2 py-0.5 text-[10px] font-medium text-[#3fb950] border border-[#238636]/30">
                                                    INC
                                                </span>
                                                <span v-else-if="coa.coa_category?.type === 'expense'"
                                                    class="inline-flex items-center rounded-full bg-[#f85149]/15 px-2 py-0.5 text-[10px] font-medium text-[#ff7b72] border border-[#f85149]/30">
                                                    EXP
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <Link v-if="coa.is_editable_full" :href="route('coas.edit', coa.id)"
                                                    class="inline-flex items-center rounded bg-[#21262d] px-2.5 py-1.5 text-xs font-medium text-[#c9d1d9] border border-[#30363d] hover:bg-[#30363d] hover:text-[#e6edf3] transition-colors">
                                                    Edit</Link>
                                                <span v-else
                                                    class="inline-flex items-center rounded bg-[#161b22] px-2.5 py-1.5 text-xs font-medium text-[#8b949e] border border-[#30363d] cursor-not-allowed"
                                                    title="Terkunci (Lebih dari 24 jam)">🔒</span>
                                                <button @click="confirmDeletion(coa.id)"
                                                    class="inline-flex items-center rounded bg-[#21262d] px-2.5 py-1.5 text-xs font-medium text-[#f85149] border border-[#30363d] hover:bg-[#f85149] hover:text-white transition-colors">Hapus</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="coas.data.length === 0">
                                        <td colspan="4" class="px-4 py-8 text-center text-[#8b949e]">Belum ada data
                                            Chart of
                                            Account.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Create Form Sidebar -->
                    <div v-show="showCreateForm" class="lg:w-1/3 w-full transition-all duration-300 ease-in-out">
                        <div class="rounded-md border border-[#30363d] bg-[#161b22] p-5 shadow-sm sticky top-6">
                            <h3 class="text-lg font-medium text-[#e6edf3] mb-4 border-b border-[#30363d] pb-2">Tambah
                                COA Baru
                            </h3>
                            <form @submit.prevent="submit" class="space-y-4">
                                <div>
                                    <label for="code" class="block text-sm font-medium text-[#e6edf3]">Kode Akun</label>
                                    <input id="code" v-model="form.code" type="text"
                                        class="mt-1 block w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm font-mono"
                                        required autofocus placeholder="Contoh: 101, 401" />
                                    <p v-if="form.errors.code" class="mt-1 text-xs text-[#ff7b72]">{{ form.errors.code
                                        }}</p>
                                </div>

                                <div>
                                    <label for="name" class="block text-sm font-medium text-[#e6edf3]">Nama Akun</label>
                                    <input id="name" v-model="form.name" type="text"
                                        class="mt-1 block w-full rounded-md border-[#30363d] bg-[#0d1117] text-[#e6edf3] shadow-sm focus:border-[#58a6ff] focus:ring-[#58a6ff] sm:text-sm"
                                        required placeholder="Contoh: Gaji" />
                                    <p v-if="form.errors.name" class="mt-1 text-xs text-[#ff7b72]">{{ form.errors.name
                                        }}</p>
                                </div>

                                <div>
                                    <label for="coa_category_id"
                                        class="block text-sm font-medium text-[#e6edf3]">Kategori</label>
                                    <div class="mt-1 flex items-center gap-2">
                                        <CustomSelect v-model="form.coa_category_id" :options="categoryFormOptions"
                                            placeholder="Pilih Kategori..." :searchable="true" />
                                        <Link :href="route('coa-categories.index', { create: 'true' })"
                                            class="flex items-center justify-center min-w-[38px] h-[38px] bg-[#21262d] border border-[#30363d] text-[#c9d1d9] rounded-md hover:bg-[#30363d] hover:text-[#e6edf3] transition-colors"
                                            title="Tambah Kategori COA Baru">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                        </Link>
                                    </div>
                                    <p v-if="form.errors.coa_category_id" class="mt-1 text-xs text-[#ff7b72]">{{
                                        form.errors.coa_category_id }}</p>
                                </div>

                                <div class="flex items-center justify-end gap-2 pt-4">
                                    <button type="button" @click="showCreateForm = false; form.reset()"
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
                    Apakah Anda yakin ingin menghapus data ini?
                </h2>

                <p class="mt-1 text-sm text-[#8b949e]">
                    Setelah data dihapus, semua transaksi yang terhubung mungkin akan ikut terpengaruh. Tindakan ini
                    tidak dapat
                    dibatalkan.
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
