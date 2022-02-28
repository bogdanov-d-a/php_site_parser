<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%college_list}}`.
 */
class m220228_153759_create_college_list_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%college_list}}', [
            'id' => $this->primaryKey(),
            'cardurl' => $this->text()->notNull(),
            'imgurl' => $this->text(),
            'name' => $this->text()->notNull(),
            'city' => $this->text(),
            'state' => $this->text(),
        ], 'DEFAULT CHARSET=utf8');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%college_list}}');
    }
}
