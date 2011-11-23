/************************
Update from v 0.4 to 0.5 
************************/


# Dump of table switches
# ------------------------------------------------------------
DROP TABLE IF EXISTS `switches`;

CREATE TABLE `switches` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hostname` varchar(32) DEFAULT NULL,
  `ip_addr` varchar(100) DEFAULT NULL,
  `vendor` varchar(156) DEFAULT NULL,
  `model` varchar(124) DEFAULT NULL,
  `version` varchar(128) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hostname` (`hostname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


# Dump of table vrf
# ------------------------------------------------------------
DROP TABLE IF EXISTS `vrf`;

CREATE TABLE `vrf` (
  `vrfId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `rd` varchar(32) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`vrfId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


/* Add vrf to subnets */
ALTER TABLE `subnets` ADD `vrfId` int(3) DEFAULT NULL after `VLAN`;