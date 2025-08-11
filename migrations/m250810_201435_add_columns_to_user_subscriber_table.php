<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles adding `is_admin` column to `{{%user_subscriber}}` table.
 */
class m250810_201435_add_columns_to_user_subscriber_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->addColumn('{{%user_subscriber}}', 'is_admin', $this->integer(1)->notNull()->defaultValue(0));
        $this->addColumn('{{%user_subscriber}}', 'current_state', $this->string()->defaultValue(null) );
        //create jsonb column for state_data
        $this->addColumn('{{%user_subscriber}}', 'state_data', $this->json()->defaultValue(null));
        $this->createIndex('idx_user_subscriber_is_admin', '{{%user_subscriber}}', 'is_admin');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropIndex('idx_user_subscriber_is_admin', '{{%user_subscriber}}');
        $this->dropColumn('{{%user_subscriber}}', 'is_admin');
        $this->dropColumn('{{%user_subscriber}}', 'current_state');
        $this->dropColumn('{{%user_subscriber}}', 'state_data');
    }
}
