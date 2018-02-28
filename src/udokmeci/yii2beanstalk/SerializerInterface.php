<?php
namespace udokmeci\yii2beanstalk;

interface SerializerInterface {
    public function isSerialized($data);
    public function serialize($data);
    public function unserialize($data);
}
