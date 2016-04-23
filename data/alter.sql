--ilahir 13-APR-2016
ALTER TABLE  `spi_form_v_3` ADD  `status` VARCHAR( 100 ) NULL DEFAULT  'pending';


--ilahir 23-Apr-2016

CREATE TABLE IF NOT EXISTS `spi_rt_3_facilities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` varchar(255) DEFAULT NULL,
  `facility_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
