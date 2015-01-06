<?php

namespace yii\sms\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Sms extends ActiveRecord{

	public static function tableName(){
		return '{{%sms}}';
	}

	public function behaviors(){
		return [
			TimestampBehavior::className(),
		];
	}

}
