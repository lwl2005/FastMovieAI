<?php

namespace plugin\model\utils\enum;

use app\expose\enum\builder\Enum;

class ModelScene extends Enum
{
    const CREATIVE_SCRIPT = [
        'label' => '创意剧本',
        'value' => 'creative_script',
        'props' => [
            'type' => 'primary',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const CREATIVE_EPISODE = [
        'label' => '续写分集',
        'value' => 'creative_episode',
        'props' => [
            'type' => 'primary',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const CREATIVE_SCENES = [
        'label' => '创作场景',
        'value' => 'creative_scenes',
        'props' => [
            'type' => 'primary',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const CREATIVE_STORYBOARDS = [
        'label' => '创作分镜',
        'value' => 'creative_storyboards',
        'props' => [
            'type' => 'primary',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const DRAMA_COVER = [
        'label' => '短剧封面',
        'value' => 'drama_cover',
        'props' => [
            'type' => 'warning',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const SCENE_IMAGE = [
        'label' => '场景图片',
        'value' => 'scene_image',
        'props' => [
            'type' => 'warning',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const ACTOR_IMAGE = [
        'label' => '角色形象',
        'value' => 'actor_image',
        'props' => [
            'type' => 'warning',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const ACTOR_THREE_VIEW_IMAGE = [
        'label' => '角色三视图',
        'value' => 'actor_three_view_image',
        'props' => [
            'type' => 'warning',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const STORYBOARD_IMAGE = [
        'label' => '分镜图片',
        'value' => 'storyboard_image',
        'props' => [
            'type' => 'warning',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const CHARACTER_LOOK_COSTUME = [
        'label' => '角色服饰',
        'value' => 'character_look_costume',
        'props' => [
            'type' => 'warning',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const ACTOR_COSTUME = [
        'label' => '角色换装',
        'value' => 'actor_costume',
        'props' => [
            'type' => 'warning',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const ACTOR_COSTUME_THREE_VIEW = [
        'label' => '角色换装三视图',
        'value' => 'actor_costume_three_view',
        'props' => [
            'type' => 'warning',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const PROP_IMAGE = [
        'label' => '物品图片',
        'value' => 'prop_image',
        'props' => [
            'type' => 'warning',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const PROP_THREE_VIEW_IMAGE = [
        'label' => '物品三视图',
        'value' => 'prop_three_view_image',
        'props' => [
            'type' => 'warning',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const STORYBOARD_VIDEO = [
        'label' => '分镜视频',
        'value' => 'storyboard_video',
        'props' => [
            'type' => 'success',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const DIALOGUE_VOICE = [
        'label' => '台词语音',
        'value' => 'dialogue_voice',
        'props' => [
            'type' => 'danger',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const STORYBOARD_NARRATION_VOICE = [
        'label' => '分镜旁白语音',
        'value' => 'storyboard_narration_voice',
        'props' => [
            'type' => 'danger',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const STORYBOARD_SFX_VOICE = [
        'label' => '分镜音效',
        'value' => 'storyboard_sfx_voice',
        'props' => [
            'type' => 'danger',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
    const STORYBOARD_MUSIC_VOICE = [
        'label' => '分镜音乐',
        'value' => 'storyboard_music_voice',
        'props' => [
            'type' => 'danger',
        ],
        'extra' => [
            'charge_unit' => '次',
        ]
    ];
}
