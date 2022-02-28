<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%college_card}}`.
 */
class m220228_153811_create_college_card_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%college_card}}', [
            'id' => $this->primaryKey(),
            'srcurl' => $this->text()->notNull(),
            'needupd' => 'bit default 1',
            'name' => $this->text(),
            'address' => $this->text(),
            'phone' => $this->text(),
            'siteurl' => $this->text(),
        ], 'DEFAULT CHARSET=utf8');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%college_card}}');
    }
}
