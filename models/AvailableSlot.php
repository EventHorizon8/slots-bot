<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "available_slot".
 *
 * @property int $id
 * @property int $admin_chat_id
 * @property string $slot_datetime
 * @property string|null $link
 * @property string|null $description
 * @property int $notified_users
 * @property int $created_at
 * @property int $updated_at
 */
class AvailableSlot extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'available_slot';
    }

    public function rules(): array
    {
        return [
            [['admin_chat_id', 'created_at', 'updated_at', 'slot_datetime'], 'required'],
            [['admin_chat_id'], 'integer'],
            [['slot_datetime'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['link', 'description'], 'string'],
            [['created_at', 'updated_at', 'slot_datetime'], 'safe'],
            [['notified_users'], 'integer', 'min' => 0],
        ];
    }
}