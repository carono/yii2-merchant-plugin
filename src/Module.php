<?php

namespace carono\yii2merchant;

use carono\yii2merchant\events\Event;
use carono\yii2plugin\helpers\ModuleHelper;
use yii\db\ActiveRecord;

/**
 * callback module definition class
 */
class Module extends \yii\base\Module
{
    public const EVENT_ON_INCREASE_BALANCE = 'increaseBalance';

    public $service;
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'bonica\modules\callback\controllers';
    /**
     * @var ActiveRecord|string
     */
    public $paymentClass;
    /**
     * @var ActiveRecord|string
     */
    public $paymentTypeClass;

    /**
     * @return \yii\base\Module|self
     */
    public static function getInstance()
    {
        return ModuleHelper::getModuleByClass(self::class);
    }

    public function onIncreaseBalance(Event $event)
    {
        $event->payment->increase($event);
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function init()
    {
        $this->on('increaseBalance', function (Event $event) {
            $this->onIncreaseBalance($event);
        });

        parent::init();

        if (!$this->service) {
            throw new \Exception('Service name required');
        }
    }
}
