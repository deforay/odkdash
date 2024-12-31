

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

-- saravanna 03-may-2016
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

-- saravanan 05-apr-2017
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;


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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Sudarmathi 05 Oct 2020
INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Application\\Controller\\DashboardV5', 'Manage DashboardV5');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\DashboardV5', 'index', 'Access');


-- Prasath M 14-Pct-2020
INSERT INTO `global_config` (`config_id`, `display_name`, `global_name`, `global_value`) VALUES (NULL, 'Version', 'web_version', 'v3');

-- Selvam 15 Oct 2020
INSERT INTO `resources` (`resource_id`, `display_name`) VALUES
('Application\\Controller\\SpiV5Reports', 'SPI V5 Reports');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`)
 VALUES ('Application\\Controller\\SpiV5Reports', 'facility-report', 'Facility Report');

-- Selvam 31 Oct 2020
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\Email', 'email-v5', 'Access V5');

-- Sudarmathi 17 Dec 2020

INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Application\\Controller\\DashboardV6', 'Manage Dashboard V6');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\DashboardV6', 'index', 'Access');

CREATE TABLE `spi_form_v_6` (
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
  `auditEndTime` varchar(255) DEFAULT NULL,
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
  `status` varchar(100) DEFAULT 'pending'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

ALTER TABLE `spi_form_v_6`
  ADD PRIMARY KEY (`id`);

CREATE TABLE `spi_v6_form_labels` (
  `id` int(11) NOT NULL,
  `field` varchar(255) NOT NULL,
  `short_label` varchar(255) DEFAULT NULL,
  `label` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `spi_v6_form_labels`
  ADD PRIMARY KEY (`id`);

INSERT INTO `spi_v6_form_labels` (`id`, `field`, `short_label`, `label`) VALUES
(1, 'start', '', ''),
(2, 'end', '', ''),
(3, 'today', '', ''),
(4, 'deviceid', '', ''),
(5, 'subscriberid', '', 'Label'),
(6, 'text_image', '', 'SPI-RRT Checklist Ver 5.0'),
(7, 'info1', '', 'Stepwise Process for Improving the Quality of HIV Rapid and Recency Testing (SPI-RRT) Checklist'),
(8, 'TESTSITE', '', 'Testing Site Information'),
(9, 'FACILITY', '', 'Facility Information'),
(10, 'info2', '', 'PART A: CHARACTERISTICS OF THE FACILITY OR TESTING POINT AUDITED'),
(13, 'assesmentofaudit', '', 'Date of Audit:'),
(14, 'auditStartTime', '', 'Audit Start Time:'),
(15, 'auditroundno', '', 'Audit Round No.:'),
(16, 'facilityname', '', 'Testing Facility Name:'),
(17, 'facilityid', '', 'Testing Facility ID (if applicable)'),
(18, 'physicaladdress', '', 'Physical Address'),
(19, 'testingpointtype', '', 'Type of testing point'),
(20, 'level', '', 'Level:'),
(21, 'affiliation', '', 'Affiliation :'),
(22, 'NumberofTester', '', 'Number of Testers:'),
(23, 'client_tested_HIV', '', 'Number of clients tested for HIV:'),
(24, 'client_tested_HIV_PM', '', 'Past Month:  '),
(25, 'client_tested_HIV_PQ', '', 'Past Quarter:  '),
(27, 'client_newly_HIV', '', 'Number of newly identified HIV positives:'),
(28, 'client_newly_HIV_PM', '', 'Past Month:  '),
(29, 'client_newly_HIV_PQ', '', 'Past Quarter:  '),
(31, 'client_negative_HIV', '', 'Number of HIV negatives:'),
(32, 'client_negative_HIV_PM', '', 'Past Month:  '),
(33, 'client_negative_HIV_PQ', '', 'Past Quarter:  '),
(35, 'client_positive_HIV_RTRI', '', 'Number of newly identified HIV positives tested by RTRI :'),
(36, 'client_positive_HIV_RTRI_PM', '', 'Past Month:  '),
(37, 'client_positive_HIV_RTRI_PQ', '', 'Past Quarter:  '),
(39, 'client_recent_RTRI', '', 'Number of  Recent by RTRI or RITA:'),
(40, 'client_recent_RTRI_PM', '', 'Past Month:  '),
(41, 'client_recent_RTRI_PQ', '', 'Past Quarter:  '),
(43, 'name_auditor_lead', '', 'Name of the Auditor 1:'),
(44, 'nameOfAuditor2', '', 'Name of the Auditor 2:'),
(45, 'INSTANCE', '', ''),
(47, 'SPIRRT', '', 'SPI -RRT - Checklist'),
(48, 'info4', '', 'PART B. SPI - RRT Checklist'),
(50, 'PERSONAL', '', '1.0 PERSONNEL TRAINING AND CERTIFICATION   - (Max Score 10)'),
(52, 'PERSONAL_Q_1_1_HIV_TRAINING', '', '1.1 Have all testers received a comprehensive training on HIV rapid testing using the nationally approved curriculum?'),
(53, 'PERSONAL_C_1_1_HIV_TRAINING', '', 'Comments :'),
(56, 'PERSONAL_Q_1_2_HIV_TESTING_REGISTER', '', '1.2 Are the testers trained on the use of standardized HIV testing registers/logbooks?'),
(57, 'PERSONAL_C_1_2_HIV_TESTING_REGISTER', '', 'Comments :'),
(60, 'PERSONAL_Q_1_3_EQA_PT', '', '1.3 Are the testers trained on external quality assessment (EQA) or proficiency testing (PT) process?'),
(61, 'PERSONAL_C_1_3_EQA_PT', '', 'Comments :'),
(64, 'PERSONAL_Q_1_4_QC_PROCESS', '', '1.4 Are the testers trained on quality control (QC) process?'),
(65, 'PERSONAL_C_1_4_QC_PROCESS', '', 'Comments :'),
(68, 'PERSONAL_Q_1_5_SAFETY_MANAGEMENT', '', '1.5 Are the testers trained on safety and waste management procedures and practices?'),
(69, 'PERSONAL_C_1_5_SAFETY_MANAGEMENT', '', 'Comments :'),
(72, 'PERSONAL_Q_1_6_REFRESHER_TRAINING', '', '1.6 Have all testers received a refresher training within the last two years?'),
(73, 'PERSONAL_C_1_6_REFRESHER_TRAINING', '', 'Comments :'),
(76, 'PERSONAL_Q_1_7_HIV_COMPETENCY_TESTING', '', '1.7 Are there records indicating all testers have demonstrated competency in HIV rapid testing prior to client testing?'),
(77, 'PERSONAL_C_1_7_HIV_COMPETENCY_TESTING', '', 'Comments :'),
(80, 'PERSONAL_Q_1_8_NATIONAL_CERTIFICATION', '', '1.8 Have all testers been certified through a national certification program?     '),
(81, 'PERSONAL_C_1_8_NATIONAL_CERTIFICATION', '', 'Comments :'),
(84, 'PERSONAL_Q_1_9_CERTIFIED_TESTERS', '', '1.9 Are only certified testers performing HIV rapid testing at the site?'),
(85, 'PERSONAL_C_1_9_CERTIFIED_TESTERS', '', 'Comments :'),
(88, 'PERSONAL_Q_1_10_RECERTIFIED', '', '1.10 Are all testers re-certified periodically (e.g., every two years)?'),
(89, 'PERSONAL_C_1_10_RECERTIFIED', '', 'Comments :'),
(91, 'PERSONAL_SCORE', '', ''),
(92, 'PERSONAL_Display', '', '1.0 PERSONNEL TRAINING AND CERTIFICATION SCORE'),
(93, 'PERSONALPHOTO', '', 'Photo - Personal Training and Certification'),
(95, 'PHYSICAL', '', '2.0 PHYSICAL FACILITY - (Max Score 5)'),
(97, 'PHYSICAL_Q_2_1_DESIGNATED_HIV_AREA', '', '2.1 Is there a designated area for HIV testing?'),
(98, 'PHYSICAL_C_2_1_DESIGNATED_HIV_AREA', '', 'Comments :'),
(101, 'PHYSICAL_Q_2_2_CLEAN_TESTING_AREA', '', '2.2 Is the testing area clean and organized for HIV rapid testing?'),
(102, 'PHYSICAL_C_2_2_CLEAN_TESTING_AREA', '', 'Comments :'),
(105, 'PHYSICAL_Q_2_3_SUFFICIENT_LIGHT_AVAILABILITY', '', '2.3 Is sufficient lighting available in the designated testing area?'),
(106, 'PHYSICAL_C_2_3_SUFFICIENT_LIGHT_AVAILABILITY', '', 'Comments :'),
(109, 'PHYSICAL_Q_2_4_TEST_KIT_STORAGE', '', '2.4  Are the test kits stored according to manufacturers’ instructions?'),
(110, 'PHYSICAL_C_2_4_TEST_KIT_STORAGE', '', 'Comments :'),
(113, 'PHYSICAL_Q_2_5_SUFFICIENT_SECURE_STORAGE', '', '2.5 Is there sufficient and secure storage space for test kits and other consumables?'),
(114, 'PHYSICAL_C_2_5_SUFFICIENT_SECURE_STORAGE', '', 'Comments :'),
(116, 'PHYSICAL_SCORE', '', ''),
(117, 'PHYSICAL_Display', '', '2.0 PHYSICAL FACILITY SCORE'),
(118, 'PHYSICALPHOTO', '', 'Photo - Physical Facility'),
(120, 'SAFETY', '', '3.0 SAFETY - (Max Score - 10)'),
(122, 'SAFETY_Q_3_1_IMPLEMENT_SAFETY_PRACTICES', '', '3.1 Are there SOPs and/or job aides in place to implement safety practices?'),
(123, 'SAFETY_C_3_1_IMPLEMENT_SAFETY_PRACTICES', '', 'Comments :'),
(126, 'SAFETY_Q_3_2_ACCIDENTAL_EXPOSURE', '', '3.2 Are there SOPs and/or job aides in place to address accidental exposure to potentially infectious body fluids through a needle stick injury, splash or other sharps injury?'),
(127, 'SAFETY_C_3_2_ACCIDENTAL_EXPOSURE', '', 'Comments :'),
(130, 'SAFETY_Q_3_3_PRACTICE_SAFETY_PRACTICES', '', '3.3 Are testers and those visiting the testing area following the safety practices outlined in the SOPs and/or job aides?'),
(131, 'SAFETY_C_3_3_PRACTICE_SAFETY_PRACTICES', '', 'Comments :'),
(134, 'SAFETY_Q_3_4_PPE_AVAILABILITY', '', '3.4 Is personal protective equipment (PPE) always available to testers?'),
(135, 'SAFETY_C_3_4_PPE_AVAILABILITY', '', 'Comments :'),
(138, 'SAFETY_Q_3_5_PPE_USED_PROPERLY', '', '3.5 Is PPE properly used by all testers consistently throughout the testing process?'),
(139, 'SAFETY_C_3_5_PPE_USED_PROPERLY', '', 'Comments :'),
(142, 'SAFETY_Q_3_6_WATER_SOAP_AVAILABILITY', '', '3.6 Is there clean water and soap available for hand washing and is it consistently used?'),
(143, 'SAFETY_C_3_6_WATER_SOAP_AVAILABILITY', '', 'Comments :'),
(146, 'SAFETY_Q_3_7_DISINFECTANT_AVAILABLE', '', '3.7 Is there an appropriate disinfectant to clean the work area available?'),
(147, 'SAFETY_C_3_7_DISINFECTANT_AVAILABLE', '', 'Comments :'),
(150, 'SAFETY_Q_3_8_DISINFECTANT_LABELED_PROPERLY', '', '3.8 Is the disinfectant solution available properly labeled with content, date of preparation and date of expiration?'),
(151, 'SAFETY_C_3_8_DISINFECTANT_LABELED_PROPERLY', '', 'Comments :'),
(154, 'SAFETY_Q_3_9_SEGREGATION_OF_WASTE', '', '3.9 Are sharps, infectious and non-infectious waste disposed of according to the segregation instructions?'),
(155, 'SAFETY_C_3_9_SEGREGATION_OF_WASTE', '', 'Comments :'),
(158, 'SAFETY_Q_3_10_INFECTIOUS_WASTE_EMPTIED', '', '3.10 Are infectious and non-infectious waste containers emptied regularly per the SOP and/or job aides?'),
(159, 'SAFETY_C_3_10_INFECTIOUS_WASTE_EMPTIED', '', 'Comments :'),
(161, 'SAFETY_SCORE', '', ''),
(162, 'SAFETY_DISPLAY', '', '3.0 SAFETY SCORE'),
(163, 'SAFETYPHOTO', '', 'Photo - Safety'),
(165, 'PRETEST', '', '4.0 PRE-TESTING PHASE - (Max Score 13)'),
(167, 'PRE_Q_4_1_NATIONAL_GUIDELINES', '', '4.1 Are there national HIV testing guidelines available at the testing point?'),
(168, 'PRE_C_4_1_NATIONAL_GUIDELINES', '', 'Comments :'),
(171, 'PRE_Q_4_2_HIV_TESTING_ALGORITHM', '', '4.2 Is the national HIV testing algorithm(s) consistently being used at the testing site?'),
(172, 'PRE_C_4_2_HIV_TESTING_ALGORITHM', '', 'Comments :'),
(175, 'PRE_Q_4_3_TEST_PROCEDURES_ACCESSIBLE', '', '4.3 Are SOPs and/or job aides on HIV rapid test procedures and the national HIV rapid test algorithm(s) available and easily accessible at the testing site?'),
(176, 'PRE_C_4_3_TEST_PROCEDURES_ACCESSIBLE', '', 'Comments :'),
(179, 'PRE_Q_4_4_TEST_PROCEDURES_ACCURATE', '', '4.4 Are SOPs and/or job aides on HIV rapid test procedures and the national testing algorithm up-to-date and accurate?'),
(180, 'PRE_C_4_4_TEST_PROCEDURES_ACCURATE', '', 'Comments :'),
(183, 'PRE_Q_4_5_APPROVED_KITS_AVAILABLE', '', '4.5 Are only nationally approved HIV rapid test kits available for use?'),
(184, 'PRE_C_4_5_APPROVED_KITS_AVAILABLE', '', 'Comments :'),
(187, 'PRE_Q_4_6_HIV_KITS_EXPIRATION', '', '4.6 Are all the test kits currently in use within the expiration date?'),
(188, 'PRE_C_4_6_HIV_KITS_EXPIRATION', '', 'Comments :'),
(191, 'PRE_Q_4_7_KIT_SUPPLIES_AVAILABILITY', '', '4.7 Are all required test kit components (i.e. test device, buffer, sample collection device, etc.) and supplies available prior to testing?'),
(192, 'PRE_C_4_7_KIT_SUPPLIES_AVAILABILITY', '', 'Comments :'),
(195, 'PRE_Q_4_8_STOCK_MANAGEMENT', '', '4.8 Is there a process in place for stock management?'),
(196, 'PRE_C_4_8_STOCK_MANAGEMENT', '', 'Comments :'),
(199, 'PRE_Q_4_9_DOCUMENTED_INVENTORY', '', '4.9 Is there a documented inventory system in place at the testing point for test kits received (i.e. who received them, date of receipt, etc.)?'),
(200, 'PRE_C_4_9_DOCUMENTED_INVENTORY', '', 'Comments :'),
(203, 'PRE_Q_4_10_SOPS_BLOOD_COLLECTION', '', '4.10 Are job aides on finger prick or venous blood collection available and posted at the testing point?'),
(204, 'PRE_C_4_10_SOPS_BLOOD_COLLECTION', '', 'Comments :'),
(207, 'PRE_Q_4_11_BLOOD_COLLECTION_SUPPLIES', '', '4.11 Are there sufficient supplies available for finger prick or venous blood collection (i.e. lancet, gauze, alcohol swab, etc.)?'),
(208, 'PRE_C_4_11_BLOOD_COLLECTION_SUPPLIES', '', 'Comments :'),
(211, 'PRE_Q_4_12_CLIENT_IDENTIFICATION', '', '4.12 Are there SOPs and/or job aides describing how client identification should be recorded in the HIV testing register?'),
(212, 'PRE_C_4_12_CLIENT_IDENTIFICATION', '', 'Comments :'),
(215, 'PRE_Q_4_13_CLIENT_ID_RECORDED', '', '4.13 Are client identifiers recorded in the HIV testing register and on test devices per SOPs and/or job aide?'),
(216, 'PRE_C_4_13_CLIENT_ID_RECORDED', '', 'Comments :'),
(218, 'PRETEST_SCORE', '', ''),
(219, 'PRETEST_Display', '', '4.0 PRE-TESTING PHASE'),
(220, 'PRETESTPHOTO', '', 'Photo - Pre-Testing'),
(222, 'TEST', '', '5.0 TESTING PHASE  - (Max Score 9)'),
(224, 'TEST_Q_5_1_PROCEDURES_TESTING_ALGORITHM', '', '5.1 Are SOPs and/or job aides on HIV testing procedures and the national testing algorithm being referred to and followed during testing?'),
(225, 'TEST_C_5_1_PROCEDURES_TESTING_ALGORITHM', '', 'Comments :'),
(228, 'TEST_Q_5_2_TIMERS_AVAILABILITY', '', '5.2 Are timers available and used routinely for HIV rapid testing?'),
(229, 'TEST_C_5_2_TIMERS_AVAILABILITY', '', 'Comments :'),
(232, 'TEST_Q_5_3_SAMPLE_DEVICE_ACCURACY', '', '5.3 Are sample collection devices (e.g., capillary tube, loop, disposable pipettes, etc.) used accurately to perform the test?'),
(233, 'TEST_C_5_3_SAMPLE_DEVICE_ACCURACY', '', 'Comments :'),
(236, 'TEST_Q_5_4_TESTING_PROCEDURE_FOLLOWED', '', '5.4 Are testing procedures adequately followed?'),
(237, 'TEST_C_5_4_TESTING_PROCEDURE_FOLLOWED', '', 'Comments :'),
(240, 'TEST_Q_5_5_QUALITY_CONTROL', '', '5.5 Are external positive and negative quality control (QC) specimens routinely used (e.g., daily, weekly or monthly) according to SOPs or guidelines?'),
(241, 'TEST_C_5_5_QUALITY_CONTROL', '', 'Comments :'),
(244, 'TEST_Q_5_6_QC_RESULTS_RECORDED', '', '5.6 Are QC results properly recorded?'),
(245, 'TEST_C_5_6_QC_RESULTS_RECORDED', '', 'Comments :'),
(248, 'TEST_Q_5_7_INCORRECT_QC_RESULTS', '', '5.7 Are incorrect/invalid QC results properly recorded?'),
(249, 'TEST_C_5_7_INCORRECT_QC_RESULTS', '', 'Comments :'),
(252, 'TEST_Q_5_8_APPROPRIATE_STEPS_TAKEN', '', '5.8 Are appropriate steps taken and documented when QC results are incorrect and/or invalid?'),
(253, 'TEST_C_5_8_APPROPRIATE_STEPS_TAKEN', '', 'Comments :'),
(256, 'TEST_Q_5_9_REVIEW_QC_RECORDS', '', '5.9 Are QC records reviewed by the person in charge routinely?'),
(257, 'TEST_C_5_9_REVIEW_QC_RECORDS', '', 'Comments :'),
(259, 'TEST_SCORE', '', ''),
(260, 'TEST_DISPLAY', '', '5.0 TESTING PHASE  '),
(261, 'TESTPHOTO', '', 'Photo -Testing'),
(263, 'POSTTEST', '', ' 6.0 POST-TESTING PHASE - DOCUMENTS AND RECORDS (Max Score 9)'),
(265, 'POST_Q_6_1_STANDARDIZED_HIV_REGISTER', '', '6.1 Is there a national standardized HIV rapid testing register/logbook that includes all of the key quality elements available and in use?'),
(266, 'POST_C_6_1_STANDARDIZED_HIV_REGISTER', '', 'Comments :'),
(269, 'POST_Q_6_2_ELEMENTS_CAPTURED_CORRECTLY', '', '6.2 Are all the elements in the register/ logbook recorded/captured correctly?  (e.g., client demographics, kit names, lot numbers, expiration dates, tester name, individual and final HIV results, etc.)?'),
(270, 'POST_C_6_2_ELEMENTS_CAPTURED_CORRECTLY', '', 'Comments :'),
(273, 'POST_Q_6_3_PAGE_TOTAL_SUMMARY', '', '6.3 Is the total summary at the end of each page of the register/logbooks complied accurately?'),
(274, 'POST_C_6_3_PAGE_TOTAL_SUMMARY', '', 'Comments :'),
(277, 'POST_Q_6_4_INVALID_TEST_RESULT_RECORDED', '', '6.4 Are invalid test results recorded properly in the register/logbook?'),
(278, 'POST_C_6_4_INVALID_TEST_RESULT_RECORDED', '', 'Comments :'),
(281, 'POST_Q_6_5_APPROPRIATE_STEPS_TAKEN', '', '6.5 Are appropriate steps taken and documented when a result is invalid?'),
(282, 'POST_C_6_5_APPROPRIATE_STEPS_TAKEN', '', 'Comments :'),
(285, 'POST_Q_6_6_REGISTERS_REVIEWED', '', '6.6 Are the register/logbook pages routinely reviewed for accuracy and completeness by the person in charge?'),
(286, 'POST_C_6_6_REGISTERS_REVIEWED', '', 'Comments :'),
(289, 'POST_Q_6_7_DOCUMENTS_SECURELY_KEPT', '', '6.7 Are all client documents and records securely kept throughout all phases of the testing process?'),
(290, 'POST_C_6_7_DOCUMENTS_SECURELY_KEPT', '', 'Comments :'),
(293, 'POST_Q_6_8_REGISTER_SECURE_LOCATION', '', '6.8 Are all registers/logbooks and other documents kept in a secure location when not in use?'),
(294, 'POST_C_6_8_REGISTER_SECURE_LOCATION', '', 'Comments :'),
(297, 'POST_Q_6_9_REGISTERS_PROPERLY_LABELED', '', '6.9 Are registers/logbooks properly labeled and archived when full?'),
(298, 'POST_C_6_9_REGISTERS_PROPERLY_LABELED', '', 'Comments :'),
(300, 'POST_SCORE', '', ''),
(301, 'POST_DISPLAY', '', ' 6.0 POST-TESTING PHASE - DOCUMENTS AND RECORDS'),
(302, 'POSTTESTPHOTO', '', 'Photo -Post Testing'),
(304, 'EQA', '', '7.0  EXTERNAL QUALITY AUDIT (PROFICIENCY TESTING/EQA AND  SITE SUPERVISION) - (Max Score 8 )'),
(306, 'EQA_Q_7_1_PT_ENROLLMENT', '', '7.1 Is the testing point enrolled in an EQA/PT program?'),
(307, 'EQA_C_7_1_PT_ENROLLMENT', '', 'Comments :'),
(310, 'EQA_Q_7_2_TESTING_EQAPT_SAMPLES', '', '7.2 Do all testers at the testing point test the EQA/PT samples?'),
(311, 'EQA_C_7_2_TESTING_EQAPT_SAMPLES', '', 'Comments :'),
(314, 'EQA_Q_7_3_REVIEW_BEFORE_SUBMISSION', '', '7.3 Does the person in charge at the testing point review the EQA/PT results before submission to NRL or designee?'),
(315, 'EQA_C_7_3_REVIEW_BEFORE_SUBMISSION', '', 'Comments :'),
(318, 'EQA_Q_7_4_FEEDBACK_RECEIVED_REVIEWED', '', '7.4 Is an EQA/PT report received from NRL and reviewed by testers and/or the person in charge at the testing point?'),
(319, 'EQA_C_7_4_FEEDBACK_RECEIVED_REVIEWED', '', 'Comments :'),
(322, 'EQA_Q_7_5_IMPLEMENT_CORRECTIVE_ACTION', '', '7.5 Does the testing point implement corrective action in case of unsatisfactory results?'),
(323, 'EQA_C_7_5_IMPLEMENT_CORRECTIVE_ACTION', '', 'Comments :'),
(326, 'EQA_Q_7_6_RECEIVE_PERIODIC_VISITS', '', '7.6 Does the testing point receive periodic supervisory visits?'),
(327, 'EQA_C_7_6_RECEIVE_PERIODIC_VISITS', '', 'Comments :'),
(330, 'EQA_Q_7_7_FEEDBACK_PROVIDED_DOCUMENTED', '', '7.7 Is feedback provided during supervisory visit and documented?'),
(331, 'EQA_C_7_7_FEEDBACK_PROVIDED_DOCUMENTED', '', 'Comments :'),
(334, 'EQA_Q_7_8_TESTERS_RETRAINED_IN_VISITS', '', '7.8 If testers need to be retrained, are they being retrained during the supervisory visit?'),
(335, 'EQA_C_7_8_TESTERS_RETRAINED_IN_VISITS', '', 'Comments :'),
(336, 'EQA_SCORE', '', ''),
(337, 'EQA_DISPLAY', '', '7.0 EXTERNAL QUALITY ASSESSMENT ( PROFICIENCY TESTING/EQA AND SITE SUPERVISION) SCORE'),
(338, 'EQAPHOTO', '', 'Photo -External Quality Assesment'),
(342, 'performrtritesting', '', 'If the country has implemented HIV-1 Recent Infection Surveillance and the testing site is performing the Rapid Test for Recent Infection (RTRI)'),
(345, 'INFECTIONSUR', '', '8.0 HIV-1 RECENT INFECTION SURVEILLANCE USING THE RAPID TEST FOR RECENT INFECTION '),
(347, 'RTRI_Q_8_1_TESTERS_RECEIVED_RTRI_TRAINING', '', '8.1 Have all testers received a comprehensive training on RTRI?'),
(348, 'RTRI_C_8_1_TESTERS_RECEIVED_RTRI_TRAINING', '', 'Comments :'),
(351, 'RTRI_Q_8_2_TESTERS_DEMONSTRATED_COMPETENCY', '', '8.2 Are there records indicating all testers have demonstrated competency in RTRI prior to testing?'),
(352, 'RTRI_C_8_2_TESTERS_DEMONSTRATED_COMPETENCY', '', 'Comments :'),
(355, 'RTRI_Q_8_3_JOBAIDS_READILY_AVAILABLE', '', '8.3 Are all current versions of recency/RTRI SOPs and/or job aids readily available at the site? '),
(356, 'RTRI_C_8_3_JOBAIDS_READILY_AVAILABLE', '', 'Comments :'),
(359, 'RTRI_Q_8_4_SUFFICIENT_SUPPLY_AVAILABLE', '', '8.4 Is there a sufficient supply of RTRI tests available at the site?\nPlease provide number of tests currently available…….\n'),
(360, 'RTRI_C_8_4_SUFFICIENT_SUPPLY_AVAILABLE', '', 'Comments :'),
(363, 'RTRI_Q_8_5_RTRI_KIT_STORAGE', '', '8.5 Are the test kits kept in a temperature controlled environment based on the manufacturers instructions?'),
(364, 'RTRI_C_8_5_RTRI_KIT_STORAGE', '', 'Comments :'),
(367, 'RTRI_Q_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED', '', '8.6 Are RTRI testing procedures being followed (i.e. right volume of sample using correct sample application device, correct read time, correct result interpretation)?'),
(368, 'RTRI_C_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED', '', 'Comments :'),
(371, 'RTRI_Q_8_7_RTRI_TESTING_RESULTS_DOCUMENTED', '', '8.7 Are the RTRI results documented in the data capture form or logbook correctly (e.g. client demographics, kit name, lot number, expiration dates, tester name, RTRI visual results and recency interpretation) and reviewed by the person in charge?'),
(372, 'RTRI_C_8_7_RTRI_TESTING_RESULTS_DOCUMENTED', '', 'Comments :'),
(375, 'RTRI_Q_8_8_QC_ROUTINELY_USED', '', '8.8 Are external quality control (QC) specimens (i.e. long-term (LT), recent and negative) routinely used (i.e. monthly) for RTRI?'),
(376, 'RTRI_C_8_8_QC_ROUTINELY_USED', '', 'Comments :'),
(379, 'RTRI_Q_8_9_QC_RESULTS_RECORDED', '', '8.9 Are QC results for RTRI properly recorded (e.g. kit name, lot number, expiration dates, tester name, RTRI visual results and recency interpretation for each level of QC)  and reviewed by person in charge?'),
(380, 'RTRI_C_8_9_QC_RESULTS_RECORDED', '', 'Comments :'),
(383, 'RTRI_Q_8_10_INCORRECT_QC_DOCUMENTED', '', '8.10 Are appropriate steps taken and documented when RTRI QC results are incorrect?'),
(384, 'RTRI_C_8_10_INCORRECT_QC_DOCUMENTED', '', 'Comments :'),
(387, 'RTRI_Q_8_11_INVALID_RTRI_RESULTS', '', '8.11 Are appropriate steps taken and documented according to the SOP or guidelines for invalid RTRI test results? If yes, how many in the last 3 months…………(use comments)'),
(388, 'RTRI_C_8_11_INVALID_RTRI_RESULTS', '', 'Comments :'),
(393, 'RTRI_SCORE', '', ''),
(395, 'RTRI_DISPLAY', '', '8.0   HIV-1 RECENT INFECTION SURVEILLANCE USING THE RAPID TEST FOR RECENT INFECTION SCORE'),
(396, 'RTRIPHOTO', '', 'Photo -RTRI'),
(399, 'AuditRequiredScore', '', ''),
(400, 'FINAL_AUDIT_SCORE', '', ''),
(401, 'MAX_AUDIT_SCORE', '', ''),
(402, 'AUDIT_SCORE_PERCANTAGE', '', ''),
(403, 'AUDIT_SCORE_PERCANTAGE_ROUNDED', '', ''),
(404, 'preclosure_questions', '', 'Continued ..'),
(405, 'staffaudited', '', 'Staff Audited Name:'),
(406, 'durationaudit', '', 'Duration of Audit:'),
(407, 'personincharge', '', 'Person in Charge Name:'),
(408, 'sitecode', '', 'Site Code (If Applicable):'),
(409, 'auditEndTime', '', 'Audit End Time:'),
(411, 'endofsurvey', '', 'You have completed all questions for SPI-RRT Checklist. '),
(412, 'scoring', '', 'SCORING_CRITERIA '),
(413, 'info5', '', 'PART C: SCORING CRITERIA '),
(414, 'info6', '', 'Each element marked will be assigned a point value: \n•   Items marked “Yes” receive 1 point each.\n•   Items marked “Partial” receive 0.5 point each. \n•   Items marked “No” receive 0 point each. '),
(415, 'info10', '', 'Total points scored for each section should be tallied and recorded at the end of the section. The total number of points expected for all eight sections is 75. If section 8.0 is not applicable then the total number of points expected for seven sections is 64.'),
(416, 'info11', '', 'The overall total points obtained by each HIV testing point audited will be weighed to correspond to a specific performance level.'),
(419, 'SUMMARY_NOT_AVL', '', 'SUMMARY - Sorry You have not completed all required sections.'),
(420, 'SUMMARY', '', 'SUMMARY'),
(421, 'info12', '', 'Part D. Auditor’s Summation Report for SPI-RT Audit\n Facility Name: ${facilityname} \n No. of Tester(s):${NumberofTester}\nType of testing point:${testingpointtype}\nAudit Start Time (hh:mm) :${auditStartTime}\nAudit End Time (hh:mm) :${auditEndTime}\nSite code (if applicable):${sitecode}\nDuration Of Audit : ${durationaudit}\n Staff Audited Name:${staffaudited} \nTotal points scored = ${FINAL_AUDIT_SCORE} \nTotal score expected = ${MAX_AUDIT_SCORE} \n% Score = ${AUDIT_SCORE_PERCANTAGE_ROUNDED}'),
(423, 'SUMMARY_CONTINUATION', '', 'continued ..'),
(424, 'info177', '', 'Audit Score Without RTRI - ${facilityname} :\n1.0 PERSONAL TRAINING & CERTIFICATION SCORE = ${PERSONAL_SCORE}/10 \n2.0 PHYSICAL FACILITY SCORE = ${PHYSICAL_SCORE}/5 \n3.0 SAFETY SCORE = ${SAFETY_SCORE}/10\n4.0 PRE-TESTING PHASE SCORE = ${PRETEST_SCORE}/13 \n5.0 TESTING PHASE SCORE = ${TEST_SCORE}/9 \n6.0 POST TESTING PHASE = ${POST_SCORE}/9 \n7.0 EXTERNAL QUALITY AUDIT = ${EQA_SCORE}/8\nTotal points scored = ${FINAL_AUDIT_SCORE} \nTotal score expected = ${MAX_AUDIT_SCORE} \n% Score = ${AUDIT_SCORE_PERCANTAGE_ROUNDED}'),
(425, 'info178', '', 'Level 0 - Less than 40% '),
(426, 'info179', '', 'Level 1 - 40% - 59% '),
(427, 'info180', '', 'Level 2 - 60%-79%'),
(428, 'info181', '', 'Level 3 - 80%-89%'),
(429, 'info182', '', 'Level 4 - 90% or higher'),
(430, 'info183', '', 'Auditor Name:${name_auditor_lead}'),
(432, 'Summary_cont_a', '', 'Continued..'),
(433, 'info17a', '', 'Audit Score with RTRI - ${facilityname} :\n1.0 PERSONAL TRAINING & CERTIFICATION SCORE = ${PERSONAL_SCORE}/10 \n2.0 PHYSICAL FACILITY SCORE = ${PHYSICAL_SCORE}/5 \n3.0 SAFETY SCORE = ${SAFETY_SCORE}/10\n4.0 PRE-TESTING PHASE SCORE = ${PRETEST_SCORE}/13 \n5.0 TESTING PHASE SCORE = ${TEST_SCORE}/9 \n6.0 POST TESTING PHASE = ${POST_SCORE}/9 \n7.0 EXTERNAL QUALITY AUDIT = ${EQA_SCORE}/8\n8.0 HIV-1 RECENT INFECTION SURVEILLANCE USING THE RAPID TEST FOR RECENT INFECTION = ${RTRI_SCORE}/11\nTotal points scored = ${FINAL_AUDIT_SCORE} \nTotal score expected = ${MAX_AUDIT_SCORE} \n% Score = ${AUDIT_SCORE_PERCANTAGE_ROUNDED}'),
(434, 'info21', '', 'Level 0 - Less than 40% '),
(435, 'info22', '', 'Level 1 - 40% - 59% '),
(436, 'info23', '', 'Level 2 - 60%-79%'),
(437, 'info24', '', 'Level 3 - 80%-89%'),
(438, 'info25', '', 'Level 4 - 90% or higher'),
(439, 'info26', '', 'Auditor Name:${name_auditor_lead}'),
(441, 'infoheading', '', 'Continued..'),
(442, 'info27', '', 'Please summarize your finding and describe the corrective actions types, the recommendations and timelines in next section. '),
(443, 'correctiveaction', '', 'Corrective Actions'),
(444, 'sectionno', '', 'Section No. :'),
(445, 'deficiency', '', 'Deficiency/Issue observed :'),
(446, 'correction', '', 'Correction Actions :'),
(447, 'auditorcomment', '', 'Auditor’s Comments :'),
(448, 'action', '', 'Recommendations - Actions :'),
(449, 'timeline', '', 'Recommendations - Timeline / Person responsible :'),
(450, 'info27', '', 'Please add more corrective action if needed.'),
(452, 'sitephoto', '', 'Site Photo'),
(453, 'lab_geopoint', '', 'Collec the GPS coordinates of this site.'),
(454, 'auditorSignature', '', 'Signature');

CREATE TABLE `r_spi_form_v_6_download` (
  `r_download_id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `auditroundno` varchar(255) DEFAULT NULL,
  `assesmentofaudit` varchar(255) DEFAULT NULL,
  `testingpointtype` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
  `affiliation` varchar(255) DEFAULT NULL,
  `level_name` varchar(255) DEFAULT NULL,
  `AUDIT_SCORE_PERCENTAGE` varchar(255) DEFAULT NULL,
  `download_status` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `r_spi_form_v_6_download`
  ADD PRIMARY KEY (`r_download_id`);

INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Application\\Controller\\SpiV6', 'Manage SPI V6 Form');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6', 'index', 'access');

INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Application\\Controller\\SpiV6Reports', 'SPI V6 Reports');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6Reports', 'facility-report', 'Facility Report');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6', 'approve-status', 'Approved Status'), ('Application\\Controller\\SpiV6', 'download-pdf', 'Download pdf'), ('Application\\Controller\\SpiV6', 'edit', 'Edit'), ('Application\\Controller\\SpiV6', 'manage-facility', 'Access to edit SPI Form');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6', 'corrective-action-pdf', 'Corrective Action PDF');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6', 'delete', 'Delete');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6', 'download-files', 'Download Zipped Files');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6', 'export', 'Export All PDF');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6', 'duplicate', 'Duplicate');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV5', 'duplicate', 'Duplicate');

CREATE TABLE `spi_form_v_5_duplicate` (
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


ALTER TABLE `spi_form_v_5_duplicate`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `spi_form_v_5_duplicate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

CREATE TABLE `spi_form_v_6_duplicate` (
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


ALTER TABLE `spi_form_v_6_duplicate`
ADD PRIMARY KEY (`id`);

ALTER TABLE `spi_form_v_6_duplicate`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6', 'view-data-v6', 'View Data');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV5', 'view-data-v5', 'View Data');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6', 'email-v6', 'Email');

ALTER TABLE `spi_form_v_6` CHANGE `formVersion` `formVersion` MEDIUMTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `spi_form_v_6` ADD `DO_SURVEILLANCE` TEXT NULL AFTER `status`;
ALTER TABLE `spi_form_v_6` ADD `S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY` TEXT NULL AFTER `DO_SURVEILLANCE`;
ALTER TABLE `spi_form_v_6` ADD `S0_C_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY` TEXT NULL AFTER `S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY`;
ALTER TABLE `spi_form_v_6` ADD `S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL` TEXT NULL AFTER `S0_C_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY`;
ALTER TABLE `spi_form_v_6` ADD `S0_C_2_COUNSELORS_FOLLOWING_PROTOCOL` TEXT NULL AFTER `S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL`;
ALTER TABLE `spi_form_v_6` ADD `S0_Q_3_TESTS_RECORDED_RECENCY` TEXT NULL AFTER `S0_C_2_COUNSELORS_FOLLOWING_PROTOCOL`;
ALTER TABLE `spi_form_v_6` ADD `S0_C_3_TESTS_RECORDED_RECENCY` TEXT NULL AFTER `S0_Q_3_TESTS_RECORDED_RECENCY`;
ALTER TABLE `spi_form_v_6` ADD `S0_Q_4_PROCESS_DOCUMENTED` TEXT NULL AFTER `S0_C_3_TESTS_RECORDED_RECENCY`;
ALTER TABLE `spi_form_v_6` ADD `S0_C_4_PROCESS_DOCUMENTED` TEXT NULL AFTER `S0_Q_4_PROCESS_DOCUMENTED`;
ALTER TABLE `spi_form_v_6` ADD `S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS` TEXT NULL AFTER `S0_C_4_PROCESS_DOCUMENTED`;
ALTER TABLE `spi_form_v_6` ADD `S0_C_5_RESULTS_RETURNED_IN_TWO_WEEKS` TEXT NULL AFTER `S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS`;
ALTER TABLE `spi_form_v_6` ADD `S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED` TEXT NULL AFTER `S0_C_5_RESULTS_RETURNED_IN_TWO_WEEKS`;
ALTER TABLE `spi_form_v_6` ADD `S0_C_6_PROTOCOL_VIOLATION_DOCUMENTED` TEXT NULL AFTER `S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED`;
ALTER TABLE `spi_form_v_6` ADD `S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS` TEXT NULL AFTER `S0_C_6_PROTOCOL_VIOLATION_DOCUMENTED`;
ALTER TABLE `spi_form_v_6` ADD `S0_C_7_DOCUMENTING_PROTOCOL_ERRORS` TEXT NULL AFTER `S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS`;
ALTER TABLE `spi_form_v_6` ADD `D0_Q_1_DIAGNOSED_HIV_ABOVE_15` TEXT NULL AFTER `S0_C_7_DOCUMENTING_PROTOCOL_ERRORS`;
ALTER TABLE `spi_form_v_6` ADD `D0_N_1_DIAGNOSED_HIV_ABOVE_15` TEXT NULL AFTER `D0_Q_1_DIAGNOSED_HIV_ABOVE_15`;
ALTER TABLE `spi_form_v_6` ADD `D0_D_1_DIAGNOSED_HIV_ABOVE_15` TEXT NULL AFTER `D0_N_1_DIAGNOSED_HIV_ABOVE_15`;
ALTER TABLE `spi_form_v_6` ADD `D0_S_1_DIAGNOSED_HIV_ABOVE_15` TEXT NULL AFTER `D0_D_1_DIAGNOSED_HIV_ABOVE_15`;
ALTER TABLE `spi_form_v_6` ADD `D0_Q_2_CANDIDATE_SCREENED_FOR_PARTICIPATION` TEXT NULL AFTER `D0_D_1_DIAGNOSED_HIV_ABOVE_15`;
ALTER TABLE `spi_form_v_6` ADD `D0_N_2_CANDIDATE_SCREENED_FOR_PARTICIPATION` TEXT NULL AFTER `D0_Q_2_CANDIDATE_SCREENED_FOR_PARTICIPATION`;
ALTER TABLE `spi_form_v_6` ADD `D0_D_2_CANDIDATE_SCREENED_FOR_PARTICIPATION` TEXT NULL AFTER `D0_N_2_CANDIDATE_SCREENED_FOR_PARTICIPATION`;
ALTER TABLE `spi_form_v_6` ADD `D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION` TEXT NULL AFTER `D0_D_2_CANDIDATE_SCREENED_FOR_PARTICIPATION`;
ALTER TABLE `spi_form_v_6` CHANGE `deviceid` `deviceid` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `spi_form_v_6` ADD `D0_Q_3_ELIGIBLE_DURING_REVIEW_PERIOD` TEXT NULL AFTER `D0_D_2_CANDIDATE_SCREENED_FOR_PARTICIPATION`;
ALTER TABLE `spi_form_v_6` ADD `D0_N_3_ELIGIBLE_DURING_REVIEW_PERIOD` TEXT NULL AFTER `D0_Q_3_ELIGIBLE_DURING_REVIEW_PERIOD`;
ALTER TABLE `spi_form_v_6` ADD `D0_D_3_ELIGIBLE_DURING_REVIEW_PERIOD` TEXT NULL AFTER `D0_N_3_ELIGIBLE_DURING_REVIEW_PERIOD`;
ALTER TABLE `spi_form_v_6` ADD `D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD` TEXT NULL AFTER `D0_D_3_ELIGIBLE_DURING_REVIEW_PERIOD`;
ALTER TABLE `spi_form_v_6` ADD `D0_Q_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD` TEXT NULL AFTER `D0_D_3_ELIGIBLE_DURING_REVIEW_PERIOD`;
ALTER TABLE `spi_form_v_6` ADD `D0_N_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD` TEXT NULL AFTER `D0_Q_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD`;
ALTER TABLE `spi_form_v_6` ADD `D0_D_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD` TEXT NULL AFTER `D0_N_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD`;
ALTER TABLE `spi_form_v_6` ADD `D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD` TEXT NULL AFTER `D0_D_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD`;

ALTER TABLE `spi_form_v_6` ADD `D0_Q_5_DOCUMENTED_AND_REFUSED` TEXT NULL AFTER `D0_D_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD`;
ALTER TABLE `spi_form_v_6` ADD `D0_N_5_DOCUMENTED_AND_REFUSED` TEXT NULL AFTER `D0_Q_5_DOCUMENTED_AND_REFUSED`;
ALTER TABLE `spi_form_v_6` ADD `D0_D_5_DOCUMENTED_AND_REFUSED` TEXT NULL AFTER `D0_N_5_DOCUMENTED_AND_REFUSED`;
ALTER TABLE `spi_form_v_6` ADD `D0_S_5_DOCUMENTED_AND_REFUSED` TEXT NULL AFTER `D0_D_5_DOCUMENTED_AND_REFUSED`;

ALTER TABLE `spi_form_v_6` ADD `D0_Q_6_PARTICIAPANTS_ENROLLED_IN_RTRI` TEXT NULL AFTER `D0_D_5_DOCUMENTED_AND_REFUSED`;
ALTER TABLE `spi_form_v_6` ADD `D0_N_6_PARTICIAPANTS_ENROLLED_IN_RTRI` TEXT NULL AFTER `D0_Q_6_PARTICIAPANTS_ENROLLED_IN_RTRI`;
ALTER TABLE `spi_form_v_6` ADD `D0_D_6_PARTICIAPANTS_ENROLLED_IN_RTRI` TEXT NULL AFTER `D0_N_6_PARTICIAPANTS_ENROLLED_IN_RTRI`;
ALTER TABLE `spi_form_v_6` ADD `D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI` TEXT NULL AFTER `D0_D_6_PARTICIAPANTS_ENROLLED_IN_RTRI`;

ALTER TABLE `spi_form_v_6` ADD `D0_Q_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI` TEXT NULL AFTER `D0_D_6_PARTICIAPANTS_ENROLLED_IN_RTRI`;
ALTER TABLE `spi_form_v_6` ADD `D0_N_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI` TEXT NULL AFTER `D0_Q_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI`;
ALTER TABLE `spi_form_v_6` ADD `D0_D_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI` TEXT NULL AFTER `D0_N_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI`;
ALTER TABLE `spi_form_v_6` ADD `D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI` TEXT NULL AFTER `D0_D_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI`;

ALTER TABLE `spi_form_v_6` ADD `D0_Q_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI` TEXT NULL AFTER `D0_D_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI`;
ALTER TABLE `spi_form_v_6` ADD `D0_N_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI` TEXT NULL AFTER `D0_Q_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI`;
ALTER TABLE `spi_form_v_6` ADD `D0_D_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI` TEXT NULL AFTER `D0_N_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI`;
ALTER TABLE `spi_form_v_6` ADD `D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI` TEXT NULL AFTER `D0_D_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI`;


-- Selvam 29-Dec-2021

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6', 'view-data-v6', 'View Data');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV5', 'view-data-v5', 'View Data');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6', 'email-v6', 'Email');
INSERT INTO `privileges`
(`resource_id`, `privilege_name`, `display_name`)
VALUES
('Application\\Controller\\SpiV6', 'view-data-section-zero-v6', 'View Section S0 Data');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6', 'view-data-section-zero-protocol-v6', 'View Section D0 Data');

-- Sakthivel 30-Sep-2021

DROP table `track`;
DROP table `spi_rt_5_facilities`;

-- Sakthivel 1-Oct-2021

 ALTER TABLE `users` ADD `contact_no` varchar(255) DEFAULT NULL AFTER `email`;
 ALTER TABLE `users` ADD `user_image` varchar(255) DEFAULT NULL AFTER `contact_no`;

 INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\Controller\UsersController', 'profile', 'Edit Profile');

-- Sakthivel 04-10-2021
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\Users', 'change-password', 'Change Password');

ALTER TABLE  `users` ADD  `last_login_datetime` DATETIME NULL DEFAULT NULL;


-- Sakthivel 20-OCT-2021


CREATE TABLE `spi_form_v_6` (
 `id` int NOT NULL AUTO_INCREMENT,
 `token` TEXT NOT NULL,
 `uuid` TEXT NULL,
 `content` TEXT NOT NULL,
 `formId` TEXT NOT NULL,
 `formVersion` TEXT NOT NULL,
 `meta-instance-id` TEXT NOT NULL,
 `meta-model-version` TEXT NOT NULL,
 `meta-ui-version` TEXT NOT NULL,
 `meta-submission-date` TEXT NOT NULL,
 `meta-is-complete` TEXT NOT NULL,
 `meta-date-marked-as-complete` TEXT NOT NULL,
 `start` TEXT DEFAULT NULL,
 `end` TEXT DEFAULT NULL,
 `today` date DEFAULT NULL,
 `deviceid` TEXT,
 `subscriberid` TEXT DEFAULT NULL,
 `text_image` TEXT DEFAULT NULL,
 `info1` TEXT DEFAULT NULL,
 `info2` TEXT DEFAULT NULL,
 `assesmentofaudit` date NOT NULL,
 `auditEndTime` TEXT DEFAULT NULL,
 `auditStartTime` TEXT DEFAULT NULL,
 `auditroundno` TEXT DEFAULT NULL,
 `facility` int DEFAULT NULL,
 `facilityname` TEXT DEFAULT NULL,
 `facilityid` TEXT DEFAULT NULL,
 `testingpointtype` TEXT DEFAULT NULL,
 `testingpointtype_other` TEXT DEFAULT NULL,
 `physicaladdress` TEXT DEFAULT NULL,
 `level` TEXT DEFAULT NULL,
 `level_other` TEXT DEFAULT NULL,
 `affiliation` TEXT DEFAULT NULL,
 `affiliation_other` TEXT DEFAULT NULL,
 `NumberofTester` TEXT DEFAULT NULL,
 `client_tested_HIV` TEXT DEFAULT NULL,
 `client_tested_HIV_PM` TEXT DEFAULT NULL,
 `client_tested_HIV_PQ` TEXT DEFAULT NULL,
 `client_newly_HIV` TEXT DEFAULT NULL,
 `client_newly_HIV_PM` TEXT DEFAULT NULL,
 `client_newly_HIV_PQ` TEXT DEFAULT NULL,
 `client_negative_HIV` TEXT DEFAULT NULL,
 `client_negative_HIV_PM` TEXT DEFAULT NULL,
 `client_negative_HIV_PQ` TEXT DEFAULT NULL,
 `client_positive_HIV_RTRI` TEXT DEFAULT NULL,
 `client_positive_HIV_RTRI_PM` TEXT DEFAULT NULL,
 `client_positive_HIV_RTRI_PQ` TEXT DEFAULT NULL,
 `client_recent_RTRI` TEXT DEFAULT NULL,
 `client_recent_RTRI_PM` TEXT DEFAULT NULL,
 `client_recent_RTRI_PQ` TEXT DEFAULT NULL,
 `name_auditor_lead` TEXT DEFAULT NULL,
 `name_auditor2` TEXT DEFAULT NULL,
 `info4` TEXT DEFAULT NULL,
 `INSTANCE` TEXT DEFAULT NULL,
 `PERSONAL_Q_1_1_HIV_TRAINING` TEXT DEFAULT NULL,
 `PERSONAL_C_1_1_HIV_TRAINING` TEXT DEFAULT NULL,
 `PERSONAL_Q_1_2_HIV_TESTING_REGISTER` TEXT DEFAULT NULL,
 `PERSONAL_C_1_2_HIV_TESTING_REGISTER` TEXT DEFAULT NULL,
 `PERSONAL_Q_1_3_EQA_PT` TEXT DEFAULT NULL,
 `PERSONAL_C_1_3_EQA_PT` TEXT DEFAULT NULL,
 `PERSONAL_Q_1_4_QC_PROCESS` TEXT DEFAULT NULL,
 `PERSONAL_C_1_4_QC_PROCESS` TEXT DEFAULT NULL,
 `PERSONAL_Q_1_5_SAFETY_MANAGEMENT` TEXT DEFAULT NULL,
 `PERSONAL_C_1_5_SAFETY_MANAGEMENT` TEXT DEFAULT NULL,
 `PERSONAL_Q_1_6_REFRESHER_TRAINING` TEXT DEFAULT NULL,
 `PERSONAL_C_1_6_REFRESHER_TRAINING` TEXT DEFAULT NULL,
 `PERSONAL_Q_1_7_HIV_COMPETENCY_TESTING` TEXT DEFAULT NULL,
 `PERSONAL_C_1_7_HIV_COMPETENCY_TESTING` TEXT DEFAULT NULL,
 `PERSONAL_Q_1_8_NATIONAL_CERTIFICATION` TEXT DEFAULT NULL,
 `PERSONAL_C_1_8_NATIONAL_CERTIFICATION` TEXT DEFAULT NULL,
 `PERSONAL_Q_1_9_CERTIFIED_TESTERS` TEXT DEFAULT NULL,
 `PERSONAL_C_1_9_CERTIFIED_TESTERS` TEXT DEFAULT NULL,
 `PERSONAL_Q_1_10_RECERTIFIED` TEXT DEFAULT NULL,
 `PERSONAL_C_1_10_RECERTIFIED` TEXT DEFAULT NULL,
 `PERSONAL_SCORE` TEXT DEFAULT NULL,
 `PERSONAL_Display` TEXT DEFAULT NULL,
 `PERSONALPHOTO` text,
 `PHYSICAL_Q_2_1_DESIGNATED_HIV_AREA` TEXT DEFAULT NULL,
 `PHYSICAL_C_2_1_DESIGNATED_HIV_AREA` TEXT DEFAULT NULL,
 `PHYSICAL_Q_2_2_CLEAN_TESTING_AREA` TEXT DEFAULT NULL,
 `PHYSICAL_C_2_2_CLEAN_TESTING_AREA` TEXT DEFAULT NULL,
 `PHYSICAL_Q_2_3_SUFFICIENT_LIGHT_AVAILABILITY` TEXT DEFAULT NULL,
 `PHYSICAL_C_2_3_SUFFICIENT_LIGHT_AVAILABILITY` TEXT DEFAULT NULL,
 `PHYSICAL_Q_2_4_TEST_KIT_STORAGE` TEXT DEFAULT NULL,
 `PHYSICAL_C_2_4_TEST_KIT_STORAGE` TEXT DEFAULT NULL,
 `PHYSICAL_Q_2_5_SUFFICIENT_SECURE_STORAGE` TEXT DEFAULT NULL,
 `PHYSICAL_C_2_5_SUFFICIENT_SECURE_STORAGE` TEXT DEFAULT NULL,
 `PHYSICAL_SCORE` TEXT DEFAULT NULL,
 `PHYSICAL_Display` TEXT DEFAULT NULL,
 `PHYSICALPHOTO` TEXT,
 `SAFETY_Q_3_1_IMPLEMENT_SAFETY_PRACTICES` TEXT DEFAULT NULL,
 `SAFETY_C_3_1_IMPLEMENT_SAFETY_PRACTICES` TEXT DEFAULT NULL,
 `SAFETY_Q_3_2_ACCIDENTAL_EXPOSURE` TEXT DEFAULT NULL,
 `SAFETY_C_3_2_ACCIDENTAL_EXPOSURE` TEXT DEFAULT NULL,
 `SAFETY_Q_3_3_PRACTICE_SAFETY_PRACTICES` TEXT DEFAULT NULL,
 `SAFETY_C_3_3_PRACTICE_SAFETY_PRACTICES` TEXT DEFAULT NULL,
 `SAFETY_Q_3_4_PPE_AVAILABILITY` TEXT DEFAULT NULL,
 `SAFETY_C_3_4_PPE_AVAILABILITY` TEXT DEFAULT NULL,
 `SAFETY_Q_3_5_PPE_USED_PROPERLY` TEXT DEFAULT NULL,
 `SAFETY_C_3_5_PPE_USED_PROPERLY` TEXT DEFAULT NULL,
 `SAFETY_Q_3_6_WATER_SOAP_AVAILABILITY` TEXT DEFAULT NULL,
 `SAFETY_C_3_6_WATER_SOAP_AVAILABILITY` TEXT DEFAULT NULL,
 `SAFETY_Q_3_7_DISINFECTANT_AVAILABLE` TEXT DEFAULT NULL,
 `SAFETY_C_3_7_DISINFECTANT_AVAILABLE` TEXT DEFAULT NULL,
 `SAFETY_Q_3_8_DISINFECTANT_LABELED_PROPERLY` TEXT DEFAULT NULL,
 `SAFETY_C_3_8_DISINFECTANT_LABELED_PROPERLY` TEXT DEFAULT NULL,
 `SAFETY_Q_3_9_SEGREGATION_OF_WASTE` TEXT DEFAULT NULL,
 `SAFETY_C_3_9_SEGREGATION_OF_WASTE` TEXT DEFAULT NULL,
 `SAFETY_Q_3_10_INFECTIOUS_WASTE_EMPTIED` TEXT DEFAULT NULL,
 `SAFETY_C_3_10_INFECTIOUS_WASTE_EMPTIED` TEXT DEFAULT NULL,
 `SAFETY_SCORE` TEXT DEFAULT NULL,
 `SAFETY_DISPLAY` TEXT DEFAULT NULL,
 `SAFETYPHOTO` TEXT,
 `PRE_Q_4_1_NATIONAL_GUIDELINES` TEXT DEFAULT NULL,
 `PRE_C_4_1_NATIONAL_GUIDELINES` TEXT DEFAULT NULL,
 `PRE_Q_4_2_HIV_TESTING_ALGORITHM` TEXT DEFAULT NULL,
 `PRE_C_4_2_HIV_TESTING_ALGORITHM` TEXT DEFAULT NULL,
 `PRE_Q_4_3_TEST_PROCEDURES_ACCESSIBLE` TEXT DEFAULT NULL,
 `PRE_C_4_3_TEST_PROCEDURES_ACCESSIBLE` TEXT DEFAULT NULL,
 `PRE_Q_4_4_TEST_PROCEDURES_ACCURATE` TEXT DEFAULT NULL,
 `PRE_C_4_4_TEST_PROCEDURES_ACCURATE` TEXT DEFAULT NULL,
 `PRE_Q_4_5_APPROVED_KITS_AVAILABLE` TEXT DEFAULT NULL,
 `PRE_C_4_5_APPROVED_KITS_AVAILABLE` TEXT DEFAULT NULL,
 `PRE_Q_4_6_HIV_KITS_EXPIRATION` TEXT DEFAULT NULL,
 `PRE_C_4_6_HIV_KITS_EXPIRATION` TEXT DEFAULT NULL,
 `PRE_Q_4_7_KIT_SUPPLIES_AVAILABILITY` TEXT DEFAULT NULL,
 `PRE_C_4_7_KIT_SUPPLIES_AVAILABILITY` TEXT DEFAULT NULL,
 `PRE_Q_4_8_STOCK_MANAGEMENT` TEXT DEFAULT NULL,
 `PRE_C_4_8_STOCK_MANAGEMENT` TEXT DEFAULT NULL,
 `PRE_Q_4_9_DOCUMENTED_INVENTORY` TEXT DEFAULT NULL,
 `PRE_C_4_9_DOCUMENTED_INVENTORY` TEXT DEFAULT NULL,
 `PRE_Q_4_10_SOPS_BLOOD_COLLECTION` TEXT DEFAULT NULL,
 `PRE_C_4_10_SOPS_BLOOD_COLLECTION` TEXT DEFAULT NULL,
 `PRE_Q_4_11_BLOOD_COLLECTION_SUPPLIES` TEXT DEFAULT NULL,
 `PRE_C_4_11_BLOOD_COLLECTION_SUPPLIES` TEXT DEFAULT NULL,
 `PRE_Q_4_12_CLIENT_IDENTIFICATION` TEXT DEFAULT NULL,
 `PRE_C_4_12_CLIENT_IDENTIFICATION` TEXT DEFAULT NULL,
 `PRE_Q_4_13_CLIENT_ID_RECORDED` TEXT DEFAULT NULL,
 `PRE_C_4_13_CLIENT_ID_RECORDED` TEXT DEFAULT NULL,
 `PRETEST_SCORE` TEXT DEFAULT NULL,
 `PRETEST_Display` TEXT DEFAULT NULL,
 `PRETESTPHOTO` text,
 `TEST_Q_5_1_PROCEDURES_TESTING_ALGORITHM` TEXT DEFAULT NULL,
 `TEST_C_5_1_PROCEDURES_TESTING_ALGORITHM` TEXT DEFAULT NULL,
 `TEST_Q_5_2_TIMERS_AVAILABILITY` TEXT DEFAULT NULL,
 `TEST_C_5_2_TIMERS_AVAILABILITY` TEXT DEFAULT NULL,
 `TEST_Q_5_3_SAMPLE_DEVICE_ACCURACY` TEXT DEFAULT NULL,
 `TEST_C_5_3_SAMPLE_DEVICE_ACCURACY` TEXT DEFAULT NULL,
 `TEST_Q_5_4_TESTING_PROCEDURE_FOLLOWED` TEXT DEFAULT NULL,
 `TEST_C_5_4_TESTING_PROCEDURE_FOLLOWED` TEXT DEFAULT NULL,
 `TEST_Q_5_5_QUALITY_CONTROL` TEXT DEFAULT NULL,
 `TEST_C_5_5_QUALITY_CONTROL` TEXT DEFAULT NULL,
 `TEST_Q_5_6_QC_RESULTS_RECORDED` TEXT DEFAULT NULL,
 `TEST_C_5_6_QC_RESULTS_RECORDED` TEXT DEFAULT NULL,
 `TEST_Q_5_7_INCORRECT_QC_RESULTS` TEXT DEFAULT NULL,
 `TEST_C_5_7_INCORRECT_QC_RESULTS` TEXT DEFAULT NULL,
 `TEST_Q_5_8_APPROPRIATE_STEPS_TAKEN` TEXT DEFAULT NULL,
 `TEST_C_5_8_APPROPRIATE_STEPS_TAKEN` TEXT DEFAULT NULL,
 `TEST_Q_5_9_REVIEW_QC_RECORDS` TEXT DEFAULT NULL,
 `TEST_C_5_9_REVIEW_QC_RECORDS` TEXT DEFAULT NULL,
 `TEST_SCORE` TEXT DEFAULT NULL,
 `TEST_DISPLAY` TEXT DEFAULT NULL,
 `TESTPHOTO` text,
 `POST_Q_6_1_STANDARDIZED_HIV_REGISTER` TEXT DEFAULT NULL,
 `POST_C_6_1_STANDARDIZED_HIV_REGISTER` TEXT DEFAULT NULL,
 `POST_Q_6_2_ELEMENTS_CAPTURED_CORRECTLY` TEXT DEFAULT NULL,
 `POST_C_6_2_ELEMENTS_CAPTURED_CORRECTLY` TEXT DEFAULT NULL,
 `POST_Q_6_3_PAGE_TOTAL_SUMMARY` TEXT DEFAULT NULL,
 `POST_C_6_3_PAGE_TOTAL_SUMMARY` TEXT DEFAULT NULL,
 `POST_Q_6_4_INVALID_TEST_RESULT_RECORDED` TEXT DEFAULT NULL,
 `POST_C_6_4_INVALID_TEST_RESULT_RECORDED` TEXT DEFAULT NULL,
 `POST_Q_6_5_APPROPRIATE_STEPS_TAKEN` TEXT DEFAULT NULL,
 `POST_C_6_5_APPROPRIATE_STEPS_TAKEN` TEXT DEFAULT NULL,
 `POST_Q_6_6_REGISTERS_REVIEWED` TEXT DEFAULT NULL,
 `POST_C_6_6_REGISTERS_REVIEWED` TEXT DEFAULT NULL,
 `POST_Q_6_7_DOCUMENTS_SECURELY_KEPT` TEXT DEFAULT NULL,
 `POST_C_6_7_DOCUMENTS_SECURELY_KEPT` TEXT DEFAULT NULL,
 `POST_Q_6_8_REGISTER_SECURE_LOCATION` TEXT DEFAULT NULL,
 `POST_C_6_8_REGISTER_SECURE_LOCATION` TEXT DEFAULT NULL,
 `POST_Q_6_9_REGISTERS_PROPERLY_LABELED` TEXT DEFAULT NULL,
 `POST_C_6_9_REGISTERS_PROPERLY_LABELED` TEXT DEFAULT NULL,
 `POST_SCORE` TEXT DEFAULT NULL,
 `POST_DISPLAY` TEXT DEFAULT NULL,
 `POSTTESTPHOTO` text,
 `EQA_Q_7_1_PT_ENROLLMENT` TEXT DEFAULT NULL,
 `EQA_C_7_1_PT_ENROLLMENT` TEXT DEFAULT NULL,
 `EQA_Q_7_2_TESTING_EQAPT_SAMPLES` TEXT DEFAULT NULL,
 `EQA_C_7_2_TESTING_EQAPT_SAMPLES` TEXT DEFAULT NULL,
 `EQA_Q_7_3_REVIEW_BEFORE_SUBMISSION` TEXT DEFAULT NULL,
 `EQA_C_7_3_REVIEW_BEFORE_SUBMISSION` TEXT DEFAULT NULL,
 `EQA_Q_7_4_FEEDBACK_RECEIVED_REVIEWED` TEXT DEFAULT NULL,
 `EQA_C_7_4_FEEDBACK_RECEIVED_REVIEWED` TEXT DEFAULT NULL,
 `EQA_Q_7_5_IMPLEMENT_CORRECTIVE_ACTION` TEXT DEFAULT NULL,
 `EQA_C_7_5_IMPLEMENT_CORRECTIVE_ACTION` TEXT DEFAULT NULL,
 `EQA_Q_7_6_RECEIVE_PERIODIC_VISITS` TEXT DEFAULT NULL,
 `EQA_C_7_6_RECEIVE_PERIODIC_VISITS` TEXT DEFAULT NULL,
 `EQA_Q_7_7_FEEDBACK_PROVIDED_DOCUMENTED` TEXT DEFAULT NULL,
 `EQA_C_7_7_FEEDBACK_PROVIDED_DOCUMENTED` TEXT DEFAULT NULL,
 `EQA_Q_7_8_TESTERS_RETRAINED_IN_VISITS` TEXT DEFAULT NULL,
 `EQA_C_7_8_TESTERS_RETRAINED_IN_VISITS` TEXT DEFAULT NULL,
 `performrtritesting` TEXT DEFAULT NULL,
 `EQA_SCORE` TEXT DEFAULT NULL,
 `EQA_DISPLAY` TEXT DEFAULT NULL,
 `EQAPHOTO` text,
 `RTRI_Q_8_1_TESTERS_RECEIVED_RTRI_TRAINING` TEXT DEFAULT NULL,
 `RTRI_C_8_1_TESTERS_RECEIVED_RTRI_TRAINING` TEXT DEFAULT NULL,
 `RTRI_Q_8_2_TESTERS_DEMONSTRATED_COMPETENCY` TEXT DEFAULT NULL,
 `RTRI_C_8_2_TESTERS_DEMONSTRATED_COMPETENCY` TEXT DEFAULT NULL,
 `RTRI_Q_8_3_JOBAIDS_READILY_AVAILABLE` TEXT DEFAULT NULL,
 `RTRI_C_8_3_JOBAIDS_READILY_AVAILABLE` TEXT DEFAULT NULL,
 `RTRI_Q_8_4_SUFFICIENT_SUPPLY_AVAILABLE` TEXT DEFAULT NULL,
 `RTRI_C_8_4_SUFFICIENT_SUPPLY_AVAILABLE` TEXT DEFAULT NULL,
 `RTRI_Q_8_5_RTRI_KIT_STORAGE` TEXT DEFAULT NULL,
 `RTRI_C_8_5_RTRI_KIT_STORAGE` TEXT DEFAULT NULL,
 `RTRI_Q_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED` TEXT DEFAULT NULL,
 `RTRI_C_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED` TEXT DEFAULT NULL,
 `RTRI_Q_8_7_RTRI_TESTING_RESULTS_DOCUMENTED` TEXT DEFAULT NULL,
 `RTRI_C_8_7_RTRI_TESTING_RESULTS_DOCUMENTED` TEXT DEFAULT NULL,
 `RTRI_Q_8_8_QC_ROUTINELY_USED` TEXT DEFAULT NULL,
 `RTRI_C_8_8_QC_ROUTINELY_USED` TEXT DEFAULT NULL,
 `RTRI_Q_8_9_QC_RESULTS_RECORDED` TEXT DEFAULT NULL,
 `RTRI_C_8_9_QC_RESULTS_RECORDED` TEXT DEFAULT NULL,
 `RTRI_Q_8_10_INCORRECT_QC_DOCUMENTED` TEXT DEFAULT NULL,
 `RTRI_C_8_10_INCORRECT_QC_DOCUMENTED` TEXT DEFAULT NULL,
 `RTRI_Q_8_11_INVALID_RTRI_RESULTS` TEXT DEFAULT NULL,
 `RTRI_C_8_11_INVALID_RTRI_RESULTS` TEXT DEFAULT NULL,
 `RTRI_SCORE` TEXT DEFAULT NULL,
 `RTRI_DISPLAY` TEXT DEFAULT NULL,
 `RTRIPHOTO` TEXT DEFAULT NULL,
 `AuditRequiredScore` TEXT DEFAULT NULL,
 `FINAL_AUDIT_SCORE` TEXT DEFAULT NULL,
 `MAX_AUDIT_SCORE` TEXT DEFAULT NULL,
 `AUDIT_SCORE_PERCENTAGE` TEXT DEFAULT NULL,
 `AUDIT_SCORE_PERCENTAGE_ROUNDED` TEXT DEFAULT NULL,
 `staffaudited` TEXT DEFAULT NULL,
 `durationaudit` TEXT DEFAULT NULL,
 `personincharge` TEXT DEFAULT NULL,
 `sitecode` TEXT DEFAULT NULL,
 `endofsurvey` TEXT DEFAULT NULL,
 `info5` TEXT DEFAULT NULL,
 `info6` TEXT DEFAULT NULL,
 `info10` TEXT DEFAULT NULL,
 `info11` TEXT DEFAULT NULL,
 `summarypage` TEXT DEFAULT NULL,
 `SUMMARY_NOT_AVL` TEXT DEFAULT NULL,
 `info12` TEXT DEFAULT NULL,
 `info177` TEXT DEFAULT NULL,
 `info178` TEXT DEFAULT NULL,
 `info179` TEXT DEFAULT NULL,
 `info180` TEXT DEFAULT NULL,
 `info181` TEXT DEFAULT NULL,
 `info182` TEXT DEFAULT NULL,
 `info183` TEXT DEFAULT NULL,
 `info17a` TEXT DEFAULT NULL,
 `info21` TEXT DEFAULT NULL,
 `info22` TEXT DEFAULT NULL,
 `info23` TEXT DEFAULT NULL,
 `info24` TEXT DEFAULT NULL,
 `info25` TEXT DEFAULT NULL,
 `info26` TEXT DEFAULT NULL,
 `info27` TEXT DEFAULT NULL,
 `correctiveaction` text,
 `sitephoto` text,
 `Latitude` TEXT DEFAULT NULL,
 `Longitude` TEXT DEFAULT NULL,
 `Altitude` TEXT DEFAULT NULL,
 `Accuracy` TEXT DEFAULT NULL,
 `auditorSignature` text,
 `instanceID` TEXT DEFAULT NULL,
 `instanceName` TEXT DEFAULT NULL,
 `status` varchar(100) DEFAULT 'pending',
 `DO_SURVEILLANCE` text,
 `S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY` text,
 `S0_C_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY` text,
 `S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL` text,
 `S0_C_2_COUNSELORS_FOLLOWING_PROTOCOL` text,
 `S0_Q_3_TESTS_RECORDED_RECENCY` text,
 `S0_C_3_TESTS_RECORDED_RECENCY` text,
 `S0_Q_4_PROCESS_DOCUMENTED` text,
 `S0_C_4_PROCESS_DOCUMENTED` text,
 `S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS` text,
 `S0_C_5_RESULTS_RETURNED_IN_TWO_WEEKS` text,
 `S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED` text,
 `S0_C_6_PROTOCOL_VIOLATION_DOCUMENTED` text,
 `S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS` text,
 `S0_C_7_DOCUMENTING_PROTOCOL_ERRORS` text,
 `D0_Q_1_DIAGNOSED_HIV_ABOVE_15` text,
 `D0_N_1_DIAGNOSED_HIV_ABOVE_15` text,
 `D0_D_1_DIAGNOSED_HIV_ABOVE_15` text,
 `D0_S_1_DIAGNOSED_HIV_ABOVE_15` text,
 `D0_Q_2_CANDIDATE_SCREENED_FOR_PARTICIPATION` text,
 `D0_N_2_CANDIDATE_SCREENED_FOR_PARTICIPATION` text,
 `D0_D_2_CANDIDATE_SCREENED_FOR_PARTICIPATION` text,
 `D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION` text,
 `D0_Q_3_ELIGIBLE_DURING_REVIEW_PERIOD` text,
 `D0_N_3_ELIGIBLE_DURING_REVIEW_PERIOD` text,
 `D0_D_3_ELIGIBLE_DURING_REVIEW_PERIOD` text,
 `D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD` text,
 `D0_Q_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD` text,
 `D0_N_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD` text,
 `D0_D_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD` text,
 `D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD` text,
 `D0_Q_5_DOCUMENTED_AND_REFUSED` text,
 `D0_N_5_DOCUMENTED_AND_REFUSED` text,
 `D0_D_5_DOCUMENTED_AND_REFUSED` text,
 `D0_S_5_DOCUMENTED_AND_REFUSED` text,
 `D0_Q_6_PARTICIAPANTS_ENROLLED_IN_RTRI` text,
 `D0_N_6_PARTICIAPANTS_ENROLLED_IN_RTRI` text,
 `D0_D_6_PARTICIAPANTS_ENROLLED_IN_RTRI` text,
 `D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI` text,
 `D0_Q_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI` text,
 `D0_N_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI` text,
 `D0_D_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI` text,
 `D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI` text,
 `D0_Q_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI` text,
 `D0_N_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI` text,
 `D0_D_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI` text,
 `D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI` text,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- Sakthivel 24-Nov-2021

CREATE TABLE `user_login_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `login_id` varchar (1000) DEFAULT NULL,
  `login_attempted_datetime` datetime DEFAULT NULL,
  `login_status` varchar (1000) DEFAULT NULL,
  `ip_address` varchar (1000) DEFAULT NULL,
  `browser` varchar (1000),
  `operating_system` varchar (1000) DEFAULT NULL,
   PRIMARY KEY (`history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Application\\Controller\\UserLoginHistory', 'Manage User Login History');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\UserLoginHistory', 'index', 'Access');

-- Amit 02-Mar-2022
ALTER TABLE `spi_form_v_6` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT;
ALTER TABLE `spi_form_v_5` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT;
ALTER TABLE `spi_form_v_3` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT;

-- Thana 21-Dec-2022
CREATE TABLE `countries` (
  `country_id` int unsigned NOT NULL AUTO_INCREMENT,
  `latitude` varchar(256) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `longitude` varchar(256) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `iso2` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `iso3` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `numeric_code` smallint NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'inactive',
  PRIMARY KEY (`country_id`),
  UNIQUE KEY `country_id` (`country_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `countries` (`country_id`, `latitude`, `longitude`, `country_name`, `iso2`, `iso3`, `numeric_code`, `status`) VALUES
(1, '33.93911', '67.709953', 'Afghanistan', 'AF', 'AFG', 4, 'inactive'),
(2, NULL, NULL, 'Aland Islands', 'AX', 'ALA', 248, 'inactive'),
(3, '41.153332', '20.168331', 'Albania', 'AL', 'ALB', 8, 'inactive'),
(4, '28.033886', '1.659626', 'Algeria', 'DZ', 'DZA', 12, 'inactive'),
(5, '-14.270972', '-170.132217', 'American Samoa', 'AS', 'ASM', 16, 'inactive'),
(6, '42.546245', '1.601554', 'Andorra', 'AD', 'AND', 20, 'inactive'),
(7, '-11.202692', '17.873887', 'Angola', 'AO', 'AGO', 24, 'active'),
(8, '18.220554', '-63.068615', 'Anguilla', 'AI', 'AIA', 660, 'inactive'),
(9, '-75.250973', '-0.071389', 'Antarctica', 'AQ', 'ATA', 10, 'inactive'),
(10, '17.060816', '-61.796428', 'Antigua and Barbuda', 'AG', 'ATG', 28, 'inactive'),
(11, '-38.416097', '-63.616672', 'Argentina', 'AR', 'ARG', 32, 'inactive'),
(12, '40.069099', '45.038189', 'Armenia', 'AM', 'ARM', 51, 'inactive'),
(13, '12.52111', '-69.968338', 'Aruba', 'AW', 'ABW', 533, 'inactive'),
(14, '-25.274398', '133.775136', 'Australia', 'AU', 'AUS', 36, 'inactive'),
(15, '47.516231', '14.550072', 'Austria', 'AT', 'AUT', 40, 'inactive'),
(16, '40.143105', '47.576927', 'Azerbaijan', 'AZ', 'AZE', 31, 'inactive'),
(17, '25.03428', '-77.39628', 'Bahamas', 'BS', 'BHS', 44, 'active'),
(18, '25.930414', '50.637772', 'Bahrain', 'BH', 'BHR', 48, 'inactive'),
(19, '23.684994', '90.356331', 'Bangladesh', 'BD', 'BGD', 50, 'inactive'),
(20, '13.193887', '-59.543198', 'Barbados', 'BB', 'BRB', 52, 'active'),
(21, '53.709807', '27.953389', 'Belarus', 'BY', 'BLR', 112, 'inactive'),
(22, '50.503887', '4.469936', 'Belgium', 'BE', 'BEL', 56, 'inactive'),
(23, '17.189877', '-88.49765', 'Belize', 'BZ', 'BLZ', 84, 'inactive'),
(24, '9.30769', '2.315834', 'Benin', 'BJ', 'BEN', 204, 'inactive'),
(25, '32.321384', '-64.75737', 'Bermuda', 'BM', 'BMU', 60, 'inactive'),
(26, '27.514162', '90.433601', 'Bhutan', 'BT', 'BTN', 64, 'inactive'),
(27, '-16.290154', '-63.588653', 'Bolivia, Plurinational State of', 'BO', 'BOL', 68, 'inactive'),
(28, NULL, NULL, 'Bonaire, Sint Eustatius and Saba', 'BQ', 'BES', 535, 'inactive'),
(29, '43.915886', '17.679076', 'Bosnia and Herzegovina', 'BA', 'BIH', 70, 'inactive'),
(30, '-22.328474', '24.684866', 'Botswana', 'BW', 'BWA', 72, 'inactive'),
(31, '-54.423199', '3.413194', 'Bouvet Island', 'BV', 'BVT', 74, 'inactive'),
(32, '-14.235004', '-51.92528', 'Brazil', 'BR', 'BRA', 76, 'inactive'),
(33, '-6.343194', '71.876519', 'British Indian Ocean Territory', 'IO', 'IOT', 86, 'inactive'),
(34, '4.535277', '114.727669', 'Brunei Darussalam', 'BN', 'BRN', 96, 'inactive'),
(35, '42.733883', '25.48583', 'Bulgaria', 'BG', 'BGR', 100, 'inactive'),
(36, '12.238333', '-1.561593', 'Burkina Faso', 'BF', 'BFA', 854, 'inactive'),
(37, '-3.373056', '29.918886', 'Burundi', 'BI', 'BDI', 108, 'active'),
(38, '12.565679', '104.990963', 'Cambodia', 'KH', 'KHM', 116, 'active'),
(39, '7.369722', '12.354722', 'Cameroon', 'CM', 'CMR', 120, 'active'),
(40, '56.130366', '-106.346771', 'Canada', 'CA', 'CAN', 124, 'inactive'),
(41, '16.002082', '-24.013197', 'Cape Verde', 'CV', 'CPV', 132, 'inactive'),
(42, '19.513469', '-80.566956', 'Cayman Islands', 'KY', 'CYM', 136, 'inactive'),
(43, '6.611111', '20.939444', 'Central African Republic', 'CF', 'CAF', 140, 'inactive'),
(44, '15.454166', '18.732207', 'Chad', 'TD', 'TCD', 148, 'inactive'),
(45, '-35.675147', '-71.542969', 'Chile', 'CL', 'CHL', 152, 'inactive'),
(46, '35.86166', '104.195397', 'China', 'CN', 'CHN', 156, 'inactive'),
(47, '-10.447525', '105.690449', 'Christmas Island', 'CX', 'CXR', 162, 'inactive'),
(48, '-12.164165', '96.870956', 'Cocos (Keeling) Islands', 'CC', 'CCK', 166, 'inactive'),
(49, '4.570868', '-74.297333', 'Colombia', 'CO', 'COL', 170, 'inactive'),
(50, '-11.875001', '43.872219', 'Comoros', 'KM', 'COM', 174, 'inactive'),
(51, '-0.228021', '15.827659', 'Congo', 'CG', 'COG', 178, 'inactive'),
(52, '-4.038333', '21.758664', 'Congo, the Democratic Republic of the', 'CD', 'COD', 180, 'active'),
(53, '-21.236736', '-159.777671', 'Cook Islands', 'CK', 'COK', 184, 'inactive'),
(54, '9.748917', '-83.753428', 'Costa Rica', 'CR', 'CRI', 188, 'active'),
(55, '7.539989', '-5.54708', 'Cote d\Ivoire', 'CI', 'CIV', 384, 'inactive'),
(56, '45.1', '15.2', 'Croatia', 'HR', 'HRV', 191, 'inactive'),
(57, '21.521757', '-77.781167', 'Cuba', 'CU', 'CUB', 192, 'inactive'),
(58, NULL, NULL, 'Cura', 'CW', 'CUW', 531, 'inactive'),
(59, '35.126413', '33.429859', 'Cyprus', 'CY', 'CYP', 196, 'inactive'),
(60, '49.817492', '15.472962', 'Czech Republic', 'CZ', 'CZE', 203, 'inactive'),
(61, '56.26392', '9.501785', 'Denmark', 'DK', 'DNK', 208, 'inactive'),
(62, '11.825138', '42.590275', 'Djibouti', 'DJ', 'DJI', 262, 'inactive'),
(63, '15.414999', '-61.370976', 'Dominica', 'DM', 'DMA', 212, 'active'),
(64, '18.735693', '-70.162651', 'Dominican Republic', 'DO', 'DOM', 214, 'inactive'),
(65, '-1.831239', '-78.183406', 'Ecuador', 'EC', 'ECU', 218, 'inactive'),
(66, '26.820553', '30.802498', 'Egypt', 'EG', 'EGY', 818, 'inactive'),
(67, '13.794185', '-88.89653', 'El Salvador', 'SV', 'SLV', 222, 'active'),
(68, '1.650801', '10.267895', 'Equatorial Guinea', 'GQ', 'GNQ', 226, 'inactive'),
(69, '15.179384', '39.782334', 'Eritrea', 'ER', 'ERI', 232, 'inactive'),
(70, '58.595272', '25.013607', 'Estonia', 'EE', 'EST', 233, 'inactive'),
(71, '9.145', '40.489673', 'Ethiopia', 'ET', 'ETH', 231, 'active'),
(72, '-51.796253', '-59.523613', 'Falkland Islands (Malvinas)', 'FK', 'FLK', 238, 'inactive'),
(73, '61.892635', '-6.911806', 'Faroe Islands', 'FO', 'FRO', 234, 'inactive'),
(74, '-16.578193', '179.414413', 'Fiji', 'FJ', 'FJI', 242, 'inactive'),
(75, '61.92411', '25.748151', 'Finland', 'FI', 'FIN', 246, 'inactive'),
(76, '46.227638', '2.213749', 'France', 'FR', 'FRA', 250, 'inactive'),
(77, '3.933889', '-53.125782', 'French Guiana', 'GF', 'GUF', 254, 'inactive'),
(78, '-17.679742', '-149.406843', 'French Polynesia', 'PF', 'PYF', 258, 'inactive'),
(79, '-49.280366', '69.348557', 'French Southern Territories', 'TF', 'ATF', 260, 'inactive'),
(80, '-0.803689', '11.609444', 'Gabon', 'GA', 'GAB', 266, 'inactive'),
(81, '13.443182', '-15.310139', 'Gambia', 'GM', 'GMB', 270, 'inactive'),
(82, '42.315407', '43.356892', 'Georgia', 'GE', 'GEO', 268, 'inactive'),
(83, '51.165691', '10.451526', 'Germany', 'DE', 'DEU', 276, 'inactive'),
(84, '7.946527', '-1.023194', 'Ghana', 'GH', 'GHA', 288, 'inactive'),
(85, '36.137741', '-5.345374', 'Gibraltar', 'GI', 'GIB', 292, 'inactive'),
(86, '39.074208', '21.824312', 'Greece', 'GR', 'GRC', 300, 'inactive'),
(87, '71.706936', '-42.604303', 'Greenland', 'GL', 'GRL', 304, 'inactive'),
(88, '12.262776', '-61.604171', 'Grenada', 'GD', 'GRD', 308, 'active'),
(89, '16.995971', '-62.067641', 'Guadeloupe', 'GP', 'GLP', 312, 'inactive'),
(90, '13.444304', '144.793731', 'Guam', 'GU', 'GUM', 316, 'inactive'),
(91, '15.783471', '-90.230759', 'Guatemala', 'GT', 'GTM', 320, 'active'),
(92, '49.465691', '-2.585278', 'Guernsey', 'GG', 'GGY', 831, 'inactive'),
(93, '9.945587', '-9.696645', 'Guinea', 'GN', 'GIN', 324, 'inactive'),
(94, '11.803749', '-15.180413', 'Guinea-Bissau', 'GW', 'GNB', 624, 'inactive'),
(95, '4.860416', '-58.93018', 'Guyana', 'GY', 'GUY', 328, 'inactive'),
(96, '18.971187', '-72.285215', 'Haiti', 'HT', 'HTI', 332, 'inactive'),
(97, '-53.08181', '73.504158', 'Heard Island and McDonald Islands', 'HM', 'HMD', 334, 'inactive'),
(98, '12.984305', '-61.287228', 'Holy See (Vatican City State)', 'VA', 'VAT', 336, 'inactive'),
(99, '15.199999', '-86.241905', 'Honduras', 'HN', 'HND', 340, 'active'),
(100, '22.396428', '114.109497', 'Hong Kong', 'HK', 'HKG', 344, 'inactive'),
(101, '47.162494', '19.503304', 'Hungary', 'HU', 'HUN', 348, 'inactive'),
(102, '64.963051', '-19.020835', 'Iceland', 'IS', 'ISL', 352, 'inactive'),
(103, '20.593684', '78.96288', 'India', 'IN', 'IND', 356, 'inactive'),
(104, '-0.789275', '113.921327', 'Indonesia', 'ID', 'IDN', 360, 'inactive'),
(105, '32.427908', '53.688046', 'Iran, Islamic Republic of', 'IR', 'IRN', 364, 'inactive'),
(106, '33.223191', '43.679291', 'Iraq', 'IQ', 'IRQ', 368, 'inactive'),
(107, '53.41291', '-8.24389', 'Ireland', 'IE', 'IRL', 372, 'inactive'),
(108, '54.236107', '-4.548056', 'Isle of Man', 'IM', 'IMN', 833, 'inactive'),
(109, '31.046051', '34.851612', 'Israel', 'IL', 'ISR', 376, 'inactive'),
(110, '41.87194', '12.56738', 'Italy', 'IT', 'ITA', 380, 'inactive'),
(111, '18.109581', '-77.297508', 'Jamaica', 'JM', 'JAM', 388, 'active'),
(112, '36.204824', '138.252924', 'Japan', 'JP', 'JPN', 392, 'inactive'),
(113, '49.214439', '-2.13125', 'Jersey', 'JE', 'JEY', 832, 'inactive'),
(114, '30.585164', '36.238414', 'Jordan', 'JO', 'JOR', 400, 'inactive'),
(115, '48.019573', '66.923684', 'Kazakhstan', 'KZ', 'KAZ', 398, 'inactive'),
(116, '-0.023559', '37.906193', 'Kenya', 'KE', 'KEN', 404, 'active'),
(117, '-3.370417', '-168.734039', 'Kiribati', 'KI', 'KIR', 296, 'inactive'),
(118, '40.339852', '127.510093', 'Korea, Democratic People\'s Republic of', 'KP', 'PRK', 408, 'inactive'),
(119, '35.907757', '127.766922', 'Korea, Republic of', 'KR', 'KOR', 410, 'inactive'),
(120, '29.31166', '47.481766', 'Kuwait', 'KW', 'KWT', 414, 'inactive'),
(121, '41.20438', '74.766098', 'Kyrgyzstan', 'KG', 'KGZ', 417, 'inactive'),
(122, '19.85627', '102.495496', 'Lao People\'s Democratic Republic', 'LA', 'LAO', 418, 'inactive'),
(123, '56.879635', '24.603189', 'Latvia', 'LV', 'LVA', 428, 'inactive'),
(124, '33.854721', '35.862285', 'Lebanon', 'LB', 'LBN', 422, 'inactive'),
(125, '-29.609988', '28.233608', 'Lesotho', 'LS', 'LSO', 426, 'inactive'),
(126, '6.428055', '-9.429499', 'Liberia', 'LR', 'LBR', 430, 'inactive'),
(127, '26.3351', '17.228331', 'Libya', 'LY', 'LBY', 434, 'inactive'),
(128, '47.166', '9.555373', 'Liechtenstein', 'LI', 'LIE', 438, 'inactive'),
(129, '55.169438', '23.881275', 'Lithuania', 'LT', 'LTU', 440, 'inactive'),
(130, '49.815273', '6.129583', 'Luxembourg', 'LU', 'LUX', 442, 'inactive'),
(131, '22.198745', '113.543873', 'Macao', 'MO', 'MAC', 446, 'inactive'),
(132, '41.608635', '21.745275', 'Macedonia, the former Yugoslav Republic of', 'MK', 'MKD', 807, 'inactive'),
(133, '-18.766947', '46.869107', 'Madagascar', 'MG', 'MDG', 450, 'inactive'),
(134, '-13.254308', '34.301525', 'Malawi', 'MW', 'MWI', 454, 'inactive'),
(135, '4.210484', '101.975766', 'Malaysia', 'MY', 'MYS', 458, 'inactive'),
(136, '3.202778', '73.22068', 'Maldives', 'MV', 'MDV', 462, 'inactive'),
(137, '17.570692', '-3.996166', 'Mali', 'ML', 'MLI', 466, 'inactive'),
(138, '35.937496', '14.375416', 'Malta', 'MT', 'MLT', 470, 'inactive'),
(139, '7.131474', '171.184478', 'Marshall Islands', 'MH', 'MHL', 584, 'inactive'),
(140, '14.641528', '-61.024174', 'Martinique', 'MQ', 'MTQ', 474, 'inactive'),
(141, '21.00789', '-10.940835', 'Mauritania', 'MR', 'MRT', 478, 'inactive'),
(142, '-20.348404', '57.552152', 'Mauritius', 'MU', 'MUS', 480, 'inactive'),
(143, '-30.559482', '22.937506', 'Mayotte', 'YT', 'MYT', 175, 'inactive'),
(144, '23.634501', '-102.552784', 'Mexico', 'MX', 'MEX', 484, 'inactive'),
(145, '7.425554', '150.550812', 'Micronesia, Federated States of', 'FM', 'FSM', 583, 'inactive'),
(146, '47.411631', '28.369885', 'Moldova, Republic of', 'MD', 'MDA', 498, 'inactive'),
(147, '43.750298', '7.412841', 'Monaco', 'MC', 'MCO', 492, 'inactive'),
(148, '46.862496', '103.846656', 'Mongolia', 'MN', 'MNG', 496, 'inactive'),
(149, '42.708678', '19.37439', 'Montenegro', 'ME', 'MNE', 499, 'inactive'),
(150, '16.742498', '-62.187366', 'Montserrat', 'MS', 'MSR', 500, 'inactive'),
(151, '31.791702', '-7.09262', 'Morocco', 'MA', 'MAR', 504, 'inactive'),
(152, '-18.665695', '35.529562', 'Mozambique', 'MZ', 'MOZ', 508, 'inactive'),
(153, '21.913965', '95.956223', 'Myanmar', 'MM', 'MMR', 104, 'inactive'),
(154, '-22.95764', '18.49041', 'Namibia', 'NA', 'NAM', 516, 'inactive'),
(155, '-0.522778', '166.931503', 'Nauru', 'NR', 'NRU', 520, 'inactive'),
(156, '28.394857', '84.124008', 'Nepal', 'NP', 'NPL', 524, 'inactive'),
(157, '52.132633', '5.291266', 'Netherlands', 'NL', 'NLD', 528, 'inactive'),
(158, '-20.904305', '165.618042', 'New Caledonia', 'NC', 'NCL', 540, 'inactive'),
(159, '-40.900557', '174.885971', 'New Zealand', 'NZ', 'NZL', 554, 'inactive'),
(160, '12.865416', '-85.207229', 'Nicaragua', 'NI', 'NIC', 558, 'active'),
(161, '17.607789', '8.081666', 'Niger', 'NE', 'NER', 562, 'inactive'),
(162, '9.081999', '8.675277', 'Nigeria', 'NG', 'NGA', 566, 'inactive'),
(163, '-19.054445', '-169.867233', 'Niue', 'NU', 'NIU', 570, 'inactive'),
(164, '-29.040835', '167.954712', 'Norfolk Island', 'NF', 'NFK', 574, 'inactive'),
(165, '17.33083', '145.38469', 'Northern Mariana Islands', 'MP', 'MNP', 580, 'inactive'),
(166, '60.472024', '8.468946', 'Norway', 'NO', 'NOR', 578, 'inactive'),
(167, '21.512583', '55.923255', 'Oman', 'OM', 'OMN', 512, 'inactive'),
(168, '30.375321', '69.345116', 'Pakistan', 'PK', 'PAK', 586, 'inactive'),
(169, '7.51498', '134.58252', 'Palau', 'PW', 'PLW', 585, 'inactive'),
(170, '31.952162', '35.233154', 'Palestine, State of', 'PS', 'PSE', 275, 'inactive'),
(171, '8.537981', '-80.782127', 'Panama', 'PA', 'PAN', 591, 'active'),
(172, '-6.314993', '143.95555', 'Papua New Guinea', 'PG', 'PNG', 598, 'inactive'),
(173, '-23.442503', '-58.443832', 'Paraguay', 'PY', 'PRY', 600, 'inactive'),
(174, '-9.189967', '-75.015152', 'Peru', 'PE', 'PER', 604, 'inactive'),
(175, '12.879721', '121.774017', 'Philippines', 'PH', 'PHL', 608, 'inactive'),
(176, '-24.703615', '-127.439308', 'Pitcairn', 'PN', 'PCN', 612, 'inactive'),
(177, '51.919438', '19.145136', 'Poland', 'PL', 'POL', 616, 'inactive'),
(178, '39.399872', '-8.224454', 'Portugal', 'PT', 'PRT', 620, 'inactive'),
(179, '18.220833', '-66.590149', 'Puerto Rico', 'PR', 'PRI', 630, 'inactive'),
(180, '25.354826', '51.183884', 'Qatar', 'QA', 'QAT', 634, 'inactive'),
(181, '-21.115141', '55.536384', 'Reunion', 'RE', 'REU', 638, 'inactive'),
(182, '45.943161', '24.96676', 'Romania', 'RO', 'ROU', 642, 'inactive'),
(183, '61.52401', '105.318756', 'Russian Federation', 'RU', 'RUS', 643, 'inactive'),
(184, '-1.940278', '29.873888', 'Rwanda', 'RW', 'RWA', 646, 'active'),
(185, NULL, NULL, 'Saint Barthelemy', 'BL', 'BLM', 652, 'inactive'),
(186, '-24.143474', '-10.030696', 'Saint Helena, Ascension and Tristan da Cunha', 'SH', 'SHN', 654, 'inactive'),
(187, '17.357822', '-62.782998', 'Saint Kitts and Nevis', 'KN', 'KNA', 659, 'active'),
(188, '13.909444', '-60.978893', 'Saint Lucia', 'LC', 'LCA', 662, 'active'),
(189, NULL, NULL, 'Saint Martin (French part)', 'MF', 'MAF', 663, 'inactive'),
(190, '46.941936', '-56.27111', 'Saint Pierre and Miquelon', 'PM', 'SPM', 666, 'inactive'),
(191, '6.42375', '-66.58973', 'Saint Vincent and the Grenadines', 'VC', 'VCT', 670, 'active'),
(192, '42.602636', '20.902977', 'Samoa', 'WS', 'WSM', 882, 'inactive'),
(193, '43.94236', '12.457777', 'San Marino', 'SM', 'SMR', 674, 'inactive'),
(194, '0.18636', '6.613081', 'Sao Tome and Principe', 'ST', 'STP', 678, 'inactive'),
(195, '23.885942', '45.079162', 'Saudi Arabia', 'SA', 'SAU', 682, 'inactive'),
(196, '14.497401', '-14.452362', 'Senegal', 'SN', 'SEN', 686, 'inactive'),
(197, '44.016521', '21.005859', 'Serbia', 'RS', 'SRB', 688, 'inactive'),
(198, '-4.679574', '55.491977', 'Seychelles', 'SC', 'SYC', 690, 'inactive'),
(199, '8.460555', '-11.779889', 'Sierra Leone', 'SL', 'SLE', 694, 'inactive'),
(200, '1.352083', '103.819836', 'Singapore', 'SG', 'SGP', 702, 'inactive'),
(201, NULL, NULL, 'Sint Maarten (Dutch part)', 'SX', 'SXM', 534, 'inactive'),
(202, '48.669026', '19.699024', 'Slovakia', 'SK', 'SVK', 703, 'inactive'),
(203, '46.151241', '14.995463', 'Slovenia', 'SI', 'SVN', 705, 'inactive'),
(204, '-9.64571', '160.156194', 'Solomon Islands', 'SB', 'SLB', 90, 'inactive'),
(205, '5.152149', '46.199616', 'Somalia', 'SO', 'SOM', 706, 'inactive'),
(206, '-13.133897', '27.849332', 'South Africa', 'ZA', 'ZAF', 710, 'inactive'),
(207, '-54.429579', '-36.587909', 'South Georgia and the South Sandwich Islands', 'GS', 'SGS', 239, 'inactive'),
(208, NULL, NULL, 'South Sudan', 'SS', 'SSD', 728, 'inactive'),
(209, '40.463667', '-3.74922', 'Spain', 'ES', 'ESP', 724, 'inactive'),
(210, '7.873054', '80.771797', 'Sri Lanka', 'LK', 'LKA', 144, 'inactive'),
(211, '12.862807', '30.217636', 'Sudan', 'SD', 'SDN', 729, 'inactive'),
(212, '3.919305', '-56.027783', 'Suriname', 'SR', 'SUR', 740, 'active'),
(213, '77.553604', '23.670272', 'Svalbard and Jan Mayen', 'SJ', 'SJM', 744, 'inactive'),
(214, '-26.522503', '31.465866', 'Swaziland', 'SZ', 'SWZ', 748, 'inactive'),
(215, '60.128161', '18.643501', 'Sweden', 'SE', 'SWE', 752, 'inactive'),
(216, '46.818188', '8.227512', 'Switzerland', 'CH', 'CHE', 756, 'inactive'),
(217, '34.802075', '38.996815', 'Syrian Arab Republic', 'SY', 'SYR', 760, 'inactive'),
(218, '23.69781', '120.960515', 'Taiwan, Province of China', 'TW', 'TWN', 158, 'inactive'),
(219, '38.861034', '71.276093', 'Tajikistan', 'TJ', 'TJK', 762, 'inactive'),
(220, '-6.369028', '34.888822', 'Tanzania, United Republic of', 'TZ', 'TZA', 834, 'inactive'),
(221, '15.870032', '100.992541', 'Thailand', 'TH', 'THA', 764, 'inactive'),
(222, '-8.874217', '125.727539', 'Timor-Leste', 'TL', 'TLS', 626, 'inactive'),
(223, '8.619543', '0.824782', 'Togo', 'TG', 'TGO', 768, 'inactive'),
(224, '-8.967363', '-171.855881', 'Tokelau', 'TK', 'TKL', 772, 'inactive'),
(225, '-21.178986', '-175.198242', 'Tonga', 'TO', 'TON', 776, 'inactive'),
(226, '10.691803', '-61.222503', 'Trinidad and Tobago', 'TT', 'TTO', 780, 'active'),
(227, '33.886917', '9.537499', 'Tunisia', 'TN', 'TUN', 788, 'inactive'),
(228, '38.963745', '35.243322', 'Turkey', 'TR', 'TUR', 792, 'inactive'),
(229, '38.969719', '59.556278', 'Turkmenistan', 'TM', 'TKM', 795, 'inactive'),
(230, '21.694025', '-71.797928', 'Turks and Caicos Islands', 'TC', 'TCA', 796, 'inactive'),
(231, '-7.109535', '177.64933', 'Tuvalu', 'TV', 'TUV', 798, 'inactive'),
(232, '1.373333', '32.290275', 'Uganda', 'UG', 'UGA', 800, 'inactive'),
(233, '48.379433', '31.16558', 'Ukraine', 'UA', 'UKR', 804, 'inactive'),
(234, '23.424076', '53.847818', 'United Arab Emirates', 'AE', 'ARE', 784, 'inactive'),
(235, '55.378051', '-3.435973', 'United Kingdom', 'GB', 'GBR', 826, 'inactive'),
(236, '-32.522779', '-55.765835', 'United States', 'US', 'USA', 840, 'inactive'),
(237, '37.09024', '-95.712891', 'United States Minor Outlying Islands', 'UM', 'UMI', 581, 'inactive'),
(238, '41.377491', '64.585262', 'Uruguay', 'UY', 'URY', 858, 'inactive'),
(239, '41.902916', '12.453389', 'Uzbekistan', 'UZ', 'UZB', 860, 'inactive'),
(240, '-13.768752', '-177.156097', 'Vanuatu', 'VU', 'VUT', 548, 'inactive'),
(241, '18.420695', '-64.639968', 'Venezuela, Bolivarian Republic of', 'VE', 'VEN', 862, 'inactive'),
(242, '-15.376706', '166.959158', 'Viet Nam', 'VN', 'VNM', 704, 'inactive'),
(243, '18.335765', '-64.896335', 'Virgin Islands, British', 'VG', 'VGB', 92, 'inactive'),
(244, '14.058324', '108.277199', 'Virgin Islands, U.S.', 'VI', 'VIR', 850, 'inactive'),
(245, '-13.759029', '-172.104629', 'Wallis and Futuna', 'WF', 'WLF', 876, 'inactive'),
(246, '24.215527', '-12.885834', 'Western Sahara', 'EH', 'ESH', 732, 'inactive'),
(247, '-12.8275', '45.166244', 'Yemen', 'YE', 'YEM', 887, 'inactive'),
(248, '-19.015438', '29.154857', 'Zambia', 'ZM', 'ZMB', 894, 'inactive'),
(249, '', '', 'Zimbabwe', 'ZW', 'ZWE', 716, 'inactive'),
(250, '-26.522503', '31.465866', 'Eswatini', 'SZ', 'SWZ', 748, 'active');

CREATE TABLE `user_country_map` (
  `user_id` int DEFAULT NULL,
  `country_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Thana 30-Dec-2022
INSERT INTO `global_config` (`config_id`, `display_name`, `global_name`, `global_value`) VALUES (NULL, 'Google Map Key', 'map_key', 'AIzaSyDhZa4hDifE6p2sbaxJehS7gcrZOJScIqM');


-- Jeyabanu 31-Dec-2023 Changing data type of columns from varchar to text

ALTER TABLE `spi_form_v_3` CHANGE `token` `token` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `content` `content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `formId` `formId` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `formVersion` `formVersion` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `meta-instance-id` `meta-instance-id` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `meta-model-version` `meta-model-version` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `meta-ui-version` `meta-ui-version` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `meta-submission-date` `meta-submission-date` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `meta-is-complete` `meta-is-complete` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `meta-date-marked-as-complete` `meta-date-marked-as-complete` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `start` `start` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `end` `end` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `deviceid` `deviceid` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `subscriberid` `subscriberid` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `text_image` `text_image` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info1` `info1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info2` `info2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `auditroundno` `auditroundno` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `facilityname` `facilityname` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `testingpointname` `testingpointname` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `testingpointtype` `testingpointtype` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `testingpointtype_other` `testingpointtype_other` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `locationaddress` `locationaddress` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `level` `level` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `level_other` `level_other` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `level_name` `level_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `affiliation` `affiliation` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `affiliation_other` `affiliation_other` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `NumberofTester` `NumberofTester` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `avgMonthTesting` `avgMonthTesting` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `name_auditor_lead` `name_auditor_lead` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `name_auditor2` `name_auditor2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info4` `info4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `INSTANCE` `INSTANCE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_1` `PERSONAL_Q_1_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_1` `PERSONAL_C_1_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_2` `PERSONAL_Q_1_2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_2` `PERSONAL_C_1_2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_3` `PERSONAL_Q_1_3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_3` `PERSONAL_C_1_3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_4` `PERSONAL_Q_1_4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_4` `PERSONAL_C_1_4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_5` `PERSONAL_Q_1_5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_5` `PERSONAL_C_1_5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_6` `PERSONAL_Q_1_6` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_6` `PERSONAL_C_1_6` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_7` `PERSONAL_Q_1_7` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_7` `PERSONAL_C_1_7` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_8` `PERSONAL_Q_1_8` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_8` `PERSONAL_C_1_8` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_9` `PERSONAL_Q_1_9` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_9` `PERSONAL_C_1_9` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_10` `PERSONAL_Q_1_10` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_10` `PERSONAL_C_1_10` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_SCORE` `PERSONAL_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Display` `PERSONAL_Display` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONALPHOTO` `PERSONALPHOTO` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_Q_2_1` `PHYSICAL_Q_2_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_C_2_1` `PHYSICAL_C_2_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_Q_2_2` `PHYSICAL_Q_2_2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_C_2_2` `PHYSICAL_C_2_2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_Q_2_3` `PHYSICAL_Q_2_3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_C_2_3` `PHYSICAL_C_2_3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_Q_2_4` `PHYSICAL_Q_2_4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_C_2_4` `PHYSICAL_C_2_4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_Q_2_5` `PHYSICAL_Q_2_5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_C_2_5` `PHYSICAL_C_2_5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_SCORE` `PHYSICAL_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_Display` `PHYSICAL_Display` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICALPHOTO` `PHYSICALPHOTO` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_1` `SAFETY_Q_3_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_1` `SAFETY_C_3_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_2` `SAFETY_Q_3_2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_2` `SAFETY_C_3_2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_3` `SAFETY_Q_3_3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_3` `SAFETY_C_3_3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_4` `SAFETY_Q_3_4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_4` `SAFETY_C_3_4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_5` `SAFETY_Q_3_5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_5` `SAFETY_C_3_5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_6` `SAFETY_Q_3_6` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_6` `SAFETY_C_3_6` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_7` `SAFETY_Q_3_7` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_7` `SAFETY_C_3_7` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_8` `SAFETY_Q_3_8` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_8` `SAFETY_C_3_8` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_9` `SAFETY_Q_3_9` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_9` `SAFETY_C_3_9` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_10` `SAFETY_Q_3_10` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_10` `SAFETY_C_3_10` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_11` `SAFETY_Q_3_11` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_11` `SAFETY_C_3_11` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_SCORE` `SAFETY_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_DISPLAY` `SAFETY_DISPLAY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETYPHOTO` `SAFETYPHOTO` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_1` `PRE_Q_4_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_1` `PRE_C_4_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_2` `PRE_Q_4_2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_2` `PRE_C_4_2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_3` `PRE_Q_4_3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_3` `PRE_C_4_3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_4` `PRE_Q_4_4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_4` `PRE_C_4_4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_5` `PRE_Q_4_5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_5` `PRE_C_4_5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_6` `PRE_Q_4_6` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_6` `PRE_C_4_6` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_7` `PRE_Q_4_7` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_7` `PRE_C_4_7` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_8` `PRE_Q_4_8` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_8` `PRE_C_4_8` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_9` `PRE_Q_4_9` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_9` `PRE_C_4_9` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_10` `PRE_Q_4_10` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_10` `PRE_C_4_10` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_11` `PRE_Q_4_11` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_11` `PRE_C_4_11` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_12` `PRE_Q_4_12` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_12` `PRE_C_4_12` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRETEST_SCORE` `PRETEST_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRETEST_Display` `PRETEST_Display` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRETESTPHOTO` `PRETESTPHOTO` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_1` `TEST_Q_5_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_1` `TEST_C_5_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_2` `TEST_Q_5_2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_2` `TEST_C_5_2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_3` `TEST_Q_5_3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_3` `TEST_C_5_3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_4` `TEST_Q_5_4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_4` `TEST_C_5_4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_5` `TEST_Q_5_5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_5` `TEST_C_5_5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_6` `TEST_Q_5_6` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_6` `TEST_C_5_6` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_7` `TEST_Q_5_7` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_7` `TEST_C_5_7` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_8` `TEST_Q_5_8` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_8` `TEST_C_5_8` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_9` `TEST_Q_5_9` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_9` `TEST_C_5_9` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_SCORE` `TEST_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_DISPLAY` `TEST_DISPLAY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TESTPHOTO` `TESTPHOTO` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_1` `POST_Q_6_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_1` `POST_C_6_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_2` `POST_Q_6_2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_2` `POST_C_6_2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_3` `POST_Q_6_3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_3` `POST_C_6_3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_4` `POST_Q_6_4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_4` `POST_C_6_4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_5` `POST_Q_6_5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_5` `POST_C_6_5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_6` `POST_Q_6_6` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_6` `POST_C_6_6` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_7` `POST_Q_6_7` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_7` `POST_C_6_7` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_8` `POST_Q_6_8` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_8` `POST_C_6_8` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_9` `POST_Q_6_9` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_9` `POST_C_6_9` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_SCORE` `POST_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_DISPLAY` `POST_DISPLAY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POSTTESTPHOTO` `POSTTESTPHOTO` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_1` `EQA_Q_7_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_1` `EQA_C_7_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_2` `EQA_Q_7_2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_2` `EQA_C_7_2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_3` `EQA_Q_7_3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_3` `EQA_C_7_3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_4` `EQA_Q_7_4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_4` `EQA_C_7_4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_5` `EQA_Q_7_5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_5` `EQA_C_7_5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_6` `EQA_Q_7_6` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_6` `EQA_C_7_6` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_7` `EQA_Q_7_7` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_7` `EQA_C_7_7` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_8` `EQA_Q_7_8` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_8` `EQA_C_7_8` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `sampleretesting` `sampleretesting` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_9` `EQA_Q_7_9` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_9` `EQA_C_7_9` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_10` `EQA_Q_7_10` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_10` `EQA_C_7_10` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_11` `EQA_Q_7_11` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_11` `EQA_C_7_11` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_12` `EQA_Q_7_12` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_12` `EQA_C_7_12` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_13` `EQA_Q_7_13` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_13` `EQA_C_7_13` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_14` `EQA_Q_7_14` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_14` `EQA_C_7_14` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_MAX_SCORE` `EQA_MAX_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_REQ` `EQA_REQ` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_OPT` `EQA_OPT` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_SCORE` `EQA_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_DISPLAY` `EQA_DISPLAY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQAPHOTO` `EQAPHOTO` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `FINAL_AUDIT_SCORE` `FINAL_AUDIT_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `MAX_AUDIT_SCORE` `MAX_AUDIT_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `AUDIT_SCORE_PERCANTAGE` `AUDIT_SCORE_PERCANTAGE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `staffaudited` `staffaudited` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `durationaudit` `durationaudit` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `personincharge` `personincharge` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `endofsurvey` `endofsurvey` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info5` `info5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info6` `info6` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info10` `info10` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info11` `info11` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `summarypage` `summarypage` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SUMMARY_NOT_AVL` `SUMMARY_NOT_AVL` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info12` `info12` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info17` `info17` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info21` `info21` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info22` `info22` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info23` `info23` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info24` `info24` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info25` `info25` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info26` `info26` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info27` `info27` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `correctiveaction` `correctiveaction` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `sitephoto` `sitephoto` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `Latitude` `Latitude` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `Longitude` `Longitude` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `Altitude` `Altitude` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `Accuracy` `Accuracy` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `auditorSignature` `auditorSignature` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `instanceID` `instanceID` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `instanceName` `instanceName` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `status` `status` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `spi_form_v_6` CHANGE `Accuracy` `Accuracy` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `affiliation` `affiliation` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `affiliation_other` `affiliation_other` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `Altitude` `Altitude` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `AUDIT_SCORE_PERCANTAGE_ROUNDED` `AUDIT_SCORE_PERCANTAGE_ROUNDED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `AUDIT_SCORE_PERCENTAGE` `AUDIT_SCORE_PERCENTAGE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `auditEndTime` `auditEndTime` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `AuditRequiredScore` `AuditRequiredScore` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `auditroundno` `auditroundno` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `auditStartTime` `auditStartTime` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_negative_HIV` `client_negative_HIV` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_negative_HIV_PM` `client_negative_HIV_PM` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_negative_HIV_PQ` `client_negative_HIV_PQ` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_newly_HIV` `client_newly_HIV` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_newly_HIV_PM` `client_newly_HIV_PM` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_newly_HIV_PQ` `client_newly_HIV_PQ` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_positive_HIV_RTRI` `client_positive_HIV_RTRI` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_positive_HIV_RTRI_PM` `client_positive_HIV_RTRI_PM` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_positive_HIV_RTRI_PQ` `client_positive_HIV_RTRI_PQ` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_recent_RTRI` `client_recent_RTRI` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_recent_RTRI_PM` `client_recent_RTRI_PM` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_recent_RTRI_PQ` `client_recent_RTRI_PQ` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_tested_HIV` `client_tested_HIV` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_tested_HIV_PM` `client_tested_HIV_PM` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `client_tested_HIV_PQ` `client_tested_HIV_PQ` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `content` `content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `durationaudit` `durationaudit` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `end` `end` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `endofsurvey` `endofsurvey` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_1_PT_ENROLLMENT` `EQA_C_7_1_PT_ENROLLMENT` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_2_TESTING_EQAPT_SAMPLES` `EQA_C_7_2_TESTING_EQAPT_SAMPLES` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_3_REVIEW_BEFORE_SUBMISSION` `EQA_C_7_3_REVIEW_BEFORE_SUBMISSION` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_4_FEEDBACK_RECEIVED_REVIEWED` `EQA_C_7_4_FEEDBACK_RECEIVED_REVIEWED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_5_IMPLEMENT_CORRECTIVE_ACTION` `EQA_C_7_5_IMPLEMENT_CORRECTIVE_ACTION` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_6_RECEIVE_PERIODIC_VISITS` `EQA_C_7_6_RECEIVE_PERIODIC_VISITS` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_7_FEEDBACK_PROVIDED_DOCUMENTED` `EQA_C_7_7_FEEDBACK_PROVIDED_DOCUMENTED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_C_7_8_TESTERS_RETRAINED_IN_VISITS` `EQA_C_7_8_TESTERS_RETRAINED_IN_VISITS` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_DISPLAY` `EQA_DISPLAY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_1_PT_ENROLLMENT` `EQA_Q_7_1_PT_ENROLLMENT` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_2_TESTING_EQAPT_SAMPLES` `EQA_Q_7_2_TESTING_EQAPT_SAMPLES` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_3_REVIEW_BEFORE_SUBMISSION` `EQA_Q_7_3_REVIEW_BEFORE_SUBMISSION` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_4_FEEDBACK_RECEIVED_REVIEWED` `EQA_Q_7_4_FEEDBACK_RECEIVED_REVIEWED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_5_IMPLEMENT_CORRECTIVE_ACTION` `EQA_Q_7_5_IMPLEMENT_CORRECTIVE_ACTION` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_6_RECEIVE_PERIODIC_VISITS` `EQA_Q_7_6_RECEIVE_PERIODIC_VISITS` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_7_FEEDBACK_PROVIDED_DOCUMENTED` `EQA_Q_7_7_FEEDBACK_PROVIDED_DOCUMENTED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_Q_7_8_TESTERS_RETRAINED_IN_VISITS` `EQA_Q_7_8_TESTERS_RETRAINED_IN_VISITS` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `EQA_SCORE` `EQA_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `facilityid` `facilityid` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `facilityname` `facilityname` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `FINAL_AUDIT_SCORE` `FINAL_AUDIT_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `formId` `formId` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info1` `info1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info10` `info10` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info11` `info11` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info12` `info12` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info177` `info177` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info178` `info178` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info179` `info179` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info17a` `info17a` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info180` `info180` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info181` `info181` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info182` `info182` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info183` `info183` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info2` `info2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info21` `info21` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info22` `info22` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info23` `info23` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info24` `info24` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info25` `info25` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info26` `info26` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info27` `info27` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info4` `info4` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info5` `info5` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `info6` `info6` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `INSTANCE` `INSTANCE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `instanceID` `instanceID` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `instanceName` `instanceName` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `Latitude` `Latitude` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `level` `level` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `level_other` `level_other` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `Longitude` `Longitude` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `MAX_AUDIT_SCORE` `MAX_AUDIT_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `meta-date-marked-as-complete` `meta-date-marked-as-complete` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `meta-instance-id` `meta-instance-id` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `meta-is-complete` `meta-is-complete` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `meta-model-version` `meta-model-version` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `meta-submission-date` `meta-submission-date` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `meta-ui-version` `meta-ui-version` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `name_auditor2` `name_auditor2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `name_auditor_lead` `name_auditor_lead` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `NumberofTester` `NumberofTester` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `performrtritesting` `performrtritesting` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_10_RECERTIFIED` `PERSONAL_C_1_10_RECERTIFIED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_1_HIV_TRAINING` `PERSONAL_C_1_1_HIV_TRAINING` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_2_HIV_TESTING_REGISTER` `PERSONAL_C_1_2_HIV_TESTING_REGISTER` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_3_EQA_PT` `PERSONAL_C_1_3_EQA_PT` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_4_QC_PROCESS` `PERSONAL_C_1_4_QC_PROCESS` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_5_SAFETY_MANAGEMENT` `PERSONAL_C_1_5_SAFETY_MANAGEMENT` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_6_REFRESHER_TRAINING` `PERSONAL_C_1_6_REFRESHER_TRAINING` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_7_HIV_COMPETENCY_TESTING` `PERSONAL_C_1_7_HIV_COMPETENCY_TESTING` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_8_NATIONAL_CERTIFICATION` `PERSONAL_C_1_8_NATIONAL_CERTIFICATION` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_C_1_9_CERTIFIED_TESTERS` `PERSONAL_C_1_9_CERTIFIED_TESTERS` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Display` `PERSONAL_Display` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_10_RECERTIFIED` `PERSONAL_Q_1_10_RECERTIFIED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_1_HIV_TRAINING` `PERSONAL_Q_1_1_HIV_TRAINING` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_2_HIV_TESTING_REGISTER` `PERSONAL_Q_1_2_HIV_TESTING_REGISTER` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_3_EQA_PT` `PERSONAL_Q_1_3_EQA_PT` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_4_QC_PROCESS` `PERSONAL_Q_1_4_QC_PROCESS` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_5_SAFETY_MANAGEMENT` `PERSONAL_Q_1_5_SAFETY_MANAGEMENT` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_6_REFRESHER_TRAINING` `PERSONAL_Q_1_6_REFRESHER_TRAINING` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_7_HIV_COMPETENCY_TESTING` `PERSONAL_Q_1_7_HIV_COMPETENCY_TESTING` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_8_NATIONAL_CERTIFICATION` `PERSONAL_Q_1_8_NATIONAL_CERTIFICATION` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_Q_1_9_CERTIFIED_TESTERS` `PERSONAL_Q_1_9_CERTIFIED_TESTERS` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PERSONAL_SCORE` `PERSONAL_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `personincharge` `personincharge` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_C_2_1_DESIGNATED_HIV_AREA` `PHYSICAL_C_2_1_DESIGNATED_HIV_AREA` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_C_2_2_CLEAN_TESTING_AREA` `PHYSICAL_C_2_2_CLEAN_TESTING_AREA` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_C_2_3_SUFFICIENT_LIGHT_AVAILABILITY` `PHYSICAL_C_2_3_SUFFICIENT_LIGHT_AVAILABILITY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_C_2_4_TEST_KIT_STORAGE` `PHYSICAL_C_2_4_TEST_KIT_STORAGE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_C_2_5_SUFFICIENT_SECURE_STORAGE` `PHYSICAL_C_2_5_SUFFICIENT_SECURE_STORAGE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_Display` `PHYSICAL_Display` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_Q_2_1_DESIGNATED_HIV_AREA` `PHYSICAL_Q_2_1_DESIGNATED_HIV_AREA` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_Q_2_2_CLEAN_TESTING_AREA` `PHYSICAL_Q_2_2_CLEAN_TESTING_AREA` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_Q_2_3_SUFFICIENT_LIGHT_AVAILABILITY` `PHYSICAL_Q_2_3_SUFFICIENT_LIGHT_AVAILABILITY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_Q_2_4_TEST_KIT_STORAGE` `PHYSICAL_Q_2_4_TEST_KIT_STORAGE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_Q_2_5_SUFFICIENT_SECURE_STORAGE` `PHYSICAL_Q_2_5_SUFFICIENT_SECURE_STORAGE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PHYSICAL_SCORE` `PHYSICAL_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `physicaladdress` `physicaladdress` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_1_STANDARDIZED_HIV_REGISTER` `POST_C_6_1_STANDARDIZED_HIV_REGISTER` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_2_ELEMENTS_CAPTURED_CORRECTLY` `POST_C_6_2_ELEMENTS_CAPTURED_CORRECTLY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_3_PAGE_TOTAL_SUMMARY` `POST_C_6_3_PAGE_TOTAL_SUMMARY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_4_INVALID_TEST_RESULT_RECORDED` `POST_C_6_4_INVALID_TEST_RESULT_RECORDED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_5_APPROPRIATE_STEPS_TAKEN` `POST_C_6_5_APPROPRIATE_STEPS_TAKEN` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_6_REGISTERS_REVIEWED` `POST_C_6_6_REGISTERS_REVIEWED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_7_DOCUMENTS_SECURELY_KEPT` `POST_C_6_7_DOCUMENTS_SECURELY_KEPT` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_8_REGISTER_SECURE_LOCATION` `POST_C_6_8_REGISTER_SECURE_LOCATION` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_C_6_9_REGISTERS_PROPERLY_LABELED` `POST_C_6_9_REGISTERS_PROPERLY_LABELED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_DISPLAY` `POST_DISPLAY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_1_STANDARDIZED_HIV_REGISTER` `POST_Q_6_1_STANDARDIZED_HIV_REGISTER` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_2_ELEMENTS_CAPTURED_CORRECTLY` `POST_Q_6_2_ELEMENTS_CAPTURED_CORRECTLY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_3_PAGE_TOTAL_SUMMARY` `POST_Q_6_3_PAGE_TOTAL_SUMMARY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_4_INVALID_TEST_RESULT_RECORDED` `POST_Q_6_4_INVALID_TEST_RESULT_RECORDED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_5_APPROPRIATE_STEPS_TAKEN` `POST_Q_6_5_APPROPRIATE_STEPS_TAKEN` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_6_REGISTERS_REVIEWED` `POST_Q_6_6_REGISTERS_REVIEWED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_7_DOCUMENTS_SECURELY_KEPT` `POST_Q_6_7_DOCUMENTS_SECURELY_KEPT` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_8_REGISTER_SECURE_LOCATION` `POST_Q_6_8_REGISTER_SECURE_LOCATION` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_Q_6_9_REGISTERS_PROPERLY_LABELED` `POST_Q_6_9_REGISTERS_PROPERLY_LABELED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `POST_SCORE` `POST_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_10_SOPS_BLOOD_COLLECTION` `PRE_C_4_10_SOPS_BLOOD_COLLECTION` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_11_BLOOD_COLLECTION_SUPPLIES` `PRE_C_4_11_BLOOD_COLLECTION_SUPPLIES` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_12_CLIENT_IDENTIFICATION` `PRE_C_4_12_CLIENT_IDENTIFICATION` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_13_CLIENT_ID_RECORDED` `PRE_C_4_13_CLIENT_ID_RECORDED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_1_NATIONAL_GUIDELINES` `PRE_C_4_1_NATIONAL_GUIDELINES` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_2_HIV_TESTING_ALGORITHM` `PRE_C_4_2_HIV_TESTING_ALGORITHM` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_3_TEST_PROCEDURES_ACCESSIBLE` `PRE_C_4_3_TEST_PROCEDURES_ACCESSIBLE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_4_TEST_PROCEDURES_ACCURATE` `PRE_C_4_4_TEST_PROCEDURES_ACCURATE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_5_APPROVED_KITS_AVAILABLE` `PRE_C_4_5_APPROVED_KITS_AVAILABLE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_6_HIV_KITS_EXPIRATION` `PRE_C_4_6_HIV_KITS_EXPIRATION` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_7_KIT_SUPPLIES_AVAILABILITY` `PRE_C_4_7_KIT_SUPPLIES_AVAILABILITY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_8_STOCK_MANAGEMENT` `PRE_C_4_8_STOCK_MANAGEMENT` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_C_4_9_DOCUMENTED_INVENTORY` `PRE_C_4_9_DOCUMENTED_INVENTORY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_10_SOPS_BLOOD_COLLECTION` `PRE_Q_4_10_SOPS_BLOOD_COLLECTION` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_11_BLOOD_COLLECTION_SUPPLIES` `PRE_Q_4_11_BLOOD_COLLECTION_SUPPLIES` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_12_CLIENT_IDENTIFICATION` `PRE_Q_4_12_CLIENT_IDENTIFICATION` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_13_CLIENT_ID_RECORDED` `PRE_Q_4_13_CLIENT_ID_RECORDED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_1_NATIONAL_GUIDELINES` `PRE_Q_4_1_NATIONAL_GUIDELINES` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_2_HIV_TESTING_ALGORITHM` `PRE_Q_4_2_HIV_TESTING_ALGORITHM` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_3_TEST_PROCEDURES_ACCESSIBLE` `PRE_Q_4_3_TEST_PROCEDURES_ACCESSIBLE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_4_TEST_PROCEDURES_ACCURATE` `PRE_Q_4_4_TEST_PROCEDURES_ACCURATE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_5_APPROVED_KITS_AVAILABLE` `PRE_Q_4_5_APPROVED_KITS_AVAILABLE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_6_HIV_KITS_EXPIRATION` `PRE_Q_4_6_HIV_KITS_EXPIRATION` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_7_KIT_SUPPLIES_AVAILABILITY` `PRE_Q_4_7_KIT_SUPPLIES_AVAILABILITY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_8_STOCK_MANAGEMENT` `PRE_Q_4_8_STOCK_MANAGEMENT` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRE_Q_4_9_DOCUMENTED_INVENTORY` `PRE_Q_4_9_DOCUMENTED_INVENTORY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRETEST_Display` `PRETEST_Display` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `PRETEST_SCORE` `PRETEST_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_C_8_10_INCORRECT_QC_DOCUMENTED` `RTRI_C_8_10_INCORRECT_QC_DOCUMENTED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_C_8_11_INVALID_RTRI_RESULTS` `RTRI_C_8_11_INVALID_RTRI_RESULTS` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_C_8_1_TESTERS_RECEIVED_RTRI_TRAINING` `RTRI_C_8_1_TESTERS_RECEIVED_RTRI_TRAINING` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_C_8_2_TESTERS_DEMONSTRATED_COMPETENCY` `RTRI_C_8_2_TESTERS_DEMONSTRATED_COMPETENCY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_C_8_3_JOBAIDS_READILY_AVAILABLE` `RTRI_C_8_3_JOBAIDS_READILY_AVAILABLE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_C_8_4_SUFFICIENT_SUPPLY_AVAILABLE` `RTRI_C_8_4_SUFFICIENT_SUPPLY_AVAILABLE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_C_8_5_RTRI_KIT_STORAGE` `RTRI_C_8_5_RTRI_KIT_STORAGE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_C_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED` `RTRI_C_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_C_8_7_RTRI_TESTING_RESULTS_DOCUMENTED` `RTRI_C_8_7_RTRI_TESTING_RESULTS_DOCUMENTED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_C_8_8_QC_ROUTINELY_USED` `RTRI_C_8_8_QC_ROUTINELY_USED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_C_8_9_QC_RESULTS_RECORDED` `RTRI_C_8_9_QC_RESULTS_RECORDED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_DISPLAY` `RTRI_DISPLAY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_Q_8_10_INCORRECT_QC_DOCUMENTED` `RTRI_Q_8_10_INCORRECT_QC_DOCUMENTED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_Q_8_11_INVALID_RTRI_RESULTS` `RTRI_Q_8_11_INVALID_RTRI_RESULTS` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_Q_8_1_TESTERS_RECEIVED_RTRI_TRAINING` `RTRI_Q_8_1_TESTERS_RECEIVED_RTRI_TRAINING` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_Q_8_2_TESTERS_DEMONSTRATED_COMPETENCY` `RTRI_Q_8_2_TESTERS_DEMONSTRATED_COMPETENCY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_Q_8_3_JOBAIDS_READILY_AVAILABLE` `RTRI_Q_8_3_JOBAIDS_READILY_AVAILABLE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_Q_8_4_SUFFICIENT_SUPPLY_AVAILABLE` `RTRI_Q_8_4_SUFFICIENT_SUPPLY_AVAILABLE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_Q_8_5_RTRI_KIT_STORAGE` `RTRI_Q_8_5_RTRI_KIT_STORAGE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_Q_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED` `RTRI_Q_8_6_RTRI_TESTING_PROCEDURE_FOLLOWED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_Q_8_7_RTRI_TESTING_RESULTS_DOCUMENTED` `RTRI_Q_8_7_RTRI_TESTING_RESULTS_DOCUMENTED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_Q_8_8_QC_ROUTINELY_USED` `RTRI_Q_8_8_QC_ROUTINELY_USED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_Q_8_9_QC_RESULTS_RECORDED` `RTRI_Q_8_9_QC_RESULTS_RECORDED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRI_SCORE` `RTRI_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `RTRIPHOTO` `RTRIPHOTO` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_10_INFECTIOUS_WASTE_EMPTIED` `SAFETY_C_3_10_INFECTIOUS_WASTE_EMPTIED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_1_IMPLEMENT_SAFETY_PRACTICES` `SAFETY_C_3_1_IMPLEMENT_SAFETY_PRACTICES` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_2_ACCIDENTAL_EXPOSURE` `SAFETY_C_3_2_ACCIDENTAL_EXPOSURE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_3_PRACTICE_SAFETY_PRACTICES` `SAFETY_C_3_3_PRACTICE_SAFETY_PRACTICES` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_4_PPE_AVAILABILITY` `SAFETY_C_3_4_PPE_AVAILABILITY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_5_PPE_USED_PROPERLY` `SAFETY_C_3_5_PPE_USED_PROPERLY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_6_WATER_SOAP_AVAILABILITY` `SAFETY_C_3_6_WATER_SOAP_AVAILABILITY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_7_DISINFECTANT_AVAILABLE` `SAFETY_C_3_7_DISINFECTANT_AVAILABLE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_8_DISINFECTANT_LABELED_PROPERLY` `SAFETY_C_3_8_DISINFECTANT_LABELED_PROPERLY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_C_3_9_SEGREGATION_OF_WASTE` `SAFETY_C_3_9_SEGREGATION_OF_WASTE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_DISPLAY` `SAFETY_DISPLAY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_10_INFECTIOUS_WASTE_EMPTIED` `SAFETY_Q_3_10_INFECTIOUS_WASTE_EMPTIED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_1_IMPLEMENT_SAFETY_PRACTICES` `SAFETY_Q_3_1_IMPLEMENT_SAFETY_PRACTICES` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_2_ACCIDENTAL_EXPOSURE` `SAFETY_Q_3_2_ACCIDENTAL_EXPOSURE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_3_PRACTICE_SAFETY_PRACTICES` `SAFETY_Q_3_3_PRACTICE_SAFETY_PRACTICES` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_4_PPE_AVAILABILITY` `SAFETY_Q_3_4_PPE_AVAILABILITY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_5_PPE_USED_PROPERLY` `SAFETY_Q_3_5_PPE_USED_PROPERLY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_6_WATER_SOAP_AVAILABILITY` `SAFETY_Q_3_6_WATER_SOAP_AVAILABILITY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_7_DISINFECTANT_AVAILABLE` `SAFETY_Q_3_7_DISINFECTANT_AVAILABLE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_8_DISINFECTANT_LABELED_PROPERLY` `SAFETY_Q_3_8_DISINFECTANT_LABELED_PROPERLY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_Q_3_9_SEGREGATION_OF_WASTE` `SAFETY_Q_3_9_SEGREGATION_OF_WASTE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SAFETY_SCORE` `SAFETY_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `sitecode` `sitecode` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `staffaudited` `staffaudited` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `start` `start` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `status` `status` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `subscriberid` `subscriberid` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `SUMMARY_NOT_AVL` `SUMMARY_NOT_AVL` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `summarypage` `summarypage` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_1_PROCEDURES_TESTING_ALGORITHM` `TEST_C_5_1_PROCEDURES_TESTING_ALGORITHM` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_2_TIMERS_AVAILABILITY` `TEST_C_5_2_TIMERS_AVAILABILITY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_3_SAMPLE_DEVICE_ACCURACY` `TEST_C_5_3_SAMPLE_DEVICE_ACCURACY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_4_TESTING_PROCEDURE_FOLLOWED` `TEST_C_5_4_TESTING_PROCEDURE_FOLLOWED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_5_QUALITY_CONTROL` `TEST_C_5_5_QUALITY_CONTROL` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_6_QC_RESULTS_RECORDED` `TEST_C_5_6_QC_RESULTS_RECORDED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_7_INCORRECT_QC_RESULTS` `TEST_C_5_7_INCORRECT_QC_RESULTS` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_8_APPROPRIATE_STEPS_TAKEN` `TEST_C_5_8_APPROPRIATE_STEPS_TAKEN` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_C_5_9_REVIEW_QC_RECORDS` `TEST_C_5_9_REVIEW_QC_RECORDS` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_DISPLAY` `TEST_DISPLAY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_1_PROCEDURES_TESTING_ALGORITHM` `TEST_Q_5_1_PROCEDURES_TESTING_ALGORITHM` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_2_TIMERS_AVAILABILITY` `TEST_Q_5_2_TIMERS_AVAILABILITY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_3_SAMPLE_DEVICE_ACCURACY` `TEST_Q_5_3_SAMPLE_DEVICE_ACCURACY` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_4_TESTING_PROCEDURE_FOLLOWED` `TEST_Q_5_4_TESTING_PROCEDURE_FOLLOWED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_5_QUALITY_CONTROL` `TEST_Q_5_5_QUALITY_CONTROL` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_6_QC_RESULTS_RECORDED` `TEST_Q_5_6_QC_RESULTS_RECORDED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_7_INCORRECT_QC_RESULTS` `TEST_Q_5_7_INCORRECT_QC_RESULTS` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_8_APPROPRIATE_STEPS_TAKEN` `TEST_Q_5_8_APPROPRIATE_STEPS_TAKEN` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_Q_5_9_REVIEW_QC_RECORDS` `TEST_Q_5_9_REVIEW_QC_RECORDS` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `TEST_SCORE` `TEST_SCORE` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `testingpointtype` `testingpointtype` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `testingpointtype_other` `testingpointtype_other` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `text_image` `text_image` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `token` `token` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Application\\Controller\\AuditTrail', 'Audit Trail');

-- Thana 27-Feb-2023
INSERT INTO `global_config` (`config_id`, `display_name`, `global_name`, `global_value`) VALUES (NULL, 'Embed Signatures In PDF', 'embed_signatures_in_pdf', 'yes');

-- Brindha 02-May-2023
ALTER TABLE `spi_form_v_6` ADD `testingpointname` TEXT NULL DEFAULT NULL AFTER `facilityid`;

-- Brindha 12-May-2023
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\EventController', 'index', 'Access');
INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Application\\Controller\\EventController', 'Manage Event Log');

-- Brindha 18-May-2023
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV3Controller', 'view-bulk-downloads', 'View Bulk Downloads');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV5Controller', 'view-bulk-downloads', 'View Bulk Downloads');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6Controller', 'view-bulk-downloads', 'View Bulk Downloads');

-- Brindha 23-May-2023
ALTER TABLE `spi_form_v_3` ADD `central_project_id` INT NULL DEFAULT NULL AFTER `token`;
ALTER TABLE `spi_form_v_6` ADD `central_project_id` INT NULL DEFAULT NULL AFTER `uuid`;

ALTER TABLE `spi_form_v_3` ADD `central_form_id` TEXT NULL DEFAULT NULL AFTER `central_project_id`;
ALTER TABLE `spi_form_v_6` ADD `central_form_id` TEXT NULL DEFAULT NULL AFTER `central_project_id`;

-- Amit 26-May-2023
ALTER TABLE `spi_form_v_6` CHANGE `AUDIT_SCORE_PERCANTAGE_ROUNDED` `AUDIT_SCORE_PERCENTAGE_ROUNDED` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `form_dump` CHANGE `data_dump` `data_dump` JSON NULL DEFAULT NULL;


-- Amit 16-Jun-2023
ALTER TABLE `global_config` ADD UNIQUE(`global_name`);

-- Amit 07-Sep-2023
ALTER TABLE `global_config` CHANGE `global_name` `global_name` VARCHAR(199) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
DROP TABLE IF EXISTS spi_form_v_3_duplicate;
CREATE TABLE `spi_form_v_3_duplicate` SELECT * from `spi_form_v_3` WHERE 1=0;
DROP TABLE IF EXISTS spi_form_v_6_duplicate;
CREATE TABLE `spi_form_v_6_duplicate` SELECT * from `spi_form_v_6` WHERE 1=0;

-- ilahir 20-Feb-2023
UPDATE `privileges`
SET `resource_id` = CONCAT(`resource_id`, 'Controller')
WHERE `resource_id` NOT LIKE '%Controller';



-- Amit 05-Mar-2024
ALTER TABLE `spi_form_v_6` CHANGE `meta-submission-date` `meta-submission-date` DATETIME(3) NOT NULL;
ALTER TABLE `spi_form_v_3` CHANGE `meta-submission-date` `meta-submission-date` DATETIME(3) NOT NULL;

-- Brindha 25-Mar-2024
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\FacilityController', 'export-facility', 'Export Facilities');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\FacilityController', 'upload-facility', 'Upload Facilities');

-- Amit 11-Apr-2024
ALTER TABLE `spi_form_v_3` ADD `district` VARCHAR(32) NULL DEFAULT NULL AFTER `facilityid`;
ALTER TABLE `spi_form_v_6` ADD `district` VARCHAR(32) NULL DEFAULT NULL AFTER `facilityid`;

-- Amit 23-Apr-2024
ALTER TABLE `form_dump` ADD `file_path` TEXT NULL DEFAULT NULL AFTER `data_dump`;


-- ilahir 24-Apr-2024
CREATE TABLE IF NOT EXISTS `geographical_divisions` (
  `geo_id` int NOT NULL AUTO_INCREMENT,
  `geo_name` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `geo_code` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `geo_parent` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `geo_status` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_sync` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`geo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Application\\Controller\\ProvincesController', 'Manage Provinces');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\ProvincesController', 'index', 'Access');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\ProvincesController', 'add', 'Add');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\ProvincesController', 'edit', 'Edit');

INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Application\\Controller\\DistrictController', 'Manage Districts');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\DistrictController', 'index', 'Access');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\DistrictController', 'add', 'Add');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\DistrictController', 'edit', 'Edit');

-- Brindha 28-May-2024
INSERT INTO `global_config` (`config_id`, `display_name`, `global_name`, `global_value`) VALUES (NULL, 'Dashboard Map Display', 'dashboard_map_display', 'horizontal');
INSERT INTO `global_config` (`config_id`, `display_name`, `global_name`, `global_value`) VALUES (NULL, 'Dashboard Map Height', 'dashboard_map_height', '1000');

-- Brindha 30-May-2024
INSERT INTO `global_config` (`config_id`, `display_name`, `global_name`, `global_value`) VALUES (NULL, 'Dashboard Map Zoom Level', 'dashboard_map_zoomlevel', '8');

-- Sakthi 06-sep-2024
ALTER TABLE privileges ADD COLUMN privilege_id INT AUTO_INCREMENT UNIQUE FIRST;

CREATE TABLE IF NOT EXISTS roles_privileges_map (
    role_id INT NOT NULL,
    privilege_id INT NOT NULL,
    PRIMARY KEY (role_id, privilege_id)
);
-- INSERT INTO `roles_privileges_map` (`role_id`, `privilege_id`) VALUES  ('1', '1'), ('1', '2'), ('1', '3'), ('1', '4'), ('1', '5'), ('1', '6'), ('1', '7'), ('1', '8'), ('1', '9'), ('1', '10'), ('1', '11'), ('1', '12'), ('1', '13'), ('1', '14'), ('1', '15'), ('1', '16'), ('1', '17'), ('1', '18'), ('1', '19'), ('1', '20'), ('1', '21'), ('1', '22'), ('1', '23'), ('1', '24'), ('1', '25'), ('1', '26'), ('1', '27'), ('1', '28'), ('1', '29'), ('1', '30'), ('1', '31'), ('1', '32'), ('1', '33'), ('1', '34'), ('1', '35'), ('1', '36'), ('1', '37'), ('1', '38'), ('1', '39'), ('1', '40'), ('1', '41'), ('1', '42'), ('1', '43'), ('1', '44'), ('1', '45'), ('1', '46'), ('1', '47'), ('1', '48'), ('1', '49'), ('1', '50'), ('1', '51'), ('1', '52'), ('1', '53'), ('1', '54'), ('1', '55'), ('1', '56'), ('1', '57'), ('1', '58'), ('1', '59'), ('1', '60'), ('1', '61'), ('1', '62'), ('1', '63'), ('1', '64'), ('1', '65'), ('1', '66'), ('1', '67'), ('1', '68'), ('1', '69'), ('1', '70');
-- INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, 'Application\\Controller\\AuditTrailController', 'index', 'access');


-- sakthi 19-sep-2024
-- ALTER TABLE `users` ADD `template_file` VARCHAR(255) NULL AFTER `user_image`;

-- sakthi 25-sep-2024
INSERT INTO `global_config` (`config_id`, `display_name`, `global_name`, `global_value`) VALUES (NULL, 'Template File', 'template_file', NULL);

-- sakthi 30-sep-2024
INSERT INTO `global_config` (`config_id`, `display_name`, `global_name`, `global_value`) VALUES (NULL, 'Template Top Margin', 'template_top_margin', NULL);

-- Amit 06-Oct-2024
UPDATE `resources`
SET `resource_id` = CONCAT(`resource_id`, 'Controller')
WHERE `resource_id` NOT LIKE '%Controller';

-- Amit 02-Dec-2024
INSERT IGNORE INTO `roles_privileges_map` (`role_id`, `privilege_id`) SELECT '1', `privileges`.`privilege_id` FROM `privileges`;
UPDATE `privileges` SET `resource_id` = 'Application\\Controller\\UsersController' WHERE `privileges`.`resource_id` = 'ApplicationControllerUsersController' AND `privileges`.`privilege_name` = 'profile';

-- Amit 09-Dec-2024

INSERT IGNORE INTO `resources` (`resource_id`, `display_name`) VALUES
('Application\\Controller\\EmailController', 'Manage Email');

INSERT IGNORE INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES
('Application\\Controller\\EmailController', 'email-v6', 'Email Audit');

-- Brindha 10-Dec-2024
ALTER TABLE `spi_form_v_6` ADD `submitterId` INT NOT NULL AFTER `formVersion`, ADD `reviewState` VARCHAR(100) NULL DEFAULT NULL AFTER `submitterId`;

-- Brindha 11-Dec-2024
INSERT INTO `global_config` (`config_id`, `display_name`, `global_name`, `global_value`) VALUES (NULL, 'Embed Images in Audit PDF', 'embed_images_in_audit_pdf', 'no');

-- Brindha 11-Dec-2024
ALTER TABLE `spi_form_v_6` ADD `form_metadata` JSON NULL DEFAULT NULL AFTER `D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI`;
ALTER TABLE `audit_mails` ADD `audit_ids` TEXT NULL DEFAULT NULL AFTER `message`;

-- Brindha 19-Dec-2024
CREATE TABLE `user_location_map` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `location_id` int NOT NULL,
  `mapping_type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Brindha 26-Dec-2024
ALTER TABLE `spi_rt_3_facilities` ADD `country` VARCHAR(255) NULL DEFAULT NULL AFTER `province`;

INSERT IGNORE INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Application\\Controller\\SpiV6Controller', 'view-data-section-zero-protocol-v6', 'View Section D0 Data');