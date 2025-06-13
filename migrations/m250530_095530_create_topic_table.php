<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%topic}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m250530_095530_create_topic_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%topic}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'image_url' => $this->string(255),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-topic-user_id}}',
            '{{%topic}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-topic-user_id}}',
            '{{%topic}}',
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
            '{{%fk-topic-user_id}}',
            '{{%topic}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-topic-user_id}}',
            '{{%topic}}'
        );

        $this->dropTable('{{%topic}}');
    }
}
