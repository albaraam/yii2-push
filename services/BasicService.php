<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 11/27/2015
 * Time: 4:34 PM
 */

namespace albaraam\push\services;


use albaraam\gcmapns\Client;
use albaraam\gcmapns\Message;
use albaraam\push\models\PushChannel;
use albaraam\push\models\PushSubscription;
use albaraam\push\models\PushUser;
use albaraam\push\IPushService;

class BasicService extends BaseService implements IPushService
{
    /**
     * @var string gcmApns Component Name.
     */
    public $gcmApnsComponent = "gcmApns";

    /**
     * @var string push service id.
     * This value mainly used as HTTP request parameter.
     */
    private $_id = "basic";
    /**
     * @var string push service name.
     * This value may be used in database records, CSS files and so on.
     */
    private $_name = "basic";
    /**
     * @var Client albaraam\gcmapns\Client.
     */
    private $_client = null;

    /**
     * @param $token
     * @param $device_type
     * @return string service_device_id
     */
    public function register($token, $device_type)
    {
        return $token;
    }

    /**
     * @param $service_device_id
     * @return bool
     */
    public function unRegister($service_device_id)
    {
        return true;
    }

     /**
     * @param $name
     * @return string service_channel_id
     */
    public function createChannel($name)
    {
        return str_replace(" ","-",strtolower($name));
    }

    /**
     * @param $service_channel_id
     * @return bool
     */
    public function deleteChannel($service_channel_id)
    {
        return true;
    }

    /**
     * @param $service_device_id
     * @param $service_channel_id
     * @return string service_subscription_id
     */
    public function subscribe($service_device_id, $service_channel_id)
    {
        return $service_channel_id . "#" . $service_device_id;
    }

    /**
     * @param $service_subscription_id
     * @return bool
     */
    public function unSubscribe($service_subscription_id)
    {
        return true;
    }

    /**
     * @param $service_channel_id
     * @param $pushMessage Message
     * @return bool
     */
    public function sendToChannel($service_channel_id, $pushMessage)
    {
        $channel = PushChannel::findOne(["service_channel_id"=>$service_channel_id]);

        // Get Related ANDROID devices
        // ============================
        $android_devices = PushSubscription::find()
            ->joinWith("user")
            ->where([
                "subscription_channel_id"=>$channel->channel_id,
                "subscription_status"=>PushSubscription::STATUS_ACTIVE
            ])
            ->andWhere([
                "push_device_type"=>PushUser::TYPE_ANDROID,
                "push_device_status"=>PushUser::STATUS_ACTIVE
            ]);
        $android_tokens = [];
        $i = 0; $j = 1;
        foreach ($android_devices as $android_device) {
            if($j % 1000 != 0){
                $android_tokens[$i][] = $android_device->user->push_device_token;
            }else{
                $i++;
                $android_tokens[$i][] = $android_device->user->push_device_token;
            }
            $j++;
        }

        // Get Related IOS devices
        // ========================
        $ios_devices = PushSubscription::find()
            ->joinWith("user")
            ->where([
                "subscription_channel_id"=>$channel->channel_id,
                "subscription_status"=>PushSubscription::STATUS_ACTIVE
            ])
            ->andWhere([
                "push_device_type"=>PushUser::TYPE_ANDROID,
                "push_device_status"=>PushUser::STATUS_ACTIVE
            ]);
        $ios_tokens = [];
        $i = 0; $j = 1;
        foreach ($ios_devices as $ios_device) {
            if($j % 1000 != 0){
                $ios_tokens[$i][] = $ios_device->user->push_device_token;
            }else{
                $i++;
                $ios_tokens[$i][] = $ios_device->user->push_device_token;
            }
            $j++;
        }

        // Send notification to the related devices
        // ========================================

        foreach ($android_tokens as $android_tokens_pack) {
            $pushMessage->android->setTo($android_tokens_pack);
            $this->getClient()->sendAndroid($pushMessage);
        }

        foreach ($ios_tokens as $ios_tokens_pack) {
            $pushMessage->ios->setTo($ios_tokens_pack);
            $this->getClient()->sendIOS($pushMessage);
        }
    }


    public function getClient(){
        if ($this->_client === null) {
            $component = $this->gcmApnsComponent;
            $client = \Yii::$app->get($component);
            // TODO check if client is null
            $this->_client = $client;
        }
        return $this->_client;
    }
}