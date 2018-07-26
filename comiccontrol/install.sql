SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `cc_temp_blogs` (
  `id` int(6) NOT NULL,
  `blog` int(12) NOT NULL,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `publishtime` int(16) NOT NULL,
  `commentid` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(256) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `cc_temp_blogs_tags` (
  `id` int(12) NOT NULL,
  `blog` varchar(256) NOT NULL,
  `blogid` int(12) NOT NULL,
  `tag` varchar(256) NOT NULL,
  `publishtime` int(16) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cc_temp_comics` (
  `id` int(8) NOT NULL,
  `comic` int(12) NOT NULL,
  `comichighres` varchar(256) NOT NULL,
  `comicthumb` varchar(256) NOT NULL,
  `imgname` varchar(256) NOT NULL,
  `publishtime` int(16) NOT NULL,
  `title` varchar(256) NOT NULL,
  `newstitle` varchar(256) DEFAULT NULL,
  `newscontent` text,
  `transcript` text,
  `storyline` int(8) NOT NULL,
  `commentid` varchar(256) DEFAULT NULL,
  `hovertext` varchar(512) DEFAULT NULL,
  `slug` varchar(256) NOT NULL,
  `width` int(8) NOT NULL,
  `height` int(8) NOT NULL,
  `mime` varchar(128) NOT NULL,
  `contentwarning` text,
  `altnext` varchar(256) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cc_temp_comics_storyline` (
  `id` int(8) NOT NULL,
  `name` varchar(256) NOT NULL,
  `sorder` int(8) NOT NULL,
  `comic` int(12) NOT NULL,
  `parent` int(12) NOT NULL,
  `level` int(8) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cc_temp_comics_tags` (
  `id` int(12) NOT NULL,
  `comic` varchar(256) NOT NULL,
  `comicid` int(12) NOT NULL,
  `tag` varchar(256) NOT NULL,
  `publishtime` int(16) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cc_temp_galleries` (
  `id` int(8) NOT NULL,
  `gallery` int(12) NOT NULL,
  `imgname` varchar(256) NOT NULL,
  `thumbname` varchar(256) NOT NULL,
  `caption` text NOT NULL,
  `porder` int(8) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cc_temp_htaccess` (
  `id` int(8) NOT NULL,
  `content` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cc_temp_images` (
  `id` int(8) NOT NULL,
  `imgname` varchar(256) NOT NULL,
  `thumbname` varchar(256) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cc_temp_languages` (
  `id` int(8) NOT NULL,
  `shortname` varchar(16) NOT NULL,
  `language` varchar(32) NOT NULL,
  `scope` varchar(8) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `cc_temp_languages` (`id`, `shortname`, `language`, `scope`) VALUES
(1, 'en', 'English', 'admin'),
(2, 'en', 'English', 'user');

CREATE TABLE `cc_temp_modules` (
  `id` int(8) NOT NULL,
  `title` varchar(256) NOT NULL,
  `moduletype` varchar(256) NOT NULL,
  `slug` varchar(128) NOT NULL,
  `language` varchar(128) NOT NULL,
  `template` varchar(128) NOT NULL,
  `description` varchar(256) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cc_temp_modules_options` (
  `id` int(8) NOT NULL,
  `moduleid` int(8) NOT NULL,
  `optionname` varchar(128) NOT NULL,
  `value` varchar(128) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cc_temp_options` (
  `id` int(12) NOT NULL,
  `optionname` varchar(64) NOT NULL,
  `optionvalue` varchar(256) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cc_temp_plugins` (
  `id` int(8) NOT NULL,
  `name` varchar(128) NOT NULL,
  `filepath` varchar(256) NOT NULL,
  `slug` varchar(256) DEFAULT NULL,
  `description` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cc_temp_sessions` (
  `id` int(16) NOT NULL,
  `userid` int(8) NOT NULL,
  `loginhash` varchar(64) NOT NULL,
  `loginexpire` int(16) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cc_temp_text` (
  `id` int(8) NOT NULL,
  `content` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cc_temp_users` (
  `id` int(8) NOT NULL,
  `username` varchar(256) NOT NULL,
  `password` varchar(256) NOT NULL,
  `email` varchar(256) NOT NULL,
  `salt` varchar(16) NOT NULL,
  `resethash` varchar(32) DEFAULT NULL,
  `resetsalt` varchar(16) DEFAULT NULL,
  `authlevel` int(2) NOT NULL,
  `avatar` varchar(128) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE `cc_temp_blogs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cc_temp_blogs_tags`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cc_temp_comics`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cc_temp_comics_storyline`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cc_temp_comics_tags`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cc_temp_galleries`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cc_temp_htaccess`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cc_temp_images`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cc_temp_languages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cc_temp_modules`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cc_temp_modules_options`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cc_temp_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `optionname` (`optionname`),
  ADD UNIQUE KEY `optionname_2` (`optionname`);

ALTER TABLE `cc_temp_plugins`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cc_temp_sessions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cc_temp_text`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cc_temp_users`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `cc_temp_blogs`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_blogs_tags`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_comics`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_comics_storyline`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_comics_tags`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_galleries`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_htaccess`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_images`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_languages`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_modules`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_modules_options`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_options`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_plugins`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_sessions`
  MODIFY `id` int(16) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_text`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cc_temp_users`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;COMMIT;
  
  
INSERT INTO `cc_temp_htaccess` (`id`, `content`) VALUES
(1, ''),
(2, ''),
(3, '# disable directory browsing\r\nOptions -Indexes\r\n\r\n# Begin ComicControl mod rewrite\r\n<IfModule mod_rewrite.c>\r\nRewriteEngine On\r\nRewriteBase /\r\nRewriteRule ^index\\.php$ - [L]\r\nRewriteCond %{REQUEST_FILENAME} !-f\r\nRewriteCond %{REQUEST_FILENAME} !-d\r\n</IfModule>');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
