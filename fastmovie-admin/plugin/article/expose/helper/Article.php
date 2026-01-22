<?php

namespace plugin\article\expose\helper;

use app\expose\build\builder\ComponentBuilder;
use app\expose\build\builder\FormBuilder;
use app\expose\enum\State;
use plugin\article\app\admin\controller\IndexController;
use plugin\article\app\model\PluginArticle;
use plugin\article\app\model\PluginArticleContent;
use plugin\article\app\validate\Article as ValidateArticle;
use support\Request;
use think\facade\Db;

class Article extends IndexController
{
    public function agreement(Request $request,$alias)
    {
        if ($request->method() == 'POST') {
            $D = $request->post();
            $validate = new ValidateArticle;
            try {
                $validate->scene('agreement')->check($D);
            } catch (\Throwable $th) {
                return $this->exception($th);
            }
            Db::startTrans();
            try {
                $PluginArticle = PluginArticle::where(['alias' => $alias])->find();
                if (!$PluginArticle) {
                    $PluginArticle = new PluginArticle;
                }
                $PluginArticle->title = $D['title'];
                $PluginArticle->alias = $D['alias'];
                $PluginArticle->state = State::YES['value'];
                $PluginArticle->release_time = date('Y-m-d H:i:s');
                $PluginArticle->save();
                $PluginArticleContent = PluginArticleContent::where(['article_id' => $PluginArticle->id])->find();
                if (!$PluginArticleContent) {
                    $PluginArticleContent = new PluginArticleContent;
                }
                $PluginArticleContent->article_id = $PluginArticle->id;
                $PluginArticleContent->content = $D['content'];
                $PluginArticleContent->save();
                Db::commit();
            } catch (\Throwable $th) {
                Db::rollback();
                return $this->fail($th->getMessage());
            }
            $this->cacheHtml($PluginArticle->id);
            return $this->success('编辑成功');
        }
        $PluginArticle = PluginArticle::alias('a')->where(['a.alias' => $alias])->join('plugin_article_content c', 'c.article_id=a.id')->field('a.*,c.content')->find();
        $builder = new FormBuilder(null, null, ['size' => 'large','submitEvent' => 'SILENT']);
        $Component = new ComponentBuilder;
        $builder->add('title', '标题', 'input', '', [
            'required' => true,
            'props' => [
                'maxlength' => 100,
                'show-word-limit' => true
            ]
        ]);
        $builder->add('alias', '别名', 'input', $alias, [
            'col' => [
                'xs' => 24,
                'md' => 12
            ],
            'prompt' => [
                $Component->add('text', ['default' => '设置别名后可通过别名访问文章'], ['type' => 'info', 'size' => 'small'])->builder()
            ],
            'props' => [
                'maxlength' => 30,
                'show-word-limit' => true,
                'disabled' => true
            ]
        ]);
        $builder->add('content', '内容', 'wangeditor', '', [
            'required' => true,
            'props' => [
                'class' => 'vh-55'
            ]
        ]);
        if($PluginArticle){
            $builder->setData($PluginArticle->toArray());
        }
        return $this->resData($builder);
    }
}
