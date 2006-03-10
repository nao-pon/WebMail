# phpMyAdmin MySQL-Dump
# version 2.3.0
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
# --------------------------------------------------------


CREATE TABLE contactbook (
  uid int(11) default NULL,
  contactid int(11) NOT NULL auto_increment,
  firstname varchar(50) default NULL,
  lastname varchar(50) default NULL,
  email varchar(255) default NULL,
  company varchar(255) default NULL,
  homeaddress varchar(255) default NULL,
  city varchar(80) default NULL,
  homephone varchar(255) default NULL,
  workphone varchar(255) default NULL,
  homepage varchar(255) default NULL,
  IM varchar(255) default NULL,
  events text,
  reminders int(11) default NULL,
  notes text,
  PRIMARY KEY  (contactid),
  KEY uid (uid),
  KEY contactid (contactid)
) TYPE=MyISAM;


# --------------------------------------------------------


CREATE TABLE popsettings (
  id int(11) NOT NULL auto_increment,
  uid int(11) default NULL,
  account varchar(50) default NULL,
  popserver varchar(255) default NULL,
  port int(5) default NULL,
  uname varchar(100) default NULL,
  passwd varchar(100) default NULL,
  numshow int(11) default NULL,
  deletefromserver char(1) default NULL,
  refresh int(11) default NULL,
  timeout int(11) default NULL,
  apop int(1) default NULL,
  sname varchar(255) default NULL,
  smail varchar(255) default NULL,
  PRIMARY KEY  (id),
  KEY id (id),
  KEY uid (uid)
) TYPE=MyISAM;


# --------------------------------------------------------


CREATE TABLE wmail_sign (
  id int(11) NOT NULL auto_increment,
  uid int(11) default '0',
  signname varchar(255) default NULL,
  signature text,
  PRIMARY KEY  (id),
  KEY uid (uid)
) TYPE=MyISAM;

