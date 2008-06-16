-- foreign keys: yes
-- multi-key primary key: yes
-- "retire/delete" colums: no
-- one-to-one relationships: yes
-- one-to-many relationships: yes
-- many-to-many relationships: yes (customer has many ordered products and a product has many customers)

DROP TABLE IF EXISTS `customer`;
CREATE TABLE  `customer` (
  `id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `product`;
CREATE TABLE  `product` (
  `category` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `price` decimal(10,0) default NULL,
  PRIMARY KEY  (`category`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `product_order`;
CREATE TABLE  `product_order` (
  `no` int(11) NOT NULL auto_increment,
  `product_category` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  PRIMARY KEY  (`no`),
  KEY `product_category` (`product_category`,`product_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `product_order_ibfk_1` FOREIGN KEY (`product_category`, `product_id`) REFERENCES `product` (`category`, `id`) ON UPDATE CASCADE,
  CONSTRAINT `product_order_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

