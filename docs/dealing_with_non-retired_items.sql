CREATE TABLE `address` (
  `address_id` int(11) NOT NULL auto_increment,
  `customer_id` int(11) default NULL,
  `address1` varchar(255) NOT NULL,
  `address2` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` char(2) NOT NULL,
  `zip` varchar(20) NOT NULL,
  `is_retired` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`address_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `is_retired` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `order` (
  `order_id` int(11) NOT NULL auto_increment,
  `customer_id` int(11) default NULL,
  `billing_address_id` int(11) default NULL,
  `shipping_address_id` int(11) default NULL,
  PRIMARY KEY  (`order_id`),
  KEY `customer_id` (`customer_id`,`billing_address_id`,`shipping_address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
