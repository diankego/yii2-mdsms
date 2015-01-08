<?php
/*!
 * yii2 extension - 漫道短信发送接口
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/diankego/yii2-mdsms
 * https://raw.githubusercontent.com/diankego/yii2-mdsms/master/LICENSE
 * create: 2014/12/28
 * update: 2015/1/8
 * version: 0.0.1
 */

namespace yii\mdsms;

use yii\base\ErrorException;
use yii\mdsms\models\Sms;

class Manager{

	//接口地址
	private $api = 'http://sdk.entinfo.cn:8061/webservice.asmx/mdsmssend';

	//序列号, 格式: XXX-XXX-XXX-XXXXX
	public $sn;

	//密码
	public $password;

	//扩展码, 可选
	private $ext;

	//定时时间, 格式: 2010-12-29 16:27:03, 置空表示立即发送, 可选
	private $stime;

	//唯一标识, 接口返回值, 最长18位, 只支持数字, 可选
	private $rrid = 1;

	//内容编码, 0(ASCII), 3(短信写卡操作), 4(二进制信息), 空或15(含GB汉字), 可选
	private $msgfmt;

	//密码, md5(sn + password)32位大写密文
	private $pwd = false;

	//提示信息
	private $messages = false;

	/**
	 * 发送
	 * @method send
	 * @since 0.0.1
	 * @param {array} $data 发送所需数据, 包括手机(支持10000个手机号, 建议<=5000, 多个以英文逗号隔开)和内容(支持长短信, utf8编码)
	 * @param {number} $uid 操作者, 0系统, >0用户id
	 * @return {boolean}
	 * @example Yii::$app->sms->send($data, $uid);
	 */
	public function send($data, $uid = 0){
		if(empty($data)){
			throw new ErrorException('Mobile and content must be required');
		}
		$status = true;
		$time = time();
		foreach($data as $phone => $content){
			$_phone = $this->formatMobile($phone);
			$sms = new Sms;
			$sms->phone = $_phone;
			$sms->content = $content;
			$sms->uid = $uid;
			$sms->status = reset(simplexml_load_string($this->curl($this->api, $this->completeParams(http_build_query([
				'sn' => $this->sn,
				'pwd' => $this->getPwd(),
				'mobile' => $_phone,
				'content' => $content,
			]))), 'SimpleXMLElement', LIBXML_NOCDATA));
			$sms->message = $this->getMessage($sms->status);
			$sms->created_at = $time;
			$sms->save();
			if($sms->status != $this->rrid){
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * 完善参数
	 * @method completeParams
	 * @since 0.0.1
	 * @param {string} $query query string
	 * @return {string}
	 */
	private function completeParams($query){
		return $query . '&ext=' . $this->ext . '&stime=' . $this->stime . '&rrid=' . $this->rrid . '&msgfmt=' . $this->msgfmt;
	}

	/**
	 * 格式化手机号码
	 * @method formatMobile
	 * @since 0.0.1
	 * @param {string} $mobile 手机号
	 * @return {string}
	 */
	private function formatMobile($mobiles){
		$mobiles = array_unique(explode(',', trim(preg_replace('/[^\d,]/', '', $mobiles), ',')));
		$_mobiles = [];
		foreach($mobiles as $mobile){
			if(preg_match('/\d{11}/', $mobile)){
				$_mobiles[] = $mobile;
			}
		}
		return implode(',', $_mobiles);
	}

	/**
	 * 获取密码
	 * @method getPwd
	 * @since 0.0.1
	 * @return {string}
	 */
	private function getPwd(){
		if($this->pwd === false){
			$this->pwd = strtoupper(md5($this->sn . $this->password));
		}

		return $this->pwd;
	}

	/**
	 * 获取信息
	 * @method getMessage
	 * @since 0.0.1
	 * @return {string}
	 */
	private function getMessage($status){
		if($this->messages === false){
			$this->messages = require(__DIR__ . '/messages.php');
		}

		return isset($this->messages[$status]) ? $this->messages[$status] : '';
	}

	/**
	 * curl远程获取数据方法
	 * @method curl
	 * @since 0.0.1
	 * @param {string} $url 请求地址
	 * @param {array|string} [$data=null] post数据
	 * @param {string} [$useragent=null] 模拟浏览器用户代理信息
	 * @return {string}
	 */
	private function curl($url, $data = null, $useragent = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if(!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		if(!empty($useragent)){
			curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		}
		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}

}