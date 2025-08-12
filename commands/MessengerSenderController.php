<?php

declare(strict_types=1);

namespace app\commands;

use app\enum\CountryIso;
use app\models\SiteChangesSnapshot;
use app\services\MessengerClient\MessengerClientInterface;
use app\services\MessengerClient\MessengerFactory;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * MessengerSenderController is responsible for managing the sending of messages
 * in a messaging application.
 *
 * This controller provides actions to:
 * - Set up the configuration for the messenger client.
 * `php yii messenger-sender/set-up-config -m={messenger}`
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
     * This action sets up the configuration for the messenger client.
     *
     * @return int
     */
    public function actionSetUpConfig(): int
    {
        $this->client = MessengerFactory::create($this->messenger);
        if ($this->client->sendConfig()) {
            Console::output('Configuration set successfully.');
        } else {
            Console::error('Failed to set configuration.');
            return ExitCode::UNSPECIFIED_ERROR;
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
            $recipientId = Yii::$app->params['telegramAdminId'];
            if (!$recipientId) {
                Console::error('No recipient ID found in the configuration.');
                return ExitCode::UNSPECIFIED_ERROR;
            }
            //We need to send report only for admin
            //load all messages during week
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
            $this->client->sendMessage($recipientId, $report);
            Console::output('Messages sent successfully.');
        } catch (\Exception $e) {
            Console::error('Failed to send message: ' . $e->getMessage());
        }
        return ExitCode::OK;
    }
}
