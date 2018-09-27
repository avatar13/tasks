-- ----------------------------
-- Table structure for shop_currencies
-- ----------------------------
DROP TABLE IF EXISTS `shop_currencies`;
CREATE TABLE `shop_currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` char(3) NOT NULL DEFAULT '',
  `exchange_rate` decimal(16,6) NOT NULL DEFAULT '0.000000',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `sorting` smallint(6) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`,`code`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
INSERT INTO `shop_currencies` VALUES ('1', 'руб.', 'RUB', 666, '0000-00-00', '1', '10', '19', '0')INSERT INTO `shop_currencies` VALUES ('2', '&euro;', 'EUR', 666, '0000-00-00', '0', '20', '19', '0')INSERT INTO `shop_currencies` VALUES ('3', '$', 'USD', 666, '0000-00-00', '0', '30', '19', '0')