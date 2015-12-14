# yii2-push
=============

Yii2 Push Extension - Provide support for different push notification services (Basic GCM &amp; Apns, Amazon, parse, ...)

For license information check the [LICENSE](LICENSE)-file.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist albaraam/yii2-push "~1.0.0"
```
or add

```json
"albaraam/yii2-push": "~1.0.0"
```

to the `require` section of your composer.json.

Setup
-------

Add & configure the GCM-Apns component in your config file: (required for the basic service)

```
'components' => [
    'gcmApns' => [
        'class' => 'albaraam\yii2_gcm_apns\GcmApns',
        'google_api_key'=>'your_google_api_key',
        'environment'=>\albaraam\yii2_gcm_apns\GcmApns::ENVIRONMENT_SANDBOX,
        'pem_file'=>'path/to/pem/file'
    ],
]
```

Then add & configure the Collection component in your config file:
`Collection is a storage for all push services in the application.`

```
'components' => [
     ...
     'pushServiceCollection' => [
         'class' => 'albaraam\push\Collection',
         'defaultService' => 'amazon',
         'services' => [
             'basic' => [
                 'class' => 'albaraam\push\services\BasicService',
                 'gcmApnsComponent' => 'gcmApns'
             ],
             'amazon' => [
                 'class' => 'albaraam\push\services\AmazonService',
                 'key' => 'amazon_key',
                 'secret' => 'amazon_secret',
                 'region' => 'amazon_region',
                 'AndroidApplicationArn' => 'android_application_arn',
                 'IosApplicationArn' => 'ios_application_arn',
             ],
             'parse' => [
                 'class' => 'albaraam\push\services\ParseService',
                 'serviceKey' => 'parse_service_key',
                 'serviceSecret' => 'parse_service_secret',
             ],
         ],
     ]
     ...
]
```

In services section it's required to configure the basic service, while the other services are optional.

Usage
-------
Now after the setup, let's see how use it.
  
```
$service = Yii::$app->pushServiceCollection->getService();
// Create a channel/topic
$service_channel_id = $service->createChannel($name);
// register a device token
$token = 'ASD11356889433-KGj45642A'; // just an example
$service_device_id = $service->register($token, $device_type);
// subscribe a device to a channel/topic
$service_device_id = $service->subscribe($service_device_id, $service_channel_id);
// create a notification message
$message = Yii::$app()->gcmApns->messageBuilder("Title","Body");
// Common attributes for both ios and android
$message
    ->setTitle("Title")
    ->setBody("Body")
    ->setSound("sound.mp3")
    ->setData(['foo'=>'bar']);
// Android specific attributes
$message->android
    ->setIcon("icon")
    ->setCollapseKey("collapse_key")
    ->setColor("#333");
// IOS specific attributes
$message->ios
    ->setSound("sound_ios.mp3") // custom sound for ios
    ->setBadge(3);
// send the message to a channel
$service->sendToChannel($service_channel_id, $message);
```

