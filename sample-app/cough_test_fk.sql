CREATE DATABASE  `cough_test_fk`; /*!40100 DEFAULT CHARACTER SET latin1 */

USE `cough_test_fk`;

CREATE TABLE  `cough_test_fk`.`customer` (
  `id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE  `cough_test_fk`.`product` (
  `category` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `price` decimal(10,0) default NULL,
  PRIMARY KEY  (`category`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE  `cough_test_fk`.`product_order` (
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

