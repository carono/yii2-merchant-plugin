<?php


/**
 * Class m190823_110500_billing
 */
class m190823_110500_billing extends \carono\yii2plugin\components\Migration
{
    public $paymentTypeTable = '{{%payment_type}}';
    public $paymentTable = '{{%payment}}';
    public $charset = 'UTF8';
    public $collate = 'UTF8_GENERAL_CI';

    public function tableOptions()
    {
        return [
            $this->driver => $this->tableOptions ?: "CHARACTER SET = '$this->charset' COLLATE = '$this->collate'"
        ];
    }

    public function newTables()
    {
        return [
            $this->paymentTypeTable => [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'sid' => $this->string(),
                'provider_class' => $this->string(),
                'is_active' => $this->boolean()->notNull()->defaultValue(true),
                'deleted_at' => $this->dateTime(),
                'params' => $this->text(),
            ],
            $this->paymentTable => [
                'id' => $this->primaryKey(),
                'amount' => $this->decimal(10, 2)->comment('Сумма пополнения'),
                'payment_type_id' => $this->foreignKey('{{%payment_type}}'),
                'user_id' => $this->integer()->comment('Пользователь, который совершает оплату'),
                'balance_id' => $this->integer()->comment('ID баланса, по которому долно быть совершено пополнение'),
                'transaction_id' => $this->integer()->comment('ID транзакции пополнения баланса'),
                'note' => $this->string()->comment('Комментарий'),
                'data' => $this->string()->comment('Входящие данные платежной системы'),
                'status' => $this->tinyInteger()->comment('Статус платежа, 1 - ожидание оплаты, 2 - оплата завершена, 3 - возврат'),
                'created_at' => $this->dateTime(),
            ]
        ];
    }

    public function newIndex()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->upNewTables();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->downNewTables();
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190823_110508_init cannot be reverted.\n";

        return false;
    }
    */
}
