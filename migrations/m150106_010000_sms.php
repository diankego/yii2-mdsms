<?php

use yii\db\Schema;

class m150106_010000_sms extends \yii\db\Migration {

	public function up() {
		$tableOptions = 'engine=innodb character set utf8';
		if($this->db->driverName === 'mysql') {
			$tableOptions .= ' collate utf8_unicode_ci';
		}

		$this->createTable('{{%sms}}', [
			'id' => Schema::TYPE_PK . ' comment "id"',
			'mobile' => Schema::TYPE_TEXT . ' not null comment "移动号码, 多个以英文逗号隔开, <=10000"',
			'content' => Schema::TYPE_STRING . ' not null comment "内容"',
			'status' => Schema::TYPE_INTEGER . ' not null default 1 comment "发送状态"',
			'message' => Schema::TYPE_STRING . '(50) comment "状态信息"',
			'sent_at' => Schema::TYPE_INTEGER . ' not null default 0 comment "发送时间, 0立即, >0定时"',
			'operator_id' => Schema::TYPE_INTEGER . ' not null default 0 comment "操作员: 0系统, >0用户id"',
			'created_at' => Schema::TYPE_INTEGER . ' not null comment "创建时间"',
		], $tableOptions . ' comment="短信发送记录"');
	}

	public function down() {
		$this->dropTable('{{%sms}}');
	}

}
