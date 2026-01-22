<?php

namespace plugin\model\utils\enum;

use app\expose\enum\builder\Enum;

class ModelTaskStatus extends Enum
{
    const WAIT = [
        'label' => '待处理',
        'value' => 'wait',
        'props' => [
            'type' => 'info'
        ]
    ];
    const PROCESSING = [
        'label' => '处理中',
        'value' => 'processing',
        'props' => [
            'type' => 'primary'
        ]
    ];
    const WAIT_DOWNLOAD = [
        'label' => '等待下载',
        'value' => 'wait_download',
        'props' => [
            'type' => 'primary'
        ]
    ];
    const DOWNLOADING = [
        'label' => '下载中',
        'value' => 'downloading',
        'props' => [
            'type' => 'primary'
        ]
    ];
    const UPLOADING = [
        'label' => '上传中',
        'value' => 'uploading',
        'props' => [
            'type' => 'primary'
        ]
    ];
    const SUCCESS = [
        'label' => '成功',
        'value' => 'success',
        'props' => [
            'type' => 'success'
        ]
    ];
    const FAIL = [
        'label' => '失败',
        'value' => 'fail',
        'props' => [
            'type' => 'danger'
        ]
    ];
}
