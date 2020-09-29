<?php

use yii\db\Migration;

/**
 * Class m200924_115908_alter_users_table
 */
class m200924_115909_alter_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200924_115908_alter_users_table cannot be reverted.\n";

        return false;
    }

    public function up()
    {
        $this->alterColumn('users', 'unitl', $this->date()->null());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200924_115908_alter_users_table cannot be reverted.\n";

        return false;
    }
    */
}
