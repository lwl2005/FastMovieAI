<template>
    <el-dialog v-model="visible" width="586px" :show-close="false" align-center append-to-body>
        <template #header>
            <div class="h1 text-center font-weight-600">邀请你的朋友</div>
        </template>
        <div class="flex flex-center flex-column" v-if="list.length > 0">
            <div class="h8">您还剩 {{ list.length }} 个邀请名额，拥有下方邀请码的任何人都可以加入</div>
            <div class="h8 mt-5">好友成功注册可得 100 积分</div>
            <div class="grid-columns-6 grid-gap-6 mt-10">
                <div v-for="(i, index) in code" :key="index" class="flex flex-center flex-column item">
                    {{ i }}
                </div>
            </div>
            <div class="flex flex-y-center grid-gap-2 mt-10 pointer" v-if="list.length > 1">
                <el-icon color="var(--el-color-success)" :size="16">
                    <Refresh />
                </el-icon>
                <span class="h9 text-success" @click="getCode">换一个</span>
            </div>
            <div class="mt-10  btn-box">
                <div class="btn" @click="close">
                    取消
                </div>
                <div class="btn btn-primary" @click="copyCode">
                    复制
                </div>
            </div>
        </div>
        <div class="flex flex-center flex-column" v-else>
            <div class="h8">您的邀请名额已用完</div>
            <el-button class="btn btn-primary mt-6 " color="var(--el-color-success)" size="large" @click="close">
                确定
            </el-button>
        </div>
    </el-dialog>
</template>
<script setup lang="ts">
import { $http } from '@/common/http';
import { ResponseCode } from '@/common/const';
const visible = ref(false);
const list = ref<any>([])
const getList = () => {
    $http.get('/app/user/api/User/getUnusedInvitationCode').then((res: any) => {
        if (res.code === ResponseCode.SUCCESS) {
            list.value = res.data
            getCode();
            visible.value = true
        }
    })
}
const code = ref<any>(null)

const normalizeCode = (str: string) =>
    str.replace(/\s+/g, '').slice(0, 6)

const getCode = () => {
    if (!list.value.length) return

    let newCodeStr = ''

    do {
        const item = list.value[Math.floor(Math.random() * list.value.length)]
        newCodeStr = normalizeCode(item.code)
    } while (
        code.value &&
        newCodeStr === code.value.join('') &&
        list.value.length > 1
    )

    code.value = newCodeStr.split('')
}

const copyCode = async () => {
    if (!Array.isArray(code.value) || !code.value.length) {
        ElMessage.warning('暂无可复制内容')
        return
    }
    const text = code.value.join('')
    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text)
        } else {
            fallbackCopy(text)
        }
        ElMessage.success('复制成功')
    } catch (err) {
        ElMessage.error('复制失败')
    }
}


const fallbackCopy = (text: string) => {
    const textarea = document.createElement('textarea')
    textarea.value = text
    textarea.style.position = 'fixed'
    textarea.style.left = '-9999px'
    textarea.style.top = '-9999px'
    document.body.appendChild(textarea)
    textarea.focus()
    textarea.select()
    document.execCommand('copy')
    document.body.removeChild(textarea)
}

const close = () => {
    visible.value = false
}
defineExpose({
    open: () => {
        getList()
    }
})
</script>
<style scoped>
.item {
    width: 71px;
    height: 71px;
    background: #1E1E1E;
    border-radius: 12px 12px 12px 12px;
    border: 1px solid #272727;
    font-size: 29px;
    font-weight: 600;
    color: #FFFFFF;
    text-align: center;
    line-height: 71px;
    cursor: pointer;
    transition: all 0.3s ease;

    &:hover {
        background: #272727;
    }
}

.btn {
    width: 100%;
    text-align: center;
    padding: 10px 0px;
    border-radius: 8px;
    background-color: #fff;
    font-size: 20px;
    font-weight: 600;
    color: #000;
    cursor: pointer;
    transition: all 0.3s ease;

    &:hover {
        background-color: #f0f0f0;
    }
}

.btn-primary {
    background-color: var(--el-color-success);

    &:hover {
        background-color: var(--el-color-success-dark-2);
    }
}

.btn-box {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-gap: 10px;
    width: 100%;
}
</style>