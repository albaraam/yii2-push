<?php
/**
 * @link http://www.albaraa.me/
 * @copyright Copyright (c) 2015 Albaraa Mishlawi
 * @license http://www.albaraa.me/license/
 */

namespace albaraam\push;

use yii\base\Component;
use yii\base\InvalidParamException;
use Yii;

/**
 * Collection is a storage for all push services in the application.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'pushServiceCollection' => [
 *         'class' => 'albaraam\push\Collection',
 *         'defaultService' => 'amazon',
 *         'services' => [
 *             'basic' => [
 *                 'class' => 'albaraam\push\services\BasicService',
 *                 'apnsGcmComponent' => 'apnsGcm',
 *                 
 *             ],
 *             'amazon' => [
 *                 'class' => 'albaraam\push\services\AmazonService',
 *                 'key' => 'amazon_key',
 *                 'secret' => 'amazon_secret',
 *                 'region' => 'amazon_region',
 *                 'AndroidApplicationArn' => 'android_application_arn',
 *                 'IosApplicationArn' => 'ios_application_arn',
 *             ],
 *             'parse' => [
 *                 'class' => 'albaraam\push\services\ParseService',
 *                 'serviceKey' => 'parse_service_key',
 *                 'serviceSecret' => 'parse_service_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @property IPushService[] $services List of push services. This property is read-only.
 *
 * @author Albaraa Mishlawi <albaraa_m@live.com>
 * @since 2.0
 */
class Collection extends Component
{
    /**
     * @var array list of Push services with their configuration in format: 'serviceId' => [...]
     */
    private $_services = [];

    /**
     * @var default service  // 'amazon', 'parse', ...
     */
    public $defaultService = "basic";


    /**
     * @param array $services list of push services
     */
    public function setServices(array $services)
    {
        $this->_services = $services;
    }

    /**
     * @return IPushService[] list of push services.
     */
    public function getServices()
    {
        $services = [];
        foreach ($this->_services as $id => $service) {
            $services[$id] = $this->getService($id);
        }

        return $services;
    }

    /**
     * @return IPushService push service instance.
     * @throws InvalidParamException on non existing service request.
     */
    public function getDefaultService()
    {
        if (!is_object($this->_services[$this->defaultService])) {
            $this->_services[$this->defaultService] = $this->createService($this->defaultService, $this->_services[$this->defaultService]);
        }

        return $this->_services[$this->defaultService];
    }

    /**
     * @param string $id service id.
     * @return IPushService push service instance.
     * @throws InvalidParamException on non existing service request.
     */
    public function getService($id)
    {
        if (!array_key_exists($id, $this->_services)) {
            throw new InvalidParamException("Unknown push service '{$id}'.");
        }
        if (!is_object($this->_services[$id])) {
            $this->_services[$id] = $this->createService($id, $this->_services[$id]);
        }

        return $this->_services[$id];
    }

    /**
     * Checks if service exists in the hub.
     * @param string $id service id.
     * @return boolean whether service exist.
     */
    public function hasService($id)
    {
        return array_key_exists($id, $this->_services);
    }

    /**
     * Creates push service instance from its array configuration.
     * @param string $id push service id.
     * @param array $config push service instance configuration.
     * @return IPushService push service instance.
     */
    protected function createService($id, $config)
    {
        $config['id'] = $id;

        return Yii::createObject($config);
    }
}
