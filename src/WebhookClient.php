<?php

namespace My\Telegram;

use Exception;
use Illuminate\Support\Str;
use My\Telegram\Handlers\BotCallbackQueryHandler;
use My\Telegram\Handlers\BotCommandHandler;
use My\Telegram\Handlers\BotInlineQueryHandler;
use My\Telegram\Handlers\BotMessageHandler;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;
use Throwable;

class WebhookClient
{
    private BotApi $bot;

    /** @var BotMessageHandler[] */
    private array $messages = [];

    private array $texts = [];

    private array $commands = [];

    /** @var BotCallbackQueryHandler[] */
    private array $callbackQueries = [];

    private array $callbackQueryData = [];

    /** @var BotInlineQueryHandler[] */
    private array $inlineQueries = [];

    private array $inlineQueriesQuery = [];

    public function __construct(
        string $token,
    ) {
        $this->bot = new BotApi($token);
    }

    public function onMessage(BotMessageHandler $messageHandler): void
    {
        $messageHandler->bot = $this->bot;
        $this->messages[] = $messageHandler;
    }

    public function onMessages(array $handlers): void
    {
        foreach ($handlers as $messageHandler) {
            $this->onMessage($messageHandler);
        }
    }

    public function onCommand(string $command, BotCommandHandler $commandHandler): void
    {
        $commandHandler->bot = $this->bot;
        $this->commands[trim($command)] = $commandHandler;
    }

    public function onCommands(array $handlers): void
    {
        foreach ($handlers as $command => $handler) {
            $this->onCommand($command, $handler);
        }
    }

    public function onMessageByText(string $text, BotMessageHandler $messageHandler): void
    {
        $messageHandler->bot = $this->bot;
        $this->texts[md5($text)] = $messageHandler;
    }

    public function onMessagesByText(array $handlers): void
    {
        foreach ($handlers as $text => $handler) {
            $this->onMessageByText($text, $handler);
        }
    }

    public function onCallbackQuery(BotCallbackQueryHandler $callbackQueryHandler): void
    {
        $callbackQueryHandler->bot = $this->bot;
        $this->callbackQueries[] = $callbackQueryHandler;
    }

    public function onCallbackQueries(array $handlers): void
    {
        foreach ($handlers as $handler) {
            $this->onCallbackQuery($handler);
        }
    }

    public function onCallbackQueryByData(string $data, BotCallbackQueryHandler $callbackQueryHandler): void
    {
        $callbackQueryHandler->bot = $this->bot;
        $this->callbackQueryData[trim($data)] = $callbackQueryHandler;
    }

    public function onCallbackQueriesByData(array $handlers): void
    {
        foreach ($handlers as $data => $handler) {
            $this->onCallbackQueryByData($data, $handler);
        }
    }

    public function onInlineQuery(BotInlineQueryHandler $inlineQueryHandler): void
    {
        $inlineQueryHandler->bot = $this->bot;
        $this->callbackQueries[] = $inlineQueryHandler;
    }

    public function onInlineQueries(array $handlers): void
    {
        foreach ($handlers as $handler) {
            $this->onInlineQuery($handler);
        }
    }

    public function onInlineQueryByQuery(string $query, BotInlineQueryHandler $inlineQueryHandler): void
    {
        $inlineQueryHandler->bot = $this->bot;
        $this->inlineQueriesQuery[trim($query)] = $inlineQueryHandler;
    }

    public function onInlineQueriesByQuery(array $handlers): void
    {
        foreach ($handlers as $query => $handler) {
            $this->onInlineQueryByQuery($query, $handler);
        }
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $input = file_get_contents('php://input');
        if ($input === false || $input === '') {
            throw new Exception('Input is empty');
        }

        $input_decoded = json_decode($input, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($input_decoded)) {
            throw new Exception("Input are not JSON array: $input");
        }

        $update = Update::fromResponse($input_decoded);

        if ($update->getInlineQuery() != null) {
            if ($update->getInlineQuery()->getQuery() != null) {
                if (isset($this->inlineQueriesQuery[$update->getInlineQuery()->getQuery()])) {
                    /** @var BotInlineQueryHandler $inlineQueryHandler */
                    $inlineQueryHandler = $this->inlineQueriesQuery[$update->getInlineQuery()->getQuery()];
                    $inlineQueryHandler->update = $update;
                    if ($inlineQueryHandler->handle($update->getInlineQuery())) {
                        return;
                    }
                }
            }
            foreach ($this->inlineQueries as $inlineQueryHandler) {
                $inlineQueryHandler->update = $update;
                if ($inlineQueryHandler->handle($update->getInlineQuery())) {
                    return;
                }
            }
        }
        if ($update->getCallbackQuery() != null) {
            if ($update->getCallbackQuery()->getData() != null) {
                if (isset($this->callbackQueryData[$update->getCallbackQuery()->getData()])) {
                    /** @var BotCallbackQueryHandler $callbackQueryHandler */
                    $callbackQueryHandler = $this->callbackQueryData[$update->getCallbackQuery()->getData()];
                    $callbackQueryHandler->update = $update;
                    if ($callbackQueryHandler->handle($update->getCallbackQuery())) {
                        return;
                    }
                }
            }
            foreach ($this->callbackQueries as $callbackQueryHandler) {
                $callbackQueryHandler->update = $update;
                if ($callbackQueryHandler->handle($update->getCallbackQuery())) {
                    return;
                }
            }
        }
        if ($update->getMessage() != null) {
            $text = $update->getMessage()->getText();
            if ($text != null) {
                $text = trim($text);
                if (isset($this->texts[md5($text)])) {
                    /** @var BotMessageHandler $messageHandler */
                    $messageHandler = $this->texts[md5($text)];
                    $messageHandler->update = $update;
                    if ($messageHandler->handle($update->getMessage())) {
                        return;
                    }
                }
                foreach ($this->commands as $command => $commandHandler) {
                    /** @var BotCommandHandler $commandHandler */
                    if (Str::startsWith($text, $command)) {
                        $param = Str::substr($text, Str::length($command) + 1);
                        if (trim($param) == '') {
                            $param = null;
                        }
                        $commandHandler->update = $update;
                        if ($commandHandler->handle($update->getMessage(), $param)) {
                            return;
                        }
                    }
                }
            }
            foreach ($this->messages as $messageHandler) {
                $messageHandler->update = $update;
                if ($messageHandler->handle($update->getMessage())) {
                    return;
                }
            }
        }
    }
}
