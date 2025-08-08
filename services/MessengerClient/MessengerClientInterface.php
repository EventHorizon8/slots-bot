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
     * Sends a message to the specified recipient.
     *
     * @param string $recipient The ID of the recipient.
     * @param string $message The message to send.
     * @return void
     */
    public function sendMessage(string $recipient, string $message): bool;

    /**
     * Receives messages from the messenger service.
     *
     * @return array An array of received messages.
     */
    public function receiveMessages(): array;

}