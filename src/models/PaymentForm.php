<?php

namespace carono\yii2merchant\models;

use carono\yii2merchant\Module;
use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\web\View;

/**
 * Class PaymentForm
 *
 * @package bonica\modules\callback\models
 * @property integer $amount
 */
class PaymentForm extends Model
{
    public $paymentOptions = [];
    protected $_amount;
    public $user_id;
    public $balance_id;
    public $payment_type_id;
    public $view = '@vendor/bonica/tools/src/modules/callback/views/main';

    public function attributeLabels()
    {
        return [
            'amount' => Yii::t('models', 'Amount'),
            'payment_type_id' => Yii::t('models', 'Payment Type ID'),
        ];
    }

    public function getDescription()
    {
        $service = Module::getInstance()->service;
        $uid = $this->user_id;
        $bid = $this->balance_id;
        return "Оплата сервиса '{$service}', клиент ID=$uid, баланс ID=$bid";
    }

    public function setAmount($value)
    {
        $this->_amount = $value;
    }

    public function getAmount()
    {
        return preg_replace('#\D*#', '', trim($this->_amount));
    }

    public function init()
    {
        $this->user_id = Yii::$app->user->id;
        parent::init();
    }

    public function rules()
    {
        return [
            [['amount', 'user_id', 'payment_type_id', 'balance_id'], 'required'],
        ];
    }

    /**
     * @return \yii\db\ActiveRecord
     */
    public function getPaymentType()
    {
        $paymentTypeClass = Module::getInstance()->paymentTypeClass;
        return $paymentTypeClass::findOne($this->payment_type_id);
    }

    /**
     * @return ActiveQuery
     */
    public function getPaymentTypeQuery()
    {
        $paymentTypeClass = Module::getInstance()->paymentTypeClass;
        return $paymentTypeClass::find();
    }

    public function registerClientJs()
    {
        $formJs = <<<JS
$(document).on('submit', '#payment-form', function (e) {
    var form = $(this);
    var data = form.find('#paymentform-payment_type_id > option:selected').data();
    var fn = window[data.js]; // get js function name

   if(typeof fn === 'function') {
        e.preventDefault();
        e.stopPropagation();
        var amount = parseInt(form.find('#paymentform-amount').inputmask('unmaskedvalue'));
        var ptid = form.find('#paymentform-payment_type_id').val();
        fn(ptid, amount, {$this->balance_id}, {$this->user_id}, data);
    }
})
JS;
        Yii::$app->view->registerJs($formJs, View::POS_READY);
    }

    public function getPaymentTypes()
    {
        $query = $this->getPaymentTypeQuery();
        $types = $query->all();
        foreach ($types as $type) {
            $type->provider->registerClientJs();
            $this->paymentOptions[$type->id] = $type->provider->jsOptions ?? [];
        }
        return ArrayHelper::map($types, 'id', 'name');
    }
}
