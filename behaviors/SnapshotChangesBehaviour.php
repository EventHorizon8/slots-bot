<?php

declare(strict_types=1);

namespace app\behaviors;

use app\models\SiteChangesSnapshot;
use yii\base\Behavior;
use yii\db\ActiveRecord;

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
        if ($this->isContentChanged($model)) {
            //todo: here we need to add telegram service to send message
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
