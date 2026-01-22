<template>
    <el-dialog v-model="payDialogVisible" width="420" title="扫码支付" top="35vh" @close="handleClose">
        <div class="payment-list">
            <div class="payment-item" v-for="item in paymentList" :key="item.id"
                :class="{ 'active': form.pay_type === item.enum.value }">
                <component :is="paymentListTemplate[item.enum.value]"
                    @click="form.pay_type = item.enum.value; form.payment_id = item.id" />
            </div>
        </div>
        <div class="payment">
            <div class="qrcode">
                <xl-qrcode :text="paymentQrcode" v-if="paymentQrcode" />
                <div v-else>请选择支付方式</div>
            </div>
            <div class="payment-info">
                <div>
                    <span>支付金额</span>
                    <span class="price"> {{ price }} </span>
                    <span>元</span>
                </div>
                <div class="mt-4">请使用 微信支付</div>
                <div class="mt-10">
                    <span>支付即视为同意</span>
                    <span class="privacy">《付费服务协议》</span>
                </div>
            </div>
        </div>
    </el-dialog>
</template>
<script setup lang="ts">
import { ResponseCode } from '@/common/const';
import { $http } from '@/common/http';
import IconWxpaySvg from '@/svg/icon/icon-wxpay.vue';
import IconZfbpaySvg from '@/svg/icon/icon-alipay.vue';
import { ElMessage, ElLoading } from 'element-plus';
import router from '@/routers';
const emit = defineEmits(['update:modelValue']);
const props = withDefaults(defineProps<{
    id: number | null,
    price?: number | string,
    type: string,
}>(), {
    id: 0,
    price: undefined,
    type: 'points',
})
//支付表单
const form = ref<any>({
    pay_type: 'wxpay',
    id: 0,
    payment_id: null,
    type: props.type,
})
watch(() => props.id, (newVal) => {
    form.value.id = newVal;
}, {
    immediate: true,
})

//弹窗状态
const payDialogVisible = ref(false);
//支付二维码
const paymentQrcode = ref<string | undefined>();
//可用支付列表
const paymentList = ref<any[]>([]);
//支付对应的图标
const paymentListTemplate: Record<string, any> = {
    'wxpay': IconWxpaySvg,
    'zfbpay': IconZfbpaySvg
}
//获取支付类型
const getPaymentType = () => {
    $http.get('/app/marketing/api/Payment/index').then((res: any) => {
        if (res.code === ResponseCode.SUCCESS) {
            paymentList.value = res.data;
            const target =
                paymentList.value.find((item: any) => item?.default === 1) ||
                paymentList.value.find(
                    (item: any) => item.enum.value === form.value.pay_type
                );
            form.value.pay_type = target?.enum.value;
            form.value.payment_id = target?.id;

        }
    })
}
//创建订单
const createOrder = async () => {
    const loading = ElLoading.service({
        lock: true,
        text: '创建订单中...',
        background: 'rgba(0, 0, 0, 0.7)',
    });
    form.value.id = props.id;
    const res: any = await $http.post('/app/finance/api/Orders/create', { ...form.value });
    loading.close();
    if (res.code !== ResponseCode.SUCCESS) {
        ElMessage.error(res.msg);
        return;
    }
    paymentQrcode.value = res.data.pay_info.qrcode;
    payDialogVisible.value = true;
    trade.value = res.data.pay_info.trade;
    timer = setInterval(() => {
        getTradeStatus();
    }, 1000);
    return null;
}
const trade = ref<string | null>(null);
let timer: any;
//获取订单状态
const getTradeStatus = async () => {
    if (!trade.value) return;
    const res: any = await $http.get(`/app/finance/api/Orders/getOrderStatus?trade=${trade.value}`);
    if (res.code !== ResponseCode.SUCCESS) {
        ElMessage.error(res.msg);
        return;
    }
    const status = res.data.status;
    if (status) {
        ElMessage.success('支付成功');
        payDialogVisible.value = false;
        clearInterval(timer);
        router.push('/');
        return;
    }
}

const handleClose = () => {
    clearInterval(timer);
    timer = null;
}

defineExpose({
    open: () => {
        clearInterval(timer);
        timer = null;
        createOrder();
    },
})
onUnmounted(() => {
    clearInterval(timer);
})
onMounted(() => {
    getPaymentType();
})
</script>
<style scoped lang="scss">
.payment-list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-gap: 10px;

    .payment-item {
        cursor: pointer;
        width: 100px;
        height: 40px;
        background-color: #fff;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
    }

    .active {
        border: 1px solid var(--el-color-success);
    }
}

.payment {
    display: flex;
    flex-direction: row;
    margin-top: 20px;
    gap: 20px;

    .qrcode {
        width: 131px;
        height: 131px;
        border-radius: 6px;
        flex-shrink: 0;
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .payment-info {
        font-size: 14px;

        .price {
            font-size: 28px;
            color: #F93434;
            font-weight: 600;
            padding: 0px 6px;
        }

        .privacy {
            color: #379EFF;
        }
    }
}
</style>