<?php

namespace albaraam\push\models;

use Yii;

/**
 * This is the model class for table "push_channel".
 *
 * @property integer $channel_id
 * @property string $channel_name
 * @property string $channel_image
 * @property string $service_channel_id
 * @property integer $channel_status
 *
 * @property PushSubscription[] $pushSubscriptions
 */
class PushChannel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'push_channel';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['channel_name', 'channel_image', 'service_channel_id', 'channel_status'], 'required'],
            [['channel_status'], 'integer'],
            [['channel_name', 'channel_image', 'service_channel_id'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'channel_id' => 'Channel ID',
            'channel_name' => 'Channel Name',
            'channel_image' => 'Channel Image',
            'service_channel_id' => 'Service Channel ID',
            'channel_status' => 'Channel Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPushSubscriptions()
    {
        return $this->hasMany(PushSubscription::className(), ['subscription_channel_id' => 'channel_id']);
    }
}
