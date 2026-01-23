<?php

namespace plugin\article\app\validate;

use app\expose\validate\Validate;

class Article extends Validate
{
    protected $rule =   [
        'classify_id'=>'require',
        'title'=>'require',
        'subtitle'=>'length:0,200',
        'thumb'=>'array',
        'keywords'=>'length:0,200',
        'description'=>'length:0,300',
        'alias'=>'alphaDash|length:0,30',
        'content'=>'require'
    ];

    protected $message  =   [
        'classify_id.require' => '请选择分类',
        'title.require' => '请输入标题',
        'subtitle.length' => '副标题长度不能超过200个字符',
        'thumb.array' => '缩略图格式错误',
        'keywords.length' => '关键词长度不能超过200个字符',
        'description.length' => '描述长度不能超过300个字符',
        'alias.alphaDash' => '别名只能是字母、数字、下划线和破折号',
        'alias.length' => '别名长度不能超过30个字符',
        'content.require' => '请输入内容'
    ];
    protected $scene = [
        'agreement'  =>  ['title','alias','content'],
    ];
}
