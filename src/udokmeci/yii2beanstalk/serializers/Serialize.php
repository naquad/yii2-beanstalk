<?php

namespace udokmeci\yii2beanstalk\serializers;

use yii\base\Object;
use udokmeci\yii2beanstalk\SerializerInterface;

class Serialize extends Object implements SerializerInterface
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
