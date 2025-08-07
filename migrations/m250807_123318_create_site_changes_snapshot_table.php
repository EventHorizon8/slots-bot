<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles the creation of table `{{%site_changes_snapshot}}`.
 */
class m250807_123318_create_site_changes_snapshot_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%site_changes_snapshot}}', [
            'id' => $this->primaryKey(),
            'country_iso' => $this->string(2)->notNull(),
            'url' => $this->text()->notNull(),
            'content' => $this->text()->notNull(),
            'is_slot_available' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => 'TIMESTAMPTZ(0) NOT NULL',
            'updated_at' => 'TIMESTAMPTZ(0) NOT NULL',
        ]);

        $this->execute("CREATE INDEX idx_site_changes_snapshot_country_iso_with_date
        ON {{%site_changes_snapshot}} (country_iso, created_at DESC)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%site_changes_snapshot}}');
        $this->execute("DROP INDEX IF EXISTS idx_site_changes_snapshot_country_iso_with_date");
    }
}
