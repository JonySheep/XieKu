ALTER TABLE `swa`.`swa_users`
  ADD COLUMN `openid` VARCHAR(50) NULL AFTER `mobile`;


CREATE TABLE `swa`.`swa_users_like`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `uid` INT(11),
  `goods_id` INT(11),
  PRIMARY KEY (`id`)
) ENGINE=INNODB CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE `swa_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '地址id',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `address_group_id` int(11) DEFAULT '0' COMMENT '分组id',
  `fullname` varchar(50) DEFAULT '' COMMENT '联系人姓名',
  `company` varchar(100) DEFAULT '' COMMENT '公司名称',
  `address` varchar(600) DEFAULT '' COMMENT '收货地址',
  `city` varchar(20) DEFAULT '' COMMENT '市',
  `province` varchar(20) DEFAULT '' COMMENT '省',
  `contory` varchar(20) DEFAULT '' COMMENT '区',
  `mobile` varchar(30) DEFAULT NULL COMMENT '电话',
  `is_default` int(2) DEFAULT '0' COMMENT '默认',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8 COMMENT='用户地址';