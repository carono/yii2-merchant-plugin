<?php


namespace carono\yii2merchant\traits;

use yii2tech\balance\ManagerDb;
use carono\yii2merchant\events\Event;
use carono\yii2plugin\helpers\ModuleHelper;

trait Yii2TechPaymentTrait
{
    /**
     * @param Event $event
     * @throws \Exception
     */
    public function increase(Event $event)
    {
        if (!class_exists(ManagerDb::class)) {
            throw new \Exception('Require yii2tech/balance package');
        }
        if (!$balance = ModuleHelper::getModuleByClass(ManagerDb::class)) {
            throw new \Exception('Yii2tech/balance component not found');
        }
        $data = ['note' => 'Пополнение ' . $this->paymentType->name];
        $user = $event->params->user_id;
        $amount = $event->params->amount;
        $attributes = [
            'transaction_id' => $balance->increase($user, $amount, $data),
            'status' => 1
        ];
        $event->payment->updateAttributes(array_merge($attributes, $data));
    }
}