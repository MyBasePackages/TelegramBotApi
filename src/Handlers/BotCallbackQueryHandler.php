<?php

namespace My\Telegram\Handlers;

use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Message;

abstract class BotCallbackQueryHandler extends BaseBotHandler
{
    abstract public function handle(CallbackQuery $callbackQuery): bool;

    public function replyMessage(
        string $text,
               $parseMode = null,
               $disablePreview = false,
               $messageThreadId = null,
               $replyToMessageId = null,
               $replyMarkup = null,
               $disableNotification = false
    ): Message {
        return $this->bot->sendMessage(
            chatId: $this->update->getCallbackQuery()->getFrom()->getId(),
            text: $text,
            parseMode: $parseMode,
            disablePreview: $disablePreview,
            messageThreadId: $messageThreadId,
            replyToMessageId: $replyToMessageId ?? $this->update->getCallbackQuery()->getMessage()?->getMessageId(),
            replyMarkup: $replyMarkup,
            disableNotification: $disableNotification,
        );
    }

    public function sendAction(string $action): void
    {
        $this->bot->sendChatAction(
            chatId: $this->update->getCallbackQuery()->getFrom()->getId(),
            action: $action,
        );
    }

    public function answer(): void
    {
        $this->bot->answerCallbackQuery(
            callbackQueryId: $this->update->getCallbackQuery()->getId(),
        );
    }
}
