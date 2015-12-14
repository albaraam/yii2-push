<?php

namespace albaraam\push\models;

use Yii;

/**
 * This is the model class for table "push_user".
 *
 * @property integer $push_user_id
 * @property integer $push_device_type
 * @property string $push_device_token
 * @property string $push_device_status
 * @property string $service_device_id
 *
 * @property User $user
 */
class PushUser extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_UNREGISTERED = 3;

    const TYPE_UNKNOWN = 0;
    const TYPE_ANDROID = 1;
    const TYPE_IOS = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'push_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['push_user_id', 'push_device_type', 'push_device_token', 'push_device_status'], 'required'],
            [['push_user_id', 'push_device_type', 'push_device_status'], 'integer'],
            [['push_device_token'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'push_user_id' => 'Push User ID',
            'push_device_type' => 'Push Device Type',
            'push_device_token' => 'Push Device Token',
            'push_device_status' => 'Push Device Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'push_user_id']);
    }

    public static function findByDeviceToken($token){
        return self::findOne(["push_device_token" => $token]);
    }

    public static function getTypeByLabel($label){
        switch ($label) {
            case "android" :
                return self::TYPE_ANDROID;
            case "ios" :
                return self::TYPE_IOS;
            default :
                return self::TYPE_UNKNOWN;
        }
    }
}
