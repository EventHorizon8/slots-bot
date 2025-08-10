<?php

declare(strict_types=1);

namespace app\services\MessengerClient;

use JetBrains\PhpStorm\ArrayShape;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
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

    /**
     * @inheritDoc
     */
    public function getInfo(): mixed
    {
        return $this->makeApiRequest('getMe');
    }

    /**
     * @inheritDoc
     */
    public function sendMessages(array $recipients, string $message, array $actions = []): void
    {
        foreach ($recipients as $recipient) {
            $data = $this->sendMessage($recipient, $message, $actions);

            Yii::getLogger()->log(
                'Telegram response: ' . print_r($data, true),
                Logger::LEVEL_INFO
            );
        }
    }

    public function editMessage(string $recipient, string $messageId, string $text, array $keyboard = null): bool
    {
        $data = [
            'chat_id' => $recipient,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        return !empty($this->makeApiRequest('editMessageText', $data));
    }

    /**
     * @inheritDoc
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = ''): mixed
    {
        $data = [
            'callback_query_id' => $callbackQueryId,
            'text' => $text
        ];

        return $this->makeApiRequest('answerCallbackQuery', $data);
    }

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function sendMessage(string $recipient, string $message, array $actions = []): bool
    {
        $requestData = [
            'chat_id' => $recipient,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($actions) {
            $requestData['reply_markup'] = json_encode([
                'inline_keyboard' => [
                    $actions
                ]
            ]);
        }

        $data = $this->makeApiRequest('sendMessage', $requestData);
        return !empty($data);
    }

    /**
     * Retrieves a list of users who have started the bot. Only new users
     */
    #[ArrayShape([
        [
            'id' => 'int',
            'full_name' => 'string|null',
            'username' => 'string|null',
        ]
    ])]
    public function getUserList(): array
    {
        // we need to remove the webhook to get the list of users,
        // because getUpdates and setWebhook cannot be used in the same account
        $this->removeWebhook();
        $data = $this->makeApiRequest('getUpdates');

        $userList = [];
        foreach ($data['result'] ?? [] as $datum) {
            if (isset($datum['message']['text']) && $datum['message']['text'] === '/start') {
                $userList[$datum['message']['from']['id'] ?? 0] = [
                    'id' => $datum['message']['from']['id'] ?? 0,
                    'full_name' => trim(
                        ($datum['message']['from']['first_name'] ?? '') . ' ' . ($datum['message']['from']['last_name'] ?? '')
                    ),
                    'username' => $datum['message']['from']['username'] ?? '',
                ];
            }
        }
        // we need to return webhook
        $this->startWebhook();
        return $userList;
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
     * @throws InvalidConfigException|Exception
     */
    public function sendConfig(bool $isActive = true): bool
    {
        $webhookUrl = Url::to(['/messenger-webhook?messenger=telegram'], true);
        $startWebhook = $isActive ? 'setWebhook' : 'deleteWebhook';
        $this->makeApiRequest($startWebhook, [
            'url' => $webhookUrl,
        ]);
        return true;
    }



    /**
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws Exception
     * @throws InvalidConfigException
     */
    private function makeApiRequest(string $method, array $params = []): mixed
    {
        $url = sprintf('%s%s/%s', self::BOT_URL, $this->botToken, $method);
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($url)
            ->setData($params)
            ->send();

        if (!$response->isOk) {
            Yii::error('Telegram API request failed: ' . print_r($response->getData(), true), __METHOD__);
            throw new \RuntimeException('Telegram API request failed');
        }

        return $response->getData();
    }

    private function startWebhook(): void
    {
        $this->sendConfig();
    }

    private function removeWebhook(): void
    {
        $this->sendConfig(false);
    }
}
