<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%comment}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%topic}}`
 * - `{{%user}}`
 */
class m250530_105624_create_comment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%comment}}', [
            'id' => $this->primaryKey(),
            'topic_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'content' => $this->text()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // creates index for column `topic_id`
        $this->createIndex(
            '{{%idx-comment-topic_id}}',
            '{{%comment}}',
            'topic_id'
        );

        // add foreign key for table `{{%topic}}`
        $this->addForeignKey(
            '{{%fk-comment-topic_id}}',
            '{{%comment}}',
            'topic_id',
            '{{%topic}}',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-comment-user_id}}',
            '{{%comment}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-comment-user_id}}',
            '{{%comment}}',
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
        // drops foreign key for table `{{%topic}}`
        $this->dropForeignKey(
            '{{%fk-comment-topic_id}}',
            '{{%comment}}'
        );

        // drops index for column `topic_id`
        $this->dropIndex(
            '{{%idx-comment-topic_id}}',
            '{{%comment}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-comment-user_id}}',
            '{{%comment}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-comment-user_id}}',
            '{{%comment}}'
        );

        $this->dropTable('{{%comment}}');
    }
}
