<script setup lang="ts">
import { $http } from '@/common/http';
import { ResponseCode } from '@/common/const';
import { ElMessage } from 'element-plus';
import IconUploadImageSvg from '@/svg/icon/icon-upload-image.vue';

const emit = defineEmits(['success']);
const propDialogVisible = ref(false);
const propForm = reactive({
    id: '',
    image_model_id: '',
    three_view_model_id: '',
    image_state: false,
    three_view_image_state: false,
    image: '',
    reference_image: '',
    three_view_image: '',
    image_reference_state: false,
})
const originProp = ref<any>();
const propLoading = ref(false);
const openPropInitDialog = (prop?: any) => {
    if (prop && prop.status_enum.value !== 'initializing') {
        return;
    }
    if (prop) {
        originProp.value = prop;
        propForm.id = prop.id;
        propForm.image = prop.image;
        propForm.three_view_image = prop.three_view_image;
    }
    propDialogVisible.value = true;
}
const cancelPropDialog = () => {
    propDialogVisible.value = false;
    propForm.id = '';
    propForm.image = '';
    propForm.three_view_image = '';
    propForm.image_model_id = '';
    propForm.three_view_model_id = '';
    propForm.image_state = false;
    propForm.three_view_image_state = false;
    propForm.image_reference_state = false;
    propForm.reference_image = '';
    originProp.value = undefined;
}
const handleImageReferenceState = (value: any) => {
    if (value) {
        propForm.reference_image = originProp.value?.image;
        propForm.image = '';
    } else {
        propForm.image = originProp.value?.image;
        propForm.reference_image = '';
    }
}
const handleImageReset = (value: any) => {
    if (value) {
        propForm.image = '';
    } else {
        propForm.image = originProp.value?.image;
    }
}
const handleThreeViewImageReset = (value: any) => {
    if (value) {
        propForm.three_view_image = '';
    } else {
        propForm.three_view_image = originProp.value?.three_view_image;
    }
}
const submitPropDialog = () => {
    if (propLoading.value) return;
    propLoading.value = true;
    $http.post('/app/shortplay/api/Prop/initializing', propForm).then((res: any) => {
        if (res.code === ResponseCode.SUCCESS) {
            emit('success', { id: propForm.id });
        } else {
            ElMessage.error(res.msg);
        }
    }).catch(() => {
        ElMessage.error('初始化失败');
    }).finally(() => {
        propLoading.value = false;
    })
}
const uploadImageRef = ref<any>(null);
const handleUploadSuccess = (response: any) => {
    if (response.code === ResponseCode.SUCCESS) {
        switch (response.data.dir_name) {
            case 'prop/image':
                if (propForm.image_reference_state) {
                    propForm.reference_image = response.data.url;
                } else {
                    propForm.image = response.data.url;
                }
                originProp.value.image = response.data.url;
                uploadImageRef.value?.clearFiles();
                break;
            case 'prop/three_view':
                propForm.three_view_image = response.data.url;
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
    return (!propForm.image && !propForm.image_model_id) || (!propForm.three_view_image && !propForm.three_view_model_id);
})
defineExpose({
    open: openPropInitDialog,
    close: cancelPropDialog
})
</script>
<template>
    <el-dialog v-model="propDialogVisible" class="generate-scene-dialog" draggable width="min(100%,800px)"
        @close="cancelPropDialog">
        <template #header>
            <span class="font-weight-600">初始化物品</span>
        </template>
        <div class="flex flex-column grid-gap-4">
            <div class="flex grid-gap-4">
                <el-upload ref="uploadImageRef" class="input-upload" drag
                    :data="{ dir_name: 'prop/image', dir_title: '物品图片' }"
                    :action="$http.getCompleteUrl('app/shortplay/api/Uploads/upload')" :headers="$http.getHeaders()"
                    accept="image/jpeg,image/png" :limit="1" type="cover" :on-success="handleUploadSuccess"
                    :show-file-list="false" :on-error="handleUploadError">
                    <template v-if="!propForm.image && !propForm.reference_image">
                        <el-icon class="el-icon--upload">
                            <IconImageSvg />
                        </el-icon>
                        <div class="el-upload__text">
                            <span class="h10 text-success">选择上传或使用AI生成</span>
                        </div>
                        <div class="el-upload__text">
                            <span class="h10">拖拽物品图片到此处或</span>
                            <span class="h10">点击上传</span>
                        </div>
                        <div class="el-upload__text">
                            <span class="h10">支持上传格式：</span>
                            <span class="h10">PNG, JPG, JPEG</span>
                        </div>
                        <div class="el-upload__text">
                            <span class="h10">物品图片建议比例：</span>
                            <span class="h10">1:1</span>
                        </div>
                    </template>
                    <template v-else>
                        <el-image :src="propForm.image_reference_state ? propForm.reference_image : propForm.image"
                            class="image-cover" fit="fill"></el-image>
                    </template>
                </el-upload>
                <div class="flex-1">
                    <div class="flex grid-gap-2">
                        <el-switch v-model="propForm.image_state" inline-prompt style="--el-switch-on-color: #13ce66;"
                            active-text="重置物品" inactive-text="关闭" @change="handleImageReset" />
                        <el-switch v-model="propForm.image_reference_state" inline-prompt
                            style="--el-switch-on-color: #13ce66;" active-text="作为AI参考" inactive-text="直接上传"
                            @change="handleImageReferenceState" />
                    </div>
                    <xl-models v-model="propForm.image_model_id" scene="prop_image" no-init
                        :scrollProps="{ height: '320px' }" />
                </div>
            </div>
            <div class="flex grid-gap-4">
                <el-upload ref="uploadImageRef" class="input-upload" drag
                    :data="{ dir_name: 'prop/three_view', dir_title: '物品六视图' }"
                    :action="$http.getCompleteUrl('app/shortplay/api/Uploads/upload')" :headers="$http.getHeaders()"
                    accept="image/jpeg,image/png" :limit="1" type="cover" :on-success="handleUploadSuccess"
                    :show-file-list="false" :on-error="handleUploadError">
                    <template v-if="!propForm.three_view_image">
                        <el-icon class="el-icon--upload">
                            <IconUploadImageSvg />
                        </el-icon>
                        <div class="el-upload__text">
                            <span class="h10 text-success">选择上传或使用AI生成</span>
                        </div>
                        <div class="el-upload__text">
                            <span class="h10">拖拽物品六视图到此处或</span>
                            <span class="h10">点击上传</span>
                        </div>
                        <div class="el-upload__text">
                            <span class="h10">支持上传格式：</span>
                            <span class="h10">PNG, JPG, JPEG</span>
                        </div>
                        <div class="el-upload__text">
                            <span class="h10">物品图片建议比例：</span>
                            <span class="h10">1:1</span>
                        </div>
                    </template>
                    <template v-else>
                        <el-image :src="propForm.three_view_image" class="image-cover" fit="contain"></el-image>
                    </template>
                </el-upload>
                <div class="flex-1">
                    <el-switch v-model="propForm.three_view_image_state" inline-prompt
                        @change="handleThreeViewImageReset" style="--el-switch-on-color: #13ce66;" active-text="重置六视图"
                        inactive-text="关闭" />
                    <xl-models v-model="propForm.three_view_model_id" scene="prop_three_view_image" no-init
                        :scrollProps="{ height: '320px' }" />
                </div>
            </div>
        </div>
        <template #footer>
            <div class="flex flex-center grid-gap-2 w-100">
                <el-button type="info" @click="cancelPropDialog" :disabled="propLoading">取消</el-button>
                <div class="flex-1"></div>
                <el-button type="success" @click="submitPropDialog" :disabled="propLoading || disabled"
                    :loading="propLoading">提交</el-button>
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