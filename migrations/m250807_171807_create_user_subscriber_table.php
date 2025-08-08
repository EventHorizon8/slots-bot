<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_subscriber}}`.
 */
class m250807_171807_create_user_subscriber_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%user_subscriber}}', [
            'id' => $this->primaryKey(),
            'full_name' => $this->string()->notNull(),
            'username' => $this->string()->notNull(),
            'chat_id' => $this->bigInteger()->notNull()->unique(),
            'created_at' => 'TIMESTAMPTZ(0) NOT NULL',
            'updated_at' => 'TIMESTAMPTZ(0) NOT NULL',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%user_subscriber}}');
    }
}
