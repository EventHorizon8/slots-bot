<?php

declare(strict_types=1);

namespace app\services\MessengerClient;

/**
 * Factory class for creating instances of MessengerClientInterface.
 */
class MessengerFactory
{
    public static function create(string $messengerType): MessengerClientInterface
    {
        return match($messengerType) {
            'telegram' => new TelegramClient(),
            'empty' => new EmptyClient(),
            default => throw new \InvalidArgumentException("Unsupported messenger type: $messengerType"),
        };
    }
}
