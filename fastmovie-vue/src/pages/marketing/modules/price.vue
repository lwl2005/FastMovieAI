<template>
    <div class="font-weight-500">
        <span :style="`font-size: ${props.currencySize}px;`">{{ props.currency }}</span>
        <span :style="`font-size: ${props.size}px;`">{{ integerNumber }}</span>
        <span :style="`font-size: ${props.decimalSize}px;`">.{{ decimalNumber }}</span>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
const decimalNumber = ref<string>('00');
const integerNumber = ref<number>(0);
const props = withDefaults(defineProps<{
    price?: number|string;
    size?: number;
    currency?: string;
    currencySize?: number;
    decimalSize?: number;
}>(), {
    price: 0,
    size: 24,
    currency: '￥',
    currencySize: 16,
    decimalSize: 16,
});

watch(() => props.price, (newVal) => {
    integerNumber.value = Math.floor(Number(newVal));
    decimalNumber.value = (Number(newVal) - integerNumber.value).toFixed(2).slice(2).padStart(2, '0');
});

onMounted(() => {
    integerNumber.value = Math.floor(Number(props.price));
    decimalNumber.value = (Number(props.price) - integerNumber.value).toFixed(2).slice(2).padStart(2, '0');
});
</script>

<style scoped lang="scss">

</style>