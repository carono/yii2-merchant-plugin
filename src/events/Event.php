<?php


namespace carono\yii2merchant\events;


use bonica\modules\callback\models\PaymentParams;
use carono\yii2merchant\traits\yii2TechPaymentTrait;
use yii\db\ActiveRecord;

/**
 * Class Event
 *
 * @package carono\yii2merchant\events
 */
class Event extends \yii\base\Event
{
    /**
     * @var ActiveRecord|yii2TechPaymentTrait
     */
    public $payment;
    /**
     * @var PaymentParams
     */
    public $params;
}