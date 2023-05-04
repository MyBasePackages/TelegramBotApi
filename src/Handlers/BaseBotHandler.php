<?php

namespace My\Telegram\Handlers;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

abstract class BaseBotHandler
{
    public BotApi $bot;

    public Update $update;
}
