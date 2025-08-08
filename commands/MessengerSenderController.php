<?php

declare(strict_types=1);

namespace app\commands;

use app\enum\CountryIso;
use app\models\SiteChangesSnapshot;
use app\models\UserSubscriber;
use app\services\MessengerClient\MessengerClientInterface;
use app\services\MessengerClient\MessengerFactory;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * MessengerSenderController is responsible for managing the sending of messages
 * in a messaging application.
 *
 * This controller provides actions to:
 * - Retrieve a list of users subscribed to the bot.
 * `php yii messenger-sender/get-users -m={messenger}`
 * - Send weekly report messages to users.
 * `php yii messenger-sender/send-weekly-report-message -m={messenger}`
 */
class MessengerSenderController extends Controller
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

    /**
     * we need to load users daily
     * @return int
     * @throws \Throwable
     */
    public function actionGetUsers(): int
    {
        $userList = $this->client->getUserList();

        foreach ($userList as $user) {
            $existedUser = UserSubscriber::find()
                ->where(['chat_id' => $user['id']])
                ->one();
            if ($existedUser !== null) {
                Console::output('User already exists: ' . $user['id']);
                continue;
            }

            if ($this->saveUser($user)) {
                Console::output('New user added: ' . $user['id']);
            } else {
                Console::error('Failed to save user: ' . $user['id']);
            }
        }
        return ExitCode::OK;
    }

    /**
     * This action is used to send a weekly report message.
     * @return int
     */
    public function actionSendWeeklyReportMessage(): int
    {
        try {
            //load recipients
            $recipients = UserSubscriber::find()->all();
            $recipientIds = ArrayHelper::getColumn($recipients, 'chat_id');
            if (!$recipientIds) {
                Console::error('No recipients found.');
                return ExitCode::UNSPECIFIED_ERROR;
            }
            //load all messages during and day
            $startDate = new \DateTimeImmutable('now');
            $data = (new SiteChangesSnapshot())->getReport($startDate, new \DateInterval('P1W'));
            $report = '';
            foreach ($data as $datum) {
                $report .= 'Country: ' . (CountryIso::tryFrom($datum['country_iso'])?->getName() ?? 'N/A') . PHP_EOL;
                $report .= 'Url: ' . ($datum['url'] ?? 'N/A') . PHP_EOL;
                $report .= 'Content: ' . ($datum['content'] ?? 'N/A') . PHP_EOL;
                $report .= 'Count: ' . ($datum['count'] ?? 0) . PHP_EOL;
                $report .= str_repeat('-', 20) . PHP_EOL;
            }
            $this->client->sendMessages($recipientIds, $report);
            Console::output('Messages sent successfully.');
        } catch (\Exception $e) {
            Console::error('Failed to send message: ' . $e->getMessage());
        }
        return ExitCode::OK;
    }

    private function saveUser(array $user): bool
    {
        $newUser = new UserSubscriber();
        $newUser->full_name = $user['full_name'] ?? '';
        $newUser->username = $user['username'] ?? '';
        $newUser->chat_id = $user['id'];
        $newUser->created_at = date('Y-m-d H:i:s');
        $newUser->updated_at = date('Y-m-d H:i:s');
        return $newUser->save();
    }
}
