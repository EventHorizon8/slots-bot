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
    public function sendMessage(string $recipient, string $message): bool
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
}
