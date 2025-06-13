<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%admin}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m250530_113059_create_admin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%admin}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->unique(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-admin-user_id}}',
            '{{%admin}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-admin-user_id}}',
            '{{%admin}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-admin-user_id}}',
            '{{%admin}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-admin-user_id}}',
            '{{%admin}}'
        );

        $this->dropTable('{{%admin}}');
    }
}
