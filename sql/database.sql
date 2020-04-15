DROP TABLE IF EXISTS `customize`;
CREATE TABLE IF NOT EXISTS `customize` (
  `scheme_id` int(11) NOT NULL,
  `scheme_color` varchar(45) NOT NULL,
  PRIMARY KEY (`scheme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `favorite`;
CREATE TABLE IF NOT EXISTS `favorite` (
  `favorite_id` int(11) NOT NULL AUTO_INCREMENT,
  `favorite_user` int(11) NOT NULL,
  `Tweet_tweet_id` int(11) NOT NULL,
  PRIMARY KEY (`favorite_id`),
  KEY `fk_Favorite_Tweet1_idx` (`Tweet_tweet_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `follow`;
CREATE TABLE IF NOT EXISTS `follow` (
  `follow_id` int(11) NOT NULL AUTO_INCREMENT,
  `follow_user` int(11) NOT NULL,
  `user_uid` int(11) NOT NULL,
  PRIMARY KEY (`follow_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `retweet`;
CREATE TABLE IF NOT EXISTS `retweet` (
  `retweet_id` int(11) NOT NULL AUTO_INCREMENT,
  `retweet_user` int(11) NOT NULL,
  `original_tweet_id` int(11) NOT NULL,
  PRIMARY KEY (`retweet_id`),
  KEY `fk_Retweet_Tweet1_idx` (`original_tweet_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `tweets`;
CREATE TABLE IF NOT EXISTS `tweets` (
  `tweet_id` int(11) NOT NULL AUTO_INCREMENT,
  `tweet` varchar(150) NOT NULL,
  `tweet_time` datetime NOT NULL,
  `user_uid` int(11) NOT NULL,
  PRIMARY KEY (`tweet_id`),
  KEY `fk_Tweet_User_idx` (`user_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `userpass` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `display` varchar(255) NOT NULL,
  `bio` varchar(255) NULL,
  `link` varchar(255) NULL,
  `user_scheme_id` int(11) NOT NULL DEFAULT '1',
  `user_date` datetime NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `fk_User_Customize1_idx` (`user_scheme_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;