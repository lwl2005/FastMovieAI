<?php

namespace plugin\notification\app\admin\controller;

use app\Basic;
use app\expose\helper\Wechat;
use GuzzleHttp\Client;
use support\Request;

class WechatTemplateController extends Basic
{
    public function getTemplateId(Request $request)
    {
        $field=$request->post('field');
        $access_token = Wechat::getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token=" . $access_token;
        $params = [
            'template_id_short' => $request->post('template_id_short'),
            'keyword_name_list' => $request->post('keywords'),
        ];
        $client = new Client([
            'content_type' => 'application/json',
        ]);
        $response = $client->post($url, ['body' => json_encode($params, JSON_UNESCAPED_UNICODE)]);
        $data = json_decode($response->getBody()->getContents(), true);
        if (empty($data['template_id'])) {
            throw new \Exception("[{$data['errcode']}]" . $data['errmsg']);
        }
        return $this->success('获取成功',[$field=>$data['template_id']]);
    }
}
