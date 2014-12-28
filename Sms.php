<?php
/*!
 * yii2 extension - 漫道短信发送接口
 * xiewulong <xiewulong@vip.qq.com>
 * https://github.com/diankego/yii2-smsMD
 * https://raw.githubusercontent.com/diankego/yii2-smsMD/master/LICENSE
 * create: 2014/12/28
 * update: 2014/12/28
 * version: 1.0.0
 */

namespace yii\smsMD;

use Yii;
use yii\base\InvalidValueException;

class sms{

	//接口地址
	private $api = 'http://sdk.entinfo.cn:8061/webservice.asmx/mdsmssend';

	//序列号, 格式: XXX-XXX-XXX-XXXXX
	public $sn;

	//密码
	public $password;

	//发送所需数据, 包括手机(支持10000个手机号, 建议<=5000, 多个以英文逗号隔开)和内容(支持长短信, utf8编码)
	private $data = [];

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

	/**
	 * 发送
	 * @method send
	 * @since 1.0.0
	 * @param {array} $data 发送所需数据, 格式: ['手机号' => '内容']
	 * @return {array}
	 * @example Yii::$app->sms->send();
	 */
	public function send($data){
		if(empty($data)){
			throw new InvalidValueException('You must specify the need to send a mobile phone number and content');
		}
		$message = require(__DIR__ . '/message.php');
		foreach($data as $mobile => $content){
			$_mobile = $this->formatMobile($mobile);
			$this->data[$mobile] = [
				'status' => reset(simplexml_load_string($this->curl($this->api, $this->completeParams(http_build_query([
					'sn' => $this->sn,
					'pwd' => $this->getPwd(),
					'mobile' => $_mobile,
					'content' => $content,
				]))), 'SimpleXMLElement', LIBXML_NOCDATA)),
				'mobile' => $_mobile,
				'content' => $content,
				'sendtime' => time(),
			];
			$this->data[$mobile]['message'] = $message[$this->data[$mobile]['status']];
		}
		return $this->data;
	}

	/**
	 * 完善参数
	 * @method send
	 * @since 1.0.0
	 * @param {string} $query query string
	 * @return {none}
	 */
	private function completeParams($query){
		return $query . '&ext=' . $this->ext . '&stime=' . $this->stime . '&rrid=' . $this->rrid . '&msgfmt=' . $this->msgfmt;
	}

	/**
	 * 格式化手机号码
	 * @method formatMobile
	 * @since 1.0.0
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
	 * @since 1.0.0
	 * @return {string}
	 */
	private function getPwd(){
		if($this->pwd === false){
			$this->pwd = strtoupper(md5($this->sn . $this->password));
		}

		return $this->pwd;
	}

	/**
	 * curl远程获取数据方法
	 * @method curl
	 * @since 1.0.0
	 * @param {string} $url 请求地址
	 * @param {array|string} [$data=null] post数据
	 * @param {string} [$useragent=null] 模拟浏览器用户代理信息
	 * @return {string} 返回获取的数据
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