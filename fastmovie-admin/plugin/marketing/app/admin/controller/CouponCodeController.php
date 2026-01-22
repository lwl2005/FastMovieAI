<?php

namespace plugin\marketing\app\admin\controller;

use app\Basic;
use app\expose\build\builder\FormBuilder;
use app\expose\build\builder\TableBuilder;
use app\expose\enum\Action;
use app\expose\enum\State;
use plugin\marketing\app\model\PluginMarketingCoupon;
use plugin\marketing\app\model\PluginMarketingCouponCode;
use plugin\marketing\utils\enum\ReceiveType;
use plugin\marketing\utils\enum\UseState;
use plugin\marketing\utils\enum\UseType;
use support\Request;

class CouponCodeController extends Basic
{
    public function __construct()
    {
        $this->model = new PluginMarketingCoupon();
    }
    public function indexGetTable(Request $request)
    {
        $coupon_id = $request->get('coupon_id');
        if ($coupon_id) {
            $coupon_id = (int)$coupon_id;
        }
        $builder = new TableBuilder();
        $builder->addAction('操作', [
            'width' => '100px',
            'fixed' => 'right'
        ]);
        $builder->addTableAction('删除', [
            'model' => Action::COMFIRM['value'],
            'path' => '/app/marketing/admin/CouponCode/delete',
            'props' => [
                'message' => '确定删除该优惠券码吗？'
            ],
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'danger',
                    'size' => 'small'
                ]
            ]
        ]);
        $builder->addHeader();
        $builder->addHeaderAction('优惠券', [
            'model' => Action::REDIRECT['value'],
            'path' => '/app/marketing/admin/Coupon/index',
            'component' => [
                'name' => 'button',
                'props' => [
                    'type' => 'success'
                ]
            ]
        ]);
        $formBuilder = new FormBuilder(null, null, [
            'inline' => true
        ]);
        $formBuilder->add('coupon_code', '券码', 'input', '', [
            'props' => [
                'placeholder' => '券码搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('state', '状态', 'select', null, [
            'options' => UseState::getOptions(),
            'props' => [
                'placeholder' => '状态搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('receive_state', '领取状态', 'select', null, [
            'options' => [
                [
                    'label' => '已领取',
                    'value' => State::YES['value']
                ],
                [
                    'label' => '未领取',
                    'value' => State::NO['value']
                ]
            ],
            'props' => [
                'placeholder' => '状态搜索',
                'clearable' => true
            ]
        ]);
        $formBuilder->add('coupon_id', '优惠券', 'select', $coupon_id, [
            'options' => PluginMarketingCoupon::options(),
            'props' => [
                'placeholder' => '优惠券搜索',
                'clearable' => true,
                'filterable' => true
            ]
        ]);
        $builder->addScreen($formBuilder);
        $builder->add('id', 'ID', [
            'props' => [
                'width' => '80px'
            ]
        ]);
        $builder->add('title', '优惠券名', [
            'component' => [
                'name' => 'table-userinfo',
                'props' => [
                    'nickname' => 'coupon.title',
                    'copy' => 'coupon_code',
                ]
            ],
            'props' => [
                'minWidth' => '340px'
            ]
        ]);
        $builder->add('user', '领取人', [
            'where' => [
                ['uid', 'not null', null]
            ],
            'component' => [
                'name' => 'table-userinfo',
                'props' => [
                    'nickname' => 'user.nickname',
                    'avatar' => 'user.headimg',
                    'info' => 'user.id'
                ]
            ],
            'props' => [
                'minWidth' => '300px'
            ]
        ]);
        $builder->add('state', '状态', [
            'component' => [
                'name' => 'tag',
                'options' => UseState::getOptions(),
            ],
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('receive_state', '领取状态', [
            'component' => [
                'name' => 'tag',
                'options' => [
                    [
                        'label' => '已领取',
                        'value' => State::YES['value'],
                        'props' => [
                            'type' => 'success'
                        ]
                    ],
                    [
                        'label' => '未领取',
                        'value' => State::NO['value'],
                        'props' => [
                            'type' => 'info'
                        ]
                    ]
                ],
            ],
            'props' => [
                'width' => '100px'
            ]
        ]);
        $builder->add('receive_timer', '领取/过期时间', [
            'component' => [
                'name' => 'tag',
                'options' => [
                    [
                        'index' => 0,
                        'props' => [
                            'type' => 'primary'
                        ]
                    ],

                    [
                        'index' => 1,
                        'props' => [
                            'type' => 'success'
                        ]
                    ],
                    [
                        'index' => 2,
                        'props' => [
                            'type' => 'danger'
                        ]
                    ],
                    [
                        'index' => 3,
                        'props' => [
                            'type' => 'warning'
                        ]
                    ]
                ]
            ],
            'props' => [
                'width' => '180px'
            ]
        ]);
        $builder->add('receive_use', '领取\使用规则', [
            'component' => [
                'name' => 'tag',
                'options' => [
                    [
                        'index' => 0,
                        'props' => [
                            'type' => 'success'
                        ]
                    ],
                    [
                        'index' => 1,
                        'props' => [
                            'type' => 'primary'
                        ]
                    ]
                ]
            ],
            'props' => [
                'width' => '160px'
            ]
        ]);
        $builder->add('timer', '使用时间', [
            'component' => [
                'name' => 'tag',
                'options' => [
                    [
                        'index' => 0,
                        'props' => [
                            'type' => 'success'
                        ]
                    ],
                    [
                        'index' => 1,
                        'props' => [
                            'type' => 'danger'
                        ]
                    ]
                ]
            ],
            'props' => [
                'minWidth' => '180px'
            ]
        ]);
        $builder->add('coupon.discount_text', '优惠', [
            'component' => [
                'name' => 'tag'
            ],
            'props' => [
                'minWidth' => '180px'
            ]
        ]);
        $builder = $builder->builder();
        return $this->resData($builder);
    }
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $where = [];
        $coupon_code = $request->get('coupon_code');
        if ($coupon_code) {
            $where[] = ['coupon_code', 'like', "%{$coupon_code}%"];
        }
        $state = $request->get('state');
        if ($state !== null) {
            $where[] = ['state', '=', $state];
        }
        $receive_state = $request->get('receive_state');
        if ($receive_state !== null) {
            if ($receive_state) {
                $where[] = ['receive_time', 'NOT NULL', NULL];
            } else {
                $where[] = ['receive_time', 'NULL', NULL];
            }
        }
        $coupon_id = $request->get('coupon_id');
        if ($coupon_id !== null) {
            $where[] = ['coupon_id', '=', $coupon_id];
        }
        $list = PluginMarketingCouponCode::where($where)->with(['user' => function ($query) {
            $query->field('id,nickname,headimg,channels_uid');
        }, 'coupon' => function ($query) {
            $query->field('id,title,receive_type,use_type,discount,full_price,money,coupon_rule');
        }])
            ->order('id desc')->paginate($limit)->each(function ($item) {
                $item->receive_use = [ReceiveType::getText($item->receive_type), UseType::getText($item->use_type)];
                $item->timer = [$item->start_time, $item->end_time];
                $receive_state = 0;
                if ($item->receive_time) {
                    $receive_state = 1;
                }
                $item->receive_state = $receive_state;
                $item->receive_timer = ["有效期：{$item->day}天", $item->receive_time, $item->expire_time,$item->use_time];
            });
        return $this->resData($list);
    }
    public function delete(Request $request)
    {
        $id = $request->post('id');
        $PluginMarketingCouponCode = PluginMarketingCouponCode::where(['id' => $id])->find();
        if (!$PluginMarketingCouponCode) {
            return $this->fail('券码不存在');
        }
        if ($PluginMarketingCouponCode->state === 1) {
            return $this->fail('已使用的券码不能删除');
        }
        if (!$PluginMarketingCouponCode->delete()) {
            return $this->fail('删除失败');
        }
        return $this->success('删除成功');
    }
}
