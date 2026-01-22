<script setup lang="ts">
import { ResponseCode } from '@/common/const';
import { setClipboard } from '@/common/functions';
import { $http } from '@/common/http';
import { usePush } from '@/composables/usePush';
import { useUserStore, useRefs } from '@/stores';
const userStore = useUserStore();
const { USERINFO } = useRefs(userStore);
const emit = defineEmits(['select']);
const ActorSearch = reactive({
    type: 'personal',
    page: 1,
    limit: 10,
})
const actorList = ref<any[]>([]);
const loading = ref(false);
const actorCreateRef = ref<any>(null);
const actorInitRef = ref<any>(null);
const total = ref<number>(0);
const getActorList = () => {
    loading.value = true;
    actorCreateRef.value?.close?.();
    actorInitRef.value?.close?.();
    $http.get('/app/shortplay/api/Actor/index', { params: ActorSearch }).then((res: any) => {
        if (res.code === ResponseCode.SUCCESS) {
            actorList.value = [...actorList.value, ...res.data.data];
            total.value = res.data.total;
        }
    }).finally(() => {
        loading.value = false;
    })
}
const previewImageVisible = ref(false);
const imageList = ref<any[]>([]);
const handlePreviewImage = (currentItem: any) => {
    if (!currentItem.headimg && !currentItem.three_view_image) return;
    imageList.value = [currentItem.headimg, currentItem.three_view_image];
    nextTick(() => {
        previewImageVisible.value = true;
    })
}
const { subscribe, unsubscribeAll } = usePush();
const addListener = () => {
    subscribe('private-generateactorimage-' + USERINFO.value?.user, (res: any) => {
        console.log('channels complete info message', res);
        const findItem = actorList.value.find((item: any) => item.actor_id === res.id);
        if (findItem) {
            findItem.status_enum = res.status;
            if (res.image) {
                findItem.headimg = res.image;
            }
        }
    });
    subscribe('private-generateactorthreeviewimage-' + USERINFO.value?.user, (res: any) => {
        console.log('channels complete info message', res);
        const findItem = actorList.value.find((item: any) => item.actor_id === res.id);
        if (findItem) {
            findItem.status_enum = res.status;
            if (res.image) {
                findItem.three_view_image = res.image;
            }
        }
    });
}
const scrollBarEndReached = () => {
    if (actorList.value.length >= total.value) return;
    ActorSearch.page++;
    getActorList();
}
onMounted(() => {
    getActorList();
    addListener();
})
onUnmounted(() => {
    console.log('actors unmounted');
    unsubscribeAll();
})
</script>
<template>
    <!-- <div class="flex flex-column grid-gap-4 p-4"> -->
    <!-- <el-form class="flex flex-center grid-gap-4" @submit.prevent="getActorList">
                <el-form-item class="mb-0">
                    <el-input v-model="ActorSearch.name" placeholder="搜索演员" clearable @change="getActorList">
                        <template #suffix>
                            <el-icon>
                                <Search />
                            </el-icon>
                        </template>
</el-input>
</el-form-item>
<el-form-item class="mb-0">
    <el-input v-model="ActorSearch.actor_id" placeholder="演员ID" clearable @change="getActorList">
        <template #suffix>
                            <el-icon>
                                <Search />
                            </el-icon>
                        </template>
    </el-input>
</el-form-item>
<div class="flex-1"></div>
<el-form-item class="mb-0" style="width: 80px;">
    <el-select v-model="ActorSearch.species_type" placeholder="物种" clearable :teleported="false" @change="getActorList">
        <el-option v-for="item in WEBCONFIG?.enum?.actor_species_type" :key="item.value" :label="item.label"
            :value="item.value" />
    </el-select>
</el-form-item>
<el-form-item class="mb-0" style="width: 80px;">
    <el-select v-model="ActorSearch.gender" placeholder="性别" clearable :teleported="false" @change="getActorList">
        <el-option v-for="item in WEBCONFIG?.enum?.actor_gender" :key="item.value" :label="item.label"
            :value="item.value" />
    </el-select>
</el-form-item>
<el-form-item class="mb-0" style="width: 80px;">
    <el-select v-model="ActorSearch.age" placeholder="年龄" clearable :teleported="false" @change="getActorList">
        <el-option v-for="item in WEBCONFIG?.enum?.actor_age" :key="item.value" :label="item.label"
            :value="item.value" />
    </el-select>
