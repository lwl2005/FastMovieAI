<script setup lang="ts">
import { $http } from '@/common/http';
import { ResponseCode } from '@/common/const';
import { ElMessage } from 'element-plus';
import IconUploadImageSvg from '@/svg/icon/icon-upload-image.vue';

const emit = defineEmits(['success']);
const actorDialogVisible = ref(false);
const actorForm = reactive({
    id: '',
    image_model_id: '',
    three_view_model_id: '',
    image_state: false,
    three_view_image_state: false,
    headimg: '',
    reference_headimg: '',
    three_view_image: '',
    image_reference_state: false,
})
const originActor = ref<any>();
const actorLoading = ref(false);
const openActorInitDialog = (actor?: any) => {
    if (actor && actor.status_enum.value !== 'initializing') {
        return;
    }
    if (actor) {
        originActor.value = actor;
        actorForm.id = actor.id;
        actorForm.headimg = actor.headimg;
        actorForm.three_view_image = actor.three_view_image;
    }
    actorDialogVisible.value = true;
}
const cancelActorDialog = () => {
    actorDialogVisible.value = false;
    actorForm.id = '';
    actorForm.headimg = '';
    actorForm.three_view_image = '';
    actorForm.image_model_id = '';
    actorForm.three_view_model_id = '';
    actorForm.image_state = false;
    actorForm.three_view_image_state = false;
    actorForm.image_reference_state = false;
    actorForm.reference_headimg = '';
    originActor.value = undefined;
}
const handleImageReferenceState = (value: any) => {
    if (value) {
        actorForm.reference_headimg = originActor.value?.headimg;
        actorForm.headimg = '';
    } else {
        actorForm.headimg = originActor.value?.headimg;
        actorForm.reference_headimg = '';
    }
}
const handleImageReset = (value: any) => {
    if (value) {
        actorForm.headimg = '';
    } else {
        actorForm.headimg = originActor.value?.headimg;
    }
}
const handleThreeViewImageReset = (value: any) => {
    if (value) {
        actorForm.three_view_image = '';
    } else {
        actorForm.three_view_image = originActor.value?.three_view_image;
    }
}
const submitActorDialog = () => {
    if (actorLoading.value) return;
    actorLoading.value = true;
    $http.post('/app/shortplay/api/Actor/initializing', actorForm).then((res: any) => {
        if (res.code === ResponseCode.SUCCESS) {
            emit('success', { id: actorForm.id });
        } else {
            ElMessage.error(res.msg);
        }
    }).catch((error) => {
        console.log(error);
        ElMessage.error('初始化失败');
    }).finally(() => {
        actorLoading.value = false;
    })
}
const uploadImageRef = ref<any>(null);
const handleUploadSuccess = (response: any) => {
    if (response.code === ResponseCode.SUCCESS) {
        switch (response.data.dir_name) {
            case 'actor/image':
                if (actorForm.image_reference_state) {
                    actorForm.reference_headimg = response.data.url;
                } else {
                    actorForm.headimg = response.data.url;
                }
                originActor.value.headimg = response.data.url;
                uploadImageRef.value?.clearFiles();
                break;
            case 'actor/three_view':
                actorForm.three_view_image = response.data.url;
                uploadImageRef.value?.clearFiles();
                break;
        }
    } else {
        ElMessage.error(response.msg);
    }
}
const handleUploadError = () => {
    uploadImageRef.value?.clearFiles();
}
const disabled = computed(() => {
    return (!actorForm.headimg && !actorForm.image_model_id) || (!actorForm.three_view_image && !actorForm.three_view_model_id);
})
defineExpose({
    open: openActorInitDialog,
    close: cancelActorDialog
})
</script>
<template>
    <el-dialog v-model="actorDialogVisible" class="generate-scene-dialog" draggable width="min(100%,800px)"
        @close="cancelActorDialog">
        <template #header>
            <span class="font-weight-600">初始化演员</span>
        </template>
        <div class="flex flex-column grid-gap-4">
            <div class="flex grid-gap-4">
                <el-upload ref="uploadImageRef" class="input-upload" drag
                    :data="{ dir_name: 'actor/image', dir_title: '演员形象照片' }"
                    :action="$http.getCompleteUrl('app/shortplay/api/Uploads/upload')" :headers="$http.getHeaders()"
                    accept="image/jpeg,image/png" :limit="1" type="cover" :on-success="handleUploadSuccess"
                    :show-file-list="false" :on-error="handleUploadError">
                    <template v-if="!actorForm.headimg && !actorForm.reference_headimg">
                        <el-icon class="el-icon--upload">
                            <IconUploadImageSvg />
                        </el-icon>
                        <div class="el-upload__text">
                            <span class="h10 text-success">选择上传或使用AI生成</span>
                        </div>
                        <div class="el-upload__text">
                            <span class="h10">拖拽演员形象照片到此处或</span>
                            <span class="h10">点击上传</span>
                        </div>
                        <div class="el-upload__text">
                            <span class="h10">支持上传格式：</span>
                            <span class="h10">PNG, JPG, JPEG</span>
                        </div>
                        <div class="el-upload__text">
                            <span class="h10">形象照片建议比例：</span>
                            <span class="h10">1:1</span>
                        </div>
                    </template>
                    <template v-else>
                        <el-image
                            :src="actorForm.image_reference_state ? actorForm.reference_headimg : actorForm.headimg"
                            class="image-cover" fit="fill"></el-image>
                    </template>
                </el-upload>
                <div class="flex-1">
                    <div class="flex grid-gap-2">
                        <el-switch v-model="actorForm.image_state" inline-prompt style="--el-switch-on-color: #13ce66;"
                            active-text="重置形象" inactive-text="关闭" @change="handleImageReset" />
                        <el-switch v-model="actorForm.image_reference_state" inline-prompt
                            style="--el-switch-on-color: #13ce66;" active-text="作为AI参考" inactive-text="直接上传"
                            @change="handleImageReferenceState" />
                    </div>
                    <xl-models v-model="actorForm.image_model_id" scene="actor_image" no-init
                        :scrollProps="{ height: '320px' }" />
                </div>
            </div>
            <div class="flex grid-gap-4">
                <el-upload ref="uploadImageRef" class="input-upload" drag
                    :data="{ dir_name: 'actor/three_view', dir_title: '演员三视图' }"
                    :action="$http.getCompleteUrl('app/shortplay/api/Uploads/upload')" :headers="$http.getHeaders()"
                    accept="image/jpeg,image/png" :limit="1" type="cover" :on-success="handleUploadSuccess"
                    :show-file-list="false" :on-error="handleUploadError">
                    <template v-if="!actorForm.three_view_image">
                        <el-icon class="el-icon--upload">
                            <IconUploadImageSvg />
                        </el-icon>
                        <div class="el-upload__text">
                            <span class="h10 text-success">选择上传或使用AI生成</span>
                        </div>
                        <div class="el-upload__text">
                            <span class="h10">拖拽演员三视图到此处或</span>
                            <span class="h10">点击上传</span>
                        </div>
                        <div class="el-upload__text">
                            <span class="h10">支持上传格式：</span>
                            <span class="h10">PNG, JPG, JPEG</span>
                        </div>
                        <div class="el-upload__text">
                            <span class="h10">形象照片建议比例：</span>
                            <span class="h10">1:1</span>
                        </div>
                    </template>
                    <template v-else>
                        <el-image :src="actorForm.three_view_image" class="image-cover" fit="contain"></el-image>
                    </template>
                </el-upload>
                <div class="flex-1">
                    <el-switch v-model="actorForm.three_view_image_state" inline-prompt
                        @change="handleThreeViewImageReset" style="--el-switch-on-color: #13ce66;" active-text="重置三视图"
                        inactive-text="关闭" />
                    <xl-models v-model="actorForm.three_view_model_id" scene="actor_three_view_image" no-init
                        :scrollProps="{ height: '320px' }" />
                </div>
            </div>
        </div>
        <template #footer>
            <div class="flex flex-center grid-gap-2 w-100">
                <el-button type="info" @click="cancelActorDialog" :disabled="actorLoading">取消</el-button>
                <div class="flex-1"></div>
                <el-button type="success" @click="submitActorDialog" :disabled="actorLoading || disabled"
                    :loading="actorLoading">提交</el-button>
            </div>
        </template>
    </el-dialog>
</template>
<style lang="scss" scoped>
.input-upload {
    width: 400px;

    :deep(.el-upload) {
        --el-fill-color-blank: var(--el-bg-color-overlay);
        --el-color-primary: var(--el-border-color-hover);
        --el-upload-dragger-padding-horizontal: 0;
        --el-upload-dragger-padding-vertical: 0;

        .el-upload-dragger {
            border: none;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 6px;
        }
    }

    .image-cover {
        width: 400px;
        height: 400px;
    }
}
</style>