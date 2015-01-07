CREATE TABLE `tbpre_sms` (
	`id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '短信id',
	`uid` INT(11) NOT NULL DEFAULT '0' COMMENT '操作者: 0系统, >0用户id',
	`phone` TEXT NOT NULL COMMENT '手机号' COLLATE 'utf8_unicode_ci',
	`content` VARCHAR(255) NOT NULL COMMENT '内容' COLLATE 'utf8_unicode_ci',
	`status` INT(11) NOT NULL DEFAULT '1' COMMENT '发送状态',
	`message` VARCHAR(50) NULL DEFAULT NULL COMMENT '状态信息' COLLATE 'utf8_unicode_ci',
	`created_at` INT(11) NOT NULL COMMENT '发送时间',
	PRIMARY KEY (`id`)
)
COMMENT='短信发送记录'
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB;
