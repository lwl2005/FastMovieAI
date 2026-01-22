<?php

namespace plugin\article\app\api\controller;

use app\Basic;
use plugin\article\app\model\PluginArticle;
use plugin\article\app\model\PluginArticleContent;
use support\Request;

class ArticleController extends Basic
{
    protected $notNeedLogin = ['index', 'details'];
    public function index(Request $request)
    {
        $alias = $request->get('key');
        $article = PluginArticle::where(['alias' => $alias, 'channels_uid' => $request->channels_uid])->find();
        if (!$article) {
            return $this->resData('文章不存在');
        }
        $content = PluginArticleContent::where(['article_id' => $article->id])->find();
        return $this->success('获取成功', $content->content);
    }
    public function details(Request $request)
    {
        $id = $request->get('id');
        if (!$id) {
            return $this->fail('文章ID不能为空');
        }
        $article = PluginArticle::where(['id' => $id, 'channels_uid' => $request->channels_uid])->with('content')->find();
        if (!$article) {
            $article = PluginArticle::where(['alias' => $id, 'channels_uid' => $request->channels_uid])->with('content')->find();
        }
        if (!$article) {
            return $this->fail('文章不存在');
        }
        return $this->success('获取成功', $article);
    }
}
