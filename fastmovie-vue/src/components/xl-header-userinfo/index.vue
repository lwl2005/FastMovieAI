<script setup lang="ts">
import { truncate } from '@/common/functions';
import { useLogin } from '@/composables/useLogin';
import { useRefs, useUserStore } from '@/stores';


const login = useLogin();
const userStore = useUserStore();
const { USERINFO } = useRefs(userStore);
const xlUserinfoRef = ref<any>(null);
</script>
<template>
    <div class="xl-header-userinfo" v-if="userStore.hasLogin()" @click="xlUserinfoRef.open()">
        <div class="xl-header-userinfo-info">
            <el-avatar :size="30" :src="USERINFO?.headimg">
                {{ truncate(USERINFO.nickname, 1) }}
            </el-avatar>
        </div>
    </div>
    <div class="xl-header-userinfo" v-else>
        <div class="xl-header-userinfo-button" @click="login.open()">
            <span>一键登录</span>
        </div>
    </div>
    <xl-userinfo ref="xlUserinfoRef" />
</template>

<style scoped lang="scss">
.xl-header-userinfo {
    cursor: pointer;

    &-info {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    &-button {
        min-width: 120px;
        height: calc(var(--xl-header-height, 70px) - 30px);
        border-radius: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #FFFFFF;
        cursor: pointer;
        background: linear-gradient(to right, #FFFFFF, #FFFFFF);
        background-position: left center;
        background-size: 0% 100%;
        background-repeat: no-repeat;
        transition: background-size 0.3s ease-in;

        &:hover {
            background-size: 100% 100%;
        }

        span {
            font-size: 14px;
            font-weight: 500;
            color: #FFFFFF;
            mix-blend-mode: difference;
            selection: none;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
    }
}


.el-divider--horizontal {
    --el-border-color: rgba(255, 255, 255, 0.1);
}
</style>