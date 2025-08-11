<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_notification".
 *
 * @property int $id
 * @property int $user_chat_id
 * @property int $slot_id
 * @property string $notification_type
 * @property string $notification_datetime
 * @property int $sent
 * @property string $created_at
 * @property string $updated_at
 *
 * @property AvailableSlot $slot
 * @property UserSubscriber $user
 */
class UserNotification extends ActiveRecord
{
    const TYPE_DAY_BEFORE = 'day_before';
    const TYPE_WEEK_BEFORE = 'week_before';

    public static function tableName(): string
    {
        return 'user_notification';
    }

    public function rules(): array
    {
        return [
            [['user_chat_id', 'slot_id', 'notification_type'], 'required'],
            [['user_chat_id', 'slot_id', 'sent'], 'integer'],
            [['notification_type'], 'in', 'range' => [self::TYPE_DAY_BEFORE, self::TYPE_WEEK_BEFORE]],
            [['notification_datetime'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
            ],
        ];
    }
}