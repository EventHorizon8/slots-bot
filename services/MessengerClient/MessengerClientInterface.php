<?php

declare(strict_types=1);

namespace app\services\MessengerClient;

interface MessengerClientInterface
{
    /**
     * Get info about the messenger service.
     * @return mixed
     */
    public function getInfo(): mixed;

    /**
     * Sets the configuration for the messenger service.
     *
     * @return bool Returns true if the configuration was set successfully, false otherwise.
     */
    public function sendConfig(): bool;

    /**
     * Retrieves a list of users from the messenger service.
     * @return array
     */
    public function getUserList(): array;

    /**
     * Sends messages to the specified recipients.
     *
     * @param array $recipients The IDs of the recipients.
     * @param string $message The message to send.
     * @return void
     */
    public function sendMessages(array $recipients, string $message): void;

    /**
     * Answers a callback query in the messenger service.
     *
     * @param string $callbackQueryId
     * @param string $text
     * @return mixed
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = ''): mixed;

    /**
     * Sends a message to the specified recipient.
     *
     * @param string $recipient The ID of the recipient.
     * @param string $message The message to send.
     * @param array $actions Optional actions to perform with the message.
     * @return bool
     */
    public function sendMessage(string $recipient, string $message, array $actions = []): bool;


}