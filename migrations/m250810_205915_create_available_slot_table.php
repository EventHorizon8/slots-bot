<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles the creation of table `{{%available_slot}}`.
 */
class m250810_205915_create_available_slot_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%available_slot}}', [
            'id' => $this->primaryKey(),
            'admin_chat_id' => $this->bigInteger()->notNull(),
            'slot_datetime' => 'TIMESTAMPTZ(0) NOT NULL',
            'link' => $this->string(500)->defaultValue(null),
            'description' => $this->text()->defaultValue(null),
            'notified_users' => $this->integer()->defaultValue(0),
            'created_at' => 'TIMESTAMPTZ(0) NOT NULL',
            'updated_at' => 'TIMESTAMPTZ(0) NOT NULL',
        ]);
        $this->createIndex('idx_available_slot_admin_chat_id', '{{%available_slot}}', 'admin_chat_id');
        $this->createIndex('idx_available_slot_slot_datetime', '{{%available_slot}}', 'slot_datetime');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%available_slot}}');
    }
}
