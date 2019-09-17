<?php

namespace carono\yii2merchant;

use carono\yii2merchant\models\PaymentParams;
use carono\yii2merchant\events\Event;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;

/**
 * Class BaseProvider
 *
 * @package bonica\modules\callback\providers
 */
abstract class BaseProvider extends Model
{
    /**
     * @var ActiveRecord $model
     */
    public $model;
    public $view;
    public $request;



    public function getPaymentTypeIdFromRequest()
    {
        return ArrayHelper::getValue($this->request, PaymentParams::PAYMENT_TYPE_FIELD);
    }

    public function getUserIdFromRequest()
    {
        return ArrayHelper::getValue($this->request, PaymentParams::USER_ID_FIELD);
    }

    public function init()
    {
        parent::init();
        if ($this->model === null && ($paymentTypeId = $this->getPaymentTypeIdFromRequest())) {
            $this->model = Module::getInstance()->paymentTypeClass::findOne(['id' => $paymentTypeId]);
        }
    }

    /**
     * Validate data from payment provider
     */
    abstract public function validateData();

    /**
     * Validate payment data from payment provider
     */
    public function validatePaymentData()
    {
        $params = $this->getPaymentParams();
        $params->validate();
        $this->addErrors($params->getErrors());
        return !$params->hasErrors();
    }

    /**
     * Parse data to Payment object params
     *
     * @return array|PaymentParams
     */
    abstract public function getPaymentParams();

    /**
     * ['data-js' => 'js', 'data-pk' => 'api_id'];
     *
     * @return array
     */
    abstract public function getJsOptions();

    public function registerClientJs()
    {

    }

    /**
     * @inheritdoc
     */
    public function getParam($key)
    {
        return isset($this->model) ? $this->model->getParam('params', $key) : null;
    }

    public function existsPayment()
    {
        $params = $this->getPaymentParams();
        return Module::getInstance()->paymentClass::find()
            ->andWhere(['data' => $params->data, 'payment_type_id' => $params->payment_type_id])
            ->exists();
    }

    /**
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function payment()
    {
        /**
         * @var Transaction $transaction
         */
        $params = $this->getPaymentParams();
        if (!$params) {
            $this->addError('params', 'Params is not set');
            return false;
        }
        if ($this->validateData() && $this->validatePaymentData() && $params->validate()) {
            if ($this->existsPayment()) {
                return true;
            }
            $transaction = Module::getInstance()->paymentClass::getDb()->beginTransaction();
            try {
                $payment = static::createPayment($params);
                $this->addErrors($params->getErrors());
                if (!$this->hasErrors()) {
                    $event = new Event(['payment' => $payment, 'params' => $params]);
                    Module::getInstance()->trigger(Module::EVENT_ON_INCREASE_BALANCE, $event);
                } else {
                    throw new \Exception(current($this->getFirstErrors()));
                }
                $transaction->commit();
                return true;
            } catch (\Exception $e) {
                $this->addError('id', $e->getMessage());
                $transaction->rollback();
                return false;
            }
        }
        $this->addErrors($params->getErrors());
        return false;
    }

    public function logError()
    {
        $msg = 'Ошибка при сохранении платежа. ' . current($this->getFirstErrors());
        \Yii::error([$msg, $this->request], 'payments');
    }

    public static function createPayment(PaymentParams $params)
    {
        /**
         * @var ActiveRecord $model
         */
        if (!$params || !$params->validate()) {
            return false;
        }
        $class = Module::getInstance()->paymentClass;
        $model = new $class;
        $model->setAttributes([
            'amount' => $params->amount,
            'payment_type_id' => $params->payment_type_id,
            'user_id' => $params->user_id,
            'balance_id' => $params->balance_id,
            'data' => $params->data,
        ], false);
        $model->save();
        $params->addErrors($model->getErrors());
        return $model;
    }
}
