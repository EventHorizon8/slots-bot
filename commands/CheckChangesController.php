<?php

declare(strict_types=1);

namespace app\commands;

use app\enum\CountryIso;
use app\models\SiteChangesSnapshot;
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
        foreach (Yii::$app->params['parsedSites'] as $key => $site) {
            $country = CountryIso::tryFrom($key);
            if ($country === null) {
                Console::stderr('Invalid country ISO code: ' . $key . PHP_EOL);
                continue;
            }
            Console::output('Checking site: ' . $country->getName());
            $strategyClass = $site['strategy'] ?? '';
            if (!$strategyClass) {
                Console::stderr('No strategy defined for site: ' . $country->getName() . PHP_EOL);
                continue;
            }
            /**
             * @var \app\services\Strategy\CheckSiteStrategyInterface $strategy
             */
            $strategy = new $strategyClass();
            $travelVisaInfos = $strategy->loadTargetData($site['url'] ?? '');
            Console::output('Result: ' . $travelVisaInfos);
            if (empty($travelVisaInfos)) {
                Console::error('Cannot find any info');
                continue;
            }

            // Save the new content to the database
            $this->saveResult($country, $site['url'], $travelVisaInfos) ?
                Console::output('Saved new content for ' . $country->getName()) :
                Console::error('Failed to save content for ' . $country->getName());
        }

        return ExitCode::OK;
    }

    private function saveResult(CountryIso $country, string $url, string $travelVisaInfos): bool
    {
        $snapshot = new SiteChangesSnapshot();
        $snapshot->country_iso = $country->value;
        $snapshot->url = $url;
        $snapshot->content = $travelVisaInfos;
        // it's better to update manually
        $snapshot->is_slot_available = 0;
        $snapshot->created_at = date('Y-m-d H:i:s');
        $snapshot->updated_at = date('Y-m-d H:i:s');
        return $snapshot->insert();
    }
}
