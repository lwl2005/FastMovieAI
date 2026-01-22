<template>
    <el-scrollbar height="70vh" @end-reached="handleScroll">
        <div class="flex flex-y-center grid-gap-6 p-4 rounded-4 active pointer" v-for="item in list" :key="item.id"
            v-if="list.length > 0">
            <el-avatar :size="48" src="" class="flex-shrink-0" alt="发斯蒂芬水电费" />
            <div class="flex flex-column grid-gap-2">
                <span class="h7 font-weight-600">{{ item.title }}</span>
                <span class="text-ellipsis-2 text-secondary">{{ item.sub_title }}</span>
            </div>
        </div>
        <div class="flex flex-center flex-column" v-else>
            <el-empty description="暂无消息" />
        </div>
    </el-scrollbar>
</template>
<script setup lang="ts">
import { $http } from '@/common/http'
import { ResponseCode } from '@/common/const'
const list = ref<any[]>([])
const query = ref({
    page: 1,
    limit: 10,
})
const total = ref(0)
const getList = async () => {
    const res: any = await $http.get('/app/notification/api/Message/list', { params: query.value })
    if (res.code === ResponseCode.SUCCESS) {
        list.value = [...list.value, ...res.data.data]
        total.value = res.data.total
    }
}
const handleScroll = () => {
    if (query.value.page < total.value) {
        query.value.page++;
        getList();
    }
}
defineExpose({
    open: () => {
        getList();
    }
})

</script>
<style scoped lang="scss">
.active:hover {
    background: rgba(255, 255, 255, 0.1);
}
</style>