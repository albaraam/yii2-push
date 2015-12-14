<?php

namespace albaraam\push\models;

use Yii;

/**
 * This is the model class for table "push_subscription".
 *
 * @property integer $subscription_id
 * @property integer $subscription_user_id
 * @property integer $subscription_channel_id
 * @property integer $subscription_status
 * @property string $service_subscription_id
 *
 * @property PushUser $user
 * @property PushChannel $channel
 */
class PushSubscription extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'push_subscription';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscription_user_id', 'subscription_channel_id', 'service_subscription_id', 'subscription_status'], 'required'],
            [['subscription_user_id', 'subscription_channel_id', 'subscription_status'], 'integer'],
            [['service_subscription_id'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'subscription_id' => 'Subscription ID',
            'subscription_user_id' => 'Subscription User ID',
            'subscription_channel_id' => 'Subscription Channel ID',
            'service_subscription_id' => 'Service Subscription ID',
            'subscription_status' => 'Subscription Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(PushUser::className(), ['push_user_id' => 'subscription_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChannel()
    {
        return $this->hasOne(PushChannel::className(), ['channel_id' => 'subscription_channel_id']);
    }
}
