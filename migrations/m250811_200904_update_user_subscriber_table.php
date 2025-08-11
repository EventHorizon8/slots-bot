<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles updating the `chat_id` column in `{{%user_subscriber}}` table from bigint to string(255).
 */
class m250811_200904_update_user_subscriber_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->alterColumn('{{%user_subscriber}}', 'chat_id', $this->string(255)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->execute(
            'ALTER TABLE {{%user_subscriber}} ALTER COLUMN chat_id TYPE bigint USING chat_id::bigint'
        );
    }
}
