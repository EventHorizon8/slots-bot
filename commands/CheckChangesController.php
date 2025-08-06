<?php

declare(strict_types=1);

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * This command check changes on the sites which we need to monitor.
 * command start: `php yii check-changes`
 */
class CheckChangesController extends Controller
{
    /**
     * This command start the process of checking changes.
     * @return int Exit code
     */
    public function actionIndex(): int
    {
        foreach (Yii::$app->params['parsedSites'] as $site) {
            Console::output('Checking site: ' . $site['name'] ?? '-');
            $strategyClass = $site['strategy'] ?? '';
            if (!$strategyClass) {
                Console::stderr('No strategy defined for site: ' . $site['name'] ?? '-' . PHP_EOL);
                continue;
            }
            /**
             * @var \services\CheckSiteStrategyInterface $strategy
             */
            $strategy = new $strategyClass();
            $travelVisaInfos = $strategy->loadChanges($site['url'] ?? '');
            Console::output('Result: '.$travelVisaInfos);
            if (empty($travelVisaInfos)) {
                Console::error('Cannot find any info');
                continue;
            }

            // Todo: Check if the site info is already in the database and compare it
        }

        return ExitCode::OK;
    }
}
