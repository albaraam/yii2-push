<?php

namespace albaraam\push\services;

use albaraam\gcmapns\Message;
use albaraam\push\IPushService;
use Aws\Sns\SnsClient;
use yii\base\InvalidConfigException;

/**
 * Author: Albaraa Mishlawi (albaraa_m@live.com)
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
        $message = $this->messageToArray($pushMessage);
        $result = $this->getClient()->publish(array(
            'TopicArn' => $service_channel_id,
            'Message' => json_encode([
                "GCM" => json_encode($message['android'],true),
                "APNS" => json_encode($message['ios'],true),
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
    private function getClient()
    {
        if ($this->_client === null) {
            $this->_client = SnsClient::factory([
                'key' => $this->key,
                'secret' => $this->secret,
                'region' => $this->region
            ]);
        }
        return $this->_client;
    }

    private function messageToArray(Message $pushMessage){
        $a = ['android'=>[],'ios'=>[]];

        /************************************ Android Message *************************************/

        /*
         * Options
         * */
        if($pushMessage->android->getCollapseKey()){
            $a['android']["collapse_key"] = $pushMessage->android->collapse_key;
        }
        if($pushMessage->android->getPriority()){
            $a['android']["priority"] = $pushMessage->android->getPriority();
        }
        if($pushMessage->android->isContentAvailable()){
            $a['android']["content_available"] = $pushMessage->android->isContentAvailable();
        }
        if($pushMessage->android->getDelayWhileIdle()){
            $a['android']["delay_while_idle"] = $pushMessage->android->getDelayWhileIdle();
        }
        if($pushMessage->android->getTimeToLive()){
            $a['android']["time_to_live"] = $pushMessage->android->getTimeToLive();
        }
        if($pushMessage->android->getRestrictedPackageName()){
            $a['android']["restricted_package_name"] = $pushMessage->android->getRestrictedPackageName();
        }
        if($pushMessage->android->isDryRun()){
            $a['android']["dry_run"] = $pushMessage->android->isDryRun();
        }

        /*
         * Payload Notification
         * */

        $a['android']["notification"]["title"] =
            ($pushMessage->android->getTitle()) ? $pushMessage->android->getTitle() : "Notification Title";

        if($pushMessage->android->getBody()){
            $a['android']["notification"]["body"] = $pushMessage->android->getBody();
        }

        $a['android']["notification"]["icon"] =
            ($pushMessage->android->getIcon()) ? $pushMessage->android->getIcon() : "default";

        if($pushMessage->android->getSound()){
            $a['android']["notification"]["sound"] = $pushMessage->android->getSound();
        }
        if($pushMessage->android->getTag()){
            $a['android']["notification"]["tag"] = $pushMessage->android->getTag();
        }
        if($pushMessage->android->getColor()){
            $a['android']["notification"]["color"] = $pushMessage->android->getColor();
        }
        if($pushMessage->android->getClickAction()){
            $a['android']["notification"]["click_action"] = $pushMessage->android->getClickAction();
        }
        if($pushMessage->android->getBodyLocKey()){
            $a['android']["notification"]["body_loc_key"] = $pushMessage->android->getBodyLocKey();
        }
        if($pushMessage->android->getBodyLocArgs()){
            $a['android']["notification"]["body_loc_args"] = $pushMessage->android->getBodyLocArgs();
        }
        if($pushMessage->android->getTitleLocKey()){
            $a['android']["notification"]["title_loc_key"] = $pushMessage->android->getTitleLocKey();
        }
        if($pushMessage->android->getTitleLocArgs()){
            $a['android']["notification"]["title_loc_args"] = $pushMessage->android->getTitleLocArgs();
        }

        /*
         * Payload data
         * */
        if($pushMessage->android->getData()){
            $a['android']["data"] = $pushMessage->android->getData();
        }

        /************************************ IOS Message *************************************/

        /*
         * Alert
         * */
        if($pushMessage->ios->getTitle()){
            $a['ios']['aps']['alert']['title'] = $pushMessage->ios->getTitle();
        }
        if($pushMessage->ios->getBody()){
            $a['ios']['aps']['alert']['body'] = $pushMessage->ios->getBody();
        }
        if($pushMessage->ios->getBodyLocKey()){
            $a['ios']['aps']['alert']['loc_key'] = $pushMessage->ios->getBodyLocKey();
        }
        if($pushMessage->ios->getBodyLocArgs()){
            $a['ios']['aps']['alert']['loc_args'] = $pushMessage->ios->getBodyLocArgs();
        }
        if($pushMessage->ios->getTitleLocKey()){
            $a['ios']['aps']['alert']['title_loc_key'] = $pushMessage->ios->getTitleLocKey();
        }
        if($pushMessage->ios->getTitleLocArgs()){
            $a['ios']['aps']['alert']['title_loc_args'] = $pushMessage->ios->getTitleLocArgs();
        }
        if($pushMessage->ios->getLaunchImage()){
            $a['ios']['aps']['alert']['launch_image'] = $pushMessage->ios->getLaunchImage();
        }

        /*
         * Options
         * */
        if($pushMessage->ios->getSound()){
            $a['ios']['aps']["sound"] = $pushMessage->ios->getSound();
        }
        if($pushMessage->ios->getBadge()){
            $a['ios']['aps']["badge"] = $pushMessage->ios->getBadge();
        }
        if($pushMessage->ios->isContentAvailable()){
            $a['ios']['aps']["content_available"] = $pushMessage->ios->isContentAvailable();
        }
        if($pushMessage->ios->getCategory()){
            $a['ios']['aps']["category"] = $pushMessage->ios->getCategory();
        }

        foreach ($pushMessage->ios->getData() as $key => $value) {
            $a['ios'][$key] = $value;
        }

        return $a;
    }

}