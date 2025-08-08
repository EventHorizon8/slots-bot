<?php

declare(strict_types=1);

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_subscriber".
 *
 * @property int $id
 * @property string $full_name
 * @property string $username
 * @property int $chat_id
 * @property string $created_at
 * @property string $updated_at
 */
class UserSubscriber extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_subscriber';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['full_name', 'username', 'chat_id', 'created_at', 'updated_at'], 'required'],
            [['chat_id'], 'default', 'value' => null],
            [['chat_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['full_name', 'username'], 'string', 'max' => 255],
            [['chat_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'full_name' => 'Full Name',
            'username' => 'Username',
            'chat_id' => 'Chat ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
