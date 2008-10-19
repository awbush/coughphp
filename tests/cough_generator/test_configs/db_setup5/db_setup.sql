CREATE TABLE `network` (
 `id` int(11) NOT NULL auto_increment COMMENT 'PK',
 `interfaceId` int(11) default NULL COMMENT 'FK_interface_id',
 `ipAdresa` varchar(16) collate utf8_czech_ci NOT NULL COMMENT 'ip adresa site',
 `maska` int(16) unsigned NOT NULL COMMENT 'maska site',
 `ipRouter` varchar(16) collate utf8_czech_ci default NULL COMMENT 'ip adresa routeru',
 `verejna` int(1) unsigned NOT NULL default '0' COMMENT 'boolean 1=je verejna / 0=neni verejna',
 `isp` enum('sumnet','poda','sloane') collate utf8_czech_ci NOT NULL default 'sumnet' COMMENT 'urceni ktery isp vlastni tuto sit , poda a sloane maji verejne',
 PRIMARY KEY  (`id`),
 KEY `interfaceId` (`interfaceId`),
 KEY `verejna` (`verejna`),
 KEY `isp` (`isp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='ip site verejne/neverejne' AUTO_INCREMENT=7 ;

CREATE TABLE `custPc` (
 `id` int(11) NOT NULL auto_increment COMMENT 'PK',
 `customerId` int(11) default NULL COMMENT 'FK customer id',
 `popis` varchar(50) collate utf8_czech_ci NOT NULL COMMENT 'textovy popis pocitace',
 `macAdresa` varchar(12) collate utf8_czech_ci default NULL COMMENT 'mac adresa stroje',
 `networkId` int(11) default NULL COMMENT 'FK network id',
 `ipAdresa` varchar(16) collate utf8_czech_ci default NULL COMMENT 'ip adresa stroje',
 `networkIdVerejna` int(11) default NULL COMMENT 'FK network id pro verejnou ip adresu',
 `ipAdresaVerejna` varchar(16) collate utf8_czech_ci default NULL COMMENT 'verejna ip adresa stroje',
 `down` int(11) default NULL COMMENT 'rychlost downloadu',
 `up` int(11) default NULL COMMENT 'rychlost uploadu',
 PRIMARY KEY  (`id`),
 KEY `customerId` (`customerId`),
 KEY `networkId` (`networkId`),
 KEY `networkIdVerejna` (`networkIdVerejna`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='stroje zakazniku' AUTO_INCREMENT=8 ;

ALTER TABLE `custPc`
 #ADD CONSTRAINT `custPc_ibfk_2` FOREIGN KEY (`customerId`) REFERENCES `customer` (`usr_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 ADD CONSTRAINT `custPc_ibfk_3` FOREIGN KEY (`networkId`) REFERENCES `network` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
 ADD CONSTRAINT `custPc_ibfk_4` FOREIGN KEY (`networkIdVerejna`) REFERENCES `network` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
