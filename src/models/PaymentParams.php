<?php

namespace carono\yii2merchant\models;

use yii\base\Model;

/**
 * Class PaymentParams
 *
 * @package bonica\modules\callback\models
 */
class PaymentParams extends Model
{
    public const USER_ID_FIELD = 'uid';
    public const PAYMENT_TYPE_FIELD = 'payment_type_id';

    public $amount;
    public $balance_id;
    public $data;
    public $user_id;
    public $payment_type_id;

    public function rules()
    {
        return [
            [['amount', 'balance_id', 'data', 'user_id', 'payment_type_id'], 'required']
        ];
    }
}