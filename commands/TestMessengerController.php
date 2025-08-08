<?php

declare(strict_types=1);

namespace app\commands;

use app\services\MessengerClient\MessengerClientInterface;
use app\services\MessengerClient\MessengerFactory;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\helpers\Console;

/**
 * This command check changes on the sites which we need to monitor.
 * command start: `php yii test-messenger/`
 * There are three actions available:
 * - `php yii test-messenger/get-info -m={messenger}`: Retrieves information about the bot.
 * - `php yii test-messenger/get-users -m={messenger}`: Retrieves a list of users subscribed to the bot.
 * - `php yii test-messenger/send-message -m={messenger} {recipient}`: Sends a message to a predefined list of recipients.
 *
 * messenger - telegram|empty
 * recipient - you can take any user ID from the list of users
 */
class TestMessengerController extends Controller
{
    public string $messenger = '';

    private MessengerClientInterface $client;

    public function options($actionID): array
    {
        return ['messenger'];
    }

    public function optionAliases(): array
    {
        return ['m' => 'messenger'];
    }

    public function beforeAction($action): bool
    {
        $this->client = MessengerFactory::create($this->messenger);
        return parent::beforeAction($action);
    }

    public function actionGetInfo(): int
    {
        $info = $this->client->getInfo();

        if ($info) {
            Console::output('Bot Info: ' . print_r($info, true));
        } else {
            Console::error('Failed to retrieve bot info.');
        }

        return ExitCode::OK;
    }

    public function actionGetUsers(): int
    {
        $userList = $this->client->getUserList();

        if (!empty($userList)) {
            Console::output(Table::widget([
                'headers' => ['ID', 'Full Name', 'Username'],
                'rows' => array_map(function ($user) {
                    return [
                        $user['id'],
                        $user['full_name'] ?? 'N/A',
                        $user['username'] ?? 'N/A',
                    ];
                }, $userList),
            ]));
        } else {
            Console::error('No users found or failed to retrieve user list.');
        }
        return ExitCode::OK;
    }

    public function actionSendMessage(string $recipient): int
    {
        $message = 'Hello, this is a test message!';

        $messageSent = $this->client->sendMessage($recipient, $message);
        Console::output($messageSent ? 'Message sent successfully.' : 'Failed to send message.');
        return ExitCode::OK;
    }
}
