<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

interface Option {
    value: string | number;
    label: string;
    sublabel?: string;
}

const props = withDefaults(defineProps<{
    modelValue: string | number;
    options: Option[];
    placeholder?: string;
    searchable?: boolean;
    disabled?: boolean;
    dropUp?: boolean;
}>(), {
    placeholder: 'Pilih...',
    searchable: false,
    disabled: false,
    dropUp: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

const isOpen = ref(false);
const searchQuery = ref('');
const dropdownRef = ref<HTMLElement | null>(null);
const searchInputRef = ref<HTMLInputElement | null>(null);
const highlightedIndex = ref(-1);

const selectedLabel = computed(() => {
    const found = props.options.find(o => String(o.value) === String(props.modelValue));
    return found ? found.label : '';
});

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    const q = searchQuery.value.toLowerCase();
    return props.options.filter(o =>
        o.label.toLowerCase().includes(q) ||
        (o.sublabel && o.sublabel.toLowerCase().includes(q))
    );
});

const toggle = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
        highlightedIndex.value = -1;
        setTimeout(() => searchInputRef.value?.focus(), 50);
    }
};

const select = (option: Option) => {
    emit('update:modelValue', option.value);
    isOpen.value = false;
    searchQuery.value = '';
};

const handleClickOutside = (e: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        isOpen.value = false;
        searchQuery.value = '';
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (!isOpen.value) return;

    if (e.key === 'Escape') {
        isOpen.value = false;
        searchQuery.value = '';
    } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        highlightedIndex.value = Math.min(highlightedIndex.value + 1, filteredOptions.value.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0);
    } else if (e.key === 'Enter' && highlightedIndex.value >= 0) {
        e.preventDefault();
        select(filteredOptions.value[highlightedIndex.value]);
    }
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div ref="dropdownRef" class="custom-select-wrapper">
        <!-- Trigger -->
        <button
            type="button"
            @click="toggle"
            class="custom-select-trigger"
            :class="{ 'is-open': isOpen, 'is-disabled': disabled }"
            :disabled="disabled"
        >
            <span :class="selectedLabel ? 'text-[#e6edf3]' : 'text-[#8b949e]'">
                {{ selectedLabel || placeholder }}
            </span>
            <svg
                class="custom-select-chevron"
                :class="{ 'rotate-180': isOpen }"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
            >
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>

        <!-- Dropdown Panel -->
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 -translate-y-1 scale-[0.98]"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 -translate-y-1 scale-[0.98]"
        >
            <div v-show="isOpen" class="custom-select-panel" :class="{ 'is-drop-up': dropUp }">
                <!-- Search Input -->
                <div v-if="searchable" class="custom-select-search-wrapper">
                    <svg class="custom-select-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                    <input
                        ref="searchInputRef"
                        v-model="searchQuery"
                        type="text"
                        placeholder="Ketik untuk mencari..."
                        class="custom-select-search-input"
                        @keydown.stop
                    />
                </div>

                <!-- Options List -->
                <ul class="custom-select-options">
                    <li
                        v-for="(option, idx) in filteredOptions"
                        :key="option.value"
                        @click="select(option)"
                        @mouseenter="highlightedIndex = idx"
                        class="custom-select-option"
                        :class="{
                            'is-selected': String(option.value) === String(modelValue),
                            'is-highlighted': highlightedIndex === idx
                        }"
                    >
                        <div class="flex items-center justify-between w-full">
                            <div>
                                <span class="block text-sm leading-tight">{{ option.label }}</span>
                                <span v-if="option.sublabel" class="block text-[11px] text-[#8b949e] mt-0.5">{{ option.sublabel }}</span>
                            </div>
                            <!-- Checkmark -->
                            <svg v-if="String(option.value) === String(modelValue)" class="h-4 w-4 text-[#58a6ff] shrink-0 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </li>
                    <li v-if="filteredOptions.length === 0" class="custom-select-empty">
                        Tidak ditemukan.
                    </li>
                </ul>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.custom-select-wrapper {
    position: relative;
    width: 100%;
}

.custom-select-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    background-color: #0d1117;
    border: 1px solid #30363d;
    border-radius: 0.375rem;
    color: #e6edf3;
    cursor: pointer;
    transition: all 0.15s ease;
    text-align: left;
}

.custom-select-trigger:hover {
    border-color: #58a6ff;
}

.custom-select-trigger.is-open {
    border-color: #58a6ff;
    box-shadow: 0 0 0 1px #58a6ff;
}

.custom-select-trigger.is-disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.custom-select-trigger.is-disabled:hover {
    border-color: #30363d;
}

.custom-select-chevron {
    width: 1rem;
    height: 1rem;
    color: #8b949e;
    transition: transform 0.2s ease;
    flex-shrink: 0;
    margin-left: 0.5rem;
}

.custom-select-panel {
    position: absolute;
    z-index: 50;
    margin-top: 0.375rem;
    width: 100%;
    background-color: #161b22;
    border: 1px solid #30363d;
    border-radius: 0.5rem;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(48, 54, 61, 0.5);
    overflow: hidden;
}

.custom-select-panel.is-drop-up {
    bottom: 100%;
    margin-top: 0;
    margin-bottom: 0.375rem;
}

.custom-select-search-wrapper {
    position: relative;
    padding: 0.5rem;
    border-bottom: 1px solid #30363d;
}

.custom-select-search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    width: 0.875rem;
    height: 0.875rem;
    color: #8b949e;
}

.custom-select-search-input {
    width: 100%;
    padding: 0.375rem 0.5rem 0.375rem 1.75rem;
    font-size: 0.8125rem;
    background-color: #0d1117;
    border: 1px solid #30363d;
    border-radius: 0.375rem;
    color: #e6edf3;
    outline: none;
}

.custom-select-search-input::placeholder {
    color: #8b949e;
}

.custom-select-search-input:focus {
    border-color: #58a6ff;
    box-shadow: 0 0 0 1px #58a6ff;
}

.custom-select-options {
    max-height: 14rem;
    overflow-y: auto;
    padding: 0.25rem 0;
    list-style: none;
    margin: 0;
}

.custom-select-options::-webkit-scrollbar {
    width: 6px;
}

.custom-select-options::-webkit-scrollbar-track {
    background: transparent;
}

.custom-select-options::-webkit-scrollbar-thumb {
    background-color: #30363d;
    border-radius: 3px;
}

.custom-select-option {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    color: #c9d1d9;
    transition: background-color 0.1s ease;
}

.custom-select-option.is-highlighted {
    background-color: #1f2937;
    color: #e6edf3;
}

.custom-select-option.is-selected {
    background-color: rgba(88, 166, 255, 0.1);
    color: #58a6ff;
}

.custom-select-option.is-selected.is-highlighted {
    background-color: rgba(88, 166, 255, 0.18);
}

.custom-select-empty {
    padding: 0.75rem;
    text-align: center;
    color: #8b949e;
    font-size: 0.8125rem;
}
</style>
