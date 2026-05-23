<script setup lang="ts">
import { useCurrencyInput, CurrencyInputOptions } from 'vue-currency-input'
import { watch, PropType } from 'vue'

const props = defineProps({
    modelValue: Number,
    options: {
        type: Object as PropType<CurrencyInputOptions>,
        default: () => ({
            currency: 'IDR',
            currencyDisplay: 'hidden',
            hideCurrencySymbolOnFocus: true,
            hideGroupingSeparatorOnFocus: false,
            hideNegligibleDecimalDigitsOnFocus: true,
            autoDecimalDigits: false,
            useGrouping: true,
            accountingSign: false,
        })
    }
})

const { inputRef, setOptions, setValue } = useCurrencyInput(props.options)

watch(
    () => props.modelValue,
    (value) => {
        setValue(value ?? null)
    }
)

watch(
    () => props.options,
    (options) => {
        setOptions(options)
    }
)
</script>

<template>
    <input ref="inputRef" type="text" />
</template>
