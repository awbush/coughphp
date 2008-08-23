DROP TABLE IF EXISTS `author`;
CREATE TABLE `author` (
  `author_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `last_modified_datetime` timestamp NULL default NULL on update CURRENT_TIMESTAMP,
  `creation_datetime` datetime NOT NULL,
  `is_retired` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `book`;
CREATE TABLE `book` (
  `book_id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `author_id` int(11) NOT NULL default '0',
  `introduction` text NOT NULL,
  `last_modified_datetime` timestamp NULL default NULL on update CURRENT_TIMESTAMP,
  `creation_datetime` datetime NOT NULL,
  `is_retired` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `book2library`;
CREATE TABLE `book2library` (
  `book2library_id` int(11) NOT NULL auto_increment,
  `book_id` int(11) NOT NULL,
  `library_id` int(11) NOT NULL,
  `last_modified_datetime` timestamp NULL default NULL on update CURRENT_TIMESTAMP,
  `creation_datetime` datetime NOT NULL,
  `is_retired` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`book2library_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `library`;
CREATE TABLE `library` (
  `library_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `last_modified_datetime` timestamp NULL default NULL on update CURRENT_TIMESTAMP,
  `creation_datetime` datetime NOT NULL,
  `is_retired` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`library_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `table_without_auto_increment`;
CREATE TABLE `table_without_auto_increment` (
  `id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;
