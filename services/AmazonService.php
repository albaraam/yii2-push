<?php

namespace albaraam\push\services;

use albaraam\gcmapns\Message;
use albaraam\push\IPushService;
use Aws\Sns\SnsClient;
use yii\base\InvalidConfigException;
use yii\bootstrap\Html;

/**
 * Created by PhpStorm.
 * User: User
 * Date: 11/27/2015
 * Time: 1:03 PM
 */
class AmazonService extends BaseService implements IPushService
{

    /*
     * @var string specifies the AWS key
     */
    public $key = null;

    /*
     * @var string specifies the AWS secret
     */
    public $secret = null;

    /*
     * @var string specifies the AWS region
     */
    public $region = null;

    /*
     * @var string specifies the AWS PlatformApplicationArn where platform is android
     */
    public $AndroidApplicationArn = null;

    /*
     * @var string specifies the AWS PlatformApplicationArn where platform is ios
     */
    public $IosApplicationArn = null;

    /**
     * @var \Aws\Sns\SnsClient
     */
    private $_client;


    /**
     * @inheritdoc
     */
    public function init()
    {
        foreach (['key', 'secret', 'region', 'AndroidApplicationArn', 'IosApplicationArn'] as $attribute) {
            if ($this->$attribute === null) {
                throw new InvalidConfigException(strtr('"{class}::{attribute}" cannot be empty.', [
                    '{class}' => static::className(),
                    '{attribute}' => '$' . $attribute
                ]));
            }
        }
        parent::init();
    }

    /**
     * @param $token
     * @param $device_type
     * @return string service_device_id
     */
    public function register($token, $device_type)
    {
        $result = $this->getClient()->createPlatformEndpoint([
            // PlatformApplicationArn is required
            'PlatformApplicationArn' => ($device_type == "android")
                    ? $this->AndroidApplicationArn
                    : $this->IosApplicationArn,
            // Token is required
            'Token' => $token,
            /*'CustomUserData' => 'string',*/
        ]);
        return $result->get('EndpointArn');
    }

    /**
     * @param $service_device_id
     * @return bool
     */
    public function unRegister($service_device_id)
    {
        $this->getClient()->deleteEndpoint(array(
            // EndpointArn is required
            'EndpointArn' => $service_device_id,
        ));
        return true;
    }

    /**
     * @param $name
     * @return string service_channel_id
     */
    public function createChannel($name)
    {
        $result = $this->getClient()->createTopic([
            'Name' => $name,
        ]);

        return $result->get('TopicArn');
    }

    /**
     * @param $service_channel_id
     * @return bool
     */
    public function deleteChannel($service_channel_id)
    {
        $this->getClient()->deleteTopic([
            'TopicArn' => $service_channel_id,
        ]);
        return true;
    }

    /**
     * @param $service_device_id
     * @param $service_channel_id
     * @return string subscription_id
     */
    public function subscribe($service_device_id, $service_channel_id)
    {
        $result = $this->getClient()->subscribe(array(
            'TopicArn' => $service_channel_id,
            'Protocol' => 'application',
            'Endpoint' => $service_device_id,
        ));
        return $result->get('SubscriptionArn');
    }

    /**
     * @param $service_subscription_id
     * @return bool
     */
    public function unSubscribe($service_subscription_id)
    {
        $this->getClient()->unsubscribe(array(
            'SubscriptionArn' => $service_subscription_id,
        ));
        return true;
    }

    /**
     * @param $service_channel_id
     * @param $pushMessage Message
     * @return bool
     */
    public function sendToChannel($service_channel_id, $pushMessage)
    {
        $result = $this->getClient()->publish(array(
            'TopicArn' => $service_channel_id,
            'Message' => json_encode([
                "GCM" => json_encode($pushMessage->getAndroidMessage()->toArray(),true),
                "APNS" => json_encode($pushMessage->getIosMessage()->toArray(),true),
                "default" => ""
            ],true),
            'Subject' => 'string',
            'MessageStructure' => 'json',
        ));
    }

    /**
     * Returns a S3Client instance
     * @return \Aws\Sns\SnsClient
     */
    public function getClient()
    {
        if ($this->_client === null) {
            $this->_client = SnsClient::factory([
                'key' => $this->key,
                'secret' => $this->secret
            ]);
        }
        return $this->_client;
    }
}