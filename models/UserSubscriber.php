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
 * @property string|null $chat_id
 * @property string $created_at
 * @property string $updated_at
 * @property int $is_admin
 * @property string|null $current_state
 * @property string|null $state_data
 */
class UserSubscriber extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'user_subscriber';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['full_name', 'username', 'chat_id', 'created_at', 'updated_at'], 'required'],
            [['chat_id', 'current_state', 'state_data'], 'default', 'value' => null],
            [['is_admin'], 'default', 'value' => 0],
            [['is_admin'], 'integer'],
            [['created_at', 'updated_at','state_data'], 'safe'],
            [['chat_id','full_name', 'username', 'current_state'], 'string', 'max' => 255],
            [['chat_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'full_name' => 'Full Name',
            'username' => 'Username',
            'chat_id' => 'Chat ID',
            'is_admin' => 'Is Admin',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'current_state' => 'Current State',
            'state_data' => 'State Data',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->is_admin == 1;
    }

    public function setState(string $state, array $data = []): static
    {
        $this->current_state = $state;
        $this->state_data = json_encode($data);
        $this->save();
        return $this;
    }

    public function getStateData(): array
    {
        return $this->state_data ? json_decode($this->state_data, true) : [];
    }

    public static function getAllUsers(): array
    {
        return self::find()->where(['is_admin' => 0])->all();
    }

    public static function getAdmins(): array
    {
        return self::find()->where(['is_admin' => 1])->all();
    }
}
