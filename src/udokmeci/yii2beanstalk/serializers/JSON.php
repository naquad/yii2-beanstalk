<?php

namespace udokmeci\yii2beanstalk\serializers;

use yii\base\Object;
use udokmeci\yii2beanstalk\SerializerInterface;

class JSON extends Object implements SerializerInterface
{
    public $encode_options = 0;
    public $encode_depth = 512;

    public $decode_assoc = false;
    public $decode_depth = 512;
    public $decode_options = 0;

    public function isSerialized($data)
    {
        if (is_string($data)) {
            @json_decode($data);
            return json_last_error() == JSON_ERROR_NONE;
        }

        return false;
    }

    public function serialize($data)
    {
        return json_encode(
            $data,
            $this->encode_options,
            $this->encode_depth
        );
    }

    public function unserialize($data)
    {
        return json_decode(
            $data,
            $this->decode_assoc,
            $this->decode_depth,
            $this->decode_options
        );
    }
}
