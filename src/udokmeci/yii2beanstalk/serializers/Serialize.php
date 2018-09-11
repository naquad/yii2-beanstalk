<?php

namespace udokmeci\yii2beanstalk\serializers;

use yii\base\BaseObject;
use udokmeci\yii2beanstalk\SerializerInterface;

class Serialize extends BaseObject implements SerializerInterface
{
    public function isSerialized($data)
    {
        $check = @unserialize($data);
        return $check !== false || $check == 'b:0;';
    }

    public function serialize($data)
    {
        return serialize($data);
    }

    public function unserialize($data)
    {
        return unserialize($data);
    }
}
