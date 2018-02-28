<?php
namespace udokmeci\yii2beanstalk;

use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use Pheanstalk\Response;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class Beanstalk
 * @package udokmeci\yii2beanstalk
 *
 * @method Beanstalk useTube($tube)
 * @method Beanstalk watch($tube)
 * @method Job reserve($timeout = null)
 * @method Response statsJob($job)
 * @method void bury($job, $priority = Pheanstalk::DEFAULT_PRIORITY)
 * @method Beanstalk release($job, $priority = Pheanstalk::DEFAULT_PRIORITY, $delay = Pheanstalk::DEFAULT_DELAY)
 * @method Beanstalk delete($job)
 * @method Beanstalk listTubes()
 */
class Beanstalk extends Component
{
    /** @var  Pheanstalk */
    public $_beanstalk;
    public $host = "127.0.0.1";
    public $port = 11300;
    public $connectTimeout = 1;
    public $connected = false;
    public $sleep = false;

    public $serializer = [
        'class' => __NAMESPACE__ . '\\serializers\\JSON'
    ];

    protected $_serializer;

    public function init()
    {
        $this->_serializer = Object::createObject($this->serializer);

        if (!$this->_serializer instanceof SerializerInterface) {
            throw new InvalidConfigException("Serializer beanstalk must implement \\udokmeci\\yii2beanstalk\\SerializerInterface.");
        }

        try {
            $this->_beanstalk = new Pheanstalk($this->host, $this->port, $this->connectTimeout);
        } catch (ConnectionException $e) {
            Yii::error($e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function put(
        $data,
        $priority = PheanstalkInterface::DEFAULT_PRIORITY,
        $delay = PheanstalkInterface::DEFAULT_DELAY,
        $ttr = PheanstalkInterface::DEFAULT_TTR
    )
    {
        try {
            if (!is_string($data)) {
                $data = $this->_serializer->serialize($data);
            }
            return $this->_beanstalk->put($data, $priority, $delay, $ttr);
        } catch (ConnectionException $e) {
            Yii::error($e);
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function putInTube(
        $tube,
        $data,
        $priority = PheanstalkInterface::DEFAULT_PRIORITY,
        $delay = PheanstalkInterface::DEFAULT_DELAY,
        $ttr = PheanstalkInterface::DEFAULT_TTR
    ) {

        try {
            $this->_beanstalk->useTube($tube);
            return $this->put($data, $priority, $delay, $ttr);
        } catch (ConnectionException $e) {
            Yii::error($e);
            return false;
        }
    }

    public function __call($method, $args)
    {

        try {
            $result = call_user_func_array(array($this->_beanstalk, $method), $args);

            //Chaining.
            if ($result instanceof Pheanstalk) {
                return $this;
            }

            //Check for json data.
            if ($result instanceof Job) {
                if ($this->_serializer->isSerialized($result->getData())) {
                    $result = new Job(
                        $result->getId(),
                        $this->_serializer->unserialize($result->getData())
                    );
                }
            }
            return $result;

        } catch (ConnectionException $e) {
            Yii::error($e);
            return false;
        }
    }
}
