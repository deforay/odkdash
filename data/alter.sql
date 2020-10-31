

--ilahir 23-Apr-2016

CREATE TABLE IF NOT EXISTS `spi_rt_3_facilities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` varchar(255) DEFAULT NULL,
  `` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

--ilahir 26-Apr-2016


CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `status`) VALUES
(1, 'SPI Form', 'active');


CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `role_code` varchar(255) DEFAULT NULL,
  `role_name` varchar(255) DEFAULT NULL,
  `description` mediumtext,
  `status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`role_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;


ALTER TABLE  `users` ADD  `first_name` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `id` ;
ALTER TABLE  `users` ADD  `last_name` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `first_name` ;
ALTER TABLE  `users` ADD  `status` VARCHAR( 255 ) NULL DEFAULT NULL ;
ALTER TABLE  `users` ADD  `created_on` DATETIME NULL DEFAULT NULL ;
ALTER TABLE  `users` ADD  `email` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `password` ;


CREATE TABLE IF NOT EXISTS `user_role_map` (
  `map_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`map_id`),
  KEY `role_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

--
-- Constraints for table `user_role_map`
--
ALTER TABLE `user_role_map`
  ADD CONSTRAINT `user_role_map_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_role_map_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

--saravanna 03-may-2016
CREATE TABLE IF NOT EXISTS  `global_config` (
 `config_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
 `display_name` VARCHAR( 255 ) NOT NULL ,
 `global_name` VARCHAR( 255 ) DEFAULT NULL ,
 `global_value` VARCHAR( 255 ) DEFAULT NULL ,
PRIMARY KEY (  `config_id` )
) ENGINE = MYISAM DEFAULT CHARSET = latin1 AUTO_INCREMENT =1;

INSERT INTO `global_config` (`config_id`, `display_name`, `global_name`, `global_value`) VALUES (NULL, 'Auto Approve Status', 'approve_status', 'yes');



