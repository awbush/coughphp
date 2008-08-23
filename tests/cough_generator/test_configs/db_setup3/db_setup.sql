-- foreign keys: no
-- multi-key primary key: no
-- "retire/delete" colums: is_retired
-- one-to-one relationships: yes
-- one-to-many relationships: yes
-- many-to-many relationships: yes

DROP TABLE IF EXISTS `person`;
CREATE TABLE `person` (
  `person_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `is_retired` tinyint(1) NOT NULL default '0',
  `political_party_id` int(11) default NULL,
  PRIMARY KEY  (`person_id`),
  KEY `political_party_id` (`political_party_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

INSERT INTO `person` (`person_id`, `name`, `is_retired`, `political_party_id`) VALUES 
(1, 'Anthony', 0, 2),
(2, 'Lewis', 0, 2),
(3, 'Tom', 0, 2);

DROP TABLE IF EXISTS `person2school`;
CREATE TABLE `person2school` (
  `person2school_id` int(11) NOT NULL auto_increment,
  `school_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `person2school_type_id` int(11) default NULL,
  `relationship_start_date` datetime default NULL,
  `relationship_end_date` datetime default NULL,
  `is_retired` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`person2school_id`),
  KEY `school_id` (`school_id`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

INSERT INTO `person2school` (`person2school_id`, `school_id`, `person_id`, `person2school_type_id`, `relationship_start_date`, `relationship_end_date`, `is_retired`) VALUES 
(1, 1, 1, 1, '2007-02-19 02:02:59', '2007-02-19 02:02:59', 1),
(2, 1, 1, 1, '2007-02-19 02:07:14', '2007-02-19 02:07:14', 1),
(3, 1, 1, 1, '2007-02-19 02:40:06', '2007-02-19 02:40:06', 0);

DROP TABLE IF EXISTS `person2school_type`;
CREATE TABLE `person2school_type` (
  `person2school_type_id` int(11) NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY  (`person2school_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

INSERT INTO `person2school_type` (`person2school_type_id`, `name`) VALUES 
(1, 'Student'),
(3, 'Professor'),
(5, 'TA');

DROP TABLE IF EXISTS `political_party`;
CREATE TABLE `political_party` (
  `political_party_id` int(11) NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  `is_retired` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`political_party_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

INSERT INTO `political_party` (`political_party_id`, `name`, `is_retired`) VALUES 
(1, 'Democratic', 0),
(2, 'Republican', 0),
(3, 'Libertarian', 0),
(4, 'Green', 0);

DROP TABLE IF EXISTS `school`;
CREATE TABLE `school` (
  `school_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(16) default NULL,
  `school_type_id` int(11) NOT NULL,
  `is_retired` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`school_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

INSERT INTO `school` (`school_id`, `name`, `short_name`, `school_type_id`, `is_retired`) VALUES 
(1, 'Texas Tech University', 'TTU', 3, 0),
(2, 'University of Texas', 'UT', 3, 0),
(3, 'Austin Community College', 'ACC', 1, 0),
(4, 'San Jacinto College', 'SJC', 1, 0);

DROP TABLE IF EXISTS `school_type`;
CREATE TABLE `school_type` (
  `school_type_id` int(11) NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY  (`school_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

INSERT INTO `school_type` (`school_type_id`, `name`) VALUES 
(1, 'College'),
(3, 'University');
