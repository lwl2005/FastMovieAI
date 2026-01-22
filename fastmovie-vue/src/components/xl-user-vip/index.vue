<template>
    <el-popover placement="bottom" :width="320" trigger="click" popper-style="border-radius: 10px;"
        v-if="userStore.hasLogin()">
        <template #reference>
            <div class="x-header-tool">
                <el-icon :size="26">
                    <IconVipSvg />
                </el-icon>
            </div>
        </template>
        <template #default>
            <div class="userinfo">
                <div class="h7 font-weight-bold">{{ USERINFO?.nickname }}</div>
                <div class="text-secondary mt-4">ID {{ USERINFO?.id }}</div>

                <div class="btn" @click="router.push('/vip')">开通会员</div>
                <el-divider />
                <div class="h8">我的积分</div>
                <div class="grid grid-columns-3 mt-7">
                    <div class="flex flex-column flex-center grid-gap-3">
                        <span class="h7 font-weight-bold">{{ USERINFO?.wallet?.points }}</span>
                        <span class="h9 text-secondary">会员积分</span>
                    </div>
                    <div class="flex flex-column flex-center grid-gap-3">
                        <span class="h7 font-weight-bold">0</span>
                        <span class="h9 text-secondary">每周积分</span>
                    </div>
                    <div class="flex flex-column flex-center grid-gap-3">
                        <span class="h7 font-weight-bold">0</span>
                        <span class="h9 text-secondary">奖励积分</span>
                    </div>
                </div>
                <el-divider />
                <div class="flex flex-y-center grid-gap-2">
                    <el-icon color="#fff" size="20">
                        <Present />
                    </el-icon>
                    <span class="h8">邀请好友</span>
                    <div class="num">1</div>
                </div>
                <div class="flex flex-y-center grid-gap-2 mt-8">
                    <el-icon color="#fff" size="20">
                        <LanguageSvg />
                    </el-icon>
                    <span class=" h8">语言</span>
                    <el-divider direction="vertical" />
                    <span class="h9 text-secondary">简体中文</span>
                    <el-icon color="#B5B5B5">
                        <CaretBottom />
                    </el-icon>
                </div>
                <div class="flex flex-y-center grid-gap-2 mt-8">
                    <el-icon color="#fff" size="20">
                        <Setting />
                    </el-icon>
                    <span class=" h8">设置中心</span>
                </div>
            </div>
        </template>
    </el-popover>
</template>

<script setup lang="ts">
import router from '@/routers';
import { useRefs, useUserStore } from '@/stores';
import LanguageSvg from '@/svg/icon/language.vue';
import IconVipSvg from '@/svg/icon/icon-vip.vue';
const userStore = useUserStore();
const { USERINFO } = useRefs(userStore);
</script>
<style scoped lang="scss">
.x-header-tool {
    width: calc(var(--xl-header-height) - 20px);
    height: calc(var(--xl-header-height) - 20px);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;

    &:hover {
        background-color: rgba(13, 242, 131, 0.10);
    }

    cursor: pointer;

    &-img {
        font-size: 30px;
        width: 50%;
        height: 50%;
    }
}



.userinfo {
    border-radius: 10px;
    color: #FFFFFF;

    .btn {
        width: 100%;
        padding: 10px 0px;
        text-align: center;
        border-radius: 20px;
        background: linear-gradient(90deg, #F4CF77 0%, #FFF1D0 100%);
        border: 0px solid rgba(255, 255, 255, 0.3);
        color: #3D2D09;
        margin-top: 16px;
        font-weight: bold;
        cursor: pointer;
    }

    .num {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: linear-gradient(90deg, #79FFFF 0%, #0DF283 100%);
        color: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }
}
</style>