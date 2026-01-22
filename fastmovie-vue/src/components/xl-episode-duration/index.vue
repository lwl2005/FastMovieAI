<script setup lang="ts">

const props = defineProps<{
    modelValue: number;
}>();
const emit = defineEmits(['update:modelValue']);
const handleSelect = (value: number) => {
    emit('update:modelValue', value);
}
const episodeSumList = ref<number[]>([60, 90, 120, 150, 180, 210, 240, 270, 300]);
</script>
<template>
    <el-popover placement="bottom-start" width="fit-content" popper-class="model-popover">
        <template #reference>
            <slot>
                <div class="flex flex-center grid-gap-2 input-button  px-6 ">
                    <span>每集时长</span>
                    <span class="h10 font-weight-600 text-episode-sum">{{ modelValue }}</span>
                    <span>秒</span>
                </div>
            </slot>
        </template>
        <span class="h10">选择每集时长</span>
        <div class="grid-columns-4 grid-gap-4 text-center mt-4">
            <div class="grid-column-2 input-button rounded-4 p-4" v-for="item in episodeSumList" :key="item" :class="{'active': modelValue === item}"
                @click.stop="handleSelect(item)">
                <span class="font-weight-600">{{ item }}秒</span>
            </div>
        </div>
    </el-popover>
</template>
<style scoped lang="scss">
.input-button {
    // background-color: var(--el-fill-color-darker);
    background: rgba(255,255,255,0.08);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border-radius: 20px;
    padding-top: 2px;
    padding-bottom: 2px;
    &:hover {
        // background-color: var(--el-fill-color-dark);
        background: rgba(255,255,255,0.16);
    }
    &.active {
        background-color: var(--el-color-success);
        color: var(--el-bg-color-page);
    }
}

.text-episode-sum {
    height: 34px;
    line-height: 34px;
}
</style>