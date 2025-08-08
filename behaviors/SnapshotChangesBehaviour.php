<?php

declare(strict_types=1);

namespace app\behaviors;

use app\enum\CountryIso;
use app\models\SiteChangesSnapshot;
use app\models\UserSubscriber;
use app\services\MessengerClient\MessengerFactory;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class SnapshotChangesBehaviour extends Behavior
{
    public function events(): array
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
        ];
    }

    /**
     * After insert we need to chack if the snapshot is different from the previous one.
     * @return void
     */
    public function afterInsert(): void
    {
        $model = $this->owner;
        //we need to send notification only if the content is changed
        if ($this->isContentChanged($model)) {
            //we need to send notification to the users
            $client = MessengerFactory::create('telegram');
            $recipients = UserSubscriber::find()->all();
            $recipientIds = ArrayHelper::getColumn($recipients, 'chat_id');
            $country = CountryIso::tryFrom($model->country_iso)?->getName() ?? 'N/A';
            $client->sendMessages(
                $recipientIds,
                "Changes detected on site: {$model->url}.\n" .
                "Country: {$country}.\n" .
                "Content: {$model->content}\n" .
                "Please check the site for more details."
            );
        }
    }

    private function isContentChanged(SiteChangesSnapshot $model): bool
    {
        $previousSnapshot = SiteChangesSnapshot::find()
            ->where([
                'country_iso' => $model->country_iso,
                'is_slot_available' => 0,
            ])
            ->andWhere(['<>', 'id', $model->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();
        return $previousSnapshot?->content !== $model->content;
    }
}
