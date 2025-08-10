<?php

declare(strict_types=1);

namespace app\services\MessengerClient;

class EmptyClient implements MessengerClientInterface
{
    /**
     * @inheritDoc
     */
    public function getInfo(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getUserList(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function sendMessages(array $recipients, string $message): void
    {
    }

    /**
     * @inheritDoc
     */
    public function editMessage(string $recipient, string $messageId, string $text, array $keyboard = null): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = ''): mixed
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function sendMessage(string $recipient, string $message, array $actions = []): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function receiveMessages(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function sendConfig(): bool
    {
        return true;
    }
}