</el-form-item>
</el-form> -->
    <el-scrollbar height="100%" wrap-class="flex-1" @end-reached="scrollBarEndReached">
        <div v-loading="loading" class="flex-1">
            <div
                class="grid-columns-xxl-6 grid-columns-xl-5 grid-columns-lg-4 grid-columns-md-3 grid-columns-sm-3 grid-columns-xs-3 grid-columns-p-2 grid-columns-p-1 grid-gap-4">
                <div class="grid-column-1 rounded-4 p-4  border-dashed actor-item flex flex-column flex-center grid-gap-4 actor-item pointer"
                    @click="actorCreateRef?.open?.(null)">
                    <el-icon class="rounded-4" size="20"
                        style="height: 40px; width: 40px;background-color: var(--el-fill-color-dark);">
                        <Plus />
                    </el-icon>
                    <span>添加演员</span>
                </div>
                <div class="grid-column-1 input-button rounded-4  border-solid flex flex-center grid-gap-2 actor-item"
                    v-for="item in actorList" :key="item.id">
                    <el-avatar :src="item.headimg" class="actor-avatar" :class="{ 'pointer': item.headimg }"
                        @click="handlePreviewImage(item)" fit="cover">
                        {{ item.name }}
                    </el-avatar>
                    <div class="flex-1 flex grid-gap-2 actor-status">
                        <span class="actor-tag pointer" title="复制演员"
                            @click.stop="setClipboard(`@${item.name}(${item.actor_id}) `)">{{
                                item.actor_id
                            }}</span>
                        <div class="flex-1"></div>
                        <span class="actor-tag"
                            :class="[`actor-tag--` + item.status_enum.props.type, item.status_enum.value === 'initializing' && item.is_edit ? 'pointer' : '']"
                            @click.stop="item.is_edit ? actorInitRef?.open?.(item.actor) : null">
                            {{ item.status_enum.label }}
                        </span>
                    </div>
                    <div class="flex-1 flex flex-column grid-gap-2 actor-info">
                        <div class="actor-name flex flex-center grid-gap-1">
                            <el-icon>
                                <UserFilled />
                            </el-icon>
                            <span>{{ item.name }}</span>
                        </div>
                        <div class="flex grid-gap-2">
                            <span class="actor-tag" v-if="item.species_type_enum">{{
                                item.species_type_enum?.label
                            }}</span>
                            <span class="actor-tag" v-if="item.gender_enum">{{ item.gender_enum?.label
                            }}</span>
                            <span class="actor-tag" v-if="item.age_enum">{{ item.age_enum?.label
                            }}</span>
                            <span class="actor-tag actor-tag--primary pointer" v-if="item.is_edit"
                                @click.stop="item.is_edit ? actorCreateRef?.open?.(item) : null">可编辑</span>
                            <span class="actor-tag actor-tag--info" v-else>公共角色</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </el-scrollbar>
    <el-image-viewer :url-list="imageList" v-if="previewImageVisible" @close="previewImageVisible = false" />
    <xl-actor-create ref="actorCreateRef" @success="getActorList" />
    <xl-actor-init ref="actorInitRef" @success="getActorList" />
    <!-- </div> -->
</template>
<style lang="scss" scoped>
.actor-item {
    height: 450px;
    position: relative;
    overflow: hidden;
    border-width: 1px;

    &:hover {
        background-color: var(--el-fill-color-dark);
    }

    .actor-avatar {
        height: 450px;
        width: 100%;
        border-radius: 0px;
    }

    .actor-status {
        position: absolute;
        top: 0;
        right: 0;
        width: 100%;
        padding: 10px;
        justify-content: flex-end;
        align-items: flex-end;
        z-index: 1;
    }

    .actor-info {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 10px;
        justify-content: flex-start;
        align-items: flex-start;
        z-index: 1;
    }

    .actor-name {
        background-color: rgba(0, 0, 0, 0.25);
        backdrop-filter: blur(10px);
        color: #FFFFFF;
        padding: 4px 8px;
        border-radius: 20px;
    }

    .actor-tag {
        background-color: rgba(0, 0, 0, 0.25);
        backdrop-filter: blur(10px);
        color: #FFFFFF;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;

        &--primary {
            color: var(--el-color-primary);
        }

        &--success {
            color: var(--el-color-success);
        }

        &--warning {
            color: var(--el-color-warning);
        }

        &--danger {
            color: var(--el-color-danger);
        }

        &--info {
            color: var(--el-color-info);
        }

        &.pointer:hover {
            background-color: rgba(0, 0, 0, 0.5);
        }
    }
}
</style>