set quoted_identifier on
go

-- phpMyAdmin MySQL-Dump
-- version 2.5.1
-- http://www.phpmyadmin.net/ (download page)
--
-- Host: localhost
-- Generation Time: Jul 13, 2003 at 02:09 AM
-- Server version: 4.0.13
-- PHP Version: 4.2.3
-- Database : `tikiwiki`
-- --------------------------------------------------------

--
-- Table structure for table `galaxia_activities`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "galaxia_activities"
go



CREATE TABLE "galaxia_activities" (
activityId numeric(14 ,0) identity,
  "name" varchar(80) default NULL NULL,
  "normalized_name" varchar(80) default NULL NULL,
  "pId" numeric(14,0) default '0' NOT NULL,
  "type" varchar(12) default NULL NULL CHECK ("type" IN ('start','end','split','switch','join','activity','standalone')),
  "isAutoRouted" char(1) default NULL NULL,
  "flowNum" numeric(10,0) default NULL NULL,
  "isInteractive" char(1) default NULL NULL,
  "lastModif" numeric(14,0) default NULL NULL,
  "description" text default '',
  PRIMARY KEY ("activityId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `galaxia_activity_roles`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "galaxia_activity_roles"
go



CREATE TABLE "galaxia_activity_roles" (
  "activityId" numeric(14,0) default '0' NOT NULL,
  "roleId" numeric(14,0) default '0' NOT NULL,
  PRIMARY KEY ("activityId","roleId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `galaxia_instance_activities`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "galaxia_instance_activities"
go



CREATE TABLE "galaxia_instance_activities" (
  "instanceId" numeric(14,0) default '0' NOT NULL,
  "activityId" numeric(14,0) default '0' NOT NULL,
  "started" numeric(14,0) default '0' NOT NULL,
  "ended" numeric(14,0) default '0' NOT NULL,
  "user" varchar(200) default NULL NULL,
  "status" varchar(11) default NULL NULL CHECK ("status" IN ('running','completed')),
  PRIMARY KEY ("instanceId","activityId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `galaxia_instance_comments`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "galaxia_instance_comments"
go



CREATE TABLE "galaxia_instance_comments" (
cId numeric(14 ,0) identity,
  "instanceId" numeric(14,0) default '0' NOT NULL,
  "user" varchar(200) default NULL NULL,
  "activityId" numeric(14,0) default NULL NULL,
  "hash" varchar(32) default NULL NULL,
  "title" varchar(250) default NULL NULL,
  "comment" text default '',
  "activity" varchar(80) default NULL NULL,
  "timestamp" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("cId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `galaxia_instances`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "galaxia_instances"
go



CREATE TABLE "galaxia_instances" (
instanceId numeric(14 ,0) identity,
  "pId" numeric(14,0) default '0' NOT NULL,
  "started" numeric(14,0) default NULL NULL,
  "owner" varchar(200) default NULL NULL,
  "nextActivity" numeric(14,0) default NULL NULL,
  "nextUser" varchar(200) default NULL NULL,
  "ended" numeric(14,0) default NULL NULL,
  "status" varchar(11) default NULL NULL CHECK ("status" IN ('active','exception','aborted','completed')),
  "properties" image default '',
  PRIMARY KEY ("instanceId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `galaxia_processes`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "galaxia_processes"
go



CREATE TABLE "galaxia_processes" (
pId numeric(14 ,0) identity,
  "name" varchar(80) default NULL NULL,
  "isValid" char(1) default NULL NULL,
  "isActive" char(1) default NULL NULL,
  "version" varchar(12) default NULL NULL,
  "description" text default '',
  "lastModif" numeric(14,0) default NULL NULL,
  "normalized_name" varchar(80) default NULL NULL,
  PRIMARY KEY ("pId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `galaxia_roles`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "galaxia_roles"
go



CREATE TABLE "galaxia_roles" (
roleId numeric(14 ,0) identity,
  "pId" numeric(14,0) default '0' NOT NULL,
  "lastModif" numeric(14,0) default NULL NULL,
  "name" varchar(80) default NULL NULL,
  "description" text default '',
  PRIMARY KEY ("roleId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `galaxia_transitions`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "galaxia_transitions"
go



CREATE TABLE "galaxia_transitions" (
  "pId" numeric(14,0) default '0' NOT NULL,
  "actFromId" numeric(14,0) default '0' NOT NULL,
  "actToId" numeric(14,0) default '0' NOT NULL,
  PRIMARY KEY ("actFromId","actToId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `galaxia_user_roles`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "galaxia_user_roles"
go



CREATE TABLE "galaxia_user_roles" (
  "pId" numeric(14,0) default '0' NOT NULL,
roleId numeric(14 ,0) identity,
  "user" varchar(200) default '' NOT NULL,
  PRIMARY KEY ("roleId","user")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `galaxia_workitems`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "galaxia_workitems"
go



CREATE TABLE "galaxia_workitems" (
itemId numeric(14 ,0) identity,
  "instanceId" numeric(14,0) default '0' NOT NULL,
  "orderId" numeric(14,0) default '0' NOT NULL,
  "activityId" numeric(14,0) default '0' NOT NULL,
  "properties" image default '',
  "started" numeric(14,0) default NULL NULL,
  "ended" numeric(14,0) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  PRIMARY KEY ("itemId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `messu_messages`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 08:29 PM
--

DROP TABLE "messu_messages"
go



CREATE TABLE "messu_messages" (
msgId numeric(14 ,0) identity,
  "user" varchar(200) default '' NOT NULL,
  "user_from" varchar(200) default '' NOT NULL,
  "user_to" text default '',
  "user_cc" text default '',
  "user_bcc" text default '',
  "subject" varchar(255) default NULL NULL,
  "body" text default '',
  "hash" varchar(32) default NULL NULL,
  "date" numeric(14,0) default NULL NULL,
  "isRead" char(1) default NULL NULL,
  "isReplied" char(1) default NULL NULL,
  "isFlagged" char(1) default NULL NULL,
  "priority" numeric(2,0) default NULL NULL,
  PRIMARY KEY ("msgId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_actionlog`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 12:29 AM
--

DROP TABLE "tiki_actionlog"
go



CREATE TABLE "tiki_actionlog" (
  "action" varchar(255) default '' NOT NULL,
  "lastModif" numeric(14,0) default NULL NULL,
  "pageName" varchar(200) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  "ip" varchar(15) default NULL NULL,
  "comment" varchar(200) default NULL
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_articles`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 01:30 AM
-- Last check: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_articles"
go



CREATE TABLE "tiki_articles" (
articleId numeric(8 ,0) identity,
  "title" varchar(80) default NULL NULL,
  "authorName" varchar(60) default NULL NULL,
  "topicId" numeric(14,0) default NULL NULL,
  "topicName" varchar(40) default NULL NULL,
  "size" numeric(12,0) default NULL NULL,
  "useImage" char(1) default NULL NULL,
  "image_name" varchar(80) default NULL NULL,
  "image_type" varchar(80) default NULL NULL,
  "image_size" numeric(14,0) default NULL NULL,
  "image_x" numeric(4,0) default NULL NULL,
  "image_y" numeric(4,0) default NULL NULL,
  "image_data" image default '',
  "publishDate" numeric(14,0) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  "heading" text default '',
  "body" text default '',
  "hash" varchar(32) default NULL NULL,
  "author" varchar(200) default NULL NULL,
  "reads" numeric(14,0) default NULL NULL,
  "votes" numeric(8,0) default NULL NULL,
  "points" numeric(14,0) default NULL NULL,
  "type" varchar(50) default NULL NULL,
  "rating" decimal(3,2) default NULL NULL,
  "isfloat" char(1) default NULL NULL,
  PRIMARY KEY ("articleId")





)   
go


CREATE  INDEX "tiki_articles_title" ON "tiki_articles"("title")
go
CREATE  INDEX "tiki_articles_heading" ON "tiki_articles"("heading")
go
CREATE  INDEX "tiki_articles_body" ON "tiki_articles"("body")
go
CREATE  INDEX "tiki_articles_reads" ON "tiki_articles"("reads")
go
CREATE  INDEX "tiki_articles_ft" ON "tiki_articles"("title","heading","body")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_banners`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_banners"
go



CREATE TABLE "tiki_banners" (
bannerId numeric(12 ,0) identity,
  "client" varchar(200) default '' NOT NULL,
  "url" varchar(255) default NULL NULL,
  "title" varchar(255) default NULL NULL,
  "alt" varchar(250) default NULL NULL,
  "which" varchar(50) default NULL NULL,
  "imageData" image default '',
  "imageType" varchar(200) default NULL NULL,
  "imageName" varchar(100) default NULL NULL,
  "HTMLData" text default '',
  "fixedURLData" varchar(255) default NULL NULL,
  "textData" text default '',
  "fromDate" numeric(14,0) default NULL NULL,
  "toDate" numeric(14,0) default NULL NULL,
  "useDates" char(1) default NULL NULL,
  "mon" char(1) default NULL NULL,
  "tue" char(1) default NULL NULL,
  "wed" char(1) default NULL NULL,
  "thu" char(1) default NULL NULL,
  "fri" char(1) default NULL NULL,
  "sat" char(1) default NULL NULL,
  "sun" char(1) default NULL NULL,
  "hourFrom" varchar(4) default NULL NULL,
  "hourTo" varchar(4) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  "maxImpressions" numeric(8,0) default NULL NULL,
  "impressions" numeric(8,0) default NULL NULL,
  "clicks" numeric(8,0) default NULL NULL,
  "zone" varchar(40) default NULL NULL,
  PRIMARY KEY ("bannerId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_banning`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_banning"
go



CREATE TABLE "tiki_banning" (
banId numeric(12 ,0) identity,
  "mode" varchar(6) default NULL NULL CHECK ("mode" IN ('user','ip')),
  "title" varchar(200) default NULL NULL,
  "ip1" char(3) default NULL NULL,
  "ip2" char(3) default NULL NULL,
  "ip3" char(3) default NULL NULL,
  "ip4" char(3) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  "date_from" timestamp NOT NULL,
  "date_to" timestamp NOT NULL,
  "use_dates" char(1) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  "message" text default '',
  PRIMARY KEY ("banId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_banning_sections`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_banning_sections"
go



CREATE TABLE "tiki_banning_sections" (
  "banId" numeric(12,0) default '0' NOT NULL,
  "section" varchar(100) default '' NOT NULL,
  PRIMARY KEY ("banId","section")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_blog_activity`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 04:52 PM
--

DROP TABLE "tiki_blog_activity"
go



CREATE TABLE "tiki_blog_activity" (
  "blogId" numeric(8,0) default '0' NOT NULL,
  "day" numeric(14,0) default '0' NOT NULL,
  "posts" numeric(8,0) default NULL NULL,
  PRIMARY KEY ("blogId","day")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_blog_posts`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 04:52 PM
-- Last check: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_blog_posts"
go



CREATE TABLE "tiki_blog_posts" (
postId numeric(8 ,0) identity,
  "blogId" numeric(8,0) default '0' NOT NULL,
  "data" text default '',
  "created" numeric(14,0) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  "trackbacks_to" text default '',
  "trackbacks_from" text default '',
  "title" varchar(80) default NULL NULL,
  PRIMARY KEY ("postId")




)   
go


CREATE  INDEX "tiki_blog_posts_data" ON "tiki_blog_posts"("data")
go
CREATE  INDEX "tiki_blog_posts_blogId" ON "tiki_blog_posts"("blogId")
go
CREATE  INDEX "tiki_blog_posts_created" ON "tiki_blog_posts"("created")
go
CREATE  INDEX "tiki_blog_posts_ft" ON "tiki_blog_posts"("data")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_blog_posts_images`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_blog_posts_images"
go



CREATE TABLE "tiki_blog_posts_images" (
imgId numeric(14 ,0) identity,
  "postId" numeric(14,0) default '0' NOT NULL,
  "filename" varchar(80) default NULL NULL,
  "filetype" varchar(80) default NULL NULL,
  "filesize" numeric(14,0) default NULL NULL,
  "data" image default '',
  PRIMARY KEY ("imgId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_blogs`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 01:07 AM
-- Last check: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_blogs"
go



CREATE TABLE "tiki_blogs" (
blogId numeric(8 ,0) identity,
  "created" numeric(14,0) default NULL NULL,
  "lastModif" numeric(14,0) default NULL NULL,
  "title" varchar(200) default NULL NULL,
  "description" text default '',
  "user" varchar(200) default NULL NULL,
  "public" char(1) default NULL NULL,
  "posts" numeric(8,0) default NULL NULL,
  "maxPosts" numeric(8,0) default NULL NULL,
  "hits" numeric(8,0) default NULL NULL,
  "activity" decimal(4,2) default NULL NULL,
  "heading" text default '',
  "use_find" char(1) default NULL NULL,
  "use_title" char(1) default NULL NULL,
  "add_date" char(1) default NULL NULL,
  "add_poster" char(1) default NULL NULL,
  "allow_comments" char(1) default NULL NULL,
  PRIMARY KEY ("blogId")




)   
go


CREATE  INDEX "tiki_blogs_title" ON "tiki_blogs"("title")
go
CREATE  INDEX "tiki_blogs_description" ON "tiki_blogs"("description")
go
CREATE  INDEX "tiki_blogs_hits" ON "tiki_blogs"("hits")
go
CREATE  INDEX "tiki_blogs_ft" ON "tiki_blogs"("title","description")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_calendar_categories`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 07:05 AM
--

DROP TABLE "tiki_calendar_categories"
go



CREATE TABLE "tiki_calendar_categories" (
calcatId numeric(11 ,0) identity,
  "calendarId" numeric(14,0) default '0' NOT NULL,
  "name" varchar(255) default '' NOT NULL,
  PRIMARY KEY ("calcatId")

)   
go


CREATE UNIQUE INDEX "tiki_calendar_categories_catname" ON "tiki_calendar_categories"("calendarId","name")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_calendar_items`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 07:43 AM
--

DROP TABLE "tiki_calendar_items"
go



CREATE TABLE "tiki_calendar_items" (
calitemId numeric(14 ,0) identity,
  "calendarId" numeric(14,0) default '0' NOT NULL,
  "start" numeric(14,0) default '0' NOT NULL,
  "end" numeric(14,0) default '0' NOT NULL,
  "locationId" numeric(14,0) default NULL NULL,
  "categoryId" numeric(14,0) default NULL NULL,
  "priority" varchar(3) default '1' NOT NULL CHECK ("priority" IN ('1','2','3','4','5','6','7','8','9')),
  "status" varchar(3) default '0' NOT NULL CHECK ("status" IN ('0','1','2')),
  "url" varchar(255) default NULL NULL,
  "lang" char(2) default 'en' NOT NULL,
  "name" varchar(255) default '' NOT NULL,
  "description" image default '',
  "user" varchar(40) default NULL NULL,
  "created" numeric(14,0) default '0' NOT NULL,
  "lastmodif" numeric(14,0) default '0' NOT NULL,
  PRIMARY KEY ("calitemId")

)   
go


CREATE  INDEX "tiki_calendar_items_calendarId" ON "tiki_calendar_items"("calendarId")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_calendar_locations`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 07:05 AM
--

DROP TABLE "tiki_calendar_locations"
go



CREATE TABLE "tiki_calendar_locations" (
callocId numeric(14 ,0) identity,
  "calendarId" numeric(14,0) default '0' NOT NULL,
  "name" varchar(255) default '' NOT NULL,
  "description" image default '',
  PRIMARY KEY ("callocId")

)   
go


CREATE UNIQUE INDEX "tiki_calendar_locations_locname" ON "tiki_calendar_locations"("calendarId","name")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_calendar_roles`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_calendar_roles"
go



CREATE TABLE "tiki_calendar_roles" (
  "calitemId" numeric(14,0) default '0' NOT NULL,
  "username" varchar(40) default '' NOT NULL,
  "role" varchar(3) default '0' NOT NULL CHECK ("role" IN ('0','1','2','3','6')),
  PRIMARY KEY ("calitemId","username","role")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_calendars`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 05, 2003 at 02:03 PM
--

DROP TABLE "tiki_calendars"
go



CREATE TABLE "tiki_calendars" (
calendarId numeric(14 ,0) identity,
  "name" varchar(80) default '' NOT NULL,
  "description" varchar(255) default NULL NULL,
  "user" varchar(40) default '' NOT NULL,
  "customlocations" varchar(3) default 'n' NOT NULL CHECK ("customlocations" IN ('n','y')),
  "customcategories" varchar(3) default 'n' NOT NULL CHECK ("customcategories" IN ('n','y')),
  "customlanguages" varchar(3) default 'n' NOT NULL CHECK ("customlanguages" IN ('n','y')),
  "custompriorities" varchar(3) default 'n' NOT NULL CHECK ("custompriorities" IN ('n','y')),
  "customparticipants" varchar(3) default 'n' NOT NULL CHECK ("customparticipants" IN ('n','y')),
  "created" numeric(14,0) default '0' NOT NULL,
  "lastmodif" numeric(14,0) default '0' NOT NULL,
  PRIMARY KEY ("calendarId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_categories`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 04, 2003 at 09:47 PM
--

DROP TABLE "tiki_categories"
go



CREATE TABLE "tiki_categories" (
categId numeric(12 ,0) identity,
  "name" varchar(100) default NULL NULL,
  "description" varchar(250) default NULL NULL,
  "parentId" numeric(12,0) default NULL NULL,
  "hits" numeric(8,0) default NULL NULL,
  PRIMARY KEY ("categId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_categorized_objects`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 01:09 AM
--

DROP TABLE "tiki_categorized_objects"
go



CREATE TABLE "tiki_categorized_objects" (
catObjectId numeric(12 ,0) identity,
  "type" varchar(50) default NULL NULL,
  "objId" varchar(255) default NULL NULL,
  "description" text default '',
  "created" numeric(14,0) default NULL NULL,
  "name" varchar(200) default NULL NULL,
  "href" varchar(200) default NULL NULL,
  "hits" numeric(8,0) default NULL NULL,
  PRIMARY KEY ("catObjectId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_category_objects`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 01:09 AM
--

DROP TABLE "tiki_category_objects"
go



CREATE TABLE "tiki_category_objects" (
  "catObjectId" numeric(12,0) default '0' NOT NULL,
  "categId" numeric(12,0) default '0' NOT NULL,
  PRIMARY KEY ("catObjectId","categId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_category_sites`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 07, 2003 at 01:53 AM
--

DROP TABLE "tiki_category_sites"
go



CREATE TABLE "tiki_category_sites" (
  "categId" numeric(10,0) default '0' NOT NULL,
  "siteId" numeric(14,0) default '0' NOT NULL,
  PRIMARY KEY ("categId","siteId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_chart_items`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_chart_items"
go



CREATE TABLE "tiki_chart_items" (
itemId numeric(14 ,0) identity,
  "title" varchar(250) default NULL NULL,
  "description" text default '',
  "chartId" numeric(14,0) default '0' NOT NULL,
  "created" numeric(14,0) default NULL NULL,
  "URL" varchar(250) default NULL NULL,
  "votes" numeric(14,0) default NULL NULL,
  "points" numeric(14,0) default NULL NULL,
  "average" decimal(4,2) default NULL NULL,
  PRIMARY KEY ("itemId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_charts`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 06, 2003 at 08:14 AM
--

DROP TABLE "tiki_charts"
go



CREATE TABLE "tiki_charts" (
chartId numeric(14 ,0) identity,
  "title" varchar(250) default NULL NULL,
  "description" text default '',
  "hits" numeric(14,0) default NULL NULL,
  "singleItemVotes" char(1) default NULL NULL,
  "singleChartVotes" char(1) default NULL NULL,
  "suggestions" char(1) default NULL NULL,
  "autoValidate" char(1) default NULL NULL,
  "topN" numeric(6,0) default NULL NULL,
  "maxVoteValue" numeric(4,0) default NULL NULL,
  "frequency" numeric(14,0) default NULL NULL,
  "showAverage" char(1) default NULL NULL,
  "isActive" char(1) default NULL NULL,
  "showVotes" char(1) default NULL NULL,
  "useCookies" char(1) default NULL NULL,
  "lastChart" numeric(14,0) default NULL NULL,
  "voteAgainAfter" numeric(14,0) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  "hist" numeric(12,0) default NULL NULL,
  PRIMARY KEY ("chartId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_charts_rankings`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_charts_rankings"
go



CREATE TABLE "tiki_charts_rankings" (
  "chartId" numeric(14,0) default '0' NOT NULL,
  "itemId" numeric(14,0) default '0' NOT NULL,
  "position" numeric(14,0) default '0' NOT NULL,
  "timestamp" numeric(14,0) default '0' NOT NULL,
  "lastPosition" numeric(14,0) default '0' NOT NULL,
  "period" numeric(14,0) default '0' NOT NULL,
  "rvotes" numeric(14,0) default '0' NOT NULL,
  "raverage" decimal(4,2) default '0.00' NOT NULL,
  PRIMARY KEY ("chartId","itemId","period")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_charts_votes`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_charts_votes"
go



CREATE TABLE "tiki_charts_votes" (
  "user" varchar(200) default '' NOT NULL,
  "itemId" numeric(14,0) default '0' NOT NULL,
  "timestamp" numeric(14,0) default NULL NULL,
  "chartId" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("user","itemId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_chat_channels`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_chat_channels"
go



CREATE TABLE "tiki_chat_channels" (
channelId numeric(8 ,0) identity,
  "name" varchar(30) default NULL NULL,
  "description" varchar(250) default NULL NULL,
  "max_users" numeric(8,0) default NULL NULL,
  "mode" char(1) default NULL NULL,
  "moderator" varchar(200) default NULL NULL,
  "active" char(1) default NULL NULL,
  "refresh" numeric(6,0) default NULL NULL,
  PRIMARY KEY ("channelId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_chat_messages`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_chat_messages"
go



CREATE TABLE "tiki_chat_messages" (
messageId numeric(8 ,0) identity,
  "channelId" numeric(8,0) default '0' NOT NULL,
  "data" varchar(255) default NULL NULL,
  "poster" varchar(200) default 'anonymous' NOT NULL,
  "timestamp" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("messageId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_chat_users`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_chat_users"
go



CREATE TABLE "tiki_chat_users" (
  "nickname" varchar(200) default '' NOT NULL,
  "channelId" numeric(8,0) default '0' NOT NULL,
  "timestamp" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("nickname","channelId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_comments`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 10:56 PM
-- Last check: Jul 11, 2003 at 01:52 AM
--

DROP TABLE "tiki_comments"
go



CREATE TABLE "tiki_comments" (
threadId numeric(14 ,0) identity,
  "object" varchar(32) default '' NOT NULL,
  "parentId" numeric(14,0) default NULL NULL,
  "userName" varchar(200) default NULL NULL,
  "commentDate" numeric(14,0) default NULL NULL,
  "hits" numeric(8,0) default NULL NULL,
  "type" char(1) default NULL NULL,
  "points" decimal(8,2) default NULL NULL,
  "votes" numeric(8,0) default NULL NULL,
  "average" decimal(8,4) default NULL NULL,
  "title" varchar(100) default NULL NULL,
  "data" text default '',
  "hash" varchar(32) default NULL NULL,
  "user_ip" varchar(15) default NULL NULL,
  "summary" varchar(240) default NULL NULL,
  "smiley" varchar(80) default NULL NULL,
  PRIMARY KEY ("threadId")






)   
go


CREATE  INDEX "tiki_comments_title" ON "tiki_comments"("title")
go
CREATE  INDEX "tiki_comments_data" ON "tiki_comments"("data")
go
CREATE  INDEX "tiki_comments_object" ON "tiki_comments"("object")
go
CREATE  INDEX "tiki_comments_hits" ON "tiki_comments"("hits")
go
CREATE  INDEX "tiki_comments_tc_pi" ON "tiki_comments"("parentId")
go
CREATE  INDEX "tiki_comments_ft" ON "tiki_comments"("title","data")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_content`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_content"
go



CREATE TABLE "tiki_content" (
contentId numeric(8 ,0) identity,
  "description" text default '',
  PRIMARY KEY ("contentId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_content_templates`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 12:37 AM
--

DROP TABLE "tiki_content_templates"
go



CREATE TABLE "tiki_content_templates" (
templateId numeric(10 ,0) identity,
  "content" image default '',
  "name" varchar(200) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("templateId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_content_templates_sections`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 12:37 AM
--

DROP TABLE "tiki_content_templates_sections"
go



CREATE TABLE "tiki_content_templates_sections" (
  "templateId" numeric(10,0) default '0' NOT NULL,
  "section" varchar(250) default '' NOT NULL,
  PRIMARY KEY ("templateId","section")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_cookies`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 10, 2003 at 04:00 AM
--

DROP TABLE "tiki_cookies"
go



CREATE TABLE "tiki_cookies" (
cookieId numeric(10 ,0) identity,
  "cookie" varchar(255) default NULL NULL,
  PRIMARY KEY ("cookieId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_copyrights`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_copyrights"
go



CREATE TABLE "tiki_copyrights" (
copyrightId numeric(12 ,0) identity,
  "page" varchar(200) default NULL NULL,
  "title" varchar(200) default NULL NULL,
  "year" numeric(11,0) default NULL NULL,
  "authors" varchar(200) default NULL NULL,
  "copyright_order" numeric(11,0) default NULL NULL,
  "userName" varchar(200) default NULL NULL,
  PRIMARY KEY ("copyrightId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_directory_categories`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 08:59 PM
--

DROP TABLE "tiki_directory_categories"
go



CREATE TABLE "tiki_directory_categories" (
categId numeric(10 ,0) identity,
  "parent" numeric(10,0) default NULL NULL,
  "name" varchar(240) default NULL NULL,
  "description" text default '',
  "childrenType" char(1) default NULL NULL,
  "sites" numeric(10,0) default NULL NULL,
  "viewableChildren" numeric(4,0) default NULL NULL,
  "allowSites" char(1) default NULL NULL,
  "showCount" char(1) default NULL NULL,
  "editorGroup" varchar(200) default NULL NULL,
  "hits" numeric(12,0) default NULL NULL,
  PRIMARY KEY ("categId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_directory_search`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_directory_search"
go



CREATE TABLE "tiki_directory_search" (
  "term" varchar(250) default '' NOT NULL,
  "hits" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("term")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_directory_sites`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 07:32 PM
--

DROP TABLE "tiki_directory_sites"
go



CREATE TABLE "tiki_directory_sites" (
siteId numeric(14 ,0) identity,
  "name" varchar(240) default NULL NULL,
  "description" text default '',
  "url" varchar(255) default NULL NULL,
  "country" varchar(255) default NULL NULL,
  "hits" numeric(12,0) default NULL NULL,
  "isValid" char(1) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  "lastModif" numeric(14,0) default NULL NULL,
  "cache" image default '',
  "cache_timestamp" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("siteId")

)   
go


CREATE  INDEX "tiki_directory_sites_ft" ON "tiki_directory_sites"("name","description")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_drawings`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 08, 2003 at 05:02 AM
--

DROP TABLE "tiki_drawings"
go



CREATE TABLE "tiki_drawings" (
drawId numeric(12 ,0) identity,
  "version" numeric(8,0) default NULL NULL,
  "name" varchar(250) default NULL NULL,
  "filename_draw" varchar(250) default NULL NULL,
  "filename_pad" varchar(250) default NULL NULL,
  "timestamp" numeric(14,0) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  PRIMARY KEY ("drawId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_dsn`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_dsn"
go



CREATE TABLE "tiki_dsn" (
dsnId numeric(12 ,0) identity,
  "name" varchar(200) default '' NOT NULL,
  "dsn" varchar(255) default NULL NULL,
  PRIMARY KEY ("dsnId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_eph`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 06, 2003 at 08:23 AM
--

DROP TABLE "tiki_eph"
go



CREATE TABLE "tiki_eph" (
ephId numeric(12 ,0) identity,
  "title" varchar(250) default NULL NULL,
  "isFile" char(1) default NULL NULL,
  "filename" varchar(250) default NULL NULL,
  "filetype" varchar(250) default NULL NULL,
  "filesize" varchar(250) default NULL NULL,
  "data" image default '',
  "textdata" image default '',
  "publish" numeric(14,0) default NULL NULL,
  "hits" numeric(10,0) default NULL NULL,
  PRIMARY KEY ("ephId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_extwiki`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_extwiki"
go



CREATE TABLE "tiki_extwiki" (
extwikiId numeric(12 ,0) identity,
  "name" varchar(200) default '' NOT NULL,
  "extwiki" varchar(255) default NULL NULL,
  PRIMARY KEY ("extwikiId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_faq_questions`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
-- Last check: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_faq_questions"
go



CREATE TABLE "tiki_faq_questions" (
questionId numeric(10 ,0) identity,
  "faqId" numeric(10,0) default NULL NULL,
  "position" numeric(4,0) default NULL NULL,
  "question" text default '',
  "answer" text default '',
  PRIMARY KEY ("questionId")




)   
go


CREATE  INDEX "tiki_faq_questions_faqId" ON "tiki_faq_questions"("faqId")
go
CREATE  INDEX "tiki_faq_questions_question" ON "tiki_faq_questions"("question")
go
CREATE  INDEX "tiki_faq_questions_answer" ON "tiki_faq_questions"("answer")
go
CREATE  INDEX "tiki_faq_questions_ft" ON "tiki_faq_questions"("question","answer")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_faqs`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 09:09 PM
-- Last check: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_faqs"
go



CREATE TABLE "tiki_faqs" (
faqId numeric(10 ,0) identity,
  "title" varchar(200) default NULL NULL,
  "description" text default '',
  "created" numeric(14,0) default NULL NULL,
  "questions" numeric(5,0) default NULL NULL,
  "hits" numeric(8,0) default NULL NULL,
  "canSuggest" char(1) default NULL NULL,
  PRIMARY KEY ("faqId")




)   
go


CREATE  INDEX "tiki_faqs_title" ON "tiki_faqs"("title")
go
CREATE  INDEX "tiki_faqs_description" ON "tiki_faqs"("description")
go
CREATE  INDEX "tiki_faqs_hits" ON "tiki_faqs"("hits")
go
CREATE  INDEX "tiki_faqs_ft" ON "tiki_faqs"("title","description")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_featured_links`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 11:08 PM
--

DROP TABLE "tiki_featured_links"
go



CREATE TABLE "tiki_featured_links" (
  "url" varchar(200) default '' NOT NULL,
  "title" varchar(200) default NULL NULL,
  "description" text default '',
  "hits" numeric(8,0) default NULL NULL,
  "position" numeric(6,0) default NULL NULL,
  "type" char(1) default NULL NULL,
  PRIMARY KEY ("url")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_file_galleries`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 01:13 AM
--

DROP TABLE "tiki_file_galleries"
go



CREATE TABLE "tiki_file_galleries" (
galleryId numeric(14 ,0) identity,
  "name" varchar(80) default '' NOT NULL,
  "description" text default '',
  "created" numeric(14,0) default NULL NULL,
  "visible" char(1) default NULL NULL,
  "lastModif" numeric(14,0) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  "hits" numeric(14,0) default NULL NULL,
  "votes" numeric(8,0) default NULL NULL,
  "points" decimal(8,2) default NULL NULL,
  "maxRows" numeric(10,0) default NULL NULL,
  "public" char(1) default NULL NULL,
  "show_id" char(1) default NULL NULL,
  "show_icon" char(1) default NULL NULL,
  "show_name" char(1) default NULL NULL,
  "show_size" char(1) default NULL NULL,
  "show_description" char(1) default NULL NULL,
  "max_desc" numeric(8,0) default NULL NULL,
  "show_created" char(1) default NULL NULL,
  "show_dl" char(1) default NULL NULL,
  PRIMARY KEY ("galleryId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_files`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 01:13 AM
-- Last check: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_files"
go



CREATE TABLE "tiki_files" (
fileId numeric(14 ,0) identity,
  "galleryId" numeric(14,0) default '0' NOT NULL,
  "name" varchar(200) default '' NOT NULL,
  "description" text default '',
  "created" numeric(14,0) default NULL NULL,
  "filename" varchar(80) default NULL NULL,
  "filesize" numeric(14,0) default NULL NULL,
  "filetype" varchar(250) default NULL NULL,
  "data" image default '',
  "user" varchar(200) default NULL NULL,
  "downloads" numeric(14,0) default NULL NULL,
  "votes" numeric(8,0) default NULL NULL,
  "points" decimal(8,2) default NULL NULL,
  "path" varchar(255) default NULL NULL,
  "reference_url" varchar(250) default NULL NULL,
  "is_reference" char(1) default NULL NULL,
  "hash" varchar(32) default NULL NULL,
  PRIMARY KEY ("fileId")




)   
go


CREATE  INDEX "tiki_files_name" ON "tiki_files"("name")
go
CREATE  INDEX "tiki_files_description" ON "tiki_files"("description")
go
CREATE  INDEX "tiki_files_downloads" ON "tiki_files"("downloads")
go
CREATE  INDEX "tiki_files_ft" ON "tiki_files"("name","description")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_forum_attachments`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_forum_attachments"
go



CREATE TABLE "tiki_forum_attachments" (
attId numeric(14 ,0) identity,
  "threadId" numeric(14,0) default '0' NOT NULL,
  "qId" numeric(14,0) default '0' NOT NULL,
  "forumId" numeric(14,0) default NULL NULL,
  "filename" varchar(250) default NULL NULL,
  "filetype" varchar(250) default NULL NULL,
  "filesize" numeric(12,0) default NULL NULL,
  "data" image default '',
  "dir" varchar(200) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  "path" varchar(250) default NULL NULL,
  PRIMARY KEY ("attId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_forum_reads`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 07:17 PM
--

DROP TABLE "tiki_forum_reads"
go



CREATE TABLE "tiki_forum_reads" (
  "user" varchar(200) default '' NOT NULL,
  "threadId" numeric(14,0) default '0' NOT NULL,
  "forumId" numeric(14,0) default NULL NULL,
  "timestamp" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("user","threadId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_forums`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 11:14 PM
--

DROP TABLE "tiki_forums"
go



CREATE TABLE "tiki_forums" (
forumId numeric(8 ,0) identity,
  "name" varchar(200) default NULL NULL,
  "description" text default '',
  "created" numeric(14,0) default NULL NULL,
  "lastPost" numeric(14,0) default NULL NULL,
  "threads" numeric(8,0) default NULL NULL,
  "comments" numeric(8,0) default NULL NULL,
  "controlFlood" char(1) default NULL NULL,
  "floodInterval" numeric(8,0) default NULL NULL,
  "moderator" varchar(200) default NULL NULL,
  "hits" numeric(8,0) default NULL NULL,
  "mail" varchar(200) default NULL NULL,
  "useMail" char(1) default NULL NULL,
  "section" varchar(200) default NULL NULL,
  "usePruneUnreplied" char(1) default NULL NULL,
  "pruneUnrepliedAge" numeric(8,0) default NULL NULL,
  "usePruneOld" char(1) default NULL NULL,
  "pruneMaxAge" numeric(8,0) default NULL NULL,
  "topicsPerPage" numeric(6,0) default NULL NULL,
  "topicOrdering" varchar(100) default NULL NULL,
  "threadOrdering" varchar(100) default NULL NULL,
  "att" varchar(80) default NULL NULL,
  "att_store" varchar(4) default NULL NULL,
  "att_store_dir" varchar(250) default NULL NULL,
  "att_max_size" numeric(12,0) default NULL NULL,
  "ui_level" char(1) default NULL NULL,
  "forum_password" varchar(32) default NULL NULL,
  "forum_use_password" char(1) default NULL NULL,
  "moderator_group" varchar(200) default NULL NULL,
  "approval_type" varchar(20) default NULL NULL,
  "outbound_address" varchar(250) default NULL NULL,
  "inbound_pop_server" varchar(250) default NULL NULL,
  "inbound_pop_port" numeric(4,0) default NULL NULL,
  "inbound_pop_user" varchar(200) default NULL NULL,
  "inbound_pop_password" varchar(80) default NULL NULL,
  "topic_smileys" char(1) default NULL NULL,
  "ui_avatar" char(1) default NULL NULL,
  "ui_flag" char(1) default NULL NULL,
  "ui_posts" char(1) default NULL NULL,
  "ui_email" char(1) default NULL NULL,
  "ui_online" char(1) default NULL NULL,
  "topic_summary" char(1) default NULL NULL,
  "show_description" char(1) default NULL NULL,
  "topics_list_replies" char(1) default NULL NULL,
  "topics_list_reads" char(1) default NULL NULL,
  "topics_list_pts" char(1) default NULL NULL,
  "topics_list_lastpost" char(1) default NULL NULL,
  "topics_list_author" char(1) default NULL NULL,
  "vote_threads" char(1) default NULL NULL,
  PRIMARY KEY ("forumId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_forums_queue`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_forums_queue"
go



CREATE TABLE "tiki_forums_queue" (
qId numeric(14 ,0) identity,
  "object" varchar(32) default NULL NULL,
  "parentId" numeric(14,0) default NULL NULL,
  "forumId" numeric(14,0) default NULL NULL,
  "timestamp" numeric(14,0) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  "title" varchar(240) default NULL NULL,
  "data" text default '',
  "type" varchar(60) default NULL NULL,
  "hash" varchar(32) default NULL NULL,
  "topic_smiley" varchar(80) default NULL NULL,
  "topic_title" varchar(240) default NULL NULL,
  "summary" varchar(240) default NULL NULL,
  PRIMARY KEY ("qId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_forums_reported`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_forums_reported"
go



CREATE TABLE "tiki_forums_reported" (
  "threadId" numeric(12,0) default '0' NOT NULL,
  "forumId" numeric(12,0) default '0' NOT NULL,
  "parentId" numeric(12,0) default '0' NOT NULL,
  "user" varchar(200) default NULL NULL,
  "timestamp" numeric(14,0) default NULL NULL,
  "reason" varchar(250) default NULL NULL,
  PRIMARY KEY ("threadId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_galleries`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 08:59 PM
-- Last check: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_galleries"
go



CREATE TABLE "tiki_galleries" (
galleryId numeric(14 ,0) identity,
  "name" varchar(80) default '' NOT NULL,
  "description" text default '',
  "created" numeric(14,0) default NULL NULL,
  "lastModif" numeric(14,0) default NULL NULL,
  "visible" char(1) default NULL NULL,
  "theme" varchar(60) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  "hits" numeric(14,0) default NULL NULL,
  "maxRows" numeric(10,0) default NULL NULL,
  "rowImages" numeric(10,0) default NULL NULL,
  "thumbSizeX" numeric(10,0) default NULL NULL,
  "thumbSizeY" numeric(10,0) default NULL NULL,
  "public" char(1) default NULL NULL,
  PRIMARY KEY ("galleryId")




)   
go


CREATE  INDEX "tiki_galleries_name" ON "tiki_galleries"("name")
go
CREATE  INDEX "tiki_galleries_description" ON "tiki_galleries"("description")
go
CREATE  INDEX "tiki_galleries_hits" ON "tiki_galleries"("hits")
go
CREATE  INDEX "tiki_galleries_ft" ON "tiki_galleries"("name","description")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_galleries_scales`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_galleries_scales"
go



CREATE TABLE "tiki_galleries_scales" (
  "galleryId" numeric(14,0) default '0' NOT NULL,
  "xsize" numeric(11,0) default '0' NOT NULL,
  "ysize" numeric(11,0) default '0' NOT NULL,
  PRIMARY KEY ("galleryId","xsize","ysize")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_games`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 05, 2003 at 08:23 PM
--

DROP TABLE "tiki_games"
go



CREATE TABLE "tiki_games" (
  "gameName" varchar(200) default '' NOT NULL,
  "hits" numeric(8,0) default NULL NULL,
  "votes" numeric(8,0) default NULL NULL,
  "points" numeric(8,0) default NULL NULL,
  PRIMARY KEY ("gameName")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_group_inclusion`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 05, 2003 at 02:03 AM
--

DROP TABLE "tiki_group_inclusion"
go



CREATE TABLE "tiki_group_inclusion" (
  "groupName" varchar(30) default '' NOT NULL,
  "includeGroup" varchar(30) default '' NOT NULL,
  PRIMARY KEY ("groupName","includeGroup")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_history`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 12:29 AM
--

DROP TABLE "tiki_history"
go



CREATE TABLE "tiki_history" (
  "pageName" varchar(160) default '' NOT NULL,
  "version" numeric(8,0) default '0' NOT NULL,
  "lastModif" numeric(14,0) default NULL NULL,
  "description" varchar(200) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  "ip" varchar(15) default NULL NULL,
  "comment" varchar(200) default NULL NULL,
  "data" image default '',
  PRIMARY KEY ("pageName","version")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_hotwords`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 10, 2003 at 11:04 PM
--

DROP TABLE "tiki_hotwords"
go



CREATE TABLE "tiki_hotwords" (
  "word" varchar(40) default '' NOT NULL,
  "url" varchar(255) default '' NOT NULL,
  PRIMARY KEY ("word")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_html_pages`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_html_pages"
go



CREATE TABLE "tiki_html_pages" (
  "pageName" varchar(200) default '' NOT NULL,
  "content" image default '',
  "refresh" numeric(10,0) default NULL NULL,
  "type" char(1) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("pageName")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_html_pages_dynamic_zones`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_html_pages_dynamic_zones"
go



CREATE TABLE "tiki_html_pages_dynamic_zones" (
  "pageName" varchar(40) default '' NOT NULL,
  "zone" varchar(80) default '' NOT NULL,
  "type" char(2) default NULL NULL,
  "content" text default '',
  PRIMARY KEY ("pageName","zone")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_images`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 08:29 PM
-- Last check: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_images"
go



CREATE TABLE "tiki_images" (
imageId numeric(14 ,0) identity,
  "galleryId" numeric(14,0) default '0' NOT NULL,
  "name" varchar(200) default '' NOT NULL,
  "description" text default '',
  "created" numeric(14,0) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  "hits" numeric(14,0) default NULL NULL,
  "path" varchar(255) default NULL NULL,
  PRIMARY KEY ("imageId")








)   
go


CREATE  INDEX "tiki_images_name" ON "tiki_images"("name")
go
CREATE  INDEX "tiki_images_description" ON "tiki_images"("description")
go
CREATE  INDEX "tiki_images_hits" ON "tiki_images"("hits")
go
CREATE  INDEX "tiki_images_ti_gId" ON "tiki_images"("galleryId")
go
CREATE  INDEX "tiki_images_ti_cr" ON "tiki_images"("created")
go
CREATE  INDEX "tiki_images_ti_hi" ON "tiki_images"("hits")
go
CREATE  INDEX "tiki_images_ti_us" ON "tiki_images"("user")
go
CREATE  INDEX "tiki_images_ft" ON "tiki_images"("name","description")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_images_data`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 12:49 PM
-- Last check: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_images_data"
go



CREATE TABLE "tiki_images_data" (
  "imageId" numeric(14,0) default '0' NOT NULL,
  "xsize" numeric(8,0) default '0' NOT NULL,
  "ysize" numeric(8,0) default '0' NOT NULL,
  "type" char(1) default '' NOT NULL,
  "filesize" numeric(14,0) default NULL NULL,
  "filetype" varchar(80) default NULL NULL,
  "filename" varchar(80) default NULL NULL,
  "data" image default '',
  PRIMARY KEY ("imageId","xsize","ysize","type")

) 
go


CREATE  INDEX "tiki_images_data_t_i_d_it" ON "tiki_images_data"("imageId","type")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_language`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_language"
go



CREATE TABLE "tiki_language" (
  "source" image NOT NULL,
  "lang" char(2) default '' NOT NULL,
  "tran" image default '',
  PRIMARY KEY ("source","lang")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_languages`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_languages"
go



CREATE TABLE "tiki_languages" (
  "lang" char(2) default '' NOT NULL,
  "language" varchar(255) default NULL NULL,
  PRIMARY KEY ("lang")
) 
go



-- --------------------------------------------------------
INSERT INTO tiki_languages VALUES('en','English')
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_link_cache`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 06:06 PM
--

DROP TABLE "tiki_link_cache"
go



CREATE TABLE "tiki_link_cache" (
cacheId numeric(14 ,0) identity,
  "url" varchar(250) default NULL NULL,
  "data" image default '',
  "refresh" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("cacheId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_links`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 11:39 PM
--

DROP TABLE "tiki_links"
go



CREATE TABLE "tiki_links" (
  "fromPage" varchar(160) default '' NOT NULL,
  "toPage" varchar(160) default '' NOT NULL,
  PRIMARY KEY ("fromPage","toPage")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_live_support_events`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_live_support_events"
go



CREATE TABLE "tiki_live_support_events" (
eventId numeric(14 ,0) identity,
  "reqId" varchar(32) default '' NOT NULL,
  "type" varchar(40) default NULL NULL,
  "seqId" numeric(14,0) default NULL NULL,
  "senderId" varchar(32) default NULL NULL,
  "data" text default '',
  "timestamp" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("eventId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_live_support_message_comments`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_live_support_message_comments"
go



CREATE TABLE "tiki_live_support_message_comments" (
cId numeric(12 ,0) identity,
  "msgId" numeric(12,0) default NULL NULL,
  "data" text default '',
  "timestamp" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("cId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_live_support_messages`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_live_support_messages"
go



CREATE TABLE "tiki_live_support_messages" (
msgId numeric(12 ,0) identity,
  "data" text default '',
  "timestamp" numeric(14,0) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  "username" varchar(200) default NULL NULL,
  "priority" numeric(2,0) default NULL NULL,
  "status" char(1) default NULL NULL,
  "assigned_to" varchar(200) default NULL NULL,
  "resolution" varchar(100) default NULL NULL,
  "title" varchar(200) default NULL NULL,
  "module" numeric(4,0) default NULL NULL,
  "email" varchar(250) default NULL NULL,
  PRIMARY KEY ("msgId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_live_support_modules`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_live_support_modules"
go



CREATE TABLE "tiki_live_support_modules" (
modId numeric(4 ,0) identity,
  "name" varchar(90) default NULL NULL,
  PRIMARY KEY ("modId")
)   
go



-- --------------------------------------------------------
INSERT INTO tiki_live_support_modules(name) VALUES('wiki')
go



INSERT INTO tiki_live_support_modules(name) VALUES('forums')
go



INSERT INTO tiki_live_support_modules(name) VALUES('image galleries')
go



INSERT INTO tiki_live_support_modules(name) VALUES('file galleries')
go



INSERT INTO tiki_live_support_modules(name) VALUES('directory')
go



INSERT INTO tiki_live_support_modules(name) VALUES('workflow')
go



INSERT INTO tiki_live_support_modules(name) VALUES('charts')
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_live_support_operators`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_live_support_operators"
go



CREATE TABLE "tiki_live_support_operators" (
  "user" varchar(200) default '' NOT NULL,
  "accepted_requests" numeric(10,0) default NULL NULL,
  "status" varchar(20) default NULL NULL,
  "longest_chat" numeric(10,0) default NULL NULL,
  "shortest_chat" numeric(10,0) default NULL NULL,
  "average_chat" numeric(10,0) default NULL NULL,
  "last_chat" numeric(14,0) default NULL NULL,
  "time_online" numeric(10,0) default NULL NULL,
  "votes" numeric(10,0) default NULL NULL,
  "points" numeric(10,0) default NULL NULL,
  "status_since" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("user")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_live_support_requests`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_live_support_requests"
go



CREATE TABLE "tiki_live_support_requests" (
  "reqId" varchar(32) default '' NOT NULL,
  "user" varchar(200) default NULL NULL,
  "tiki_user" varchar(200) default NULL NULL,
  "email" varchar(200) default NULL NULL,
  "operator" varchar(200) default NULL NULL,
  "operator_id" varchar(32) default NULL NULL,
  "user_id" varchar(32) default NULL NULL,
  "reason" text default '',
  "req_timestamp" numeric(14,0) default NULL NULL,
  "timestamp" numeric(14,0) default NULL NULL,
  "status" varchar(40) default NULL NULL,
  "resolution" varchar(40) default NULL NULL,
  "chat_started" numeric(14,0) default NULL NULL,
  "chat_ended" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("reqId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_mail_events`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 11, 2003 at 05:28 AM
--

DROP TABLE "tiki_mail_events"
go



CREATE TABLE "tiki_mail_events" (
  "event" varchar(200) default NULL NULL,
  "object" varchar(200) default NULL NULL,
  "email" varchar(200) default NULL
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_mailin_accounts`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_mailin_accounts"
go



CREATE TABLE "tiki_mailin_accounts" (
accountId numeric(12 ,0) identity,
  "user" varchar(200) default '' NOT NULL,
  "account" varchar(50) default '' NOT NULL,
  "pop" varchar(255) default NULL NULL,
  "port" numeric(4,0) default NULL NULL,
  "username" varchar(100) default NULL NULL,
  "pass" varchar(100) default NULL NULL,
  "active" char(1) default NULL NULL,
  "type" varchar(40) default NULL NULL,
  "smtp" varchar(255) default NULL NULL,
  "useAuth" char(1) default NULL NULL,
  "smtpPort" numeric(4,0) default NULL NULL,
  PRIMARY KEY ("accountId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_menu_languages`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_menu_languages"
go



CREATE TABLE "tiki_menu_languages" (
menuId numeric(8 ,0) identity,
  "language" char(2) default '' NOT NULL,
  PRIMARY KEY ("menuId","language")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_menu_options`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_menu_options"
go



CREATE TABLE "tiki_menu_options" (
optionId numeric(8 ,0) identity,
  "menuId" numeric(8,0) default NULL NULL,
  "type" char(1) default NULL NULL,
  "name" varchar(200) default NULL NULL,
  "url" varchar(255) default NULL NULL,
  "position" numeric(4,0) default NULL NULL,
  PRIMARY KEY ("optionId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_menus`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_menus"
go



CREATE TABLE "tiki_menus" (
menuId numeric(8 ,0) identity,
  "name" varchar(200) default '' NOT NULL,
  "description" text default '',
  "type" char(1) default NULL NULL,
  PRIMARY KEY ("menuId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_minical_events`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 09, 2003 at 04:06 AM
--

DROP TABLE "tiki_minical_events"
go



CREATE TABLE "tiki_minical_events" (
  "user" varchar(200) default NULL NULL,
eventId numeric(12 ,0) identity,
  "title" varchar(250) default NULL NULL,
  "description" text default '',
  "start" numeric(14,0) default NULL NULL,
  "end" numeric(14,0) default NULL NULL,
  "security" char(1) default NULL NULL,
  "duration" numeric(3,0) default NULL NULL,
  "topicId" numeric(12,0) default NULL NULL,
  "reminded" char(1) default NULL NULL,
  PRIMARY KEY ("eventId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_minical_topics`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_minical_topics"
go



CREATE TABLE "tiki_minical_topics" (
  "user" varchar(200) default NULL NULL,
topicId numeric(12 ,0) identity,
  "name" varchar(250) default NULL NULL,
  "filename" varchar(200) default NULL NULL,
  "filetype" varchar(200) default NULL NULL,
  "filesize" varchar(200) default NULL NULL,
  "data" image default '',
  "path" varchar(250) default NULL NULL,
  "isIcon" char(1) default NULL NULL,
  PRIMARY KEY ("topicId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_modules`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 11:44 PM
--

DROP TABLE "tiki_modules"
go



CREATE TABLE "tiki_modules" (
  "name" varchar(200) default '' NOT NULL,
  "position" char(1) default NULL NULL,
  "ord" numeric(4,0) default NULL NULL,
  "type" char(1) default NULL NULL,
  "title" varchar(40) default NULL NULL,
  "cache_time" numeric(14,0) default NULL NULL,
  "rows" numeric(4,0) default NULL NULL,
  "params" varchar(255) default NULL NULL,
  "groups" text default '',
  PRIMARY KEY ("name")
) 
go



-- --------------------------------------------------------
INSERT INTO tiki_modules(name,position,ord,cache_time) VALUES('login_box','r',1,0)
go



INSERT INTO tiki_modules(name,position,ord,cache_time) VALUES('application_menu','l',1,0)
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_newsletter_subscriptions`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_newsletter_subscriptions"
go



CREATE TABLE "tiki_newsletter_subscriptions" (
  "nlId" numeric(12,0) default '0' NOT NULL,
  "email" varchar(255) default '' NOT NULL,
  "code" varchar(32) default NULL NULL,
  "valid" char(1) default NULL NULL,
  "subscribed" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("nlId","email")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_newsletters`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_newsletters"
go



CREATE TABLE "tiki_newsletters" (
nlId numeric(12 ,0) identity,
  "name" varchar(200) default NULL NULL,
  "description" text default '',
  "created" numeric(14,0) default NULL NULL,
  "lastSent" numeric(14,0) default NULL NULL,
  "editions" numeric(10,0) default NULL NULL,
  "users" numeric(10,0) default NULL NULL,
  "allowAnySub" char(1) default NULL NULL,
  "frequency" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("nlId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_newsreader_marks`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_newsreader_marks"
go



CREATE TABLE "tiki_newsreader_marks" (
  "user" varchar(200) default '' NOT NULL,
  "serverId" numeric(12,0) default '0' NOT NULL,
  "groupName" varchar(255) default '' NOT NULL,
  "timestamp" numeric(14,0) default '0' NOT NULL,
  PRIMARY KEY ("user","serverId","groupName")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_newsreader_servers`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_newsreader_servers"
go



CREATE TABLE "tiki_newsreader_servers" (
  "user" varchar(200) default '' NOT NULL,
serverId numeric(12 ,0) identity,
  "server" varchar(250) default NULL NULL,
  "port" numeric(4,0) default NULL NULL,
  "username" varchar(200) default NULL NULL,
  "password" varchar(200) default NULL NULL,
  PRIMARY KEY ("serverId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_page_footnotes`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 10:00 AM
-- Last check: Jul 12, 2003 at 10:00 AM
--

DROP TABLE "tiki_page_footnotes"
go



CREATE TABLE "tiki_page_footnotes" (
  "user" varchar(200) default '' NOT NULL,
  "pageName" varchar(250) default '' NOT NULL,
  "data" text default '',
  PRIMARY KEY ("user","pageName")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_pages`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 01:52 AM
-- Last check: Jul 12, 2003 at 10:01 AM
--

DROP TABLE "tiki_pages"
go



CREATE TABLE "tiki_pages" (
  "pageName" varchar(160) default '' NOT NULL,
  "hits" numeric(8,0) default NULL NULL,
  "data" text default '',
  "description" varchar(200) default NULL NULL,
  "lastModif" numeric(14,0) default NULL NULL,
  "comment" varchar(200) default NULL NULL,
  "version" numeric(8,0) default '0' NOT NULL,
  "user" varchar(200) default NULL NULL,
  "ip" varchar(15) default NULL NULL,
  "flag" char(1) default NULL NULL,
  "points" numeric(8,0) default NULL NULL,
  "votes" numeric(8,0) default NULL NULL,
  "cache" text default '',
  "cache_timestamp" numeric(14,0) default NULL NULL,
  "pageRank" decimal(4,3) default NULL NULL,
  "creator" varchar(200) default NULL NULL,
  PRIMARY KEY ("pageName")




) 
go


CREATE  INDEX "tiki_pages_pageName" ON "tiki_pages"("pageName")
go
CREATE  INDEX "tiki_pages_data" ON "tiki_pages"("data")
go
CREATE  INDEX "tiki_pages_pageRank" ON "tiki_pages"("pageRank")
go
CREATE  INDEX "tiki_pages_ft" ON "tiki_pages"("pageName","data")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_pageviews`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 01:52 AM
--

DROP TABLE "tiki_pageviews"
go



CREATE TABLE "tiki_pageviews" (
  "day" numeric(14,0) default '0' NOT NULL,
  "pageviews" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("day")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_poll_options`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 06, 2003 at 07:57 PM
--

DROP TABLE "tiki_poll_options"
go



CREATE TABLE "tiki_poll_options" (
  "pollId" numeric(8,0) default '0' NOT NULL,
optionId numeric(8 ,0) identity,
  "title" varchar(200) default NULL NULL,
  "votes" numeric(8,0) default NULL NULL,
  PRIMARY KEY ("optionId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_polls`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 06, 2003 at 07:57 PM
--

DROP TABLE "tiki_polls"
go



CREATE TABLE "tiki_polls" (
pollId numeric(8 ,0) identity,
  "title" varchar(200) default NULL NULL,
  "votes" numeric(8,0) default NULL NULL,
  "active" char(1) default NULL NULL,
  "publishDate" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("pollId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_preferences`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 12:04 PM
--

DROP TABLE "tiki_preferences"
go



CREATE TABLE "tiki_preferences" (
  "name" varchar(40) default '' NOT NULL,
  "value" varchar(250) default NULL NULL,
  PRIMARY KEY ("name")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_private_messages`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_private_messages"
go



CREATE TABLE "tiki_private_messages" (
messageId numeric(8 ,0) identity,
  "toNickname" varchar(200) default '' NOT NULL,
  "data" varchar(255) default NULL NULL,
  "poster" varchar(200) default 'anonymous' NOT NULL,
  "timestamp" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("messageId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_programmed_content`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_programmed_content"
go



CREATE TABLE "tiki_programmed_content" (
pId numeric(8 ,0) identity,
  "contentId" numeric(8,0) default '0' NOT NULL,
  "publishDate" numeric(14,0) default '0' NOT NULL,
  "data" text default '',
  PRIMARY KEY ("pId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_quiz_question_options`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_quiz_question_options"
go



CREATE TABLE "tiki_quiz_question_options" (
optionId numeric(10 ,0) identity,
  "questionId" numeric(10,0) default NULL NULL,
  "optionText" text default '',
  "points" numeric(4,0) default NULL NULL,
  PRIMARY KEY ("optionId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_quiz_questions`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_quiz_questions"
go



CREATE TABLE "tiki_quiz_questions" (
questionId numeric(10 ,0) identity,
  "quizId" numeric(10,0) default NULL NULL,
  "question" text default '',
  "position" numeric(4,0) default NULL NULL,
  "type" char(1) default NULL NULL,
  "maxPoints" numeric(4,0) default NULL NULL,
  PRIMARY KEY ("questionId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_quiz_results`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_quiz_results"
go



CREATE TABLE "tiki_quiz_results" (
resultId numeric(10 ,0) identity,
  "quizId" numeric(10,0) default NULL NULL,
  "fromPoints" numeric(4,0) default NULL NULL,
  "toPoints" numeric(4,0) default NULL NULL,
  "answer" text default '',
  PRIMARY KEY ("resultId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_quiz_stats`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_quiz_stats"
go



CREATE TABLE "tiki_quiz_stats" (
  "quizId" numeric(10,0) default '0' NOT NULL,
  "questionId" numeric(10,0) default '0' NOT NULL,
  "optionId" numeric(10,0) default '0' NOT NULL,
  "votes" numeric(10,0) default NULL NULL,
  PRIMARY KEY ("quizId","questionId","optionId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_quiz_stats_sum`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_quiz_stats_sum"
go



CREATE TABLE "tiki_quiz_stats_sum" (
  "quizId" numeric(10,0) default '0' NOT NULL,
  "quizName" varchar(255) default NULL NULL,
  "timesTaken" numeric(10,0) default NULL NULL,
  "avgpoints" decimal(5,2) default NULL NULL,
  "avgavg" decimal(5,2) default NULL NULL,
  "avgtime" decimal(5,2) default NULL NULL,
  PRIMARY KEY ("quizId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_quizzes`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_quizzes"
go



CREATE TABLE "tiki_quizzes" (
quizId numeric(10 ,0) identity,
  "name" varchar(255) default NULL NULL,
  "description" text default '',
  "canRepeat" char(1) default NULL NULL,
  "storeResults" char(1) default NULL NULL,
  "questionsPerPage" numeric(4,0) default NULL NULL,
  "timeLimited" char(1) default NULL NULL,
  "timeLimit" numeric(14,0) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  "taken" numeric(10,0) default NULL NULL,
  PRIMARY KEY ("quizId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_received_articles`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_received_articles"
go



CREATE TABLE "tiki_received_articles" (
receivedArticleId numeric(14 ,0) identity,
  "receivedFromSite" varchar(200) default NULL NULL,
  "receivedFromUser" varchar(200) default NULL NULL,
  "receivedDate" numeric(14,0) default NULL NULL,
  "title" varchar(80) default NULL NULL,
  "authorName" varchar(60) default NULL NULL,
  "size" numeric(12,0) default NULL NULL,
  "useImage" char(1) default NULL NULL,
  "image_name" varchar(80) default NULL NULL,
  "image_type" varchar(80) default NULL NULL,
  "image_size" numeric(14,0) default NULL NULL,
  "image_x" numeric(4,0) default NULL NULL,
  "image_y" numeric(4,0) default NULL NULL,
  "image_data" image default '',
  "publishDate" numeric(14,0) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  "heading" text default '',
  "body" image default '',
  "hash" varchar(32) default NULL NULL,
  "author" varchar(200) default NULL NULL,
  "type" varchar(50) default NULL NULL,
  "rating" decimal(3,2) default NULL NULL,
  PRIMARY KEY ("receivedArticleId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_received_pages`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 09, 2003 at 03:56 AM
--

DROP TABLE "tiki_received_pages"
go



CREATE TABLE "tiki_received_pages" (
receivedPageId numeric(14 ,0) identity,
  "pageName" varchar(160) default '' NOT NULL,
  "data" image default '',
  "description" varchar(200) default NULL NULL,
  "comment" varchar(200) default NULL NULL,
  "receivedFromSite" varchar(200) default NULL NULL,
  "receivedFromUser" varchar(200) default NULL NULL,
  "receivedDate" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("receivedPageId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_referer_stats`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 01:30 AM
--

DROP TABLE "tiki_referer_stats"
go



CREATE TABLE "tiki_referer_stats" (
  "referer" varchar(50) default '' NOT NULL,
  "hits" numeric(10,0) default NULL NULL,
  "last" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("referer")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_related_categories`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_related_categories"
go



CREATE TABLE "tiki_related_categories" (
  "categId" numeric(10,0) default '0' NOT NULL,
  "relatedTo" numeric(10,0) default '0' NOT NULL,
  PRIMARY KEY ("categId","relatedTo")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_rss_modules`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 10:19 AM
--

DROP TABLE "tiki_rss_modules"
go



CREATE TABLE "tiki_rss_modules" (
rssId numeric(8 ,0) identity,
  "name" varchar(30) default '' NOT NULL,
  "description" text default '',
  "url" varchar(255) default '' NOT NULL,
  "refresh" numeric(8,0) default NULL NULL,
  "lastUpdated" numeric(14,0) default NULL NULL,
  "content" image default '',
  PRIMARY KEY ("rssId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_search_stats`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 10:55 PM
--

DROP TABLE "tiki_search_stats"
go



CREATE TABLE "tiki_search_stats" (
  "term" varchar(50) default '' NOT NULL,
  "hits" numeric(10,0) default NULL NULL,
  PRIMARY KEY ("term")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_semaphores`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 01:52 AM
--

DROP TABLE "tiki_semaphores"
go



CREATE TABLE "tiki_semaphores" (
  "semName" varchar(250) default '' NOT NULL,
  "user" varchar(200) default NULL NULL,
  "timestamp" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("semName")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_sent_newsletters`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_sent_newsletters"
go



CREATE TABLE "tiki_sent_newsletters" (
editionId numeric(12 ,0) identity,
  "nlId" numeric(12,0) default '0' NOT NULL,
  "users" numeric(10,0) default NULL NULL,
  "sent" numeric(14,0) default NULL NULL,
  "subject" varchar(200) default NULL NULL,
  "data" image default '',
  PRIMARY KEY ("editionId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_sessions`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 01:52 AM
--

DROP TABLE "tiki_sessions"
go



CREATE TABLE "tiki_sessions" (
  "sessionId" varchar(32) default '' NOT NULL,
  "user" varchar(200) default NULL NULL,
  "timestamp" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("sessionId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_shoutbox`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 08:21 PM
--

DROP TABLE "tiki_shoutbox"
go



CREATE TABLE "tiki_shoutbox" (
msgId numeric(10 ,0) identity,
  "message" varchar(255) default NULL NULL,
  "timestamp" numeric(14,0) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  "hash" varchar(32) default NULL NULL,
  PRIMARY KEY ("msgId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_structures`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_structures"
go



CREATE TABLE "tiki_structures" (
  "page" varchar(240) default '' NOT NULL,
  "parent" varchar(240) default '' NOT NULL,
  "pos" numeric(4,0) default NULL NULL,
  PRIMARY KEY ("page","parent")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_submissions`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 08, 2003 at 04:16 PM
--

DROP TABLE "tiki_submissions"
go



CREATE TABLE "tiki_submissions" (
subId numeric(8 ,0) identity,
  "title" varchar(80) default NULL NULL,
  "authorName" varchar(60) default NULL NULL,
  "topicId" numeric(14,0) default NULL NULL,
  "topicName" varchar(40) default NULL NULL,
  "size" numeric(12,0) default NULL NULL,
  "useImage" char(1) default NULL NULL,
  "image_name" varchar(80) default NULL NULL,
  "image_type" varchar(80) default NULL NULL,
  "image_size" numeric(14,0) default NULL NULL,
  "image_x" numeric(4,0) default NULL NULL,
  "image_y" numeric(4,0) default NULL NULL,
  "image_data" image default '',
  "publishDate" numeric(14,0) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  "heading" text default '',
  "body" text default '',
  "hash" varchar(32) default NULL NULL,
  "author" varchar(200) default NULL NULL,
  "reads" numeric(14,0) default NULL NULL,
  "votes" numeric(8,0) default NULL NULL,
  "points" numeric(14,0) default NULL NULL,
  "type" varchar(50) default NULL NULL,
  "rating" decimal(3,2) default NULL NULL,
  "isfloat" char(1) default NULL NULL,
  PRIMARY KEY ("subId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_suggested_faq_questions`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 11, 2003 at 08:52 PM
--

DROP TABLE "tiki_suggested_faq_questions"
go



CREATE TABLE "tiki_suggested_faq_questions" (
sfqId numeric(10 ,0) identity,
  "faqId" numeric(10,0) default '0' NOT NULL,
  "question" text default '',
  "answer" text default '',
  "created" numeric(14,0) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  PRIMARY KEY ("sfqId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_survey_question_options`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 11, 2003 at 12:55 AM
--

DROP TABLE "tiki_survey_question_options"
go



CREATE TABLE "tiki_survey_question_options" (
optionId numeric(12 ,0) identity,
  "questionId" numeric(12,0) default '0' NOT NULL,
  "qoption" text default '',
  "votes" numeric(10,0) default NULL NULL,
  PRIMARY KEY ("optionId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_survey_questions`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 11, 2003 at 11:55 PM
--

DROP TABLE "tiki_survey_questions"
go



CREATE TABLE "tiki_survey_questions" (
questionId numeric(12 ,0) identity,
  "surveyId" numeric(12,0) default '0' NOT NULL,
  "question" text default '',
  "options" text default '',
  "type" char(1) default NULL NULL,
  "position" numeric(5,0) default NULL NULL,
  "votes" numeric(10,0) default NULL NULL,
  "value" numeric(10,0) default NULL NULL,
  "average" decimal(4,2) default NULL NULL,
  PRIMARY KEY ("questionId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_surveys`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 07:40 PM
--

DROP TABLE "tiki_surveys"
go



CREATE TABLE "tiki_surveys" (
surveyId numeric(12 ,0) identity,
  "name" varchar(200) default NULL NULL,
  "description" text default '',
  "taken" numeric(10,0) default NULL NULL,
  "lastTaken" numeric(14,0) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  "status" char(1) default NULL NULL,
  PRIMARY KEY ("surveyId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_tags`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 06, 2003 at 02:58 AM
--

DROP TABLE "tiki_tags"
go



CREATE TABLE "tiki_tags" (
  "tagName" varchar(80) default '' NOT NULL,
  "pageName" varchar(160) default '' NOT NULL,
  "hits" numeric(8,0) default NULL NULL,
  "description" varchar(200) default NULL NULL,
  "data" image default '',
  "lastModif" numeric(14,0) default NULL NULL,
  "comment" varchar(200) default NULL NULL,
  "version" numeric(8,0) default '0' NOT NULL,
  "user" varchar(200) default NULL NULL,
  "ip" varchar(15) default NULL NULL,
  "flag" char(1) default NULL NULL,
  PRIMARY KEY ("tagName","pageName")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_theme_control_categs`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_theme_control_categs"
go



CREATE TABLE "tiki_theme_control_categs" (
  "categId" numeric(12,0) default '0' NOT NULL,
  "theme" varchar(250) default '' NOT NULL,
  PRIMARY KEY ("categId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_theme_control_objects`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_theme_control_objects"
go



CREATE TABLE "tiki_theme_control_objects" (
  "objId" varchar(250) default '' NOT NULL,
  "type" varchar(250) default '' NOT NULL,
  "name" varchar(250) default '' NOT NULL,
  "theme" varchar(250) default '' NOT NULL,
  PRIMARY KEY ("objId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_theme_control_sections`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_theme_control_sections"
go



CREATE TABLE "tiki_theme_control_sections" (
  "section" varchar(250) default '' NOT NULL,
  "theme" varchar(250) default '' NOT NULL,
  PRIMARY KEY ("section")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_topics`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 04, 2003 at 10:10 PM
--

DROP TABLE "tiki_topics"
go



CREATE TABLE "tiki_topics" (
topicId numeric(14 ,0) identity,
  "name" varchar(40) default NULL NULL,
  "image_name" varchar(80) default NULL NULL,
  "image_type" varchar(80) default NULL NULL,
  "image_size" numeric(14,0) default NULL NULL,
  "image_data" image default '',
  "active" char(1) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("topicId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_tracker_fields`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 08, 2003 at 01:48 PM
--

DROP TABLE "tiki_tracker_fields"
go



CREATE TABLE "tiki_tracker_fields" (
fieldId numeric(12 ,0) identity,
  "trackerId" numeric(12,0) default '0' NOT NULL,
  "name" varchar(80) default NULL NULL,
  "options" text default '',
  "type" char(1) default NULL NULL,
  "isMain" char(1) default NULL NULL,
  "isTblVisible" char(1) default NULL NULL,
  PRIMARY KEY ("fieldId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_tracker_item_attachments`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_tracker_item_attachments"
go



CREATE TABLE "tiki_tracker_item_attachments" (
attId numeric(12 ,0) identity,
  "itemId" varchar(40) default '' NOT NULL,
  "filename" varchar(80) default NULL NULL,
  "filetype" varchar(80) default NULL NULL,
  "filesize" numeric(14,0) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  "data" image default '',
  "path" varchar(255) default NULL NULL,
  "downloads" numeric(10,0) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  "comment" varchar(250) default NULL NULL,
  PRIMARY KEY ("attId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_tracker_item_comments`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 08:12 AM
--

DROP TABLE "tiki_tracker_item_comments"
go



CREATE TABLE "tiki_tracker_item_comments" (
commentId numeric(12 ,0) identity,
  "itemId" numeric(12,0) default '0' NOT NULL,
  "user" varchar(200) default NULL NULL,
  "data" text default '',
  "title" varchar(200) default NULL NULL,
  "posted" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("commentId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_tracker_item_fields`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 08:26 AM
--

DROP TABLE "tiki_tracker_item_fields"
go



CREATE TABLE "tiki_tracker_item_fields" (
  "itemId" numeric(12,0) default '0' NOT NULL,
  "fieldId" numeric(12,0) default '0' NOT NULL,
  "value" text default '',
  PRIMARY KEY ("itemId","fieldId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_tracker_items`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 08:26 AM
--

DROP TABLE "tiki_tracker_items"
go



CREATE TABLE "tiki_tracker_items" (
itemId numeric(12 ,0) identity,
  "trackerId" numeric(12,0) default '0' NOT NULL,
  "created" numeric(14,0) default NULL NULL,
  "status" char(1) default NULL NULL,
  "lastModif" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("itemId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_trackers`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 08:26 AM
--

DROP TABLE "tiki_trackers"
go



CREATE TABLE "tiki_trackers" (
trackerId numeric(12 ,0) identity,
  "name" varchar(80) default NULL NULL,
  "description" text default '',
  "created" numeric(14,0) default NULL NULL,
  "lastModif" numeric(14,0) default NULL NULL,
  "showCreated" char(1) default NULL NULL,
  "showStatus" char(1) default NULL NULL,
  "showLastModif" char(1) default NULL NULL,
  "useComments" char(1) default NULL NULL,
  "useAttachments" char(1) default NULL NULL,
  "items" numeric(10,0) default NULL NULL,
  PRIMARY KEY ("trackerId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_untranslated`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_untranslated"
go



CREATE TABLE "tiki_untranslated" (
id numeric(14 ,0) identity,
  "source" image NOT NULL,
  "lang" char(2) default '' NOT NULL,
  PRIMARY KEY ("source","lang")


)   
go


CREATE  INDEX "tiki_untranslated_id_2" ON "tiki_untranslated"("id")
go
CREATE UNIQUE INDEX "tiki_untranslated_id" ON "tiki_untranslated"("id")
go

-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_answers`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_user_answers"
go



CREATE TABLE "tiki_user_answers" (
  "userResultId" numeric(10,0) default '0' NOT NULL,
  "quizId" numeric(10,0) default '0' NOT NULL,
  "questionId" numeric(10,0) default '0' NOT NULL,
  "optionId" numeric(10,0) default '0' NOT NULL,
  PRIMARY KEY ("userResultId","quizId","questionId","optionId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_assigned_modules`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 08:25 PM
--

DROP TABLE "tiki_user_assigned_modules"
go



CREATE TABLE "tiki_user_assigned_modules" (
  "name" varchar(200) default '' NOT NULL,
  "position" char(1) default NULL NULL,
  "ord" numeric(4,0) default NULL NULL,
  "type" char(1) default NULL NULL,
  "title" varchar(40) default NULL NULL,
  "cache_time" numeric(14,0) default NULL NULL,
  "rows" numeric(4,0) default NULL NULL,
  "groups" text default '',
  "params" varchar(250) default NULL NULL,
  "user" varchar(200) default '' NOT NULL,
  PRIMARY KEY ("name","user")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_bookmarks_folders`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 11, 2003 at 08:35 AM
--

DROP TABLE "tiki_user_bookmarks_folders"
go



CREATE TABLE "tiki_user_bookmarks_folders" (
folderId numeric(12 ,0) identity,
  "parentId" numeric(12,0) default NULL NULL,
  "user" varchar(200) default '' NOT NULL,
  "name" varchar(30) default NULL NULL,
  PRIMARY KEY ("user","folderId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_bookmarks_urls`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 11, 2003 at 08:36 AM
--

DROP TABLE "tiki_user_bookmarks_urls"
go



CREATE TABLE "tiki_user_bookmarks_urls" (
urlId numeric(12 ,0) identity,
  "name" varchar(30) default NULL NULL,
  "url" varchar(250) default NULL NULL,
  "data" image default '',
  "lastUpdated" numeric(14,0) default NULL NULL,
  "folderId" numeric(12,0) default '0' NOT NULL,
  "user" varchar(200) default '' NOT NULL,
  PRIMARY KEY ("urlId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_mail_accounts`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_user_mail_accounts"
go



CREATE TABLE "tiki_user_mail_accounts" (
accountId numeric(12 ,0) identity,
  "user" varchar(200) default '' NOT NULL,
  "account" varchar(50) default '' NOT NULL,
  "pop" varchar(255) default NULL NULL,
  "current" char(1) default NULL NULL,
  "port" numeric(4,0) default NULL NULL,
  "username" varchar(100) default NULL NULL,
  "pass" varchar(100) default NULL NULL,
  "msgs" numeric(4,0) default NULL NULL,
  "smtp" varchar(255) default NULL NULL,
  "useAuth" char(1) default NULL NULL,
  "smtpPort" numeric(4,0) default NULL NULL,
  PRIMARY KEY ("accountId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_menus`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 11, 2003 at 10:58 PM
--

DROP TABLE "tiki_user_menus"
go



CREATE TABLE "tiki_user_menus" (
  "user" varchar(200) default '' NOT NULL,
menuId numeric(12 ,0) identity,
  "url" varchar(250) default NULL NULL,
  "name" varchar(40) default NULL NULL,
  "position" numeric(4,0) default NULL NULL,
  "mode" char(1) default NULL NULL,
  PRIMARY KEY ("menuId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_modules`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 05, 2003 at 03:16 AM
--

DROP TABLE "tiki_user_modules"
go



CREATE TABLE "tiki_user_modules" (
  "name" varchar(200) default '' NOT NULL,
  "title" varchar(40) default NULL NULL,
  "data" image default '',
  PRIMARY KEY ("name")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_notes`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 07:52 AM
--

DROP TABLE "tiki_user_notes"
go



CREATE TABLE "tiki_user_notes" (
  "user" varchar(200) default '' NOT NULL,
noteId numeric(12 ,0) identity,
  "created" numeric(14,0) default NULL NULL,
  "name" varchar(255) default NULL NULL,
  "lastModif" numeric(14,0) default NULL NULL,
  "data" text default '',
  "size" numeric(14,0) default NULL NULL,
  "parse_mode" varchar(20) default NULL NULL,
  PRIMARY KEY ("noteId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_postings`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 01:12 AM
--

DROP TABLE "tiki_user_postings"
go



CREATE TABLE "tiki_user_postings" (
  "user" varchar(200) default '' NOT NULL,
  "posts" numeric(12,0) default NULL NULL,
  "last" numeric(14,0) default NULL NULL,
  "first" numeric(14,0) default NULL NULL,
  "level" numeric(8,0) default NULL NULL,
  PRIMARY KEY ("user")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_preferences`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 01:09 AM
--

DROP TABLE "tiki_user_preferences"
go



CREATE TABLE "tiki_user_preferences" (
  "user" varchar(200) default '' NOT NULL,
  "prefName" varchar(40) default '' NOT NULL,
  "value" varchar(250) default NULL NULL,
  PRIMARY KEY ("user","prefName")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_quizzes`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_user_quizzes"
go



CREATE TABLE "tiki_user_quizzes" (
  "user" varchar(100) default NULL NULL,
  "quizId" numeric(10,0) default NULL NULL,
  "timestamp" numeric(14,0) default NULL NULL,
  "timeTaken" numeric(14,0) default NULL NULL,
  "points" numeric(12,0) default NULL NULL,
  "maxPoints" numeric(12,0) default NULL NULL,
  "resultId" numeric(10,0) default NULL NULL,
userResultId numeric(10 ,0) identity,
  PRIMARY KEY ("userResultId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_taken_quizzes`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_user_taken_quizzes"
go



CREATE TABLE "tiki_user_taken_quizzes" (
  "user" varchar(200) default '' NOT NULL,
  "quizId" varchar(255) default '' NOT NULL,
  PRIMARY KEY ("user","quizId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_tasks`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 08, 2003 at 05:30 PM
--

DROP TABLE "tiki_user_tasks"
go



CREATE TABLE "tiki_user_tasks" (
  "user" varchar(200) default NULL NULL,
taskId numeric(14 ,0) identity,
  "title" varchar(250) default NULL NULL,
  "description" text default '',
  "date" numeric(14,0) default NULL NULL,
  "status" char(1) default NULL NULL,
  "priority" numeric(2,0) default NULL NULL,
  "completed" numeric(14,0) default NULL NULL,
  "percentage" numeric(4,0) default NULL NULL,
  PRIMARY KEY ("taskId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_votings`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 11, 2003 at 11:55 PM
--

DROP TABLE "tiki_user_votings"
go



CREATE TABLE "tiki_user_votings" (
  "user" varchar(200) default '' NOT NULL,
  "id" varchar(255) default '' NOT NULL,
  PRIMARY KEY ("user","id")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_user_watches`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 08:07 AM
--

DROP TABLE "tiki_user_watches"
go



CREATE TABLE "tiki_user_watches" (
  "user" varchar(200) default '' NOT NULL,
  "event" varchar(40) default '' NOT NULL,
  "object" varchar(200) default '' NOT NULL,
  "hash" varchar(32) default NULL NULL,
  "title" varchar(250) default NULL NULL,
  "type" varchar(200) default NULL NULL,
  "url" varchar(250) default NULL NULL,
  "email" varchar(200) default NULL NULL,
  PRIMARY KEY ("user","event","object")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_userfiles`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_userfiles"
go



CREATE TABLE "tiki_userfiles" (
  "user" varchar(200) default '' NOT NULL,
fileId numeric(12 ,0) identity,
  "name" varchar(200) default NULL NULL,
  "filename" varchar(200) default NULL NULL,
  "filetype" varchar(200) default NULL NULL,
  "filesize" varchar(200) default NULL NULL,
  "data" image default '',
  "hits" numeric(8,0) default NULL NULL,
  "isFile" char(1) default NULL NULL,
  "path" varchar(255) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("fileId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_userpoints`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 11, 2003 at 05:47 AM
--

DROP TABLE "tiki_userpoints"
go



CREATE TABLE "tiki_userpoints" (
  "user" varchar(200) default NULL NULL,
  "points" decimal(8,2) default NULL NULL,
  "voted" numeric(8,0) default NULL
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_users`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_users"
go



CREATE TABLE "tiki_users" (
  "user" varchar(200) default '' NOT NULL,
  "password" varchar(40) default NULL NULL,
  "email" varchar(200) default NULL NULL,
  "lastLogin" numeric(14,0) default NULL NULL,
  PRIMARY KEY ("user")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_webmail_contacts`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_webmail_contacts"
go



CREATE TABLE "tiki_webmail_contacts" (
contactId numeric(12 ,0) identity,
  "firstName" varchar(80) default NULL NULL,
  "lastName" varchar(80) default NULL NULL,
  "email" varchar(250) default NULL NULL,
  "nickname" varchar(200) default NULL NULL,
  "user" varchar(200) default '' NOT NULL,
  PRIMARY KEY ("contactId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_webmail_messages`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_webmail_messages"
go



CREATE TABLE "tiki_webmail_messages" (
  "accountId" numeric(12,0) default '0' NOT NULL,
  "mailId" varchar(255) default '' NOT NULL,
  "user" varchar(200) default '' NOT NULL,
  "isRead" char(1) default NULL NULL,
  "isReplied" char(1) default NULL NULL,
  "isFlagged" char(1) default NULL NULL,
  PRIMARY KEY ("accountId","mailId")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_wiki_attachments`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_wiki_attachments"
go



CREATE TABLE "tiki_wiki_attachments" (
attId numeric(12 ,0) identity,
  "page" varchar(200) default '' NOT NULL,
  "filename" varchar(80) default NULL NULL,
  "filetype" varchar(80) default NULL NULL,
  "filesize" numeric(14,0) default NULL NULL,
  "user" varchar(200) default NULL NULL,
  "data" image default '',
  "path" varchar(255) default NULL NULL,
  "downloads" numeric(10,0) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  "comment" varchar(250) default NULL NULL,
  PRIMARY KEY ("attId")
)   
go



-- --------------------------------------------------------

--
-- Table structure for table `tiki_zones`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 07:42 PM
--

DROP TABLE "tiki_zones"
go



CREATE TABLE "tiki_zones" (
  "zone" varchar(40) default '' NOT NULL,
  PRIMARY KEY ("zone")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `users_grouppermissions`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 11, 2003 at 07:22 AM
--

DROP TABLE "users_grouppermissions"
go



CREATE TABLE "users_grouppermissions" (
  "groupName" varchar(30) default '' NOT NULL,
  "permName" varchar(30) default '' NOT NULL,
  "value" char(1) default '' NOT NULL,
  PRIMARY KEY ("groupName","permName")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `users_groups`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 03, 2003 at 08:57 PM
--

DROP TABLE "users_groups"
go



CREATE TABLE "users_groups" (
  "groupName" varchar(30) default '' NOT NULL,
  "groupDesc" varchar(255) default NULL NULL,
  PRIMARY KEY ("groupName")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `users_objectpermissions`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 07:20 AM
--

DROP TABLE "users_objectpermissions"
go



CREATE TABLE "users_objectpermissions" (
  "groupName" varchar(30) default '' NOT NULL,
  "permName" varchar(30) default '' NOT NULL,
  "objectType" varchar(20) default '' NOT NULL,
  "objectId" varchar(32) default '' NOT NULL,
  PRIMARY KEY ("objectId","groupName","permName")
) 
go



-- --------------------------------------------------------

--
-- Table structure for table `users_permissions`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 11, 2003 at 07:22 AM
--

DROP TABLE "users_permissions"
go



CREATE TABLE "users_permissions" (
  "permName" varchar(30) default '' NOT NULL,
  "permDesc" varchar(250) default NULL NULL,
  "level" varchar(80) default NULL NULL,
  "type" varchar(20) default NULL NULL,
  PRIMARY KEY ("permName")
) 
go



-- --------------------------------------------------------
-- Data set
INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_galleries', 'Can admin Image Galleries', 'editors', 'image galleries')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_file_galleries', 'Can admin file galleries', 'editors', 'file galleries')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_create_file_galleries', 'Can create file galleries', 'editors', 'file galleries')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_upload_files', 'Can upload files', 'registered', 'file galleries')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_download_files', 'Can download files', 'basic', 'file galleries')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_post_comments', 'Can post new comments', 'registered', 'comments')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_read_comments', 'Can read comments', 'basic', 'comments')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_remove_comments', 'Can delete comments', 'editors', 'comments')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_vote_comments', 'Can vote comments', 'registered', 'comments')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin', 'Administrator, can manage users groups and permissions and all the weblog features', 'admin', 'tiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_edit', 'Can edit pages', 'registered', 'wiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view', 'Can view page/pages', 'basic', 'wiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_remove', 'Can remove', 'editors', 'wiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_rollback', 'Can rollback pages', 'editors', 'wiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_create_galleries', 'Can create image galleries', 'editors', 'image galleries')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_upload_images', 'Can upload images', 'registered', 'image galleries')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_use_HTML', 'Can use HTML in pages', 'editors', 'tiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_create_blogs', 'Can create a blog', 'editors', 'blogs')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_blog_post', 'Can post to a blog', 'registered', 'blogs')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_blog_admin', 'Can admin blogs', 'editors', 'blogs')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_edit_article', 'Can edit articles', 'editors', 'cms')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_remove_article', 'Can remove articles', 'editors', 'cms')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_read_article', 'Can read articles', 'basic', 'cms')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_submit_article', 'Can submit articles', 'basic', 'cms')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_edit_submission', 'Can edit submissions', 'editors', 'cms')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_remove_submission', 'Can remove submissions', 'editors', 'cms')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_approve_submission', 'Can approve submissions', 'editors', 'cms')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_edit_templates', 'Can edit site templates', 'admin', 'tiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_dynamic', 'Can admin the dynamic content system', 'editors', 'tiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_banners', 'Administrator, can admin banners', 'admin', 'tiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_wiki', 'Can admin the wiki', 'editors', 'wiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_cms', 'Can admin the cms', 'editors', 'cms')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_categories', 'Can admin categories', 'editors', 'tiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_send_pages', 'Can send pages to other sites', 'registered', 'comm')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_sendme_pages', 'Can send pages to this site', 'registered', 'comm')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_received_pages', 'Can admin received pages', 'editors', 'comm')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_forum', 'Can admin forums', 'editors', 'forums')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_forum_post', 'Can post in forums', 'registered', 'forums')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_forum_post_topic', 'Can start threads in forums', 'registered', 'forums')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_forum_read', 'Can read forums', 'basic', 'forums')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_forum_vote', 'Can vote comments in forums', 'registered', 'forums')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_read_blog', 'Can read blogs', 'basic', 'blogs')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view_image_gallery', 'Can view image galleries', 'basic', 'image galleries')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view_file_gallery', 'Can view file galleries', 'basic', 'file galleries')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_edit_comments', 'Can edit all comments', 'editors', 'comments')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_vote_poll', 'Can vote polls', 'basic', 'tiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_chat', 'Administrator, can create channels remove channels etc', 'editors', 'chat')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_chat', 'Can use the chat system', 'registered', 'chat')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_topic_read', 'Can read a topic (Applies only to individual topic perms)', 'basic', 'topics')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_play_games', 'Can play games', 'basic', 'games')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_games', 'Can admin games', 'editors', 'games')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_edit_cookies', 'Can admin cookies', 'editors', 'tiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view_stats', 'Can view site stats', 'basic', 'tiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_create_bookmarks', 'Can create user bookmarksche user bookmarks', 'registered', 'user')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_configure_modules', 'Can configure modules', 'registered', 'user')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_cache_bookmarks', 'Can cache user bookmarks', 'registered', 'user')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_faqs', 'Can admin faqs', 'editors', 'faqs')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view_faqs', 'Can view faqs', 'basic', 'faqs')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_send_articles', 'Can send articles to other sites', 'editors', 'comm')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_sendme_articles', 'Can send articles to this site', 'registered', 'comm')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_received_articles', 'Can admin received articles', 'editors', 'comm')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view_referer_stats', 'Can view referer stats', 'editors', 'tiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_wiki_attach_files', 'Can attach files to wiki pages', 'registered', 'wiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_wiki_admin_attachments', 'Can admin attachments to wiki pages', 'editors', 'wiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_wiki_view_attachments', 'Can view wiki attachments and download', 'registered', 'wiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_batch_upload_images', 'Can upload zip files with images', 'editors', 'image galleries')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_drawings', 'Can admin drawings', 'editors', 'drawings')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_edit_drawings', 'Can edit drawings', 'basic', 'drawings')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view_html_pages', 'Can view HTML pages', 'basic', 'html pages')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_edit_html_pages', 'Can edit HTML pages', 'editors', 'html pages')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view_shoutbox', 'Can view shoutbox', 'basic', 'shoutbox')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_shoutbox', 'Can admin shoutbox (Edit/remove msgs)', 'editors', 'shoutbox')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_post_shoutbox', 'Can post messages in shoutbox', 'basic', 'shoutbox')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_suggest_faq', 'Can suggest faq questions', 'basic', 'faqs')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_edit_content_templates', 'Can edit content templates', 'editors', 'content templates')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_use_content_templates', 'Can use content templates', 'registered', 'content templates')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_quizzes', 'Can admin quizzes', 'editors', 'quizzes')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_take_quiz', 'Can take quizzes', 'basic', 'quizzes')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view_quiz_stats', 'Can view quiz stats', 'basic', 'quizzes')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view_user_results', 'Can view user quiz results', 'editors', 'quizzes')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_newsletters', 'Can admin newsletters', 'editors', 'newsletters')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_subscribe_newsletters', 'Can subscribe to newsletters', 'basic', 'newsletters')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_subscribe_email', 'Can subscribe any email to newsletters', 'editors', 'newsletters')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_use_webmail', 'Can use webmail', 'registered', 'webmail')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_surveys', 'Can admin surveys', 'editors', 'surveys')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_take_survey', 'Can take surveys', 'basic', 'surveys')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view_survey_stats', 'Can view survey stats', 'basic', 'surveys')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_modify_tracker_items', 'Can change tracker items', 'registered', 'trackers')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_comment_tracker_items', 'Can insert comments for tracker items', 'basic', 'trackers')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_create_tracker_items', 'Can create new items for trackers', 'registered', 'trackers')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_trackers', 'Can admin trackers', 'editors', 'trackers')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view_trackers', 'Can view trackers', 'basic', 'trackers')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_attach_trackers', 'Can attach files to tracker items', 'registered', 'trackers')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_upload_picture', 'Can upload pictures to wiki pages', 'registered', 'wiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_batch_upload_files', 'Can upload zip files with files', 'editors', 'file galleries')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_minor', 'Can save as minor edit', 'registered', 'wiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_rename', 'Can rename pages', 'editors', 'wiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_lock', 'Can lock pages', 'editors', 'wiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_usermenu', 'Can create items in personal menu', 'registered', 'user')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_minical', 'Can use the mini event calendar', 'registered', 'user')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_eph_admin', 'Can admin ephemerides', 'editors', 'tiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_userfiles', 'Can upload personal files', 'registered', 'user')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_tasks', 'Can use tasks', 'registered', 'user')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_notepad', 'Can use the notepad', 'registered', 'user')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_newsreader', 'Can use the newsreader', 'registered', 'user')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_messages', 'Can use the messaging system', 'registered', 'messu')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_broadcast', 'Can broadcast messages to groups', 'admin', 'messu')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_broadcast_all', 'Can broadcast messages to all user', 'admin', 'messu')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_mailin', 'Can admin mail-in accounts', 'admin', 'tiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_edit_structures', 'Can create and edit structures', 'editors', 'wiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_directory', 'Can admin the directory', 'editors', 'directory')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view_directory', 'Can use the directory', 'basic', 'directory')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_directory_cats', 'Can admin directory categories', 'editors', 'directory')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_directory_sites', 'Can admin directory sites', 'editors', 'directory')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_submit_link', 'Can submit sites to the directory', 'basic', 'directory')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_autosubmit_link', 'Submited links are valid', 'editors', 'directory')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_validate_links', 'Can validate submited links', 'editors', 'directory')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_edit_languages', 'Can edit translations and create new languages', 'editors', 'tiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_autoapprove_submission', 'Submited articles automatically approved', 'editors', 'cms')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_forums_report', 'Can report msgs to moderator', 'registered', 'forums')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_banning', 'Can ban users or ips', 'admin', 'tiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_forum_attach', 'Can attach to forum posts', 'registered', 'forums')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_live_support_admin', 'Admin live support system', 'admin', 'support')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_live_support', 'Can use live support system', 'basic', 'support')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_forum_autoapp', 'Auto approve forum posts', 'editors', 'forums')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_charts', 'Can admin charts', 'admin', 'charts')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view_chart', 'Can view charts', 'basic', 'charts')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_vote_chart', 'Can vote', 'basic', 'charts')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_suggest_chart_item', 'Can suggest items', 'basic', 'charts')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_autoval_chart_suggestio', 'Autovalidate suggestions', 'editors', 'charts')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_edit_copyrights', 'Can edit copyright notices', 'editors', 'wiki')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_workflow', 'Can admin workflow processes', 'admin', 'workflow')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_abort_instance', 'Can abort a process instance', 'editors', 'workflow')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_use_workflow', 'Can execute workflow activities', 'registered', 'workflow')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_exception_instance', 'Can declare an instance as exception', 'registered', 'workflow')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_send_instance', 'Can send instances after completion', 'registered', 'workflow')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_view_calendar', 'Can browse the calendar', 'basic', 'calendar')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_change_events', 'Can change events in the calendar', 'registered', 'calendar')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_add_events', 'Can add events in the calendar', 'registered', 'calendar')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_admin_calendar', 'Can create/admin calendars', 'admin', 'calendar')
go



INSERT INTO "users_permissions" ("permName","permDesc","level","type") VALUES ('tiki_p_create_css', 'Can create new css suffixed with -user', 'registered', 'tiki')
go



-- --------------------------------------------------------

--
-- Table structure for table `users_usergroups`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 12, 2003 at 09:31 PM
--

DROP TABLE "users_usergroups"
go



CREATE TABLE "users_usergroups" (
  "userId" numeric(8,0) default '0' NOT NULL,
  "groupName" varchar(30) default '' NOT NULL,
  PRIMARY KEY ("userId","groupName")
) 
go



-- --------------------------------------------------------
INSERT INTO users_groups(groupName,groupDesc) VALUES('Anonymous','Public users not logged')
go



INSERT INTO users_groups(groupName,groupDesc) VALUES('Registered','Users logged into the system')
go



-- --------------------------------------------------------

--
-- Table structure for table `users_users`
--
-- Creation: Jul 03, 2003 at 07:42 PM
-- Last update: Jul 13, 2003 at 01:07 AM
--

DROP TABLE "users_users"
go



CREATE TABLE "users_users" (
userId numeric(8 ,0) identity,
  "email" varchar(200) default NULL NULL,
  "login" varchar(40) default '' NOT NULL,
  "password" varchar(30) default '' NOT NULL,
  "provpass" varchar(30) default NULL NULL,
  "realname" varchar(80) default NULL NULL,
  "homePage" varchar(200) default NULL NULL,
  "lastLogin" numeric(14,0) default NULL NULL,
  "currentLogin" numeric(14,0) default NULL NULL,
  "registrationDate" numeric(14,0) default NULL NULL,
  "challenge" varchar(32) default NULL NULL,
  "pass_due" numeric(14,0) default NULL NULL,
  "hash" varchar(32) default NULL NULL,
  "created" numeric(14,0) default NULL NULL,
  "country" varchar(80) default NULL NULL,
  "avatarName" varchar(80) default NULL NULL,
  "avatarSize" numeric(14,0) default NULL NULL,
  "avatarFileType" varchar(250) default NULL NULL,
  "avatarData" image default '',
  "avatarLibName" varchar(200) default NULL NULL,
  "avatarType" char(1) default NULL NULL,
  PRIMARY KEY ("userId")
)   
go



-- --------------------------------------------------------
------ Administrator account
INSERT INTO users_users(email,login,password,realname,hash) VALUES('','admin','admin','System Administrator',md5('adminadmin'))
go



UPDATE users_users set currentLogin=lastLogin,registrationDate=lastLogin
go



-- --------------------------------------------------------

-- Inserts of all default values for preferences
INSERT INTO "tiki_preferences" ("name","value") VALUES ('allowRegister','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('anonCanEdit','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('article_comments_default_ordering','points_desc')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('article_comments_per_page','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('art_list_author','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('art_list_date','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('art_list_img','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('art_list_reads','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('art_list_size','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('art_list_title','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('art_list_topic','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_create_user_auth','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_create_user_tiki','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_ldap_adminpass','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_ldap_adminuser','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_ldap_basedn','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_ldap_groupattr','cn')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_ldap_groupdn','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_ldap_groupoc','groupOfUniqueNames')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_ldap_host','localhost')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_ldap_memberattr','uniqueMember')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_ldap_memberisdn','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_ldap_port','389')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_ldap_scope','sub')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_ldap_userattr','uid')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_ldap_userdn','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_ldap_useroc','inetOrgPerson')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_method','tiki')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('auth_skip_admin','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('blog_comments_default_ordering','points_desc')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('blog_comments_per_page','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('blog_list_activity','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('blog_list_created','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('blog_list_description','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('blog_list_lastmodif','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('blog_list_order','created_desc')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('blog_list_posts','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('blog_list_title','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('blog_list_user','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('blog_list_visits','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('blog_spellcheck','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('cacheimages','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('cachepages','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('change_language','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('change_theme','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('cms_bot_bar','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('cms_left_column','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('cms_right_column','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('cms_spellcheck','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('cms_top_bar','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('contact_user','admin')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('count_admin_pvs','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('directory_columns','3')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('directory_links_per_page','20')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('directory_open_links','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('directory_validate_urls','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('direct_pagination','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('display_timezone','EST')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('faq_comments_default_ordering','points_desc')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('faq_comments_per_page','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_article_comments','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_articles','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_backlinks','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_banners','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_banning','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_blog_comments','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_blogposts_comments','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_blog_rankings','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_blogs','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_bot_bar','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_calendar','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_categories','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_categoryobjects','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_categorypath','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_challenge','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_charts','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_chat','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_clear_passwords','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_cms_rankings','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_cms_templates','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_comm','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_contact','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_custom_home','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_debug_console','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_debugger_console','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_directory','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_drawings','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_dump','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_dynamic_content','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_editcss','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_edit_templates','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_eph','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_faq_comments','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_faqs','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_featuredLinks','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_file_galleries_comments','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_file_galleries','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_file_galleries_rankings','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_forum_parse','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_forum_quickjump','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_forum_rankings','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_forums','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_forum_topicd','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_galleries','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_gal_rankings','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_games','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_history','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_hotwords_nw','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_hotwords','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_html_pages','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_image_galleries_comments','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_lastChanges','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_left_column','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_likePages','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_listPages','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_live_support','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_menusfolderstyle','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_messages','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_minical','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_newsletters','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_newsreader','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_notepad','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_obzip','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_page_title','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_phpopentracker','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_poll_comments','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_polls','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_quizzes','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_ranking','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_referer_stats','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_right_column','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_sandbox','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_search_fulltext','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_search_stats','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_search','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_shoutbox','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_smileys','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_stats','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_submissions','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_surveys','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_tasks','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_theme_control','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_top_bar','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_trackers','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_user_bookmarks','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_userfiles','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_usermenu','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_userPreferences','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_userVersions','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_user_watches','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_warn_on_edit','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_webmail','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_attachments','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_comments','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_description','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_discuss','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_footnotes','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_monosp','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_multiprint','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_notepad','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_pdf','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_pictures','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_rankings','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_tables','old')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_templates','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_undo','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki_usrlock','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wikiwords','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_wiki','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_workflow','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('feature_xmlrpc','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('fgal_list_created','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('fgal_list_description','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('fgal_list_files','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('fgal_list_hits','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('fgal_list_lastmodif','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('fgal_list_name','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('fgal_list_user','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('fgal_match_regex','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('fgal_nmatch_regex','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('fgal_use_db','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('fgal_use_dir','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('file_galleries_comments_default_ordering','points_desc')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('file_galleries_comments_per_page','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('forgotPass','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('forum_list_desc','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('forum_list_lastpost','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('forum_list_posts','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('forum_list_ppd','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('forum_list_topics','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('forum_list_visits','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('forums_ordering','created_desc')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('gal_list_created','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('gal_list_description','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('gal_list_imgs','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('gal_list_lastmodif','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('gal_list_name','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('gal_list_user','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('gal_list_visits','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('gal_match_regex','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('gal_nmatch_regex','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('gal_use_db','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('gal_use_dir','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('gal_use_lib','gd')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('home_file_gallery','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('http_domain','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('http_port','80')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('http_prefix','/')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('https_domain','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('https_login','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('https_login_required','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('https_port','443')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('https_prefix','/')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('image_galleries_comments_default_orderin','points_desc')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('image_galleries_comments_per_page','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('keep_versions','1')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('language','en')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('lang_use_db','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('layout_section','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('long_date_format','%A %d of %B, %Y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('long_time_format','%H:%M:%S %Z')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('maxArticles','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('maxRecords','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('max_rss_articles','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('max_rss_blog','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('max_rss_blogs','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('max_rss_file_galleries','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('max_rss_file_gallery','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('max_rss_forum','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('max_rss_forums','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('max_rss_image_galleries','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('max_rss_image_gallery','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('max_rss_wiki','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('maxVersions','0')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('min_pass_length','1')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('modallgroups','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('pass_chr_num','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('pass_due','999')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('poll_comments_default_ordering','points_desc')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('poll_comments_per_page','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('popupLinks','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('proxy_host','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('proxy_port','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('record_untranslated','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('registerPasscode','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('rememberme','disabled')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('remembertime','7200')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('rnd_num_reg','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('rss_articles','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('rss_blog','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('rss_blogs','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('rss_file_galleries','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('rss_file_gallery','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('rss_forums','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('rss_forum','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('rss_image_galleries','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('rss_image_gallery','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('rss_wiki','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('sender_email','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('short_date_format','%a %d of %b, %Y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('short_time_format','%H:%M %Z')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('siteTitle','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('slide_style','slidestyle.css')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('style','moreneat.css')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('system_os','unix')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('tikiIndex','tiki-index.php')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('tmpDir','/tmp')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('t_use_db','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('t_use_dir','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('uf_use_db','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('uf_use_dir','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('urlIndex','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('use_proxy','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('user_assigned_modules','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('useRegisterPasscode','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('userfiles_quota','30')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('useUrlIndex','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('validateUsers','n')
go


INSERT INTO "tiki_preferences" ("name","value") VALUES ('eponymousGroups','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('warn_on_edit_time','2')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('webmail_max_attachment','1500000')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('webmail_view_html','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('webserverauth','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_bot_bar','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_cache','0')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_comments_default_ordering','points_desc')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_comments_per_page','10')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_creator_admin','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_feature_copyrights','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_forum','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_forum_id','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wikiHomePage','HomePage')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_left_column','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wikiLicensePage','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_list_backlinks','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_list_comment','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_list_creator','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_list_hits','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_list_lastmodif','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_list_lastver','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_list_links','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_list_name','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_list_size','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_list_status','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_list_user','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_list_versions','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_page_regex','strict')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_right_column','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_spellcheck','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wikiSubmitNotice','')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('wiki_top_bar','n')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('w_use_db','y')
go



INSERT INTO "tiki_preferences" ("name","value") VALUES ('w_use_dir','')
go




go


