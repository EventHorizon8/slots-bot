<?php

declare(strict_types=1);

namespace app\services\MessengerClient;

use JetBrains\PhpStorm\ArrayShape;
use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\Exception;
use yii\log\Logger;

class TelegramClient implements MessengerClientInterface
{
    private const BOT_URL = 'https://api.telegram.org/bot';

    private ?string $botToken;

    public function __construct()
    {
        $this->botToken = Yii::$app->params['telegramToken'] ?? null;
        if ($this->botToken === null) {
            throw new \RuntimeException('Telegram bot token is not set in application parameters.');
        }
    }

    public function getInfo(): mixed
    {
        $url = sprintf('%s%s/getMe', self::BOT_URL, $this->botToken);

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($url)
            ->send();

        return $response->getData();
    }

    public function sendMessages(array $recipients, string $message): void
    {
        $url = sprintf('%s%s/sendMessage', self::BOT_URL, $this->botToken);
        foreach ($recipients as $recipient) {
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl($url)
                ->setData([
                    'chat_id' => $recipient,
                    'text' => $message,
                    //'parse_mode' => 'MarkdownV2',
                ])
                ->send();
            Yii::getLogger()->log(
                'Telegram response: ' . print_r($response->getData(), true),
                Logger::LEVEL_INFO
            );
        }
    }

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function sendMessage(string $recipient, string $message): bool
    {
        $url = sprintf('%s%s/sendMessage', self::BOT_URL, $this->botToken);
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($url)
            ->setData([
                'chat_id' => $recipient,
                'text' => $message,
            ])
            ->send();
        return $response->isOk;
    }

    #[ArrayShape([[
        'id' => 'int',
        'full_name' => 'string|null',
        'username' => 'string|null',
    ]])]
    public function getUserList(): array
    {
        $url = sprintf('%s%s/getUpdates', self::BOT_URL, $this->botToken);
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($url)
            ->send();

        if (!$response->isOk) {
            throw new \RuntimeException('Failed to retrieve updates from Telegram API');
        }

        $userList = [];
        foreach ($response->getData()['result'] ?? [] as $datum) {
            if (isset($datum['message']['text']) && $datum['message']['text'] === '/start') {
                $userList[$datum['message']['from']['id'] ?? 0] = [
                    'id' => $datum['message']['from']['id'] ?? 0,
                    'full_name' => trim(($datum['message']['from']['first_name'] ?? '') . ' ' . ($datum['message']['from']['last_name'] ?? '')),
                    'username' => $datum['message']['from']['username'] ?? '',
                ];
            }
        }
        return $userList;
    }

    public function receiveMessages(): array
    {
        return [];
    }
}
