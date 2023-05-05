<?php

namespace My\Telegram\Handlers;

use TelegramBot\Api\Types\Message;

abstract class BotCommandHandler extends BaseBotHandler
{
    abstract public function handle(Message $message, ?string $param): bool;

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
            chatId: $this->update->getMessage()->getChat()->getId(),
            text: $text,
            parseMode: $parseMode,
            disablePreview: $disablePreview,
            messageThreadId: $messageThreadId,
            replyToMessageId: $replyToMessageId ?? $this->update->getMessage()->getMessageId(),
            replyMarkup: $replyMarkup,
            disableNotification: $disableNotification,
        );
    }

    public function sendAction(string $action): void
    {
        $this->bot->sendChatAction(
            chatId: $this->update->getMessage()->getChat()->getId(),
            action: $action,
        );
    }
}
