<?php

declare(strict_types=1);

namespace app\models;

use app\behaviors\SnapshotChangesBehaviour;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "site_changes_snapshot".
 *
 * @property int $id
 * @property string $country_iso
 * @property string $url
 * @property string $content
 * @property int $is_slot_available
 * @property string $created_at
 * @property string $updated_at
 */
class SiteChangesSnapshot extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'site_changes_snapshot';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['is_slot_available'], 'default', 'value' => 0],
            [['country_iso', 'url', 'content', 'created_at', 'updated_at'], 'required'],
            [['url', 'content'], 'string'],
            [['is_slot_available'], 'default', 'value' => null],
            [['is_slot_available'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['country_iso'], 'string', 'max' => 2],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'country_iso' => 'Country Iso',
            'url' => 'Url',
            'content' => 'Content',
            'is_slot_available' => 'Is Slot Available',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            SnapshotChangesBehaviour::class,
        ];
    }
}
