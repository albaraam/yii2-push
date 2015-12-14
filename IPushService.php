<?php

namespace albaraam\push;

use albaraam\gcmapns\Message;

interface IPushService
{
    /**
     * @param string $id service id.
     */
    public function setId($id);

    /**
     * @return string service id
     */
    public function getId();

    /**
     * @return string service name.
     */
    public function getName();

    /**
     * @param string $name service name.
     */
    public function setName($name);


    /**
     * @param $token
     * @param $device_type
     * @return string service_device_id
     */
    public function register($token, $device_type);

    /**
     * @param $service_device_id
     * @return bool
     */
    public function unRegister($service_device_id);

    /**
     * @param $name
     * @return string service_channel_id
     */
    public function createChannel($name);

    /**
     * @param $service_channel_id
     * @return bool
     */
    public function deleteChannel($service_channel_id);

    /**
     * @param $service_device_id
     * @param $service_channel_id
     * @return string service_subscription_id
     */
    public function subscribe($service_device_id, $service_channel_id);

    /**
     * @param $service_subscription_id
     * @return bool
     */
    public function unSubscribe($service_subscription_id);


    /**
     * @param $service_channel_id
     * @param $pushMessage Message
     * @return bool
     */
    public function sendToChannel($service_channel_id, $pushMessage);

}
