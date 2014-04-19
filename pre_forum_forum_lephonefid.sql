/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50524
Source Host           : localhost:3306
Source Database       : discuzx31

Target Server Type    : MYSQL
Target Server Version : 50524
File Encoding         : 65001

Date: 2014-04-12 02:46:17
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for pre_forum_forum_lephonefid
-- ----------------------------

DROP TABLE IF EXISTS `pre_common_member_lenovoid`;
CREATE TABLE `pre_common_member_lenovoid` (
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `lenovoid` char(15) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `lenovoid` (`lenovoid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `pre_common_member_lephoneuid`;
CREATE TABLE `pre_common_member_lephoneuid` (
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `lephoneuid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `lephoneusername` char(15) NOT NULL DEFAULT '',
  `lephoneemail` char(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `lephoneuid` (`lephoneuid`),
  UNIQUE KEY `lephoneusername` (`lephoneusername`),
  KEY `lephoneemail` (`lephoneemail`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pre_common_member_accountchange`;
CREATE TABLE `pre_common_member_accountchange` (
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username`  char(24) NOT NULL DEFAULT '',
  `email`  char(255) NOT NULL DEFAULT '',
  `rename` tinyint(1) unsigned NOT NULL DEFAULT '0', -- 是否更改用户名
  `renamed` tinyint(1) unsigned NOT NULL DEFAULT '0', -- 是否成功更改
  PRIMARY KEY (`uid`),
  UNIQUE KEY `renamed` (`rename`,`renamed`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pre_forum_forum_lephonefid`;
CREATE TABLE `pre_forum_forum_lephonefid` (
  `fid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `lephonefid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`fid`),
  UNIQUE KEY `lephonefid` (`lephonefid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pre_forum_thread_lephonetid`;
CREATE TABLE `pre_forum_thread_lephonetid` (
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `lephonetid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`),
  UNIQUE KEY `lephonetid` (`lephonetid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pre_forum_thread_lephonepid`;
CREATE TABLE `pre_forum_thread_lephonepid` (
  `pid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `lephonepid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`pid`),
  UNIQUE KEY `lephonepid` (`lephonepid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;







  

ALTER TABLE  `pre_common_member` CHANGE  `username`  `username` CHAR( 24 ) ; 
ALTER TABLE  `pre_common_card_log`    CHANGE  `username`  `username` CHAR( 24 ) ;            
ALTER TABLE  `pre_common_diy_data`    CHANGE  `username`  `username` CHAR( 24 ) ;            
ALTER TABLE  `pre_common_invite`    CHANGE  `fusername`  `fusername` CHAR( 24 ) ; 
ALTER TABLE  `pre_common_member`    CHANGE  `username`  `username` CHAR( 24 ) ;             
ALTER TABLE  `pre_common_mytask`  CHANGE  `username`  `username` CHAR( 24 ) ;              
ALTER TABLE  `pre_common_report`  CHANGE  `username`  `username` CHAR( 24 ) ;              
ALTER TABLE  `pre_common_session` CHANGE  `username`  `username` CHAR( 24 ) ;                
ALTER TABLE  `pre_forum_collection` CHANGE  `username`  `username` CHAR( 24 ) ;           
ALTER TABLE  `pre_forum_collectioncomment`  CHANGE  `username`  `username` CHAR( 24 ) ;    
ALTER TABLE  `pre_forum_collectionfollow` CHANGE  `username`  `username` CHAR( 24 ) ;     
ALTER TABLE  `pre_forum_collectionteamworker` CHANGE  `username`  `username` CHAR( 24 ) ; 
ALTER TABLE  `pre_forum_groupuser`  CHANGE  `username`  `username` CHAR( 24 ) ;            
ALTER TABLE  `pre_forum_pollvoter`  CHANGE  `username`  `username` CHAR( 24 ) ;            
ALTER TABLE  `pre_forum_promotion`  CHANGE  `username`  `username` CHAR( 24 ) ;            
ALTER TABLE  `pre_forum_ratelog`  CHANGE  `username`  `username` CHAR( 24 ) ;              
ALTER TABLE  `pre_forum_threadmod`  CHANGE  `username`  `username` CHAR( 24 ) ;            
ALTER TABLE  `pre_home_album`   CHANGE  `username`  `username` CHAR( 24 ) ;                 
ALTER TABLE  `pre_home_blog`    CHANGE  `username`  `username` CHAR( 24 ) ;                  
ALTER TABLE  `pre_home_clickuser` CHANGE  `username`  `username` CHAR( 24 ) ;             
ALTER TABLE  `pre_home_docomment` CHANGE  `username`  `username` CHAR( 24 ) ;             
ALTER TABLE  `pre_home_doing`   CHANGE  `username`  `username` CHAR( 24 ) ;                 
ALTER TABLE  `pre_home_feed`    CHANGE  `username`  `username` CHAR( 24 ) ;                  
ALTER TABLE  `pre_home_feed_app`  CHANGE  `username`  `username` CHAR( 24 ) ;              
ALTER TABLE  `pre_home_follow`    CHANGE  `username`  `username` CHAR( 24 ) ; 
ALTER TABLE  `pre_home_follow`    CHANGE  `fusername`  `fusername` CHAR( 24 ) ;                 
ALTER TABLE  `pre_home_follow_feed` CHANGE  `username`  `username` CHAR( 24 ) ;           
ALTER TABLE  `pre_home_follow_feed_archiver`  CHANGE  `username`  `username` CHAR( 24 ) ;  
ALTER TABLE  `pre_home_pic`   CHANGE  `username`  `username` CHAR( 24 ) ;                   
ALTER TABLE  `pre_home_share`   CHANGE  `username`  `username` CHAR( 24 ) ;                 
ALTER TABLE  `pre_home_show`    CHANGE  `username`  `username` CHAR( 24 ) ;     
ALTER TABLE  `pre_home_specialuser` CHANGE  `username`  `username` CHAR( 24 ) ;
ALTER TABLE  `pre_home_specialuser` CHANGE  `opusername`  `opusername` CHAR( 24 ) ;
ALTER TABLE  `pre_home_visitor` CHANGE  `vusername`  `vusername` CHAR( 24 ) ;
        
ALTER TABLE  `pre_portal_topic_pic` CHANGE  `username`  `username` CHAR( 24 ) ;           
ALTER TABLE  `pre_ucenter_admins` CHANGE  `username`  `username` CHAR( 24 ) ;             
ALTER TABLE  `pre_ucenter_feeds`  CHANGE  `username`  `username` CHAR( 24 ) ;              
ALTER TABLE  `pre_ucenter_members`  CHANGE  `username`  `username` CHAR( 24 ) ;            
ALTER TABLE  `pre_ucenter_mergemembers` CHANGE  `username`  `username` CHAR( 24 ) ;       
ALTER TABLE  `pre_ucenter_protectedmembers` CHANGE  `username`  `username` CHAR( 24 ) ; 
ALTER TABLE  `pre_common_grouppm` CHANGE  `author`  `author` CHAR( 24 ) ;       
ALTER TABLE  `pre_forum_announcement` CHANGE  `author`  `author` CHAR( 24 ) ;   
ALTER TABLE  `pre_forum_forumrecommend` CHANGE  `author`  `author` CHAR( 24 ) ; 
ALTER TABLE  `pre_forum_post` CHANGE  `author`  `author` CHAR( 24 ) ;           
ALTER TABLE  `pre_forum_postcomment`  CHANGE  `author`  `author` CHAR( 24 ) ;    
ALTER TABLE  `pre_forum_rsscache` CHANGE  `author`  `author` CHAR( 24 ) ;       
ALTER TABLE  `pre_forum_thread` CHANGE  `author`  `author` CHAR( 24 ) ;         
ALTER TABLE  `pre_forum_warning`  CHANGE  `author`  `author` CHAR( 24 ) ;        
ALTER TABLE  `pre_home_comment` CHANGE  `author`  `author` CHAR( 24 ) ;         
ALTER TABLE  `pre_home_notification`  CHANGE  `author`  `author` CHAR( 24 ) ;    
ALTER TABLE  `pre_portal_article_title` CHANGE  `author`  `author` CHAR( 24 ) ; 
ALTER TABLE  `pre_portal_rsscache`  CHANGE  `author`  `author` CHAR( 24 ) ;      