--ilahir 04-May-2016
CREATE TABLE IF NOT EXISTS `event_log` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `actor` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `event_type` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `resource_name` varchar(255) DEFAULT NULL,
  `date_time` datetime DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `actor` (`actor`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

ALTER TABLE `event_log`
  ADD CONSTRAINT `event_log_ibfk_1` FOREIGN KEY (`actor`) REFERENCES `users` (`id`);
  
--ilahir 10-MAY-2016

CREATE TABLE IF NOT EXISTS `resources` (
  `resource_id` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `privileges` (
  `resource_id` varchar(255) NOT NULL DEFAULT '',
  `privilege_name` varchar(255) NOT NULL DEFAULT '',
  `display_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`resource_id`,`privilege_name`),
  UNIQUE KEY `resource_id_2` (`resource_id`,`privilege_name`),
  KEY `resource_id` (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO `resources` (`resource_id`, `display_name`) VALUES
('Application\\Controller\\Config', 'Global Config'),
('Application\\Controller\\Facility', 'Manage Facility'),
('Application\\Controller\\Index', 'Dashboard'),
('Application\\Controller\\Roles', 'Manage Roles'),
('Application\\Controller\\SpiV3', 'Manage SpiV3 Form'),
('Application\\Controller\\Users', 'Manage Users');


INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES
('Application\\Controller\\Config', 'edit-global', 'Edit'),
('Application\\Controller\\Config', 'index', 'Access'),
('Application\\Controller\\Facility', 'add', 'Add'),
('Application\\Controller\\Facility', 'edit', 'Edit'),
('Application\\Controller\\Facility', 'get-facility-name', 'Merge Facilities'),
('Application\\Controller\\Facility', 'index', 'Access'),
('Application\\Controller\\Index', 'index', 'Access'),
('Application\\Controller\\Roles', 'add', 'Add'),
('Application\\Controller\\Roles', 'edit', 'Edit'),
('Application\\Controller\\Roles', 'index', 'Access'),
('Application\\Controller\\SpiV3', 'approve-status', 'Approved Status'),
('Application\\Controller\\SpiV3', 'download-pdf', 'Download pdf'),
('Application\\Controller\\SpiV3', 'edit', 'Edit'),
('Application\\Controller\\SpiV3', 'index', 'Access'),
('Application\\Controller\\SpiV3', 'manage-facility', 'Access to edit SPI Form'),
('Application\\Controller\\Users', 'add', 'Add'),
('Application\\Controller\\Users', 'edit', 'Edit'),
('Application\\Controller\\Users', 'index', 'Access');

--Pal 12-MAY-2016
INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Application\\Controller\\Email', 'Manage Email');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\Email', 'index', 'Access');


INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV3', 'corrective-action-pdf', 'Download corrective action pdf');

--Pal 13-MAY-2016
CREATE TABLE IF NOT EXISTS `temp_mail` (
  `temp_id` int(11) NOT NULL AUTO_INCREMENT,
  `from_full_name` varchar(255) DEFAULT NULL,
  `from_mail` varchar(255) DEFAULT NULL,
  `to_email` varchar(255) NOT NULL,
  `cc` varchar(500) DEFAULT NULL,
  `bcc` varchar(500) DEFAULT NULL,
  `subject` mediumtext,
  `message` mediumtext,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`temp_id`)
)

--Pal 17-MAY-2016
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV3', 'delete', 'Delete');


--Amit 17-MAY-2016
ALTER TABLE `spi_form_v_3` CHANGE `avgMonthTesting` `avgMonthTesting` INT NULL DEFAULT '0';


--Pal 17-MAY-2016
ALTER TABLE `spi_rt_3_facilities` ADD `status` VARCHAR(255) NOT NULL DEFAULT 'active' AFTER `longitude`;

--Pal 24th-Aug-2016
CREATE TABLE `user_token_map` (
  `user_id` int(11) NOT NULL,
  `token` varchar(45) NOT NULL
)

CREATE TABLE `audit_mails` (
  `mail_id` int(11) NOT NULL,
  `from_full_name` varchar(255) DEFAULT NULL,
  `from_mail` varchar(255) DEFAULT NULL,
  `to_email` varchar(255) NOT NULL,
  `cc` varchar(500) DEFAULT NULL,
  `bcc` varchar(500) DEFAULT NULL,
  `subject` mediumtext,
  `message` mediumtext,
  `status` varchar(255) NOT NULL DEFAULT 'pending'
)

ALTER TABLE `audit_mails`
  ADD PRIMARY KEY (`mail_id`);
  
ALTER TABLE `audit_mails`
  MODIFY `mail_id` int(11) NOT NULL AUTO_INCREMENT;
  
--Pal 25th-Aug-2016
INSERT INTO `global_config` (`config_id`, `display_name`, `global_name`, `global_value`) VALUES (NULL, 'Header', 'header', NULL), (NULL, 'Logo', 'logo', NULL);


--ilahir 16-NOV-2016
INSERT INTO `odkdash`.`global_config` (`config_id`, `display_name`, `global_name`, `global_value`) VALUES (NULL, 'Language', 'language', 'English');

INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Application\\Controller\\Dashboard', 'Manage Dashboard');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\Dashboard', 'index', 'Access'), ('Application\\Controller\\Dashboard', 'audi-details', 'Manage Audit details');
UPDATE `resources` SET `display_name` = 'Home' WHERE `resources`.`resource_id` = 'Application\\Controller\\Index';

ALTER TABLE `roles` ADD PRIMARY KEY(`role_id`);
ALTER TABLE `roles` ADD UNIQUE(`role_id`);
ALTER TABLE `roles` CHANGE `role_id` `role_id` INT(11) NOT NULL AUTO_INCREMENT;

--Pal 04-Apr-2017
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\Facility', 'map-province', 'Map Province');

UPDATE `privileges` SET `privilege_name` = 'get-province-list' WHERE `privileges`.`resource_id` = 'Application\\Controller\\Facility' AND `privileges`.`privilege_name` = 'map-province';

--Pal 06-Apr-2017
CREATE TABLE `r_spi_form_v_3_download` (
  `r_download_id` int(11) NOT NULL,
  `auditroundno` varchar(255) DEFAULT NULL,
  `assesmentofaudit` varchar(255) DEFAULT NULL,
  `testingpointtype` varchar(255) DEFAULT NULL,
  `testingpointname` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
  `affiliation` varchar(255) DEFAULT NULL,
  `level_name` varchar(255) DEFAULT NULL,
  `AUDIT_SCORE_PERCANTAGE` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `download_status` int(11) NOT NULL DEFAULT '0'
);

ALTER TABLE `r_spi_form_v_3_download`
  ADD PRIMARY KEY (`r_download_id`);
  
ALTER TABLE `r_spi_form_v_3_download`
  MODIFY `r_download_id` int(11) NOT NULL AUTO_INCREMENT;
  
ALTER TABLE `r_spi_form_v_3_download` ADD `user` INT(11) NOT NULL AFTER `r_download_id`;

--saravanan 05-apr-2017
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV3', 'duplicate', 'Duplicate');

CREATE TABLE `spi_form_v_3_duplicate` (
  `id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `formId` varchar(255) NOT NULL,
  `formVersion` varchar(255) NOT NULL,
  `meta-instance-id` varchar(255) NOT NULL,
  `meta-model-version` varchar(255) NOT NULL,
  `meta-ui-version` varchar(255) NOT NULL,
  `meta-submission-date` varchar(255) NOT NULL,
  `meta-is-complete` varchar(255) NOT NULL,
  `meta-date-marked-as-complete` varchar(255) NOT NULL,
  `start` varchar(255) DEFAULT NULL,
  `end` varchar(255) DEFAULT NULL,
  `today` date DEFAULT NULL,
  `deviceid` varchar(255) DEFAULT NULL,
  `subscriberid` varchar(255) DEFAULT NULL,
  `text_image` varchar(255) DEFAULT NULL,
  `info1` varchar(255) DEFAULT NULL,
  `info2` varchar(255) DEFAULT NULL,
  `assesmentofaudit` date NOT NULL,
  `auditroundno` varchar(255) DEFAULT NULL,
  `facilityname` varchar(255) DEFAULT NULL,
  `facilityid` varchar(255) DEFAULT NULL,
  `testingpointname` varchar(255) DEFAULT NULL,
  `testingpointtype` varchar(255) DEFAULT NULL,
  `testingpointtype_other` varchar(255) DEFAULT NULL,
  `locationaddress` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
  `level_other` varchar(255) DEFAULT NULL,
  `level_name` varchar(255) DEFAULT NULL,
  `affiliation` varchar(255) DEFAULT NULL,
  `affiliation_other` varchar(255) DEFAULT NULL,
  `NumberofTester` varchar(255) DEFAULT NULL,
  `avgMonthTesting` int(11) DEFAULT '0',
  `name_auditor_lead` varchar(255) DEFAULT NULL,
  `name_auditor2` varchar(255) DEFAULT NULL,
  `info4` varchar(255) DEFAULT NULL,
  `INSTANCE` varchar(255) DEFAULT NULL,
  `PERSONAL_Q_1_1` varchar(255) DEFAULT NULL,
  `PERSONAL_C_1_1` varchar(255) DEFAULT NULL,
  `PERSONAL_Q_1_2` varchar(255) DEFAULT NULL,
  `PERSONAL_C_1_2` varchar(255) DEFAULT NULL,
  `PERSONAL_Q_1_3` varchar(255) DEFAULT NULL,
  `PERSONAL_C_1_3` varchar(255) DEFAULT NULL,
  `PERSONAL_Q_1_4` varchar(255) DEFAULT NULL,
  `PERSONAL_C_1_4` varchar(255) DEFAULT NULL,
  `PERSONAL_Q_1_5` varchar(255) DEFAULT NULL,
  `PERSONAL_C_1_5` varchar(255) DEFAULT NULL,
  `PERSONAL_Q_1_6` varchar(255) DEFAULT NULL,
  `PERSONAL_C_1_6` varchar(255) DEFAULT NULL,
  `PERSONAL_Q_1_7` varchar(255) DEFAULT NULL,
  `PERSONAL_C_1_7` varchar(255) DEFAULT NULL,
  `PERSONAL_Q_1_8` varchar(255) DEFAULT NULL,
  `PERSONAL_C_1_8` varchar(255) DEFAULT NULL,
  `PERSONAL_Q_1_9` varchar(255) DEFAULT NULL,
  `PERSONAL_C_1_9` varchar(255) DEFAULT NULL,
  `PERSONAL_Q_1_10` varchar(255) DEFAULT NULL,
  `PERSONAL_C_1_10` varchar(255) DEFAULT NULL,
  `PERSONAL_SCORE` varchar(255) DEFAULT NULL,
  `PERSONAL_Display` varchar(255) DEFAULT NULL,
  `PERSONALPHOTO` varchar(255) DEFAULT NULL,
  `PHYSICAL_Q_2_1` varchar(255) DEFAULT NULL,
  `PHYSICAL_C_2_1` varchar(255) DEFAULT NULL,
  `PHYSICAL_Q_2_2` varchar(255) DEFAULT NULL,
  `PHYSICAL_C_2_2` varchar(255) DEFAULT NULL,
  `PHYSICAL_Q_2_3` varchar(255) DEFAULT NULL,
  `PHYSICAL_C_2_3` varchar(255) DEFAULT NULL,
  `PHYSICAL_Q_2_4` varchar(255) DEFAULT NULL,
  `PHYSICAL_C_2_4` varchar(255) DEFAULT NULL,
  `PHYSICAL_Q_2_5` varchar(255) DEFAULT NULL,
  `PHYSICAL_C_2_5` varchar(255) DEFAULT NULL,
  `PHYSICAL_SCORE` varchar(255) DEFAULT NULL,
  `PHYSICAL_Display` varchar(255) DEFAULT NULL,
  `PHYSICALPHOTO` varchar(255) DEFAULT NULL,
  `SAFETY_Q_3_1` varchar(255) DEFAULT NULL,
  `SAFETY_C_3_1` varchar(255) DEFAULT NULL,
  `SAFETY_Q_3_2` varchar(255) DEFAULT NULL,
  `SAFETY_C_3_2` varchar(255) DEFAULT NULL,
  `SAFETY_Q_3_3` varchar(255) DEFAULT NULL,
  `SAFETY_C_3_3` varchar(255) DEFAULT NULL,
  `SAFETY_Q_3_4` varchar(255) DEFAULT NULL,
  `SAFETY_C_3_4` varchar(255) DEFAULT NULL,
  `SAFETY_Q_3_5` varchar(255) DEFAULT NULL,
  `SAFETY_C_3_5` varchar(255) DEFAULT NULL,
  `SAFETY_Q_3_6` varchar(255) DEFAULT NULL,
  `SAFETY_C_3_6` varchar(255) DEFAULT NULL,
  `SAFETY_Q_3_7` varchar(255) DEFAULT NULL,
  `SAFETY_C_3_7` varchar(255) DEFAULT NULL,
  `SAFETY_Q_3_8` varchar(255) DEFAULT NULL,
  `SAFETY_C_3_8` varchar(255) DEFAULT NULL,
  `SAFETY_Q_3_9` varchar(255) DEFAULT NULL,
  `SAFETY_C_3_9` varchar(255) DEFAULT NULL,
  `SAFETY_Q_3_10` varchar(255) DEFAULT NULL,
  `SAFETY_C_3_10` varchar(255) DEFAULT NULL,
  `SAFETY_Q_3_11` varchar(255) DEFAULT NULL,
  `SAFETY_C_3_11` varchar(255) DEFAULT NULL,
  `SAFETY_SCORE` varchar(255) DEFAULT NULL,
  `SAFETY_DISPLAY` varchar(255) DEFAULT NULL,
  `SAFETYPHOTO` varchar(255) DEFAULT NULL,
  `PRE_Q_4_1` varchar(255) DEFAULT NULL,
  `PRE_C_4_1` varchar(255) DEFAULT NULL,
  `PRE_Q_4_2` varchar(255) DEFAULT NULL,
  `PRE_C_4_2` varchar(255) DEFAULT NULL,
  `PRE_Q_4_3` varchar(255) DEFAULT NULL,
  `PRE_C_4_3` varchar(255) DEFAULT NULL,
  `PRE_Q_4_4` varchar(255) DEFAULT NULL,
  `PRE_C_4_4` varchar(255) DEFAULT NULL,
  `PRE_Q_4_5` varchar(255) DEFAULT NULL,
  `PRE_C_4_5` varchar(255) DEFAULT NULL,
  `PRE_Q_4_6` varchar(255) DEFAULT NULL,
  `PRE_C_4_6` varchar(255) DEFAULT NULL,
  `PRE_Q_4_7` varchar(255) DEFAULT NULL,
  `PRE_C_4_7` varchar(255) DEFAULT NULL,
  `PRE_Q_4_8` varchar(255) DEFAULT NULL,
  `PRE_C_4_8` varchar(255) DEFAULT NULL,
  `PRE_Q_4_9` varchar(255) DEFAULT NULL,
  `PRE_C_4_9` varchar(255) DEFAULT NULL,
  `PRE_Q_4_10` varchar(255) DEFAULT NULL,
  `PRE_C_4_10` varchar(255) DEFAULT NULL,
  `PRE_Q_4_11` varchar(255) DEFAULT NULL,
  `PRE_C_4_11` varchar(255) DEFAULT NULL,
  `PRE_Q_4_12` varchar(255) DEFAULT NULL,
  `PRE_C_4_12` varchar(255) DEFAULT NULL,
  `PRETEST_SCORE` varchar(255) DEFAULT NULL,
  `PRETEST_Display` varchar(255) DEFAULT NULL,
  `PRETESTPHOTO` varchar(255) DEFAULT NULL,
  `TEST_Q_5_1` varchar(255) DEFAULT NULL,
  `TEST_C_5_1` varchar(255) DEFAULT NULL,
  `TEST_Q_5_2` varchar(255) DEFAULT NULL,
  `TEST_C_5_2` varchar(255) DEFAULT NULL,
  `TEST_Q_5_3` varchar(255) DEFAULT NULL,
  `TEST_C_5_3` varchar(255) DEFAULT NULL,
  `TEST_Q_5_4` varchar(255) DEFAULT NULL,
  `TEST_C_5_4` varchar(255) DEFAULT NULL,
  `TEST_Q_5_5` varchar(255) DEFAULT NULL,
  `TEST_C_5_5` varchar(255) DEFAULT NULL,
  `TEST_Q_5_6` varchar(255) DEFAULT NULL,
  `TEST_C_5_6` varchar(255) DEFAULT NULL,
  `TEST_Q_5_7` varchar(255) DEFAULT NULL,
  `TEST_C_5_7` varchar(255) DEFAULT NULL,
  `TEST_Q_5_8` varchar(255) DEFAULT NULL,
  `TEST_C_5_8` varchar(255) DEFAULT NULL,
  `TEST_Q_5_9` varchar(255) DEFAULT NULL,
  `TEST_C_5_9` varchar(255) DEFAULT NULL,
  `TEST_SCORE` varchar(255) DEFAULT NULL,
  `TEST_DISPLAY` varchar(255) DEFAULT NULL,
  `TESTPHOTO` varchar(255) DEFAULT NULL,
  `POST_Q_6_1` varchar(255) DEFAULT NULL,
  `POST_C_6_1` varchar(255) DEFAULT NULL,
  `POST_Q_6_2` varchar(255) DEFAULT NULL,
  `POST_C_6_2` varchar(255) DEFAULT NULL,
  `POST_Q_6_3` varchar(255) DEFAULT NULL,
  `POST_C_6_3` varchar(255) DEFAULT NULL,
  `POST_Q_6_4` varchar(255) DEFAULT NULL,
  `POST_C_6_4` varchar(255) DEFAULT NULL,
  `POST_Q_6_5` varchar(255) DEFAULT NULL,
  `POST_C_6_5` varchar(255) DEFAULT NULL,
  `POST_Q_6_6` varchar(255) DEFAULT NULL,
  `POST_C_6_6` varchar(255) DEFAULT NULL,
  `POST_Q_6_7` varchar(255) DEFAULT NULL,
  `POST_C_6_7` varchar(255) DEFAULT NULL,
  `POST_Q_6_8` varchar(255) DEFAULT NULL,
  `POST_C_6_8` varchar(255) DEFAULT NULL,
  `POST_Q_6_9` varchar(255) DEFAULT NULL,
  `POST_C_6_9` varchar(255) DEFAULT NULL,
  `POST_SCORE` varchar(255) DEFAULT NULL,
  `POST_DISPLAY` varchar(255) DEFAULT NULL,
  `POSTTESTPHOTO` varchar(255) DEFAULT NULL,
  `EQA_Q_7_1` varchar(255) DEFAULT NULL,
  `EQA_C_7_1` varchar(255) DEFAULT NULL,
  `EQA_Q_7_2` varchar(255) DEFAULT NULL,
  `EQA_C_7_2` varchar(255) DEFAULT NULL,
  `EQA_Q_7_3` varchar(255) DEFAULT NULL,
  `EQA_C_7_3` varchar(255) DEFAULT NULL,
  `EQA_Q_7_4` varchar(255) DEFAULT NULL,
  `EQA_C_7_4` varchar(255) DEFAULT NULL,
  `EQA_Q_7_5` varchar(255) DEFAULT NULL,
  `EQA_C_7_5` varchar(255) DEFAULT NULL,
  `EQA_Q_7_6` varchar(255) DEFAULT NULL,
  `EQA_C_7_6` varchar(255) DEFAULT NULL,
  `EQA_Q_7_7` varchar(255) DEFAULT NULL,
  `EQA_C_7_7` varchar(255) DEFAULT NULL,
  `EQA_Q_7_8` varchar(255) DEFAULT NULL,
  `EQA_C_7_8` varchar(255) DEFAULT NULL,
  `sampleretesting` varchar(255) DEFAULT NULL,
  `EQA_Q_7_9` varchar(255) DEFAULT NULL,
  `EQA_C_7_9` varchar(255) DEFAULT NULL,
  `EQA_Q_7_10` varchar(255) DEFAULT NULL,
  `EQA_C_7_10` varchar(255) DEFAULT NULL,
  `EQA_Q_7_11` varchar(255) DEFAULT NULL,
  `EQA_C_7_11` varchar(255) DEFAULT NULL,
  `EQA_Q_7_12` varchar(255) DEFAULT NULL,
  `EQA_C_7_12` varchar(255) DEFAULT NULL,
  `EQA_Q_7_13` varchar(255) DEFAULT NULL,
  `EQA_C_7_13` varchar(255) DEFAULT NULL,
  `EQA_Q_7_14` varchar(255) DEFAULT NULL,
  `EQA_C_7_14` varchar(255) DEFAULT NULL,
  `EQA_MAX_SCORE` varchar(255) DEFAULT NULL,
  `EQA_REQ` varchar(255) DEFAULT NULL,
  `EQA_OPT` varchar(255) DEFAULT NULL,
  `EQA_SCORE` varchar(255) DEFAULT NULL,
  `EQA_DISPLAY` varchar(255) DEFAULT NULL,
  `EQAPHOTO` varchar(255) DEFAULT NULL,
  `FINAL_AUDIT_SCORE` varchar(255) DEFAULT NULL,
  `MAX_AUDIT_SCORE` varchar(255) DEFAULT NULL,
  `AUDIT_SCORE_PERCANTAGE` varchar(255) DEFAULT NULL,
  `staffaudited` varchar(255) DEFAULT NULL,
  `durationaudit` varchar(255) DEFAULT NULL,
  `personincharge` varchar(255) DEFAULT NULL,
  `endofsurvey` varchar(255) DEFAULT NULL,
  `info5` varchar(255) DEFAULT NULL,
  `info6` varchar(255) DEFAULT NULL,
  `info10` varchar(255) DEFAULT NULL,
  `info11` varchar(255) DEFAULT NULL,
  `summarypage` varchar(255) DEFAULT NULL,
  `SUMMARY_NOT_AVL` varchar(255) DEFAULT NULL,
  `info12` varchar(255) DEFAULT NULL,
  `info17` varchar(255) DEFAULT NULL,
  `info21` varchar(255) DEFAULT NULL,
  `info22` varchar(255) DEFAULT NULL,
  `info23` varchar(255) DEFAULT NULL,
  `info24` varchar(255) DEFAULT NULL,
  `info25` varchar(255) DEFAULT NULL,
  `info26` varchar(255) DEFAULT NULL,
  `info27` varchar(255) DEFAULT NULL,
  `correctiveaction` text,
  `sitephoto` varchar(255) DEFAULT NULL,
  `Latitude` varchar(255) DEFAULT NULL,
  `Longitude` varchar(255) DEFAULT NULL,
  `Altitude` varchar(255) DEFAULT NULL,
  `Accuracy` varchar(255) DEFAULT NULL,
  `auditorSignature` text,
  `instanceID` varchar(255) DEFAULT NULL,
  `instanceName` varchar(255) DEFAULT NULL,
  `status` varchar(100) DEFAULT 'pending'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE `spi_form_v_3_duplicate`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `spi_form_v_3_duplicate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
  

--Pal 06-Apr-2017
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV3', 'download-files', 'Download Zipped Files');

ALTER TABLE `r_spi_form_v_3_download` DROP `file_name`;

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV3', 'view-data', 'Dashboard - View Details');

--saravnana 24-may-2017
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\Facility', 'export-facility', 'Export Facilities');
--Pal 06-Jun-2017
ALTER TABLE `spi_form_v_3` ADD `facility` INT(11) NULL DEFAULT NULL AFTER `auditroundno`;
--saravanna 21-jun-2017
INSERT INTO `odkdash`.`privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV3', 'spiv3-import-csv-file', 'SPIV3 Import CSV');

--Pal 27-Jun-2017
ALTER TABLE `spi_form_v_3_duplicate` DROP `facilityname`;

ALTER TABLE `spi_form_v_3` DROP `facilityname`;

ALTER TABLE `spi_form_v_3` DROP `facilityid`;

ALTER TABLE `spi_form_v_3_duplicate` CHANGE `facilityid` `facility` INT(11) NULL DEFAULT NULL;

ALTER TABLE `spi_form_v_3` ADD `facilityid` INT(11) NULL DEFAULT NULL AFTER `facility`, ADD `facilityname` VARCHAR(500) NULL DEFAULT NULL AFTER `facilityid`;

ALTER TABLE `spi_form_v_3_duplicate` ADD `facilityid` INT(11) NULL DEFAULT NULL AFTER `facility`, ADD `facilityname` VARCHAR(500) NULL DEFAULT NULL AFTER `facilityid`;

-- Amit 06 Feb 2018

UPDATE spi_form_v_3 t1, spi_rt_3_facilities t2 SET t1.facility = t2.id, t1.facilityid=t2.facility_id where t1.facilityname = t2.facility_name;

--Selvam 22 September 2020

CREATE TABLE `spi_v5_form_labels` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `field` varchar(255) NOT NULL,
 `short_label` varchar(255) DEFAULT NULL,
 `label` text NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1

CREATE TABLE `spi_form_v_5` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `token` varchar(255) NOT NULL,
 `content` varchar(255) NOT NULL,
 `formId` varchar(255) NOT NULL,
 `formVersion` varchar(255) NOT NULL,
 `meta-instance-id` varchar(255) NOT NULL,
 `meta-model-version` varchar(255) NOT NULL,
 `meta-ui-version` varchar(255) NOT NULL,
 `meta-submission-date` varchar(255) NOT NULL,
 `meta-is-complete` varchar(255) NOT NULL,
 `meta-date-marked-as-complete` varchar(255) NOT NULL,
 `start` varchar(255) DEFAULT NULL,
 `end` varchar(255) DEFAULT NULL,
 `today` date DEFAULT NULL,
 `deviceid` varchar(255) DEFAULT NULL,
 `subscriberid` varchar(255) DEFAULT NULL,
 `text_image` varchar(255) DEFAULT NULL,
 `info1` varchar(255) DEFAULT NULL,
 `info2` varchar(255) DEFAULT NULL,
 `assesmentofaudit` date NOT NULL,
 `auditStartTime` varchar(255) DEFAULT NULL,
 `auditroundno` varchar(255) DEFAULT NULL,
 `facility` int(11) DEFAULT NULL,
 `facilityname` varchar(255) DEFAULT NULL,
 `facilityid` varchar(255) DEFAULT NULL,
 `testingpointtype` varchar(255) DEFAULT NULL,
 `testingpointtype_other` varchar(255) DEFAULT NULL,
 `physicaladdress` varchar(255) DEFAULT NULL,
 `level` varchar(255) DEFAULT NULL,
 `level_other` varchar(255) DEFAULT NULL,
 `affiliation` varchar(255) DEFAULT NULL,
 `affiliation_other` varchar(255) DEFAULT NULL,
 `NumberofTester` varchar(255) DEFAULT NULL,
 `client_tested_HIV` varchar(255) DEFAULT NULL,
 `client_tested_HIV_PM` varchar(255) DEFAULT NULL,
 `client_tested_HIV_PQ` varchar(255) DEFAULT NULL,
 `client_newly_HIV` varchar(255) DEFAULT NULL,
 `client_newly_HIV_PM` varchar(255) DEFAULT NULL,
 `client_newly_HIV_PQ` varchar(255) DEFAULT NULL,
 `client_negative_HIV` varchar(255) DEFAULT NULL,
 `client_negative_HIV_PM` varchar(255) DEFAULT NULL,
 `client_negative_HIV_PQ` varchar(255) DEFAULT NULL,
 `client_positive_HIV_RTRI` varchar(255) DEFAULT NULL,
 `client_positive_HIV_RTRI_PM` varchar(255) DEFAULT NULL,
 `client_positive_HIV_RTRI_PQ` varchar(255) DEFAULT NULL,
 `client_recent_RTRI` varchar(255) DEFAULT NULL,
 `client_recent_RTRI_PM` varchar(255) DEFAULT NULL,
 `client_recent_RTRI_PQ` varchar(255) DEFAULT NULL,
 `name_auditor_lead` varchar(255) DEFAULT NULL,
 `name_auditor2` varchar(255) DEFAULT NULL,
 `info4` varchar(255) DEFAULT NULL,
 `INSTANCE` varchar(255) DEFAULT NULL,
 `PERSONAL_Q_1_1_HIV_TRAINING` varchar(255) DEFAULT NULL,
 `PERSONAL_C_1_1_HIV_TRAINING` varchar(255) DEFAULT NULL,
 `PERSONAL_Q_1_2_HIV_TESTING_REGISTER` varchar(255) DEFAULT NULL,
 `PERSONAL_C_1_2_HIV_TESTING_REGISTER` varchar(255) DEFAULT NULL,
 `PERSONAL_Q_1_3_EQA_PT` varchar(255) DEFAULT NULL,
 `PERSONAL_C_1_3_EQA_PT` varchar(255) DEFAULT NULL,
 `PERSONAL_Q_1_4_QC_PROCESS` varchar(255) DEFAULT NULL,
 `PERSONAL_C_1_4_QC_PROCESS` varchar(255) DEFAULT NULL,
 `PERSONAL_Q_1_5_SAFETY_MANAGEMENT` varchar(255) DEFAULT NULL,
 `PERSONAL_C_1_5_SAFETY_MANAGEMENT` varchar(255) DEFAULT NULL,
 `PERSONAL_Q_1_6_REFRESHER_TRAINING` varchar(255) DEFAULT NULL,
 `PERSONAL_C_1_6_REFRESHER_TRAINING` varchar(255) DEFAULT NULL,
 `PERSONAL_Q_1_7_HIV_COMPETENCY_TESTING` varchar(255) DEFAULT NULL,
 `PERSONAL_C_1_7_HIV_COMPETENCY_TESTING` varchar(255) DEFAULT NULL,
 `PERSONAL_Q_1_8_NATIONAL_CERTIFICATION` varchar(255) DEFAULT NULL,
 `PERSONAL_C_1_8_NATIONAL_CERTIFICATION` varchar(255) DEFAULT NULL,
 `PERSONAL_Q_1_9_CERTIFIED_TESTERS` varchar(255) DEFAULT NULL,
 `PERSONAL_C_1_9_CERTIFIED_TESTERS` varchar(255) DEFAULT NULL,
 `PERSONAL_Q_1_10_RECERTIFIED` varchar(255) DEFAULT NULL,
 `PERSONAL_C_1_10_RECERTIFIED` varchar(255) DEFAULT NULL,
 `PERSONAL_SCORE` varchar(255) DEFAULT NULL,
 `PERSONAL_Display` varchar(255) DEFAULT NULL,
 `PERSONALPHOTO` text,
 `PHYSICAL_Q_2_1_DESIGNATED_HIV_AREA` varchar(255) DEFAULT NULL,
 `PHYSICAL_C_2_1_DESIGNATED_HIV_AREA` varchar(255) DEFAULT NULL,
 `PHYSICAL_Q_2_2_CLEAN_TESTING_AREA` varchar(255) DEFAULT NULL,
 `PHYSICAL_C_2_2_CLEAN_TESTING_AREA` varchar(255) DEFAULT NULL,
 `PHYSICAL_Q_2_3_SUFFICIENT_LIGHT_AVAILABILITY` varchar(255) DEFAULT NULL,
 `PHYSICAL_C_2_3_SUFFICIENT_LIGHT_AVAILABILITY` varchar(255) DEFAULT NULL,
 `PHYSICAL_Q_2_4_TEST_KIT_STORAGE` varchar(255) DEFAULT NULL,
 `PHYSICAL_C_2_4_TEST_KIT_STORAGE` varchar(255) DEFAULT NULL,
 `PHYSICAL_Q_2_5_SUFFICIENT_SECURE_STORAGE` varchar(255) DEFAULT NULL,
 `PHYSICAL_C_2_5_SUFFICIENT_SECURE_STORAGE` varchar(255) DEFAULT NULL,
 `PHYSICAL_SCORE` varchar(255) DEFAULT NULL,
 `PHYSICAL_Display` varchar(255) DEFAULT NULL,
 `PHYSICALPHOTO` text,
 `SAFETY_Q_3_1_IMPLEMENT_SAFETY_PRACTICES` varchar(255) DEFAULT NULL,
 `SAFETY_C_3_1_IMPLEMENT_SAFETY_PRACTICES` varchar(255) DEFAULT NULL,
 `SAFETY_Q_3_2_ACCIDENTAL_EXPOSURE` varchar(255) DEFAULT NULL,
 `SAFETY_C_3_2_ACCIDENTAL_EXPOSURE` varchar(255) DEFAULT NULL,
 `SAFETY_Q_3_3_PRACTICE_SAFETY_PRACTICES` varchar(255) DEFAULT NULL,
 `SAFETY_C_3_3_PRACTICE_SAFETY_PRACTICES` varchar(255) DEFAULT NULL,
 `SAFETY_Q_3_4_PPE_AVAILABILITY` varchar(255) DEFAULT NULL,
 `SAFETY_C_3_4_PPE_AVAILABILITY` varchar(255) DEFAULT NULL,
 `SAFETY_Q_3_5_PPE_USED_PROPERLY` varchar(255) DEFAULT NULL,
 `SAFETY_C_3_5_PPE_USED_PROPERLY` varchar(255) DEFAULT NULL,
 `SAFETY_Q_3_6_WATER_SOAP_AVAILABILITY` varchar(255) DEFAULT NULL,
 `SAFETY_C_3_6_WATER_SOAP_AVAILABILITY` varchar(255) DEFAULT NULL,
 `SAFETY_Q_3_7_DISINFECTANT_AVAILABLE` varchar(255) DEFAULT NULL,
 `SAFETY_C_3_7_DISINFECTANT_AVAILABLE` varchar(255) DEFAULT NULL,
 `SAFETY_Q_3_8_DISINFECTANT_LABELED_PROPERLY` varchar(255) DEFAULT NULL,
 `SAFETY_C_3_8_DISINFECTANT_LABELED_PROPERLY` varchar(255) DEFAULT NULL,
 `SAFETY_Q_3_9_SEGREGATION_OF_WASTE` varchar(255) DEFAULT NULL,
 `SAFETY_C_3_9_SEGREGATION_OF_WASTE` varchar(255) DEFAULT NULL,
 `SAFETY_Q_3_10_INFECTIOUS_WASTE_EMPTIED` varchar(255) DEFAULT NULL,
 `SAFETY_C_3_10_INFECTIOUS_WASTE_EMPTIED` varchar(255) DEFAULT NULL,
 `SAFETY_SCORE` varchar(255) DEFAULT NULL,
 `SAFETY_DISPLAY` varchar(255) DEFAULT NULL,
 `SAFETYPHOTO` text,
 `PRE_Q_4_1_NATIONAL_GUIDELINES` varchar(255) DEFAULT NULL,
 `PRE_C_4_1_NATIONAL_GUIDELINES` varchar(255) DEFAULT NULL,
 `PRE_Q_4_2_HIV_TESTING_ALGORITHM` varchar(255) DEFAULT NULL,
 `PRE_C_4_2_HIV_TESTING_ALGORITHM` varchar(255) DEFAULT NULL,
 `PRE_Q_4_3_TEST_PROCEDURES_ACCESSIBLE` varchar(255) DEFAULT NULL,
 `PRE_C_4_3_TEST_PROCEDURES_ACCESSIBLE` varchar(255) DEFAULT NULL,
 `PRE_Q_4_4_TEST_PROCEDURES_ACCURATE` varchar(255) DEFAULT NULL,
 `PRE_C_4_4_TEST_PROCEDURES_ACCURATE` varchar(255) DEFAULT NULL,
 `PRE_Q_4_5_APPROVED_KITS_AVAILABLE` varchar(255) DEFAULT NULL,
 `PRE_C_4_5_APPROVED_KITS_AVAILABLE` varchar(255) DEFAULT NULL,
 `PRE_Q_4_6_HIV_KITS_EXPIRATION` varchar(255) DEFAULT NULL,
 `PRE_C_4_6_HIV_KITS_EXPIRATION` varchar(255) DEFAULT NULL,
 `PRE_Q_4_7_KIT_SUPPLIES_AVAILABILITY` varchar(255) DEFAULT NULL,
 `PRE_C_4_7_KIT_SUPPLIES_AVAILABILITY` varchar(255) DEFAULT NULL,
 `PRE_Q_4_8_STOCK_MANAGEMENT` varchar(255) DEFAULT NULL,
 `PRE_C_4_8_STOCK_MANAGEMENT` varchar(255) DEFAULT NULL,
 `PRE_Q_4_9_DOCUMENTED_INVENTORY` varchar(255) DEFAULT NULL,
 `PRE_C_4_9_DOCUMENTED_INVENTORY` varchar(255) DEFAULT NULL,
 `PRE_Q_4_10_SOPS_BLOOD_COLLECTION` varchar(255) DEFAULT NULL,
 `PRE_C_4_10_SOPS_BLOOD_COLLECTION` varchar(255) DEFAULT NULL,
 `PRE_Q_4_11_BLOOD_COLLECTION_SUPPLIES` varchar(255) DEFAULT NULL,
 `PRE_C_4_11_BLOOD_COLLECTION_SUPPLIES` varchar(255) DEFAULT NULL,
 `PRE_Q_4_12_CLIENT_IDENTIFICATION` varchar(255) DEFAULT NULL,
 `PRE_C_4_12_CLIENT_IDENTIFICATION` varchar(255) DEFAULT NULL,
 `PRE_Q_4_13_CLIENT_ID_RECORDED` varchar(255) DEFAULT NULL,
 `PRE_C_4_13_CLIENT_ID_RECORDED` varchar(255) DEFAULT NULL,
 `PRETEST_SCORE` varchar(255) DEFAULT NULL,
 `PRETEST_Display` varchar(255) DEFAULT NULL,
 `PRETESTPHOTO` text,
 `TEST_Q_5_1_PROCEDURES_TESTING_ALGORITHM` varchar(255) DEFAULT NULL,
 `TEST_C_5_1_PROCEDURES_TESTING_ALGORITHM` varchar(255) DEFAULT NULL,
 `TEST_Q_5_2_TIMERS_AVAILABILITY` varchar(255) DEFAULT NULL,
 `TEST_C_5_2_TIMERS_AVAILABILITY` varchar(255) DEFAULT NULL,
 `TEST_Q_5_3_SAMPLE_DEVICE_ACCURACY` varchar(255) DEFAULT NULL,
 `TEST_C_5_3_SAMPLE_DEVICE_ACCURACY` varchar(255) DEFAULT NULL,
 `TEST_Q_5_4_TESTING_PROCEDURE_FOLLOWED` varchar(255) DEFAULT NULL,
 `TEST_C_5_4_TESTING_PROCEDURE_FOLLOWED` varchar(255) DEFAULT NULL,
 `TEST_Q_5_5_QUALITY_CONTROL` varchar(255) DEFAULT NULL,
 `TEST_C_5_5_QUALITY_CONTROL` varchar(255) DEFAULT NULL,
 `TEST_Q_5_6_QC_RESULTS_RECORDED` varchar(255) DEFAULT NULL,
 `TEST_C_5_6_QC_RESULTS_RECORDED` varchar(255) DEFAULT NULL,
 `TEST_Q_5_7_INCORRECT_QC_RESULTS` varchar(255) DEFAULT NULL,
 `TEST_C_5_7_INCORRECT_QC_RESULTS` varchar(255) DEFAULT NULL,
 `TEST_Q_5_8_APPROPRIATE_STEPS_TAKEN` varchar(255) DEFAULT NULL,
 `TEST_C_5_8_APPROPRIATE_STEPS_TAKEN` varchar(255) DEFAULT NULL,
 `TEST_Q_5_9_REVIEW_QC_RECORDS` varchar(255) DEFAULT NULL,
 `TEST_C_5_9_REVIEW_QC_RECORDS` varchar(255) DEFAULT NULL,
 `TEST_SCORE` varchar(255) DEFAULT NULL,
 `TEST_DISPLAY` varchar(255) DEFAULT NULL,
 `TESTPHOTO` text,
 `POST_Q_6_1_STANDARDIZED_HIV_REGISTER` varchar(255) DEFAULT NULL,
 `POST_C_6_1_STANDARDIZED_HIV_REGISTER` varchar(255) DEFAULT NULL,
 `POST_Q_6_2_ELEMENTS_CAPTURED_CORRECTLY` varchar(255) DEFAULT NULL,
 `POST_C_6_2_ELEMENTS_CAPTURED_CORRECTLY` varchar(255) DEFAULT NULL,
 `POST_Q_6_3_PAGE_TOTAL_SUMMARY` varchar(255) DEFAULT NULL,
 `POST_C_6_3_PAGE_TOTAL_SUMMARY` varchar(255) DEFAULT NULL,
 `POST_Q_6_4_INVALID_TEST_RESULT_RECORDED` varchar(255) DEFAULT NULL,
 `POST_C_6_4_INVALID_TEST_RESULT_RECORDED` varchar(255) DEFAULT NULL,
 `POST_Q_6_5_APPROPRIATE_STEPS_TAKEN` varchar(255) DEFAULT NULL,
 `POST_C_6_5_APPROPRIATE_STEPS_TAKEN` varchar(255) DEFAULT NULL,
 `POST_Q_6_6_REGISTERS_REVIEWED` varchar(255) DEFAULT NULL,
 `POST_C_6_6_REGISTERS_REVIEWED` varchar(255) DEFAULT NULL,
 `POST_Q_6_7_DOCUMENTS_SECURELY_KEPT` varchar(255) DEFAULT NULL,
 `POST_C_6_7_DOCUMENTS_SECURELY_KEPT` varchar(255) DEFAULT NULL,
 `POST_Q_6_8_REGISTER_SECURE_LOCATION` varchar(255) DEFAULT NULL,
 `POST_C_6_8_REGISTER_SECURE_LOCATION` varchar(255) DEFAULT NULL,
 `POST_Q_6_9_REGISTERS_PROPERLY_LABELED` varchar(255) DEFAULT NULL,
 `POST_C_6_9_REGISTERS_PROPERLY_LABELED` varchar(255) DEFAULT NULL,
 `POST_SCORE` varchar(255) DEFAULT NULL,
 `POST_DISPLAY` varchar(255) DEFAULT NULL,
 `POSTTESTPHOTO` text,
 `EQA_Q_7_1_PT_ENROLLMENT` varchar(255) DEFAULT NULL,
 `EQA_C_7_1_PT_ENROLLMENT` varchar(255) DEFAULT NULL,
 `EQA_Q_7_2_TESTING_EQAPT_SAMPLES` varchar(255) DEFAULT NULL,
 `EQA_C_7_2_TESTING_EQAPT_SAMPLES` varchar(255) DEFAULT NULL,
 `EQA_Q_7_3_REVIEW_BEFORE_SUBMISSION` varchar(255) DEFAULT NULL,
 `EQA_C_7_3_REVIEW_BEFORE_SUBMISSION` varchar(255) DEFAULT NULL,
 `EQA_Q_7_4_FEEDBACK_RECEIVED_REVIEWED` varchar(255) DEFAULT NULL,
 `EQA_C_7_4_FEEDBACK_RECEIVED_REVIEWED` varchar(255) DEFAULT NULL,
 `EQA_Q_7_5_IMPLEMENT_CORRECTIVE_ACTION` varchar(255) DEFAULT NULL,
 `EQA_C_7_5_IMPLEMENT_CORRECTIVE_ACTION` varchar(255) DEFAULT NULL,
 `EQA_Q_7_6_RECEIVE_PERIODIC_VISITS` varchar(255) DEFAULT NULL,
 `EQA_C_7_6_RECEIVE_PERIODIC_VISITS` varchar(255) DEFAULT NULL,
 `EQA_Q_7_7_FEEDBACK_PROVIDED_DOCUMENTED` varchar(255) DEFAULT NULL,
 `EQA_C_7_7_FEEDBACK_PROVIDED_DOCUMENTED` varchar(255) DEFAULT NULL,
 `EQA_Q_7_8_TESTERS_RETRAINED_IN_VISITS` varchar(255) DEFAULT NULL,
 `EQA_C_7_8_TESTERS_RETRAINED_IN_VISITS` varchar(255) DEFAULT NULL,
 `performrtritesting` varchar(255) DEFAULT NULL,
 `EQA_SCORE` varchar(255) DEFAULT NULL,
 `EQA_DISPLAY` varchar(255) DEFAULT NULL,
 `EQAPHOTO` text,
 `RTRI_Q_8_1_TESTERS_RECEIVED_RTRI_TRAINING` varchar(255) DEFAULT NULL,
 `RTRI_C_8_1_TESTERS_RECEIVED_RTRI_TRAINING` varchar(255) DEFAULT NULL,
 `RTRI_Q_8_2_TESTERS_DEMONSTRATED_COMPETENCY` varchar(255) DEFAULT NULL,
 `RTRI_C_8_2_TESTERS_DEMONSTRATED_COMPETENCY` varchar(255) DEFAULT NULL,
 `RTRI_Q_8_3_JOBAIDS_READILY_AVAILABLE` varchar(255) DEFAULT NULL,
 `RTRI_C_8_3_JOBAIDS_READILY_AVAILABLE` varchar(255) DEFAULT NULL,
 `RTRI_Q_8_4_SUFFICIENT_SUPPLY_AVAILABLE` varchar(255) DEFAULT NULL,
 `RTRI_C_8_4_SUFFICIENT_SUPPLY_AVAILABLE` varchar(255) DEFAULT NULL,
 `RTRI_Q_8_5_RTRI_KIT_STORAGE` varchar(255) DEFAULT NULL,
 `RTRI_C_8_5_RTRI_KIT_STORAGE` varchar(255) DEFAULT NULL,
 `RTRI_Q_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED` varchar(255) DEFAULT NULL,
 `RTRI_C_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED` varchar(255) DEFAULT NULL,
 `RTRI_Q_8_7_RTRI_TESTING_RESULTS_DOCUMENTED` varchar(255) DEFAULT NULL,
 `RTRI_C_8_7_RTRI_TESTING_RESULTS_DOCUMENTED` varchar(255) DEFAULT NULL,
 `RTRI_Q_8_8_QC_ROUTINELY_USED` varchar(255) DEFAULT NULL,
 `RTRI_C_8_8_QC_ROUTINELY_USED` varchar(255) DEFAULT NULL,
 `RTRI_Q_8_9_QC_RESULTS_RECORDED` varchar(255) DEFAULT NULL,
 `RTRI_C_8_9_QC_RESULTS_RECORDED` varchar(255) DEFAULT NULL,
 `RTRI_Q_8_10_INCORRECT_QC_DOCUMENTED` varchar(255) DEFAULT NULL,
 `RTRI_C_8_10_INCORRECT_QC_DOCUMENTED` varchar(255) DEFAULT NULL,
 `RTRI_Q_8_11_INVALID_RTRI_RESULTS` varchar(255) DEFAULT NULL,
 `RTRI_C_8_11_INVALID_RTRI_RESULTS` varchar(255) DEFAULT NULL,
 `RTRI_SCORE` varchar(255) DEFAULT NULL,
 `RTRI_DISPLAY` varchar(255) DEFAULT NULL,
 `RTRIPHOTO` varchar(255) DEFAULT NULL,
 `AuditRequiredScore` varchar(255) DEFAULT NULL,
 `FINAL_AUDIT_SCORE` varchar(255) DEFAULT NULL,
 `MAX_AUDIT_SCORE` varchar(255) DEFAULT NULL,
 `AUDIT_SCORE_PERCENTAGE` varchar(255) DEFAULT NULL,
 `AUDIT_SCORE_PERCANTAGE_ROUNDED` varchar(255) DEFAULT NULL,
 `staffaudited` varchar(255) DEFAULT NULL,
 `durationaudit` varchar(255) DEFAULT NULL,
 `personincharge` varchar(255) DEFAULT NULL,
 `sitecode` varchar(255) DEFAULT NULL,
 `auditEndTime` varchar(255) DEFAULT NULL,
 `endofsurvey` varchar(255) DEFAULT NULL,
 `info5` varchar(255) DEFAULT NULL,
 `info6` varchar(255) DEFAULT NULL,
 `info10` varchar(255) DEFAULT NULL,
 `info11` varchar(255) DEFAULT NULL,
 `summarypage` varchar(255) DEFAULT NULL,
 `SUMMARY_NOT_AVL` varchar(255) DEFAULT NULL,
 `info12` varchar(255) DEFAULT NULL,
 `info177` varchar(255) DEFAULT NULL,
 `info178` varchar(255) DEFAULT NULL,
 `info179` varchar(255) DEFAULT NULL,
 `info180` varchar(255) DEFAULT NULL,
 `info181` varchar(255) DEFAULT NULL,
 `info182` varchar(255) DEFAULT NULL,
 `info183` varchar(255) DEFAULT NULL,
 `info17a` varchar(255) DEFAULT NULL,
 `info21` varchar(255) DEFAULT NULL,
 `info22` varchar(255) DEFAULT NULL,
 `info23` varchar(255) DEFAULT NULL,
 `info24` varchar(255) DEFAULT NULL,
 `info25` varchar(255) DEFAULT NULL,
 `info26` varchar(255) DEFAULT NULL,
 `info27` varchar(255) DEFAULT NULL,
 `correctiveaction` text,
 `sitephoto` text,
 `Latitude` varchar(255) DEFAULT NULL,
 `Longitude` varchar(255) DEFAULT NULL,
 `Altitude` varchar(255) DEFAULT NULL,
 `Accuracy` varchar(255) DEFAULT NULL,
 `auditorSignature` text,
 `instanceID` varchar(255) DEFAULT NULL,
 `instanceName` varchar(255) DEFAULT NULL,
 `status` varchar(100) DEFAULT 'pending',
 PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC


-- Selvam 29 September 2020
INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Application\\Controller\\SpiV5', 'Manage SPI V5 Form');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES

('Application\\Controller\\SpiV5', 'approve-status', 'Approved Status'),
('Application\\Controller\\SpiV5', 'download-pdf', 'Download pdf'),
('Application\\Controller\\SpiV5', 'edit', 'Edit'),
('Application\\Controller\\SpiV5', 'index', 'Access'),
('Application\\Controller\\SpiV5', 'manage-facility', 'Access to edit SPI Form');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV5', 'corrective-action-pdf', 'Corrective Action PDF');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV5', 'delete', 'Delete');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV5', 'download-files', 'Download Zipped Files')

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV5', 'export', 'Export All PDF');

CREATE TABLE `r_spi_form_v_5_download` (
 `r_download_id` int(11) NOT NULL AUTO_INCREMENT,
 `user` int(11) NOT NULL,
 `auditroundno` varchar(255) DEFAULT NULL,
 `assesmentofaudit` varchar(255) DEFAULT NULL,
 `testingpointtype` varchar(255) DEFAULT NULL,
 `level` varchar(255) DEFAULT NULL,
 `affiliation` varchar(255) DEFAULT NULL,
 `level_name` varchar(255) DEFAULT NULL,
 `AUDIT_SCORE_PERCENTAGE` varchar(255) DEFAULT NULL,
 `download_status` int(11) NOT NULL DEFAULT '0',
 PRIMARY KEY (`r_download_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1

--Sudarmathi 05 Oct 2020
INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Application\\Controller\\DashboardV5', 'Manage DashboardV5');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\DashboardV5', 'index', 'Access');


-- Prasath M 14-Pct-2020
INSERT INTO `global_config` (`config_id`, `display_name`, `global_name`, `global_value`) VALUES (NULL, 'Version', 'web_version', 'v3');

--Selvam 15 Oct 2020
INSERT INTO `resources` (`resource_id`, `display_name`) VALUES
('Application\\Controller\\SpiV5Reports', 'SPI V5 Reports');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`)
 VALUES ('Application\\Controller\\SpiV5Reports', 'facility-report', 'Facility Report');

--Selvam 31 Oct 2020
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\Email', 'email-v5', 'Access V5');
