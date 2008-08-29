DROP TABLE IF EXISTS `table_one`;
CREATE TABLE `table_one` (
  `table_one_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`table_one_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

DROP TABLE IF EXISTS `table_two`;
CREATE TABLE `table_two` (
  `table_one_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;
