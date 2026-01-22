<?php

namespace plugin\control\utils\yidevs;

use plugin\control\utils\yidevs\trait\Audio;
use plugin\control\utils\yidevs\trait\AudioAssistant;
use plugin\control\utils\yidevs\trait\Chat;
use plugin\control\utils\yidevs\trait\ChatAssistant;
use plugin\control\utils\yidevs\trait\Draw;
use plugin\control\utils\yidevs\trait\DrawAssistant;
use plugin\control\utils\yidevs\trait\Video;
use plugin\control\utils\yidevs\trait\VideoAssistant;

class Yidevs
{
    use Chat, ChatAssistant, Draw, DrawAssistant, Video, VideoAssistant, Audio, AudioAssistant;
}
