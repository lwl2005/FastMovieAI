<script setup lang="ts">

const props = defineProps<{
    modelValue: string;
}>();
const emit = defineEmits(['update:modelValue']);
const handleSelect = (aspectRatio: string) => {
    emit('update:modelValue', aspectRatio);
}
const showArrowDown = ref(false);

const aspectRatioList = ref<string[]>(['9:16', '16:9', '3:4', '4:3', '2:3', '3:2', '1:1']);
</script>
<template>
    <el-popover placement="bottom-start" width="fit-content" popper-class="model-popover" @show="showArrowDown = false"
        @hide="showArrowDown = true">
        <template #reference>
            <slot>
                <div class="flex flex-center grid-gap-2 input-button input-button-selected   px-6">
                    <span class="icon-aspect-ratio" :view="modelValue"></span>
                    <span class="h10">{{ modelValue }}</span>
                    <el-icon v-if="showArrowDown">
                        <ArrowUpBold />
                    </el-icon>
                    <el-icon v-else>
                        <ArrowDownBold />
                    </el-icon>
                </div>
            </slot>
        </template>
        <!-- <span class="h10">选择比例</span> -->
        <el-scrollbar height="300px">
            <div class="grid-columns-2 grid-gap-4 text-center mt-4">
                <div v-for="item in aspectRatioList" :key="item"
                    class="grid-column-2 grid-gap-2 input-button rounded-4 p-2" :class="{ 'input-button-selected': modelValue === item }" @click.stop="handleSelect(item)">
                    <span class="icon-aspect-ratio" :view="item"></span>
                    <span class="font-weight-600">{{ item }}</span>
                    <el-icon v-if="modelValue === item" class="ml-auto">
                        <Check />
                    </el-icon>
                </div>
            </div>
        </el-scrollbar>
    </el-popover>
</template>
<style scoped lang="scss">
.input-button {
    // background-color: var(--el-fill-color-darker);
    // background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding-top: 2px;
    padding-bottom: 2px;

    &-selected {
        background: rgba(255, 255, 255, 0.08);
    }

    &:hover {
        // background-color: var(--el-fill-color-dark);
        background: rgba(255, 255, 255, 0.16);
    }
}
</style>