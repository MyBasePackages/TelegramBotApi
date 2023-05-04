<?php

namespace My\Telegram\Handlers;

use TelegramBot\Api\Types\Inline\InlineQuery;

abstract class BotInlineQueryHandler extends BaseBotHandler
{
    abstract public function handle(InlineQuery $inlineQuery): bool;
}
