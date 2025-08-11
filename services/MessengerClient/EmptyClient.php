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
    public function answerCallbackQuery(string $callbackQueryId, string $text = ''): mixed
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function sendMessage( $recipient, string $message, array $actions = []): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function sendConfig(): bool
    {
        return true;
    }
}
