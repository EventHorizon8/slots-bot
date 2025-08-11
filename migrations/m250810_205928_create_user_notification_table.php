<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_notification}}`.
 */
class m250810_205928_create_user_notification_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%user_notification}}', [
            'id' => $this->primaryKey(),
            'user_chat_id' => $this->bigInteger()->notNull(),
            'slot_id' => $this->integer()->notNull(),
            'notification_type' => $this->string(255)->notNull(),
            'notification_datetime' => 'TIMESTAMPTZ(0) NOT NULL',
            'sent' => $this->integer(1)->defaultValue(0),
            'created_at' => 'TIMESTAMPTZ(0) NOT NULL',
            'updated_at' => 'TIMESTAMPTZ(0) NOT NULL',
        ]);

        $this->createIndex('idx_user_notification_user_chat_id', '{{%user_notification}}', 'user_chat_id');
        $this->createIndex('idx_user_notification_slot_id', '{{%user_notification}}', 'slot_id');
        $this->createIndex(
            'idx_user_notification_notification_datetime',
            '{{%user_notification}}',
            'notification_datetime'
        );

        $this->addForeignKey(
            'fk_user_notification_slot_id_table',
            '{{%user_notification}}',
            'slot_id',
            '{{%available_slot}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%user_notification}}');
    }
}
