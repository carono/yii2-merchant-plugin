<?php


namespace carono\yii2merchant\interfaces;


use carono\yii2merchant\events\Event;

interface PaymentInterface
{
    public function increase(Event $event);
}