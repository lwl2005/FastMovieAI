<template>
    <div class="container">
        <el-scrollbar height="100%" wrap-class="flex-1" @end-reached="scrollBarEndReached" v-if="columns.length > 0">
            <div ref="containerRef" class="waterfall">
                <div class="column" v-for="(col, colIndex) in columns" :key="colIndex">
                    <div class="item" v-for="item in col.list" :key="item.id">
                        <img :src="item.src" />
                        <div
                            class="flex p-4 flex-y-center grid-gap-2 flex-x-space-between position-absolute bottom-0 left-0 right-0">
                            <div class="flex flex-y-center grid-gap-2">
                                <el-avatar src="item.avatar" alt="1" :size="24" />
                                <span class="h9">11</span>
                            </div>
                            <div class="flex flex-y-center grid-gap-2">
                                <el-icon :size="16">
                                    <IconLike />
                                </el-icon>
                                <span class="h9">11</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </el-scrollbar>
        <div class="flex flex-center" v-else>
            <el-empty description="暂无数据" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import IconLike from '@/svg/icon/icon-like.vue'


interface WaterfallItem {
    id: string
    src: string
    height: number
}

interface WaterfallColumn {
    height: number
    list: WaterfallItem[]
}

const COLUMN_COUNT = 4
const GAP = 16

const containerRef = ref<HTMLDivElement | null>(null)

const columns = ref<WaterfallColumn[]>(
    Array.from({ length: COLUMN_COUNT }, () => ({
        height: 0,
        list: [],
    }))
)


/**
 * 加载图片并计算渲染高度
 */
function loadImage(
    src: string,
    colWidth: number
): Promise<Pick<WaterfallItem, 'src' | 'height'>> {
    return new Promise((resolve) => {
        const img = new Image()
        img.src = src
        img.onload = () => {
            const height = img.height * (colWidth / img.width)
            resolve({ src, height })
        }
    })
}

/**
 * 获取当前最短列
 */
function getMinColumn(): WaterfallColumn {
    return columns.value.reduce((a, b) =>
        a.height <= b.height ? a : b
    )
}

/**
 * 添加 item 到瀑布流
 */
function appendItem(item: WaterfallItem): void {
    const minCol = getMinColumn()
    minCol.list.push(item)
    minCol.height += item.height + GAP
}

async function init(images: string[]): Promise<void> {
    await nextTick()

    if (!containerRef.value) return

    const containerWidth = containerRef.value.clientWidth
    const colWidth =
        (containerWidth - GAP * (COLUMN_COUNT - 1)) / COLUMN_COUNT

    for (let i = 0; i < images.length; i++) {
        const imgData = await loadImage(images[i], colWidth)
        appendItem({
            id: `${Date.now()}-${i}`,
            src: imgData.src,
            height: imgData.height,
        })
    }
}

const scrollBarEndReached = () => {
    console.log('scrollBarEndReached')
    init(demoImages)
}
const demoImages: string[] = [
    'https://img0.baidu.com/it/u=3591665277,2616537962&fm=253&fmt=auto&app=138&f=JPEG?w=800&h=1333',
    'https://img1.baidu.com/it/u=2456334644,3378803144&fm=253&fmt=auto&app=120&f=JPEG?w=800&h=1422',
    'https://img0.baidu.com/it/u=3446103776,1318769391&fm=253&fmt=auto&app=138&f=JPEG?w=500&h=667',
    'https://img0.baidu.com/it/u=3591665277,2616537962&fm=253&fmt=auto&app=138&f=JPEG?w=800&h=1333',
    'https://img1.baidu.com/it/u=2456334644,3378803144&fm=253&fmt=auto&app=120&f=JPEG?w=800&h=1422',
    'https://img0.baidu.com/it/u=3446103776,1318769391&fm=253&fmt=auto&app=138&f=JPEG?w=500&h=667',
    'https://img0.baidu.com/it/u=3591665277,2616537962&fm=253&fmt=auto&app=138&f=JPEG?w=800&h=1333',
    'https://img1.baidu.com/it/u=2456334644,3378803144&fm=253&fmt=auto&app=120&f=JPEG?w=800&h=1422',
    'https://img0.baidu.com/it/u=3446103776,1318769391&fm=253&fmt=auto&app=138&f=JPEG?w=500&h=667',
    'https://img0.baidu.com/it/u=3591665277,2616537962&fm=253&fmt=auto&app=138&f=JPEG?w=800&h=1333',
    'https://img1.baidu.com/it/u=2456334644,3378803144&fm=253&fmt=auto&app=120&f=JPEG?w=800&h=1422',
    'https://img0.baidu.com/it/u=3446103776,1318769391&fm=253&fmt=auto&app=138&f=JPEG?w=500&h=667',
]

onMounted(() => {
    init(demoImages)
})
</script>

<style scoped>
.container {
    width: 100%;
    height: calc(100dvh - var(--xl-header-height));
    padding: 20px 150px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.waterfall {
    display: flex;
    gap: 1px;
    min-width: 700px;
}

.column {
    flex: 1;
}

.item {
    margin-bottom: 1px;
    position: relative;
}

.item img {
    width: 100%;
    display: block;
    border-radius: 8px;
}


</style>