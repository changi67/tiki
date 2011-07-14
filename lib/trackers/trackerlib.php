<?php
// (c) Copyright 2002-2010 by authors of the Tiki Wiki/CMS/Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Tracker Library
 *
 * \brief Functions to support accessing and processing of the Trackers.
 *
 * @package		Tiki
 * @subpackage		Trackers
 * @author		Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * @copyright		Copyright (c) 2002-2009, All Rights Reserved.
 * 			See copyright.txt for details and a complete list of authors.
 * @license		LGPL - See license.txt for details.
 * @version		SVN $Rev: 25023 $
 * @filesource
 * @link		http://dev.tiki.org/Trackers
 * @since		Always
 */
/**
 * This script may only be included, so it is better to die if called directly.
 */
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}

/**
 * TrackerLib Class
 *
 * This class extends the TikiLib class.
 */
class TrackerLib extends TikiLib
{

	var $trackerinfo_cache;

	// allowed types for image fields
	var $imgMimeTypes;
	var $imgMaxSize;

	function __construct() {
		parent::__construct();
		$this->imgMimeTypes = array('image/jpeg', 'image/gif', 'image/png', 'image/pjpeg', 'image/bmp');
		$this->imgMaxSize = (1048576 * 4); // 4Mo
	}

	// check that the image type is good
	function check_image_type($mimeType) {
		return in_array( $mimeType, $this->imgMimeTypes );
	}

	function get_image_filename($imageFileName, $itemId, $fieldId) {
		do {
			$name = md5( uniqid("$imageFileName.$itemId.$fieldId"));
		} while (file_exists("img/trackers/$name"));

		return "img/trackers/$name";
	}

	function remove_field_images($fieldId) {
		$query = 'select `value` from `tiki_tracker_item_fields` where `fieldId`=?';
		$result = $this->query( $query, array((int)$fieldId) );
		while( $r = $result->fetchRow() ) {
			if( file_exists($r['value']) ) {
				unlink( $r['value'] );
			}
		}
	}

	function add_item_attachment_hit($id) {
		global $prefs, $user;
		if ($user != 'admin' || $prefs['count_admin_pvs'] == 'y' ) {
			$query = "update `tiki_tracker_item_attachments` set `hits`=`hits`+1 where `attId`=?";
			$result = $this->query($query,array((int) $id));
		}
		return true;
	}

	function get_item_attachment_owner($attId) {
		return $this->getOne("select `user` from `tiki_tracker_item_attachments` where `attId`=?",array((int) $attId));
	}

	function list_item_attachments($itemId, $offset, $maxRecords, $sort_mode, $find) {
		if ($find) {
			$findesc = '%' . $find . '%';
			$mid = " where `itemId`=? and (`filename` like ?)";
			$bindvars=array((int) $itemId,$findesc);
		} else {
			$mid = " where `itemId`=? ";
			$bindvars=array((int) $itemId);
		}
		$query = "select `user`,`attId`,`itemId`,`filename`,`filesize`,`filetype`,`hits`,`created`,`comment`,`longdesc`,`version` ";
		$query.= " from `tiki_tracker_item_attachments` $mid order by ".$this->convertSortMode($sort_mode);
		$query_cant = "select count(*) from `tiki_tracker_item_attachments` $mid";
		$ret = $this->fetchAll($query,$bindvars,$maxRecords,$offset);
		$cant = $this->getOne($query_cant,$bindvars);

		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}

	function get_item_nb_attachments($itemId) {
		$query = "select sum(`hits`) as hits, count(*) as attachments from `tiki_tracker_item_attachments` where `itemId`=?";
		$result = $this->query($query, array($itemId));
		if ($res = $result->fetchRow())
			return $res;
		return array();
	}

	function get_item_nb_comments($itemId) {
		return $this->getOne('select count(*) from `tiki_tracker_item_comments` where `itemId`=?', array((int)$itemId));
	}

	function list_all_attachements($offset=0, $maxRecords=-1, $sort_mode='created_desc', $find='') {
		if ($find) {
			$findesc = '%' . $find . '%';
			$mid = " where `filename` like ?";
			$bindvars=array($findesc);
		} else {
			$mid = "";
			$bindvars=array();
		}
		$query = "select `user`,`attId`,`itemId`,`filename`,`filesize`,`filetype`,`hits`,`created`,`comment`,`path` ";
		$query.= " from `tiki_tracker_item_attachments` $mid order by ".$this->convertSortMode($sort_mode);
		$query_cant = "select count(*) from `tiki_tracker_item_attachments` $mid";
		$ret = $this->fetchAll($query,$bindvars,$maxRecords,$offset);
		$cant = $this->getOne($query_cant,$bindvars);
		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}

	function file_to_db($path,$attId) {
		if (is_file($path)) {
			$fp = fopen($path,'rb');
			$data = '';
			while (!feof($fp)) {
				$data .= fread($fp, 8192 * 16);
			}
			fclose ($fp);
			$query = "update `tiki_tracker_item_attachments` set `data`=?,`path`=? where `attId`=?";
			if ($this->query($query,array($data,'',(int)$attId))) {
				unlink($path);
			}
		}
	}

	function db_to_file($path,$attId) {
		$fw = fopen($path,'wb');
		$data = $this->getOne("select `data` from `tiki_tracker_item_attachments` where `attId`=?",array((int)$attId));
		if ($data) {
			fwrite($fw, $data);
		}
		fclose ($fw);
		if (is_file($path)) {
			$query = "update `tiki_tracker_item_attachments` set `data`=?,`path`=? where `attId`=?";
			$this->query($query,array('',basename($path),(int)$attId));
		}
	}

	function get_item_attachment($attId) {
		$query = "select * from `tiki_tracker_item_attachments` where `attId`=?";
		$result = $this->query($query,array((int) $attId));
		if (!$result->numRows()) return false;
		$res = $result->fetchRow();
		return $res;
	}

	function remove_item_attachment($attId=0, $itemId=0) {
		global $prefs;
		if (empty($attId)) {
			$paths = $this->fetchAll('select `path` from `tiki_tracker_item_attachments` where `itemId`=?', array($itemId));
			foreach ($paths as $path) {
				if (!empty($path['path'])) {
					@unlink ($prefs['t_use_dir'] . $path['path']);
				}
			}
			$this->query('delete from `tiki_tracker_item_attachments` where `itemId`=?', array($itemId));
			$this->query('update `tiki_tracker_item_fields` ttif left join `tiki_tracker_fields` ttf using (`fieldId`) set `value`=? where ttif.`itemId`=? and ttf.`type`=?', array('', $itemId, 'A'));
		} else {
			$path = $this->getOne("select `path` from `tiki_tracker_item_attachments` where `attId`=?",array((int) $attId));
			if ($path) @unlink ($prefs['t_use_dir'] . $path);
			$query = "delete from `tiki_tracker_item_attachments` where `attId`=?";
			$result = $this->query($query,array((int) $attId));
			$query = 'update `tiki_tracker_item_fields` ttif left join `tiki_tracker_fields` ttf using (`fieldId`) set `value`=\'\' where ttif.`value`=? and ttf.`type`=?';
			$this->query($query, array((int)$attId, 'A'));
		}
	}

	function replace_item_attachment($attId, $filename, $type, $size, $data, $comment, $user, $fhash, $version, $longdesc, $trackerId=0, $itemId=0,$options='', $notif=true) {
		global $prefs;
		$comment = strip_tags($comment);
		$now = $this->now;
		if (empty($attId)) {
			$query = "insert into `tiki_tracker_item_attachments`(`itemId`,`filename`,`filesize`,`filetype`,`data`,`created`,`hits`,`user`,";
			$query.= "`comment`,`path`,`version`,`longdesc`) values(?,?,?,?,?,?,?,?,?,?,?,?)";
			$result = $this->query($query,array((int) $itemId,$filename,$size,$type,$data,(int) $now,0,$user,$comment,$fhash,$version,$longdesc));
			$query = 'select `attId` from `tiki_tracker_item_attachments` where `itemId`=?  and `created`=? and `filename`=?';
			$attId = $this->getOne($query, array($itemId, $now, $filename));
		} elseif (empty($filename)) {
			$query = "update `tiki_tracker_item_attachments` set `comment`=?,`user`=?,`version`=?,`longdesc`=? where `attId`=?";
			$result = $this->query($query,array($comment, $user, $version, $longdesc, $attId));
		} else {
			$path = $this->getOne("select `path` from `tiki_tracker_item_attachments` where `attId`=?",array((int) $attId));
			if ($path) @unlink ($prefs['t_use_dir'] . $path);
			$query = "update `tiki_tracker_item_attachments` set `filename`=?,`filesize`=?,`filetype`=?, `data`=?,`comment`=?,`user`=?,`path`=?, `version`=?,`longdesc`=? where `attId`=?";
			$result = $this->query($query,array($filename, $size, $type, $data, $comment, $user, $fhash, $version, $longdesc, (int)$attId));
		}
		if (!$notif)
			return $attId;
		$watchers = $this->get_notification_emails($trackerId, $itemId, $options);
		if (count($watchers > 0)) {
			global $smarty;
			$trackerName = $this->getOne("select `name` from `tiki_trackers` where `trackerId`=?",array((int) $trackerId));
			$smarty->assign('mail_date', $this->now);
			$smarty->assign('mail_user', $user);
			$smarty->assign('mail_action', 'New File Atttached to Item:' . $itemId . ' at tracker ' . $trackerName);
			$smarty->assign('mail_itemId', $itemId);
			$smarty->assign('mail_trackerId', $trackerId);
			$smarty->assign('mail_trackerName', $trackerName);
			$smarty->assign('mail_attId', $attId);
			$smarty->assign('mail_data', $filename."\n".$comment."\n".$version."\n".$longdesc);
			$foo = parse_url($_SERVER["REQUEST_URI"]);
			$machine = $this->httpPrefix( true ). $foo["path"];
			$smarty->assign('mail_machine', $machine);
			$parts = explode('/', $foo['path']);
			if (count($parts) > 1)
				unset ($parts[count($parts) - 1]);
			$smarty->assign('mail_machine_raw', $this->httpPrefix( true ). implode('/', $parts));
			if (!isset($_SERVER["SERVER_NAME"])) {
				$_SERVER["SERVER_NAME"] = $_SERVER["HTTP_HOST"];
			}
			include_once ('lib/webmail/tikimaillib.php');
			$smarty->assign('server_name', $_SERVER['SERVER_NAME']);
			$desc = $this->get_isMain_value($trackerId, $itemId);
			$smarty->assign('mail_item_desc', $desc);
			foreach ($watchers as $w) {
				$mail = new TikiMail($w['user']);
				$mail->setHeader("From", $prefs['sender_email']);
				$mail->setSubject($smarty->fetchLang($w['language'], 'mail/tracker_changed_notification_subject.tpl'));
				$mail->setText($smarty->fetchLang($w['language'], 'mail/tracker_changed_notification.tpl'));
				$mail->send(array($w['email']));
			}
		}
		return $attId;
	}

	function replace_item_comment($commentId, $itemId, $title, $data, $user, $options) {
		global $smarty, $notificationlib, $prefs;
		include_once ('lib/notifications/notificationlib.php');
		$title = strip_tags($title);
		$data = strip_tags($data, "<a>");

		if ($commentId) {
			$query = "update `tiki_tracker_item_comments` set `title`=?, `data`=? , `user`=? where `commentId`=?";

			$result = $this->query($query,array($title,$data,$user,(int) $commentId));
		} else {

			$query = "insert into `tiki_tracker_item_comments`(`itemId`,`title`,`data`,`user`,`posted`) values (?,?,?,?,?)";
			$result = $this->query($query,array((int) $itemId,$title,$data,$user,(int) $this->now));
			$commentId
				= $this->getOne("select max(`commentId`) from `tiki_tracker_item_comments` where `posted`=? and `title`=? and `itemId`=?",array((int) $this->now,$title,(int)$itemId));
		}

		$trackerId = $this->getOne("select `trackerId` from `tiki_tracker_items` where `itemId`=?",array((int) $itemId));

		$watchers = $this->get_notification_emails($trackerId, $itemId, $options);

		if (count($watchers > 0)) {
			$trackerName = $this->getOne("select `name` from `tiki_trackers` where `trackerId`=?",array((int) $trackerId));
			$smarty->assign('mail_date', $this->now);
			$smarty->assign('mail_user', $user);
			$smarty->assign('mail_action', 'New comment added for item:' . $itemId . ' at tracker ' . $trackerName);
			$smarty->assign('mail_data', $title . "\n\n" . $data);
			$smarty->assign('mail_itemId', $itemId);
			$smarty->assign('mail_trackerId', $trackerId);
			$smarty->assign('mail_trackerName', $trackerName);
			$foo = parse_url($_SERVER["REQUEST_URI"]);
			$machine = $this->httpPrefix( true ). $foo["path"];
			$smarty->assign('mail_machine', $machine);
			$parts = explode('/', $foo['path']);
			if (count($parts) > 1)
				unset ($parts[count($parts) - 1]);
			$smarty->assign('mail_machine_raw', $this->httpPrefix( true ). implode('/', $parts));
			if (!isset($_SERVER["SERVER_NAME"])) {
				$_SERVER["SERVER_NAME"] = $_SERVER["HTTP_HOST"];
			}
			include_once ('lib/webmail/tikimaillib.php');
			$smarty->assign('server_name', $_SERVER['SERVER_NAME']);
			$desc = $this->get_isMain_value($trackerId, $itemId);
			$smarty->assign('mail_item_desc', $desc);
			foreach ($watchers as $w) {
				$mail = new TikiMail($w['user']);
				$mail->setHeader("From", $prefs['sender_email']);
				$mail->setSubject($smarty->fetchLang($w['language'], 'mail/tracker_changed_notification_subject.tpl'));
				$mail->setText($smarty->fetchLang($w['language'], 'mail/tracker_changed_notification.tpl'));
				$mail->send(array($w['email']));
			}
		}

		return $commentId;
	}

	function remove_item_comment($commentId) {
		$query = "delete from `tiki_tracker_item_comments` where `commentId`=?";
		$result = $this->query($query,array((int) $commentId));
	}

	function list_item_comments($itemId, $offset=0, $maxRecords=-1, $sort_mode='posted_des', $find='') {
		if ($find) {
			$findesc = '%' . $find . '%';
			$mid = " and (`title` like ? or `data` like ?)";
			$bindvars = array((int) $itemId,$findesc,$findesc);
		} else {
			$mid = "";
			$bindvars = array((int) $itemId);
		}

		$query = "select * from `tiki_tracker_item_comments` where `itemId`=? $mid order by ".$this->convertSortMode($sort_mode);
		$query_cant = "select count(*) from `tiki_tracker_item_comments` where `itemId`=? $mid";
		$ret = $this->fetchAll($query,$bindvars,$maxRecords,$offset);
		$cant = $this->getOne($query_cant,$bindvars);

		foreach ( $ret as &$res ) {
			$res["parsed"] = nl2br(htmlspecialchars($res["data"]));
		}

		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}

	function list_last_comments($trackerId = 0, $itemId = 0, $offset = -1, $maxRecords = -1) {
		global $user;
	    $mid = "1=1";
	    $bindvars = array();

	    if ($itemId != 0) {
		$mid .= " and `itemId`=?";
		$bindvars[] = (int) $itemId;
	    }

	    if ($trackerId != 0) {
		$query = "select t.* from `tiki_tracker_item_comments` t left join `tiki_tracker_items` a on t.`itemId`=a.`itemId` where $mid and a.`trackerId`=? order by t.`posted` desc";
		$bindvars[] = $trackerId;
		$query_cant = "select count(*) from `tiki_tracker_item_comments` t left join `tiki_tracker_items` a on t.`itemId`=a.`itemId` where $mid and a.`trackerId`=? order by t.`posted` desc";
	    }
	    else {
			if (!$this->user_has_perm_on_object($user, $trackerId, 'tracker', 'tiki_p_view_trackers') ) {
				return array('cant'=>0);
			}
		$query = "select t.*, a.`trackerId` from `tiki_tracker_item_comments` t left join `tiki_tracker_items` a on t.`itemId`=a.`itemId` where $mid order by `posted` desc";
		$query_cant = "select count(*) from `tiki_tracker_item_comments` where $mid";
	    }
	    $ret = $this->fetchAll($query,$bindvars,$maxRecords,$offset);
	    $cant = $this->getOne($query_cant,$bindvars);

			foreach ( $ret as &$res ) {
				if (!$trackerId && !$this->user_has_perm_on_object($user, $res['trackerId'], 'tracker', 'tiki_p_view_trackers') ) {
					--$cant;
					continue;
				}
				$res["parsed"] = nl2br($res["data"]);
			}

	    $retval = array();
	    $retval["data"] = $ret;
	    $retval["cant"] = $cant;

	    return $retval;
	}


	function get_item_comment($commentId) {
		$query = "select * from `tiki_tracker_item_comments` where `commentId`=?";
		$result = $this->query($query,array((int) $commentId));
		if (!$result->numRows()) return false;
		$res = $result->fetchRow();
		return $res;
	}

	function get_last_position($id) {
		return $this->getOne("select max(`position`) from `tiki_tracker_fields` where `trackerId` = ?",array((int)$id));
	}

	function get_tracker_item($itemid) {
		$query = "select * from `tiki_tracker_items` where `itemId`=?";

		$result = $this->query($query,array((int) $itemid));

		if (!$result->numrows())
			return false;

		$res = $result->fetchrow();
		$query = "select * from `tiki_tracker_item_fields` ttif, `tiki_tracker_fields` ttf where ttif.`fieldId`=ttf.`fieldId` and `itemId`=?";
		$result = $this->query($query,array((int) $itemid));
		$fields = array();

		while ($res2 = $result->fetchrow()) {
			$id = $res2["fieldId"];
			$res["$id".$res2["lang"].""] = $res2["value"];
		}
		return $res;
	}

	function get_item_id($trackerId,$fieldId,$value) {
		$query = "select distinct ttif.`itemId` from `tiki_tracker_items` tti, `tiki_tracker_fields` ttf, `tiki_tracker_item_fields` ttif ";
		$query.= " where tti.`trackerId`=ttf.`trackerId` and ttif.`fieldId`=ttf.`fieldId` and ttf.`trackerId`=? and ttf.`fieldId`=? and ttif.`value`=?";
		$ret = $this->getOne($query,array((int) $trackerId,(int)$fieldId,$value));
		return $ret;
	}

	function get_item($trackerId,$fieldId,$value) {
		$itemId = $this->get_item_id($trackerId,$fieldId,$value);
		return $this->get_tracker_item($itemId);
	}

	/* experimental shared */
	/* trackerId is useless */
	function get_item_value($trackerId,$itemId,$fieldId) {
		global $prefs;
		$query = "select ttif.`value`, ttif.`lang` from `tiki_tracker_item_fields` ttif where ttif.`fieldId`=? and ttif.`itemId`=? ";
		$result = $this->query($query, array((int)$fieldId, (int)$itemId));

		if (!$result->numRows()) {
			return false;
		}
		if ($this->is_multilingual($fieldId) == 'y') {
			while ($res = $result->fetchRow()) {
				if ($res['lang'] == $prefs['language']) {
					return $res['value'];
				}
				$ret = $res['value'];
			}
		} else {
			$res = $result->fetchRow();
			$ret =  $res['value'];
		}
		return $ret;
	}

	/* experimental shared */
	function get_items_list($trackerId, $fieldId, $value, $status='o') {
		$query = "select distinct tti.`itemId` from `tiki_tracker_items` tti, `tiki_tracker_item_fields` ttif ";
		$query.= " where tti.`itemId`=ttif.`itemId` and ttif.`fieldId`=? and ttif.`value`=?";
		$bindVars = array((int)$fieldId, $value);
		if (!empty($status)) {
			$query .= ' and tti.`status`=?';
			$bindVars[] = $status;
		}
		$result = $this->query($query, $bindVars);
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res['itemId'];
		}
		return $ret;
	}

        function concat_item_from_fieldslist($trackerId,$itemId,$fieldsId,$status='o',$separator=' '){
                $res='';
                $sts = preg_split('/\|/', $fieldsId, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($sts as $field){
                    $myfield=$this->get_tracker_field($field);
                    $is_date=($myfield['type']=='f');
                    $is_trackerlink=($myfield['type']=='r');

                    $tmp=$this->get_item_value($trackerId,$itemId,$field);
                    if ($is_trackerlink){
                      $options = preg_split('/,/', $myfield["options"]);
                      $tmp=$this->concat_item_from_fieldslist($options[0],$this->get_item_id($options[0],$options[1],$tmp),$options[3]);
                     }
                    if ($is_date) $tmp=$this->date_format("%e/%m/%y",$tmp);
                    $res.=$separator.$tmp;
                }
                return $res;
        }

        function concat_all_items_from_fieldslist($trackerId,$fieldsId,$status='o',$separator=' ') {
           $sts = preg_split('/\|/', $fieldsId, -1, PREG_SPLIT_NO_EMPTY);
		   $res = array();
           foreach ($sts as $field){
                $myfield=$this->get_tracker_field($field);
                $is_date=($myfield['type']=='f');
                $is_trackerlink=($myfield['type']=='r');
                $tmp="";
                $tmp=$this->get_all_items($trackerId,$field,$status, false);//deliberatly do not check perm on categs on items
                $options = preg_split('/,/', $myfield["options"]);
                foreach ($tmp as $key=>$value){
                    if ($is_date) $value=$this->date_format("%e/%m/%y",$value);
                    if ($is_trackerlink){
                      $value=$this->concat_item_from_fieldslist($options[0],$this->get_item_id($options[0],$options[1],$value),$options[3]);
                    }
					if (!empty($res[$key])) {
						$res[$key].=$separator.$value;
					} else {
						$res[$key] = $value;
                    }
                }
			}
            return $res;
        }


	function valid_status($status) {
		if ($status == 'o' || $status == 'c' || $status == 'p' || $status == 'op' || $status == 'oc'
			|| $status == 'pc' || $status == 'opc') {
			return true;
		} else {
			return false;
		}
	}
	// allfields == false will not check the perm on categ
	function get_all_items($trackerId,$fieldId,$status='o', $allfields='') {
		global $cachelib, $prefs;

		$jail = '';
		$needToCheckCategPerms = $this->need_to_check_categ_perms($allfields);
		if ($prefs['feature_categories'] == 'y' && $needToCheckCategPerms) {
			global $categlib; include_once('lib/categories/categlib.php');
			$jail = $categlib->get_jail();
		}

		$sort_mode = "value_asc";
		$cache = md5('trackerfield'.$fieldId.$status);
		if ($this->is_multilingual($fieldId) == 'y') {
			$multi_languages=$prefs['available_languages'];
			$cache = md5('trackerfield'.$fieldId.$status.$prefs['language']);
		} else {
			unset($multi_languages);
		}
		if (!empty($jail)) {
			$cache .= md5(serialize($jail));
		}

		if ( ( ! $ret = $cachelib->getSerialized($cache) ) || !$this->valid_status($status)) {
			$sts = preg_split('//', $status, -1, PREG_SPLIT_NO_EMPTY);
			$mid = "  (".implode('=? or ',array_fill(0,count($sts),'tti.`status`'))."=?) ";
			$fieldIdArray = preg_split('/\|/', $fieldId, -1, PREG_SPLIT_NO_EMPTY);
			$mid.= " and (".implode('=? or ',array_fill(0,count($fieldIdArray),'ttif.`fieldId`'))."=?) ";
			if ($this->is_multilingual($fieldId) == 'y'){
				$mid.=" and ttif.`lang`=?";
				$bindvars = array_merge($sts,$fieldIdArray,array((string)$prefs['language']));
			}else {
				$bindvars = array_merge($sts,$fieldIdArray);
			}
			$join = '';
			if (!empty($jail)) {
				$categlib->getSqlJoin($jail, 'trackeritem', 'tti.`itemId`', $join, $mid, $bindvars);
			}
			$query = "select ttif.`itemId` , ttif.`value` FROM `tiki_tracker_items` tti,`tiki_tracker_item_fields` ttif $join ";
			$query.= " WHERE  $mid and  tti.`itemId` = ttif.`itemId` order by ".$this->convertSortMode($sort_mode);
			$ret = $this->fetchAll($query,$bindvars);
			$cachelib->cacheItem($cache,serialize($ret));
		}
		if ($needToCheckCategPerms) {
			$ret = $this->filter_categ_items($ret);
		}
		$ret2 = array();
		foreach ($ret as $res) {
			$k = $res['itemId'];
			$ret2[$k] = $res['value'];
		}
		return $ret2;
	}
	function need_to_check_categ_perms($allfields='') {
		global $prefs;
		if ($allfields === false) { // use for itemlink field - otherwise will be too slow
			return false;
		}
		$needToCheckCategPerms = false;
		if ($prefs['feature_categories'] == 'y') {
			global $categlib; require_once('lib/categories/categlib.php');
			if (empty($allfields['data'])) {
				$needToCheckCategPerms = true;
			} else {
				foreach ($allfields['data'] as $f) {
					if ($f['type'] == 'e') {
						$needToCheckCategPerms = true;
						break;
					}
				}
			}
		}
		return $needToCheckCategPerms;
	}

	function get_all_tracker_items($trackerId){
		$ret = array();
		$query = "select distinct(`itemId`) from `tiki_tracker_items` where`trackerId`=?";
		$result = $this->query($query,array((int)$trackerId));
		while ($res = $result->fetchRow()) {
			$ret[] = $res['itemId'];
		}
		return $ret;
	}

	function getSqlStatus($status, &$mid, &$bindvars, $trackerId) {
		global $user;
		if (is_array($status)) {
			$status = implode('', $status);
		}

		// Check perms
		if ( $status && ! $this->user_has_perm_on_object($user, $trackerId, 'tracker', 'tiki_p_view_trackers_pending') && ! $this->group_creator_has_perm($trackerId, 'tiki_p_view_trackers_pending') ) {
			$status = str_replace('p', '', $status);
		}
		if ( $status && ! $this->user_has_perm_on_object($user, $trackerId, 'tracker', 'tiki_p_view_trackers_closed')  && ! $this->group_creator_has_perm($trackerId, 'tiki_p_view_trackers_closed') ) {
			$status = str_replace('c', '', $status);
		}

		if (!$status) {
			return false;
		} elseif ($status == 'opc') {
				return true;
		} elseif (strlen($status) > 1) {
			$sts = preg_split('//', $status, -1, PREG_SPLIT_NO_EMPTY);
			if (count($sts)) {
				$mid.= " and (".implode('=? or ',array_fill(0,count($sts),'`status`'))."=?) ";
				$bindvars = array_merge($bindvars,$sts);
			}
		} else {
			$mid.= " and tti.`status`=? ";
			$bindvars[] = $status;
		}
		return true;
	}
	function group_creator_has_perm($trackerId, $perm) {
		global $prefs;
		if ($groupCreatorFieldId = $this->get_field_id_from_type($trackerId, 'g', '1%')) {
			$tracker_info = $this->get_tracker($trackerId);
			$perms = $this->get_special_group_tracker_perm($tracker_info);
			return empty($perms[$perm])? false: true;
		} else {
			return false;
		}
	}
	/* group creator perms can only add perms,they can not take away perm
	   and they are only used if tiki_p_view_trackers is not set for the tracker and if the tracker ha a group creator field
	   must always be combined with a filter on the groups
	*/
	function get_special_group_tracker_perm($tracker_info, $global=false) {
		global $prefs, $userlib, $smarty;
		$ret = array();
		$perms = $userlib->get_object_permissions($tracker_info['trackerId'], 'tracker', $prefs['trackerCreatorGroupName']);
		foreach ($perms as $perm) {
			$ret[$perm['permName']] ='y';
			if ($global) {
				$p = $perm['permName'];
				global $$p;
				$$p = 'y';
				$smarty->assign("$p", 'y');
			}
		}
		if ($tracker_info['writerGroupCanModify'] == 'y') { // old configuration
			$ret['tiki_p_modify_tracker_items'] = 'y';
			if ($global) {
				$tiki_p_modify_tracker_items = 'y';
				$smarty->assign('tiki_p_modify_tracker_items', 'y');
			}
		}
		return $ret;
	}
	/* to filter filterfield is an array of fieldIds
	 * and the value of each field is either filtervalue or exactvalue
	 * ex: filterfield=array('1','2', 'sqlsearch'=>array('3', '4'), '5')
	 * ex: filtervalue=array(array('this', '*that'), '')
	 * ex: exactvalue= array('', array('there', 'those'), 'these', array('>'=>10))
	 * will filter items with fielId 1 with a value %this% or %that, and fieldId 2 with the value there or those, and fieldId 3 or 4 containing these and fieldId 5 > 10
	 * listfields = array(fieldId=>array('type'=>, 'name'=>...), ...)
	 * allfields is only for performance issue - check if one field is a category
	 */
	function list_items($trackerId, $offset=0, $maxRecords=-1, $sort_mode ='' , $listfields='', $filterfield = '', $filtervalue = '', $status = '', $initial = '', $exactvalue = '', $filter='', $allfields=null) {
		//echo '<pre>FILTERFIELD:'; print_r($filterfield); echo '<br />FILTERVALUE:';print_r($filtervalue); echo '<br />EXACTVALUE:'; print_r($exactvalue); echo '<br />STATUS:'; print_r($status); echo '<br />FILTER:'; print_r($filter); /*echo '<br />LISTFIELDS'; print_r($listfields);*/ echo '</pre>';
		global $prefs;

		$cat_table = '';
		$sort_tables = '';
		$sort_join_clauses = '';
		$csort_mode = '';
		$corder = '';
		$trackerId = (int)$trackerId;
		$numsort = false;

		$mid = ' WHERE tti.`trackerId` = ? ';
		$bindvars = array($trackerId);
		$join = '';

		if (!empty($filter)) {
			$mid2 = array();
			$this->parse_filter($filter, $mid2, $bindvars);
			if (!empty($mid2)) {
				$mid .= ' AND '.implode(' AND ', $mid2);
			}
		}

		if ( $status && ! $this->getSqlStatus($status, $mid, $bindvars, $trackerId) ) {
			return array('cant' => 0, 'data' => '');
		}
		if ( substr($sort_mode, 0, 2) == 'f_' ) {
			list($a, $asort_mode, $corder) = preg_split('/_/', $sort_mode);
		}
		if ( $initial ) {
			$mid .= ' AND ttif.`value` LIKE ?';
			$bindvars[] = $initial.'%';
			if (isset($asort_mode)) {
				$mid .= ' AND ttif.`fieldId` = ?';
				$bindvars[] = $asort_mode;
			}
		}
		if ( ! $sort_mode ) $sort_mode = 'lastModif_desc';

		if ( substr($sort_mode, 0, 2) == 'f_' or !empty($filterfield) ) {
			$cat_table = '';
			if ( substr($sort_mode, 0, 2) == 'f_' ) {
				$csort_mode = 'sttif.`value` ';
				if (isset($listfields[$asort_mode]['type']) && $listfields[$asort_mode]['type'] == 'l') {// item list
					$optsl = preg_split('/,/', $listfields[$asort_mode]['options']);
					$optsl[1] = preg_split('/:/', $optsl[1]);
					$sort_tables = $this->get_left_join_sql(array_merge(array($optsl[2]), $optsl[1], array($optsl[3])));
				} else {
					$sort_tables = ' LEFT JOIN (`tiki_tracker_item_fields` sttif)'
						.' ON (tti.`itemId` = sttif.`itemId`'
						." AND sttif.`fieldId` = $asort_mode"
						.')';
				}
				// Do we need a numerical sort on the field ?
				$field = $this->get_tracker_field($asort_mode);
				switch ($field['type']) {
					case 'C':
					case '*':
					case 'q':
					case 'n': $numsort = true;
						break;
					case 's': if ($field['name'] == 'Rating' || $field['name'] == tra('Rating')) {
							$numsort = true;
						}
						break;
				}
			} else {
				list($csort_mode, $corder) = preg_split('/_/', $sort_mode);
				$csort_mode = 'tti.`'.$csort_mode.'` ';
			}

			if (empty($filterfield)) {
				$nb_filtered_fields = 0;
			} elseif ( ! is_array($filterfield) ) {
				$fv = $filtervalue;
				$ev = $exactvalue;
				$ff = $filterfield;
				$nb_filtered_fields = 1;
			} else {
				$nb_filtered_fields = count($filterfield);
			}

			for ( $i = 0 ; $i < $nb_filtered_fields ; $i++ ) {
				if ( is_array($filterfield) ) { //multiple filter on an exact value or a like value - each value can be simple or an array
					$ff = $filterfield[$i];
					$ev = isset($exactvalue[$i])? $exactvalue[$i]:'';
					$fv = isset($filtervalue[$i])?$filtervalue[$i]:'' ;
				}
				$filter = $this->get_tracker_field($ff);

				// Determine if field is an item list field and postpone filtering till later if so
				if ($filter["type"] == 'l' && isset($filter['options_array'][2]) && isset($filter['options_array'][2]) && isset($filter['options_array'][3]) ) {
					$linkfilter[] = array('filterfield' => $ff, 'exactvalue' => $ev, 'filtervalue' => $fv);
					continue;
				}
				$j = ( $i > 0 ) ? '0' : '';
				$cat_table .= " INNER JOIN `tiki_tracker_item_fields` ttif$i ON (ttif$i.`itemId` = ttif$j.`itemId`)";

				if (is_array($ff['sqlsearch'])) {
					$mid .= " AND ttif$i.`fieldId` in (".implode(',', array_fill(0,count($ff['sqlsearch']),'?')).')';
					$bindvars = array_merge($bindvars, $ff['sqlsearch']);
				} elseif ( $ff ) {
					$mid .= " AND ttif$i.`fieldId`=? ";
					$bindvars[] = $ff;
				}

				if ( $filter['type'] == 'e' && $prefs['feature_categories'] == 'y' ) { //category

					$value = empty($fv) ? $ev : $fv;
					if ( ! is_array($value) && $value != '' ) {
						$value = array($value);
						$not = '';
					} elseif (is_array($value) && array_key_exists('not', $value)) {
						$value = array($value['not']);
						$not = 'not';
					}
					if (empty($not)) {
						$cat_table .= " INNER JOIN `tiki_objects` tob$ff ON (tob$ff.`itemId` = tti.`itemId`)"
							." INNER JOIN `tiki_category_objects` tco$ff ON (tob$ff.`objectId` = tco$ff.`catObjectId`)";
						$mid .= " AND tob$ff.`type` = 'trackeritem' AND tco$ff.`categId` IN ( ";
					} else {
						$cat_table .= " left JOIN `tiki_objects` tob$ff ON (tob$ff.`itemId` = tti.`itemId`)"
							." left JOIN `tiki_category_objects` tco$ff ON (tob$ff.`objectId` = tco$ff.`catObjectId`)";
						$mid .= " AND tob$ff.`type` = 'trackeritem' AND tco$ff.`categId` NOT IN ( ";
					}
					$first = true;
					foreach ( $value as $k => $catId ) {
						if (is_array($catId)) {
							// this is a grouped AND logic for optimization indicated by the value being array 
							$innerfirst = true;
							foreach ( $catId as $c ) {
								if (is_array($c)) {
									$innerfirst = true;
									foreach ($c as $d) {
										$bindvars[] = $d; 
										if ($innerfirst)  
											$innerfirst = false;
										else
											$mid .= ','; 
										$mid .= '?';
									}
								} else {
									$bindvars[] = $c;
									$mid .= '?';
								} 
							}
							if ($k < count($value) - 1 ) {
								$mid .= " ) AND ";
								if (empty($not)) {
									$ff2 = $ff . '_' . $k;
									$cat_table .= " INNER JOIN `tiki_category_objects` tco$ff2 ON (tob$ff.`objectId` = tco$ff2.`catObjectId`)";
									$mid .= "tco$ff2.`categId` IN ( ";
								} else {
									$ff2 = $ff . '_' . $k;
									$cat_table .= " left JOIN `tiki_category_objects` tco$ff2 ON (tob$ff.`objectId` = tco$ff2.`catObjectId`)";
									$mid .= "tco$ff2.`categId` NOT IN ( ";
								}
							}
						} else {
							$bindvars[] = $catId;
							if ($first)
								$first = false;
							else
								$mid .= ',';
							$mid .= '?';
						}
					}
					$mid .= " ) ";
					if (!empty($not)) {
						$mid .= " OR tco$ff.`categId` IS NULL ";
					}
				} elseif ( $filter['type'] == 'usergroups' ) {
					$userFieldId = $this->get_field_id_from_type($trackerId, 'u', '1%'); // user creator field;
					$cat_table .= " INNER JOIN `tiki_tracker_item_fields` ttifu ON (tti.`itemId`=ttifu.`itemId`) INNER JOIN `users_users` uu ON (ttifu.`value`=uu.`login`) INNER JOIN `users_usergroups` uug ON (uug.`userId`=uu.`userId`)";
					$mid .= ' AND ttifu.`fieldId`=? AND  uug.`groupName`=? ';
					$bindvars[] = $userFieldId;
					$bindvars[] = empty($ev)?$fv: $ev;
				} elseif ( $filter['type'] == '*') { // star
					$mid .= " AND ttif$i.`value`*1>=? ";
					$bindvars[] = $ev;
					if (($j = array_search($ev, $filter['options_array'])) !== false && $j+1 < count($filter['options_array'])) {
						$mid .= " AND ttif$i.`value`*1<? ";
						$bindvars[] = $filter['options_array'][$j+1];
					}
				} elseif ($ev) {
					if (is_array($ev)) {
						$keys = array_keys($ev);
						if (in_array((string)$keys[0], array('<', '>', '<=', '>='))) {
							$mid .= " AND ttif$i.`value`".$keys[0].'?';
							$bindvars[] = $ev[$keys[0]];
						} elseif ($keys[0] == 'not') {
							$mid .= " AND ttif$i.`value` not in (".implode(',', array_fill(0,count($ev),'?')).")";
							$bindvars = array_merge($bindvars, array_values($ev));
						} else {
							$mid .= " AND ttif$i.`value` in (".implode(',', array_fill(0,count($ev),'?')).")";
							$bindvars = array_merge($bindvars, array_values($ev));
						}
					} elseif (is_array($ff['sqlsearch'])) {
						$mid .= " AND MATCH(ttif$i.`value`) AGAINST(? IN BOOLEAN MODE)";
						$bindvars[] = $ev;
					} else {
						$mid.= " AND ttif$i.`value`=? ";
						$bindvars[] = empty($ev)? $fv: $ev;
					}

				} elseif ( $fv ) {
					if (!is_array($fv)) {
						$value = array($fv);
					}
					$mid .= ' AND(';
					$cpt = 0;
					foreach ($value as $v) {
						if ($cpt++)
							$mid .= ' OR ';
						$mid .= " upper(ttif$i.`value`) like upper(?) ";
						if ( substr($v, 0, 1) == '*' || substr($v, 0, 1) == '%') {
							$bindvars[] = '%'.substr($v, 1);
						} elseif ( substr($v, -1, 1) == '*' || substr($v, -1, 1) == '%') {
							$bindvars[] = substr($v, 0, strlen($v)-1).'%';
						} else {
							$bindvars[] = '%'.$v.'%';
						}
					}
					$mid .= ')';
				} elseif (empty($ev) && empty($fv)) { // test null value
					$mid.= " AND ttif$i.`value`=? OR ttif$i.`value` IS NULL";
					$bindvars[] = '';
				}
			}
		} else {
			list($csort_mode, $corder) = preg_split('/_/', $sort_mode);
			$csort_mode = "`" . $csort_mode . "`";
			if ($csort_mode == '`itemId`')
				$csort_mode = 'tti.`itemId`';
			$sort_tables = '';
			$cat_tables = '';
		}

		$needToCheckCategPerms = $this->need_to_check_categ_perms($allfields);
		if( $needToCheckCategPerms) {
			global $categlib; include_once('lib/categories/categlib.php');
			if ( $jail = $categlib->get_jail() ) {
				$categlib->getSqlJoin($jail, 'trackeritem', 'tti.`itemId`', $join, $mid, $bindvars);
			}
		}
		$base_tables = '('
			.' `tiki_tracker_items` tti'
			.' INNER JOIN `tiki_tracker_item_fields` ttif ON tti.`itemId` = ttif.`itemId`'
			.' INNER JOIN `tiki_tracker_fields` ttf ON ttf.`fieldId` = ttif.`fieldId`'
			.')'.$join;

		$query = 'SELECT tti.*, ttif.`value`, ttf.`type`'
				.', '.( ($numsort) ? "right(lpad($csort_mode,40,'0'),40)" : $csort_mode).' as `sortvalue`'
			.' FROM '.$base_tables.$sort_tables.$cat_table
			.$mid
			.' GROUP BY tti.`itemId`'
			.' ORDER BY '.$this->convertSortMode('sortvalue_'.$corder);
		//echo htmlentities($query); print_r($bindvars);
		$query_cant = 'SELECT count(DISTINCT ttif.`itemId`) FROM '.$base_tables.$sort_tables.$cat_table.$mid;

		$ret1 = $this->fetchAll($query, $bindvars, $maxRecords, $offset);
		$cant = $this->getOne($query_cant, $bindvars);
		$type = '';
		$ret = array();
		if ($needToCheckCategPerms) {
			$ret1 = $this->filter_categ_items($ret1);
		}

		foreach ($ret1 as $res) {
			$res['itemUser'] = '';
			if ($listfields !== null) {
				$res['field_values'] = $this->get_item_fields($trackerId, $res['itemId'], $listfields, $res['itemUser']);
			}
			if (!empty($asort_mode)) {
				foreach ($res['field_values'] as $i=>$field)
					if ($field['fieldId'] == $asort_mode ) {
						$kx = $field['value'].'.'.$res['itemId'];
				}
			}
			if (isset($linkfilter) && $linkfilter) {
				$filterout = false;
				// NOTE: This implies filterfield if is link field has to be in fields set
				foreach ($res['field_values'] as $i=>$field) {
					foreach ($linkfilter as $lf) {
						if ($field['fieldId'] == $lf["filterfield"]) {
							// extra comma at the front and back of filtervalue to avoid ambiguity in partial match
							if ($lf["filtervalue"] && strpos(',' . implode(',',$field['links']) . ',', $lf["filtervalue"]) === false
							|| $lf["exactvalue"] && implode(',',$field['links']) != $lf["exactvalue"] && implode(':',$field['links']) != $lf["exactvalue"] ) {
								$filterout = true;
								break 2;
							}
						}
					}	
				}
				if ($filterout) {
					continue;
				}	
			}
			if (empty($kx)) // ex: if the sort field is non visible, $kx is null
				$ret[] = $res;
			else
				$ret[$kx] = $res;
		}
		$retval = array();
		$retval['data'] = array_values($ret);
		$retval['cant'] = $cant;
		return $retval;
	}
	function filter_categ_items($ret) {
		//this is an approxomation - the perm should be function of the status
		global $categlib; include_once('lib/categories/categlib.php');
		if (empty($ret['itemId']) || $categlib->is_categorized('trackeritem', $ret['itemId'])) {
			return Perms::filter(array('type' => 'trackeritem'), 'object', $ret, array('object' => 'itemId'), 'view_trackers');
		} else {
			return $ret;
		}
	}
	/* listfields fieldId=>ooptions */
	function get_item_fields($trackerId, $itemId, $listfields, &$itemUser, $alllang=false) {
		global $prefs, $user, $tiki_p_admin_trackers;
		$fields = array();
		$fil = array();
		$kx = '';

		$bindvars = array((int)$itemId);

		$query2 = 'SELECT ttf.`fieldId`, `value`, `isPublic`, `lang`, `isMultilingual` '
			.' FROM `tiki_tracker_item_fields` ttif INNER JOIN `tiki_tracker_fields` ttf ON ttif.`fieldId` = ttf.`fieldId`'
			." WHERE `itemId` = ?";
		if (!$alllang) {
			$query2 .= " AND (`lang` = ? or `lang` is null or `lang` = '') ";
			$bindvars[] = (string)$prefs['language'];
		}
		if (!empty($listfields)) {
			$query2 .= " AND " . $this->in('ttif.fieldId', array_keys($listfields), $bindvars);
		}
		$query2 .= ' ORDER BY `position` ASC, `lang` DESC';
		$result2 = $this->fetchAll($query2, $bindvars);

		foreach( $result2 as $res1 ) {
			if ($alllang && $res1['isMultilingual'] == 'y') {
				if ($prefs['language'] == $res1['lang'])
					$fil[$res1['fieldId']] = $res1['value'];
				$sup[$res1['fieldId']]['lingualvalue'][] = array('lang' => $res1['lang'], 'value' => $res1['value']);
			} else {
				$fil[$res1['fieldId']] = $res1['value'];
			}
		}

		foreach ( $listfields as $fieldId =>$fopt ) { // be possible to need the userItem before this field
			if ($fopt['type'] == 'u' && $fopt['options_array'][0] == 1) {
				$itemUser = isset($fil[$fieldId]) ? $fil[$fieldId] : '';
			}
		}
		foreach ( $listfields as $fieldId =>$fopt ) {
			if (empty($fopt['fieldId'])) // to accept listfield as a simple table
				$fopt['fieldId'] = $fieldId;
			$fieldId = $fopt['fieldId'];
			if (isset($fil[$fieldId])) {
				$fopt['value'] = $fil[$fieldId];
			}
			if (isset($sup[$fieldId]['lingualvalue'])) {
				$fopt['lingualvalue'] = $sup[$fieldId]['lingualvalue'];
				$fopt['isMultilingual'] = 'y';
			}
			if ($tiki_p_admin_trackers != 'y') {
				if ($fopt['isHidden'] == 'y') {
					$fopt['value'] = '';
				} elseif ($fopt['isHidden'] == 'c') {
					if (empty($itemUser)) {
						$itemUser = $this->get_item_creator($trackerId, $itemId);
					}
					if ($itemUser != $user) {
						$fopt['value'] = '';
					}
				}
			}
			$fopt['linkId'] = '';
			if (!empty($fopt['options'])) {
				$fopt['options_array'] = preg_split('/\s*,\s*/', $fopt['options']);
			}
			if ($fopt['isHidden'] == 'c' && empty($itemUser)) { // need itemUser
				$itemUser = $this->get_item_creator($trackerId, $itemId);
			}
			if (!isset($fopt['value'])) {
				$fopt['isset'] = 'n';
				$fopt['value'] = '';
			}
			switch ( $fopt['type'] ) {
			case 'r':
				$fopt['links'] = array();
				$opts = preg_split('/,/', $fopt['options']);
				$fopt['linkId'] = $this->get_item_id($opts[0], $opts[1], $fopt['value']);
				$fopt['trackerId'] = $opts[0];
				break;
			case 'a':
				$fopt['pvalue'] = $this->parse_data(trim($fopt['value']));
				break;
			case 's':
			case '*':
				$this->update_star_field($trackerId, $itemId, $fopt);
				break;
			case 'e':
				//affects plugin trackerlist and tiki-view_tracker display of category for each item
				global $categlib;
				include_once('lib/categories/categlib.php');
				$itemcats = $categlib->get_object_categories('trackeritem', $itemId);
				$all_descends = (isset($fopt['options_array'][3]) && $fopt['options_array'][3] == 1);
				foreach ($itemcats as $itemcat) {
					$mycats = $categlib->get_viewable_child_categories($fopt['options_array'][0], $all_descends);
					foreach ($mycats as $acat) {
						if ($acat['categId'] == $itemcat) {
							$fopt['categs'][] = $acat;
							$fopt['value'] = $itemcat;
							break;
						}
					}
				}
				break;
			case 'l':
				if ( isset($fopt['options_array'][2]) && isset($fopt['options_array'][2]) && isset($fopt['options_array'][3])) {
					$opts[1] = preg_split('/:/', $fopt['options_array'][1]);
					$finalFields = explode('|', $fopt['options_array'][3]);
					$fopt['links'] = $this->get_join_values($trackerId, $itemId, array_merge(array($fopt['options_array'][2]), array($fopt['options_array'][1]), array($finalFields[0])), $fopt['options_array'][0], $finalFields, ' ', empty($fopt['options_array'][5])?'':$fopt['options_array'][5]);
					$fopt['trackerId'] = $fopt['options_array'][0];
				}
				if (isset($fopt['links']) && count($fopt['links']) == 1) { //if a computed field use it
					foreach ($fopt['links'] as $linkItemId=>$linkValue) {
						if (is_numeric($linkValue)) {
							$fil[$fieldId] = $linkValue;
						}
					}
				}
				break;
			case 'u':
				if ($fopt['options_array'][0] == 1) {
					$itemUser = $fopt['value'];
				}
				break;
			case 'usergroups':
				if (empty($itemUser)) {
					$itemUser = $this->get_item_creator($trackerId, $itemId);
				}
				if (!empty($itemUser)) {
					global $tikilib;
					$fopt['value'] = array_diff($tikilib->get_user_groups($itemUser), array('Registered', 'Anonymous'));
				}
				break;
			case 'p':
				if (empty($itemUser)) {
					$itemUser = $this->get_item_creator($trackerId, $itemId);
				}
				if ($fopt['options_array'][0] == 'password') {
				} elseif ($fopt['options_array'][0] == 'email' && !empty($itemUser)) {
					global $userlib;
					$fopt['value'] = $userlib->get_user_email($itemUser);
				} elseif ($fopt['options_array'][0] == 'language' && !empty($itemUser)) {
					global $userlib;
					$fopt['value'] = $userlib->get_language($itemUser);
				} elseif (!empty($itemUser)) {
					global $userlib;
					$fopt['value'] = $userlib->get_user_preference($itemUser, $fopt['options_array'][0]);
				}
				break;
			case 'N':
				if (empty($itemUser)) {
					$itemUser = $this->get_item_creator($trackerId, $itemId);
				}
				$fopt['value'] = $this->in_group_value($fopt, $itemUser);
				break;
			case 'A':
				if (!empty($fopt['options_array'][0]) && !empty($fopt['value'])) {
					$fopt['info'] = $this->get_item_attachment($fopt['value']);
				}
				break;
			case 'G':
				$vals = preg_split('/ *, */', $fopt['value']);
				$fopt['x'] = $vals[0];
				$fopt['y'] = $vals[1];
				$fopt['z'] = empty($vals[2]) ? 1 : $vals[2];
				break;
			case 'F':
				global $freetaglib;
				if (!is_object($freetaglib)) {
					include_once('lib/freetag/freetaglib.php');
				}
				$fopt["freetags"] = $freetaglib->_parse_tag($fopt['value']);
			default:
				break;
			}

			if ( isset($fopt['options']) ) {
				if ( $fopt['type'] == 'i' ) {
					global $imagegallib;
					include_once('lib/imagegals/imagegallib.php');
					if ( $imagegallib->readimagefromfile($fopt['value']) ) {
						$imagegallib->getimageinfo();
						if ( ! isset($fopt['options_array'][1]) ) $fopt['options_array'][1] = 0;
						$t = $imagegallib->ratio($imagegallib->xsize, $imagegallib->ysize, $fopt['options_array'][0], $fopt['options_array'][1] );
						$fopt['options_array'][0] = round($t * $imagegallib->xsize);
						$fopt['options_array'][1] = round($t * $imagegallib->ysize);
						if ( isset($fopt['options_array'][2]) ) {
							if ( ! isset($fopt['options_array'][3]) ) $fopt['options_array'][3] = 0;
							$t = $imagegallib->ratio($imagegallib->xsize, $imagegallib->ysize, $fopt['options_array'][2], $fopt['options_array'][3] );
							$fopt['options_array'][2] = round($t * $imagegallib->xsize);
							$fopt['options_array'][3] = round($t * $imagegallib->ysize);
						}
					}
				} elseif ( $fopt['type'] == 'r' && isset($fopt['options_array'][3]) ) {
					$fopt['displayedvalue'] = $this->concat_item_from_fieldslist(
						$fopt['options_array'][0],
						$this->get_item_id($fopt['options_array'][0], $fopt['options_array'][1], $fopt['value']),
						$fopt['options_array'][3]
					);
					$fopt = $this->set_default_dropdown_option($fopt);
				} elseif ( $fopt['type'] == 'd' || $fopt['type'] == 'D' || $fopt['type'] == 'R' ) {
					$fopt = $this->set_default_dropdown_option($fopt);
				}
			}
			$fields[] = $fopt;
		}
		return($fields);
	}
	function in_group_value($field, $itemUser) {
		if (empty($itemUser)) {
			return '';
		}
		if (!isset($this->tracker_infocache['users_group'][$field['options_array'][0]])) {
			global $userlib;
			$this->tracker_infocache['users_group'][$field['options_array'][0]] = $userlib->get_users_created_group($field['options_array'][0]);
		}
		if (isset($this->tracker_infocache['users_group'][$field['options_array'][0]][$itemUser])) {
			if (isset($field['options_array'][1]) && $field['options_array'][1] == 'date') {
				$value = $this->tracker_infocache['users_group'][$field['options_array'][0]][$itemUser];
			} else {
				$value = 'Yes';
			}
		} else {
			if (isset($field['options_array'][1]) && $field['options_array'][1] == 'date') {
				$value = '';
			} else {
				$value = 'No';
			}
		}
		return $value;
	}

	function replace_item($trackerId, $itemId, $ins_fields, $status = '', $ins_categs = 0, $bulk_import = false, $tracker_info='') {
		global $user, $smarty, $notificationlib, $prefs, $cachelib, $categlib, $tiki_p_admin_trackers, $userlib, $tikilib, $tiki_p_admin_users;
		include_once('lib/categories/categlib.php');
		include_once('lib/notifications/notificationlib.php');
		global $logslib; include_once('lib/logs/logslib.php');
		$fil = array();
		if (!empty($itemId)) { // prefill with current value - in case a computed use some other fields
			$query = 'select `value`, `fieldId` from `tiki_tracker_item_fields` where `itemId`=?';
			$result = $this->query($query, array($itemId));
			while ($res = $result->fetchRow()) {
				$fil[$res['fieldId']] = $res['value'];
			}
		}

		if (empty($tracker_info)) {
			$tracker_info = $this->get_tracker($trackerId);
			if ($options = $this->get_tracker_options($trackerId)) {
				$tracker_info = array_merge($tracker_info, $options);
			}
		}

		if (!empty($itemId)) {
			$new_itemId = 0;
			$oldStatus = $this->getOne("select `status` from `tiki_tracker_items` where `itemId`=?", array($itemId));
			if ($status) {
				$query = "update `tiki_tracker_items` set `status`=?,`lastModif`=?,`lastModifBy`=? where `itemId`=?";
				$result = $this->query($query,array($status,(int) $this->now,$user,(int) $itemId));
			} else {
				$query = "update `tiki_tracker_items` set `lastModif`=?, `lastModifBy`=? where `itemId`=?";
				$result = $this->query($query,array((int) $this->now,$user,(int) $itemId));
				$status = $oldStatus;
			}
			$version = $this->last_log_version($itemId) + 1;
			if (($logslib->add_action('Updated', $itemId, 'trackeritem', $version)) == 0) {
				$version = 0;
			}
		} else {
			if (!$status) {
				$status = $this->getOne("select `value` from `tiki_tracker_options` where `trackerId`=? and `name`=?",array((int) $trackerId,'newItemStatus'));
			}
			if (empty($status)) { $status = 'o'; }
			$query = "insert into `tiki_tracker_items`(`trackerId`,`created`,`createdBy`,`lastModif`,`lastModifBy`,`status`) values(?,?,?,?,?,?)";
			$result = $this->query($query,array((int) $trackerId,(int) $this->now,$user,(int) $this->now,$user,$status));
			$new_itemId = $this->getOne("select max(`itemId`) from `tiki_tracker_items` where `created`=? and `trackerId`=?",array((int) $this->now,(int) $trackerId));
			$logslib->add_action('Created', $new_itemId, 'trackeritem');
			$version = 0;
		}

		if ($prefs['feature_categories'] == 'y') {
			$old_categs = $categlib->get_object_categories('trackeritem', $itemId ? $itemId : $new_itemId);
			if (is_array($ins_categs)) {
				$new_categs = array_diff($ins_categs, $old_categs);
				$del_categs = array_diff($old_categs, $ins_categs);
				$remain_categs = array_diff($old_categs, $new_categs, $del_categs);
			} else {
				$new_categs = array();
				$del_categs = array();
				$remain_categs = $old_categs;
			}
		}
		if (!empty($oldStatus) || !empty($status)) {
			$the_data = '-[Status]-: ';
			$statusTypes = $this->status_types();
			if (isset($oldStatus) && $oldStatus != $status) {
				$the_data .= $statusTypes[$oldStatus]['label'] . ' -> ';
			}
			if (!empty($status)) {
				$the_data .= $statusTypes[$status]['label'] . "\n\n";
			}
			if (!empty($itemId) && $oldStatus != $status) {
			   $this->log($version, $itemId, -1, $oldStatus);
			}
		} else {
			$the_data = '';
		}

		$trackersync = false;
		if (!empty($prefs["user_trackersync_trackers"])) {
			$trackersync_trackers = preg_split('/\s*,\s*/', $prefs["user_trackersync_trackers"]);
			if (in_array($trackerId, $trackersync_trackers)) {
				$trackersync = true;
			}
		}
		if ($trackersync && !empty($prefs["user_trackersync_realname"])) {
			// Fields to concatenate are delimited by + and priority sets are delimited by , 
			$trackersync_realnamefields = preg_split('/\s*,\s*/', $prefs["user_trackersync_realname"]);
			foreach ($trackersync_realnamefields as &$r) {
				$r = preg_split('/\s*\+\s*/', $r);
			}
		}
		
		// If this is a user tracker it needs to be detected right here before actual looping of fields happen
		$trackersync_user = $user;
		foreach($ins_fields["data"] as $i=>$array) {
			if ($ins_fields['data'][$i]['type'] == 'u' && isset($ins_fields['data'][$i]['options_array'][0]) && $ins_fields['data'][$i]['options_array'][0] == '1') {
				if ($prefs['user_selector_realnames_tracker'] == 'y' && $ins_fields['data'][$i]['type'] == 'u') {
					if (!$userlib->user_exists($ins_fields['data'][$i]['value'])) {
						$finalusers = $userlib->find_best_user(array($ins_fields['data'][$i]['value']), '' , 'login');
						if (!empty($finalusers[0]) && !(isset($_REQUEST['register']) && isset($_REQUEST['name']) && $_REQUEST['name'] == $ins_fields['data'][$i]['value'])) {
							// It could be in fact that a new user is required (when no match is found or during registration even if match is found)
							$ins_fields['data'][$i]['value'] = $finalusers[0];
						}
					}
				}
				$trackersync_user = $ins_fields['data'][$i]['value'];
			}
		}
		
		foreach($ins_fields["data"] as $i=>$array) {
			if ($trackersync) {
				if (isset($trackersync_realnamefields)) {
					foreach ($trackersync_realnamefields as $index => $realnamefieldset) {
						foreach ($realnamefieldset as $index2 => $realnamefield) {
							if ($realnamefield == $ins_fields['data'][$i]["fieldId"]) {
								$trackersync_realnames[$index][$index2] = $ins_fields['data'][$i]['value']; 
							}
						}
					}
				}
			}
			if ($prefs['user_selector_realnames_tracker'] == 'y' && $ins_fields['data'][$i]['type'] == 'u') {
				if (!$userlib->user_exists($ins_fields['data'][$i]['value'])) {
					$finalusers = $userlib->find_best_user(array($ins_fields['data'][$i]['value']), '' , 'login');
					if (!empty($finalusers[0]) && !(isset($_REQUEST['register']) && isset($_REQUEST['name']) && $_REQUEST['name'] == $ins_fields['data'][$i]['value'])) {
						// It could be in fact that a new user is required (when no match is found or during registration even if match is found)
						$ins_fields['data'][$i]['value'] = $finalusers[0];
					}
				}
			}
			if ($ins_fields['data'][$i]['type'] == 'G' && isset($ins_fields['data'][$i]['options_array'][0]) && $ins_fields['data'][$i]['options_array'][0] == 'y') {
				// Set geo attributes if google map field is set as item
				global $attributelib;
				if (!is_object($attributelib)) {
					include_once('lib/attributes/attributelib.php');
				}
				$geo = explode(',', $ins_fields['data'][$i]['value']);
				if (!empty($geo[0]) && !empty($geo[1])) {
					$geoattribute = $geo;
					if ($trackersync && $prefs["user_trackersync_geo"] == 'y') {
						$trackersync_lon = $geo[0];
						$trackersync_lat = $geo[1];
					}
				}
				if (!empty($geo[2])) {
					if ($trackersync && $prefs["user_trackersync_geo"] == 'y') {
						$trackersync_zoom = $geo[2];
					}
				}
			}				
			if (!isset($ins_fields["data"][$i]["type"]) or $ins_fields["data"][$i]["type"] == 's') {
				// system type, do nothing
				continue;
			} else if ($ins_fields["data"][$i]["type"] == 'S' && !empty($ins_fields["data"][$i]['description'])) {	// static text
				
				$the_data .= '[-[' . $ins_fields["data"][$i]['name'] . "]-] -[(unchanged)]-:\n";
				if (isset($ins_fields["data"][$i]['options_array'][0]) && $ins_fields["data"][$i]['options_array'][0] == 1) {
					$the_data .= strip_tags($tikilib->parse_data( $ins_fields["data"][$i]['description']));	// parse then strip wiki markup
				} else {
					$the_data .= $ins_fields["data"][$i]['description'];
				}
				$the_data .=  "\n----------\n";
				
			} else if ($ins_fields["data"][$i]["type"] != 'u' && $ins_fields["data"][$i]["type"] != 'g' && $ins_fields["data"][$i]["type"] != 'I' && isset($ins_fields['data'][$i]['isHidden']) && ($ins_fields["data"][$i]["isHidden"] == 'p' or $ins_fields["data"][$i]["isHidden"] == 'y')and $tiki_p_admin_trackers != 'y') {
					// hidden field type require tracker amdin perm
			} elseif (empty($ins_fields["data"][$i]["fieldId"])) {
					// can have been unset for a user field
			} else {
				// -----------------------------
				// save image on disk
				if ( $ins_fields["data"][$i]["type"] == 'i' && empty($ins_fields["data"][$i]['value'])) {
					continue;
				}
				if ( $ins_fields["data"][$i]["type"] == 'i' && isset($ins_fields["data"][$i]['value'])) {
					$itId = $itemId ? $itemId : $new_itemId;
					$old_file = $this->get_item_value($trackerId, $itemId, $ins_fields["data"][$i]['fieldId']);

					if($ins_fields["data"][$i]["value"] == 'blank') {
						if(file_exists($old_file)) {
							unlink($old_file);
						}
						$ins_fields["data"][$i]["value"] = '';
					} else if( $ins_fields["data"][$i]['value'] != '' && $this->check_image_type( $ins_fields["data"][$i]['file_type'] ) ) {
						$opts = preg_split('/,/', $ins_fields['data'][$i]["options"]);
						if (!empty($opts[4])) {
							global $imagegallib;include_once('lib/imagegals/imagegallib.php');
							$imagegallib->image = $ins_fields["data"][$i]['value'];
							$imagegallib->readimagefromstring();
							$imagegallib->getimageinfo();
							if ($imagegallib->xsize > $opts[4] || $imagegallib->xsize > $opts[4]) {
								$imagegallib->rescaleImage($opts[4], $opts[4]);
								$ins_fields["data"][$i]['value'] = $imagegallib->image;
							}
						}
						if ($ins_fields["data"][$i]['file_size'] <= $this->imgMaxSize) {

							$file_name = $this->get_image_filename(	$ins_fields["data"][$i]['file_name'],
																	$itemId,
																	$ins_fields["data"][$i]['fieldId']);

							$fw = fopen( $file_name, "wb");
							fwrite($fw, $ins_fields["data"][$i]['value']);
							fflush($fw);
							fclose($fw);
							chmod($file_name, 0644); // seems necessary on some system (see move_uploaded_file doc on php.net

							$ins_fields['data'][$i]['value'] = $file_name;

							if(file_exists($old_file) && $old_file != $file_name) {
								unlink($old_file);
							}
						}
					}
					else {
						continue;
					}
				} elseif ($ins_fields['data'][$i]['type'] == 'A') { //attachment
					$perms = $tikilib->get_perm_object($trackerId, 'tracker', '', false);
					if ($perms['tiki_p_attach_trackers'] == 'y' && !empty($ins_fields['data'][$i]['file_name'])) {
						if ($prefs['t_use_db'] == 'n') {
							$fhash = md5($ins_fields['data'][$i]['file_name'].$this->now);
							if (!$fw = fopen($prefs['t_use_dir'] . $fhash, 'wb')) {
								$smarty->assign('msg', tra('Cannot write to this file:'). $fhash);
								$smarty->display("error.tpl");
								die;
							}
							if (fwrite($fw, $ins_fields['data'][$i]['value']) === false) {
								$smarty->assign('msg', tra('Cannot write to this file:'). $fhash);
								$smarty->display("error.tpl");
								die;
							}
							fclose($fw);
							$ins_fields['data'][$i]['value'] = '';
						} else {
							$fhash = 0;
						}
						$ins_fields['data'][$i]['value'] = $this->replace_item_attachment($ins_fields['data'][$i]['old_value'], $ins_fields['data'][$i]['file_name'], $ins_fields['data'][$i]['file_type'], $ins_fields['data'][$i]['file_size'], $ins_fields['data'][$i]['value'], '', $user, $fhash, '', '', $trackerId, $itemId ? $itemId : $new_itemId, '', false);
					} else {
						continue;
					}
				} elseif ($ins_fields['data'][$i]['type'] == 'k') { //page selector
					if ($ins_fields['data'][$i]['value'] != '') {
						if (!$this->page_exists($ins_fields['data'][$i]['value'])) {
							$opts = preg_split('/,/', $ins_fields['data'][$i]['options']);
							if (!empty($opts[2])) {
								global $IP;
								$info = $this->get_page_info($opts[2]);
								$this->create_page($ins_fields['data'][$i]['value'], 0, $info['data'], $this->now, '', $user, $IP, $info['description'], $info['lang'], $info['is_html'], array(), $info['wysiwyyg'], $info['wiki_authors_style']);
							}
						}
					}
				}

			// Handle truncated fields. Only for textareas which have option 3 set
			if ( $ins_fields["data"][$i]["type"] == 'a' && isset($ins_fields["data"][$i]["options_array"][3]) && ($ins_fields["data"][$i]['options_array'][3]) ) {
				if (function_exists('mb_substr') && function_exists('mb_strlen')) {
					if ( mb_strlen($ins_fields["data"][$i]['value']) > $ins_fields["data"][$i]['options_array'][3] ) {
						$ins_fields['data'][$i]['value'] = mb_substr($ins_fields["data"][$i]['value'],0,$ins_fields["data"][$i]['options_array'][3]);
					}
				} else {
					if ( strlen($ins_fields["data"][$i]['value']) > $ins_fields["data"][$i]['options_array'][3] ) {
						$ins_fields['data'][$i]['value'] = substr($ins_fields["data"][$i]['value'],0,$ins_fields["data"][$i]['options_array'][3]);
					}
				}
			}

			// Normalize on/y on a checkbox
			if ($ins_fields["data"][$i]["type"] == 'c' && $ins_fields['data'][$i]['value'] == 'on') {
				$ins_fields['data'][$i]['value'] = 'y';
			}

			if ($ins_fields['data'][$i]['type'] == 'g' && $ins_fields['data'][$i]['options_array'][0] == 1) {
				$creatorGroupFieldId = $ins_fields['data'][$i]['fieldId'];
				if ($prefs['groupTracker'] == 'y' && isset($tracker_info['autoCreateGroup']) && $tracker_info['autoCreateGroup'] == 'y' && empty($itemId)) {
					$groupName = $this->groupName($tracker_info, $new_itemId, $groupInc);
					$ins_fields['data'][$i]['value'] = $groupName;
				}
			}

			// Handle freetagging
			if ($ins_fields["data"][$i]["type"] == 'F') {
				if ($prefs['feature_freetags'] == 'y') {
    				global $freetaglib;
    				if (!is_object($freetaglib)) {
						include_once('lib/freetag/freetaglib.php');
    				}
    				$freetaglib->update_tags($user, $itemId ? $itemId : $new_itemId, 'trackeritem', $ins_fields["data"][$i]["value"]);
    			}
			}
			
				// ---------------------------
                if (isset($ins_fields["data"][$i]["fieldId"]))
				   $fieldId = $ins_fields["data"][$i]["fieldId"];
				if (isset($ins_fields["data"][$i]["name"])) {
					$name = $ins_fields["data"][$i]["name"];
				} else {
					$name = $this->getOne("select `name` from `tiki_tracker_fields` where `fieldId`=?",array((int)$fieldId));
				}
				$value = @ $ins_fields["data"][$i]["value"];

				if (isset($ins_fields['data'][$i]['type']) and $ins_fields['data'][$i]['type'] == 'C') {
					$calc = preg_replace('/#([0-9]+)/', '$fil[\1]', $ins_fields['data'][$i]['options']);
					eval('$value = '.$calc.';');

				} elseif (isset($ins_fields["data"][$i]["type"]) and $ins_fields["data"][$i]["type"] == 'q') {
					if (isset($ins_fields["data"][$i]['options_array'][3]) && $ins_fields["data"][$i]['options_array'][3] == 'itemId') {
						$value = $itemId?$itemId: $new_itemId;
					} elseif ($itemId == false) {
						$value = $this->getOne("select max(cast(`value` as UNSIGNED)) from `tiki_tracker_item_fields` where `fieldId`=?",array((int)$fieldId));
						if ($value == NULL) {
							$value = isset($ins_fields["data"][$i]['options_array'][0]) ? $ins_fields["data"][$i]['options_array'][0] : 1;
						} else {
							$value += 1;
						}
					}
				}
				if ($ins_fields['data'][$i]['type']=='*') {
					$this->replace_star($ins_fields['data'][$i]['value'], $trackerId, $itemId, $ins_fields['data'][$i], $user, false);
				}

				if ($ins_fields["data"][$i]["type"] == 'e' && $prefs['feature_categories'] == 'y') {
				// category type

					$my_categs = $categlib->get_child_categories($ins_fields['data'][$i]["options"]);
					$aux = array();
					foreach ($my_categs as $cat) {
						$aux[] = $cat['categId'];
					}
					$my_categs = $aux;

					$my_new_categs = array_intersect($new_categs, $my_categs);
					$my_del_categs = array_intersect($del_categs, $my_categs);
					$my_remain_categs = array_intersect($remain_categs, $my_categs);
					if (!empty($itemId) && (!empty($my_new_categs) || !empty($my_del_categs))) {
						$this->log($version, $itemId, $ins_fields['data'][$i]['fieldId'], implode(',', $old_categs));
					}


					if (count($my_new_categs) + count($my_del_categs) == 0) {
							$the_data .= "$name -[(unchanged)]-:\n";
					} else {
							$the_data .= "$name :\n";
					}

					if (count($my_new_categs) > 0) {
							$the_data .= "  -[Added]-:\n";
							$the_data .= $this->_describe_category_list($my_new_categs);
					}
					if (count($my_del_categs) > 0) {
							$the_data .= "  -[Removed]-:\n";
							$the_data .= $this->_describe_category_list($my_del_categs);
					}
					if (count($my_remain_categs) > 0) {
							$the_data .= "  -[Remaining]-:\n";
							$the_data .= $this->_describe_category_list($my_remain_categs);
					}
					$the_data .= "\n";

					if ($itemId) {
						$query = "select `itemId`, `value` as `old_value` from `tiki_tracker_item_fields` where `itemId`=? and `fieldId`=?";
						if ($this->getOne($query,array((int) $itemId, (int) $fieldId))) {
							$query = "update `tiki_tracker_item_fields` set `value`=? where `itemId`=? and `fieldId`=?";
							$this->query($query,array('',(int) $itemId,(int) $fieldId));
						} else {
							$query = "insert into `tiki_tracker_item_fields`(`itemId`,`fieldId`,`value`) values(?,?,?)";
							$this->query($query,array((int) $itemId,(int) $fieldId,''));
						}
					} else {
						$query = "insert into `tiki_tracker_item_fields`(`itemId`,`fieldId`,`value`) values(?,?,?)";
						$this->query($query,array((int) $new_itemId,(int) $fieldId,''));
					}
				} elseif ((isset($ins_fields['data'][$i]['isMultilingual']) && $ins_fields['data'][$i]['isMultilingual'] == 'y') && ($ins_fields['data'][$i]['type'] =='a' || $ins_fields['data'][$i]['type']=='t')){

					if (!isset($multi_languages))
						$multi_languages=$prefs['available_languages'];
					if (empty($ins_fields["data"][$i]['lingualvalue'])) {
						$ins_fields["data"][$i]['lingualvalue'][] = array('lang'=>$prefs['language'], 'value'=>$ins_fields["data"][$i]['value']);
					}

					foreach ($ins_fields["data"][$i]['lingualvalue'] as $linvalue) {
						if ($itemId) {
							$result = $this->query('select `value` from `tiki_tracker_item_fields` where `itemId`=? and `fieldId`=? and `lang`=?',array((int) $itemId,(int) $fieldId,(string)$linvalue['lang']));
							if ($row = $result->fetchRow()){
								$old_value = $row['value'];
								$query = "update `tiki_tracker_item_fields` set `value`=? where `itemId`=? and `fieldId`=? and `lang`=?";
								$result=$this->query($query,array($linvalue['value'],(int) $itemId,(int) $fieldId,(string)$linvalue['lang']));
							}else{
								$old_value = '';
								$query = "insert into `tiki_tracker_item_fields`(`itemId`,`fieldId`,`value`,`lang`) values(?,?,?,?)";
								$result=$this->query($query,array((int) $itemId,(int) $fieldId,(string)$linvalue['value'],(string)$linvalue['lang']));
							}
						} else {
							//echo "error in this insert";
							$query = "insert into `tiki_tracker_item_fields`(`itemId`,`fieldId`,`value`,`lang`) values(?,?,?,?)";
							$this->query($query,array((int) $new_itemId,(int) $fieldId,(string)$linvalue['value'],(string)$linvalue['lang']));
						}
						if (!empty($itemId) && $old_value != $linvalue['value']) {
						   $this->log($version, $itemId, $ins_fields['data'][$i]['fieldId'], $old_value, $linvalue['lang']);
						}
					}
				} elseif ($ins_fields['data'][$i]['type']=='p' && ($user == $trackersync_user || $tiki_p_admin_users == 'y')) {
					if ($ins_fields['data'][$i]['options_array'][0] == 'password') {
						if (!empty($ins_fields['data'][$i]['value']) && $prefs['change_password'] == 'y' && ($e = $userlib->check_password_policy($ins_fields['data'][$i]['value'])) == '') {
							$userlib->change_user_password($trackersync_user, $ins_fields['data'][$i]['value']);
						}
						if (!empty($itemId)) {
						   $this->log($version, $itemId, $ins_fields['data'][$i]['fieldId'], '?');
						}
					} elseif ($ins_fields['data'][$i]['options_array'][0] == 'email') {
						if (!empty($ins_fields['data'][$i]['value']) && validate_email($ins_fields['data'][$i]['value'])) {
							$old_value = $userlib->get_user_email($trackersync_user);
							$userlib->change_user_email($trackersync_user, $ins_fields['data'][$i]['value']);
						}
						if (!empty($itemId) && $old_value != $ins_fields['data'][$i]['value']) {
						   $this->log($version, $itemId, $ins_fields['data'][$i]['fieldId'], $old_value);
						}
					} else {
						$old_value = $tikilib->get_user_preference($trackersync_user, $ins_fields['data'][$i]['options_array'][0]);
						$tikilib->set_user_preference($trackersync_user, $ins_fields['data'][$i]['options_array'][0], $ins_fields['data'][$i]['value']);
						if (!empty($itemId) && $old_value != $ins_fields['data'][$i]['value']) {
						   $this->log($version, $itemId, $ins_fields['data'][$i]['fieldId'], $ins_fields['data'][$i]['value']);
						}
					}
				} else {

					$is_date = (isset($ins_fields["data"][$i]["type"]) and ($ins_fields["data"][$i]["type"] == 'f' or $ins_fields["data"][$i]["type"] == 'j'));

					$is_visible = !isset($ins_fields["data"][$i]["isHidden"]) || $ins_fields["data"][$i]["isHidden"] == 'n';

					if ($itemId && $ins_fields['data'][$i]['type'] == 'q') { // do not change autoincrement of an item
					} elseif ($itemId) {
						$result = $this->query('select `value` from `tiki_tracker_item_fields` where `itemId`=? and `fieldId`=?',array((int) $itemId,(int) $fieldId));
						if ($row = $result->fetchRow()) {
							$old_value = $row['value'];
							if ($is_visible) {
								if ($is_date) {
									$dformat = $prefs['short_date_format'].' '.$prefs['short_time_format'];
									$old_value = $this->date_format($dformat, (int)$old_value);
									$new_value = $this->date_format($dformat, (int)$value);
								} else {
									$new_value = $value;
								}
								if ($old_value != $new_value) {
									// split old value by lines
									$lines = preg_split("/\n/", $old_value);
									// mark every old value line with standard email reply character
									$old_value_lines = '';
									foreach ($lines as $line) {
										$old_value_lines .= '> '.$line;
									}
									$the_data .= "[-[$name]-]:\n--[Old]--:\n$old_value_lines\n\n*-[New]-*:\n$new_value\n----------\n";
									if (!empty($itemId)) {
									   $this->log($version, $itemId, $ins_fields['data'][$i]['fieldId'], $old_value);
									}
								} else {
									$the_data .= "[-[$name]-] -[(unchanged)]-:\n$new_value\n----------\n";
								}
							}

							$query = "update `tiki_tracker_item_fields` set `value`=? where `itemId`=? and `fieldId`=?";
							$this->query($query,array($value,(int) $itemId,(int) $fieldId));
							$this->update_item_link_value($trackerId, $fieldId, $old_value, $value);
						} else {
							if ($is_visible) {
								if ($is_date) {
									$dformat = $prefs['short_date_format'].' '.$prefs['short_time_format'];
									$new_value = $this->date_format($dformat, (int)$value);
								} else {
									$new_value = $value;
								}
								$the_data .= "[-[$name]-]:\n$new_value\n----------\n";
							}
							$query = "insert into `tiki_tracker_item_fields`(`itemId`,`fieldId`,`value`) values(?,?,?)";
							$this->query($query,array((int) $itemId,(int) $fieldId,(string)$value));
						}
					} else {
						if ($is_visible) {
							if ($is_date) {
								$new_value = $this->date_format("%a, %e %b %Y %H:%M:%S %O",(int)$value);
							} else {
								$new_value = $value;
							}
							$the_data .= "[-[$name]-]:\n$new_value\n----------\n";
						}

						$query = "insert into `tiki_tracker_item_fields`(`itemId`,`fieldId`,`value`) values(?,?,?)";
						$this->query($query,array((int) $new_itemId,(int) $fieldId,(string)$value));
					}
					
					$fil[$fieldId] = $value;
					$cachelib->invalidate(md5('trackerfield'.$fieldId.'o'));
					$cachelib->invalidate(md5('trackerfield'.$fieldId.'c'));
					$cachelib->invalidate(md5('trackerfield'.$fieldId.'p'));
					$cachelib->invalidate(md5('trackerfield'.$fieldId.'op'));
					$cachelib->invalidate(md5('trackerfield'.$fieldId.'oc'));
					$cachelib->invalidate(md5('trackerfield'.$fieldId.'pc'));
					$cachelib->invalidate(md5('trackerfield'.$fieldId.'opc'));
				}
			}
		}

		if ($prefs['groupTracker'] == 'y' && isset($tracker_info['autoCreateGroup']) && $tracker_info['autoCreateGroup'] == 'y' && empty($itemId)) {
			if (!empty($creatorGroupFieldId) && !empty($tracker_info['autoAssignGroupItem']) && $tracker_info['autoAssignGroupItem'] == 'y') {
				if (!empty($tracker_info['autoCopyGroup'])) {
					global $group;
					$query = "insert into `tiki_tracker_item_fields`(`itemId`,`fieldId`,`value`) values(?,?,?)";
					$this->query($query, array($new_itemId, $tracker_info['autoCopyGroup'], $group));
				}
				
			}
			$desc = $this->get_isMain_value($trackerId, $new_itemId);
			if (empty($desc))
				$desc = $tracker_info['description'];
			if ($userlib->add_group($groupName, $desc, '', 0, $trackerId, '', 'y', 0, '', '', $creatorGroupFieldId)) {
				if (!empty($tracker_info['autoCreateGroupInc'])) {
					$userlib->group_inclusion($groupName, $groupInc);
				}
			}
			if ($tracker_info['autoAssignCreatorGroup'] == 'y') {
				$userlib->assign_user_to_group($user, $groupName);
			}
			if ($tracker_info['autoAssignCreatorGroupDefault'] == 'y') {
				$userlib->set_default_group($user, $groupName);
				$_SESSION['u_info']['group'] = $groupName;
			}
		}

		// Don't send a notification if this operation is part of a bulk import
		if(!$bulk_import) {
			$options = $this->get_tracker_options( $trackerId );
			$watchers = $this->get_notification_emails($trackerId, $itemId, $options, $new_itemId, $status, isset($oldStatus)?$oldStatus: '');

			if (count($watchers) > 0) {
				if( array_key_exists( "simpleEmail", $options ) ) {
					$simpleEmail = $options["simpleEmail"];
				} else {
					$simpleEmail = "n";
				}
				$trackerName = $this->getOne("select `name` from `tiki_trackers` where `trackerId`=?",array((int) $trackerId));
				if (!isset($_SERVER["SERVER_NAME"])) {
					$_SERVER["SERVER_NAME"] = $_SERVER["HTTP_HOST"];
				}
				include_once('lib/webmail/tikimaillib.php');
				if( $simpleEmail == "n" ) {
					if (empty($desc)) {
						$desc = $this->get_isMain_value($trackerId, $itemId ? $itemId : $new_itemId);
					}
					if ($options['doNotShowEmptyField'] === 'y') {	// remove empty fields if tracker says so
						$the_data = preg_replace('/\[-\[.*?\]-\] -\[\(.*?\)\]-:\n\n----------\n/', '', $the_data);
					}
					$smarty->assign('mail_date', $this->now);
					$smarty->assign('mail_user', $user);
					if ($itemId) {
						$smarty->assign('mail_itemId', $itemId);
					} else {
						$smarty->assign('mail_itemId', $new_itemId);
					}
					$smarty->assign('mail_item_desc', $desc);
					$smarty->assign('mail_trackerId', $trackerId);
					$smarty->assign('mail_trackerName', $trackerName);
					$smarty->assign('server_name', $_SERVER['SERVER_NAME']);
					$foo = parse_url($_SERVER["REQUEST_URI"]);
					$machine = $this->httpPrefix( true ). $foo["path"];
					$smarty->assign('mail_machine', $machine);
					$parts = explode('/', $foo['path']);
					if (count($parts) > 1)
						unset ($parts[count($parts) - 1]);
					$smarty->assign('mail_machine_raw', $this->httpPrefix( true ). implode('/', $parts));
					$smarty->assign_by_ref('status', $status);
					foreach ($watchers as $watcher) {
						if ($itemId) {
							$mail_action = "\r\n".tra('Item Modification', $watcher['language'])."\r\n\r\n";
							$mail_action.= tra('Tracker', $watcher['language']).":\n   ".$trackerName."\r\n";
							$mail_action.= tra('Item', $watcher['language']).":\n   ".$itemId . ' ' . $desc;
						} else {
							$mail_action = "\r\n".tra('Item creation', $watcher['language'])."\r\n\r\n";
							$mail_action.= tra('Tracker', $watcher['language']).': '.$trackerName;
							$mail_action.= tra('Item', $watcher['language']).":\n   ".$new_itemId . ' ' . $desc;
						}
						$smarty->assign('mail_action', $mail_action);
						$smarty->assign('mail_data', $the_data);
						if (isset($watcher['action']))
							$smarty->assign('mail_action', $watcher['action']);


						$mail_data = $smarty->fetchLang($watcher['language'], 'mail/tracker_changed_notification.tpl');

						$mail = new TikiMail($watcher['user']);
						$mail->setSubject($smarty->fetchLang($watcher['language'], 'mail/tracker_changed_notification_subject.tpl'));
						$mail->setText($mail_data);
						$mail->setHeader("From", $prefs['sender_email']);
						$mail->send(array($watcher['email']));
					}
				} else {
			    		// Use simple email
					$foo = parse_url($_SERVER["REQUEST_URI"]);
					$machine = $this->httpPrefix( true ). $foo["path"];
					$parts = explode('/', $foo['path']);
					if (count($parts) > 1) {
						unset ($parts[count($parts) - 1]);
					}
					$machine = $this->httpPrefix( true ). implode('/', $parts);
					if (!$itemId) {
						$itemId = $new_itemId;
					}

			    		global $userlib;

						if (!empty($user)) {
							$my_sender = $userlib->get_user_email($user);
						} else { // look if a email field exists
							$fieldId = $this->get_field_id_from_type($trackerId, 'm');
							if (!empty($fieldId))
								$my_sender = $this->get_item_value($trackerId, (!empty($itemId)? $itemId:$new_itemId), $fieldId);
						}


			    	// Try to find a Subject in $the_data looking for strings marked "-[Subject]-" TODO: remove the tra (language translation by submitter)
			    	$the_string = '/^\[-\['.tra('Subject').'\]-\] -\[[^\]]*\]-:\n(.*)/m';
			    	$subject_test_unchanged = preg_match( $the_string, $the_data, $unchanged_matches );
			    	$the_string = '/^\[-\['.tra('Subject').'\]-\]:\n(.*)\n(.*)\n\n(.*)\n(.*)/m';
			    	$subject_test_changed = preg_match( $the_string, $the_data, $matches );
						$subject = '';

			    	if( $subject_test_unchanged == 1 ) {
							$subject = $unchanged_matches[1];
			    	}
			    	if( $subject_test_changed == 1 ) {
							$subject = $matches[1].' '.$matches[2].' '.$matches[3].' '.$matches[4];
			    	}

						$i = 0;
						foreach ($watchers as $watcher) {
							$mail = new TikiMail($watcher['user']);
							// first we look for strings marked "-[...]-" to translate by watcher language
							$translate_strings[$i] = preg_match_all( '/-\[([^\]]*)\]-/', $the_data, $tra_matches );
							$watcher_subject = $subject;
							$watcher_data = $the_data;
							if ($translate_strings[$i] > 0) {
								foreach ($tra_matches[1] as $match) {
									// now we replace the marked strings with correct translations
									$tra_replace = tra($match, $watcher['language']);
									$tra_match = "/-\[".preg_quote($match)."\]-/m";
									$watcher_subject = preg_replace($tra_match, $tra_replace, $watcher_subject);
									$watcher_data = preg_replace($tra_match, $tra_replace, $watcher_data);
								}
							}

							$mail->setSubject('['.$trackerName.'] '.str_replace('> ','',$watcher_subject).' ('.tra('Tracker was modified at ', $watcher['language']). $_SERVER["SERVER_NAME"].' '.tra('by', $watcher['language']).' '.$user.')');
							$mail->setText(tra('View the tracker item at:', $watcher['language'])."  $machine/tiki-view_tracker_item.php?itemId=$itemId\n\n" . $watcher_data);
							if( ! empty( $my_sender ) ) {
								$mail->setHeader("Reply-To", $my_sender);
							}
							$mail->send(array($watcher['email']));
							$i++;
						}
				}
			}
		}

		$cant_items = $this->getOne("select count(*) from `tiki_tracker_items` where `trackerId`=?",array((int) $trackerId));
		$query = "update `tiki_trackers` set `items`=?,`lastModif`=?  where `trackerId`=?";
		$result = $this->query($query,array((int)$cant_items,(int) $this->now,(int) $trackerId));

		if (!$itemId) $itemId = $new_itemId;

		global $cachelib;
		require_once('lib/cache/cachelib.php');
		$cachelib->invalidate('trackerItemLabel'.$itemId);

		if ( isset($tracker_info['autoCreateCategories']) && $tracker_info['autoCreateCategories'] == 'y' && $prefs['feature_categories'] == 'y' ) {
			$tracker_item_desc = $this->get_isMain_value($trackerId, $itemId);

			// Verify that parentCat exists Or Create It
			$parentcategId = $categlib->get_category_id("Tracker $trackerId");
			if ( ! isset($parentcategId) ) {
				$parentcategId = $categlib->add_category(0,"Tracker $trackerId",$tracker_info['description']);
			}
			// Verify that the sub Categ doesn't already exists
			$currentCategId = $categlib->get_category_id("Tracker Item $itemId");
			if ( ! isset($currentCategId) || $currentCategId == 0 ) {
				$currentCategId = $categlib->add_category($parentcategId,"Tracker Item $itemId",$tracker_item_desc);
			} else {
				$categlib->update_category($currentCategId, "Tracker Item $itemId", $tracker_item_desc, $parentcategId);
			}
			$cat_type = "trackeritem";
			$cat_objid = $itemId;
			$cat_desc = '';
			$cat_name = "Tracker Item $itemId";
			$cat_href = "tiki-view_tracker_item.php?trackerId=$trackerId&itemId=$itemId";
			// ?? HAS to do it ?? $categlib->uncategorize_object($cat_type, $cat_objid);
			$catObjectId = $categlib->is_categorized($cat_type, $cat_objid);
			if ( ! $catObjectId ) {
				$catObjectId = $categlib->add_categorized_object($cat_type, $cat_objid, $cat_desc, $cat_name, $cat_href);
			}
			$categlib->categorize($catObjectId, $currentCategId);
		}

		if ( $prefs['feature_search'] == 'y' && $prefs['feature_search_fulltext'] != 'y' && $prefs['search_refresh_index_mode'] == 'normal' ) {
			require_once('lib/search/refresh-functions.php');
			refresh_index('tracker_items', $itemId);
		}
		$parsed = '';
		foreach($ins_fields["data"] as $i=>$array) {
			if ($ins_fields['data'][$i]['type'] == 'a') {
				$parsed .= $ins_fields['data'][$i]['value']."\n";
				if (!empty($ins_fields["data"][$i]['lingualvalue'])) {
					foreach ($ins_fields["data"][$i]['lingualvalue'] as $linvalue) {
						$parsed .= $linvalue['value']."\n";
					}
				}
			}
		}
		if (!empty($trackersync_realnames)) {
			ksort($trackersync_realnames);
			$trackersync_realnames = array_reverse($trackersync_realnames);
			foreach ($trackersync_realnames as $realname) {
				$t_r = '';
				foreach($realname as $r) {
					if ($t_r && $r) {
						$t_r .= " ";
					}
					$t_r .= $r;
				}
				if ($t_r) {
					$trackersync_realname = $t_r;
				}
			}
			if (!empty($trackersync_realname)) {
				$tikilib->set_user_preference($trackersync_user, 'realName', $trackersync_realname);
			}
			if (!empty($trackersync_lon)) {
				$tikilib->set_user_preference($trackersync_user, 'lon', $trackersync_lon);
			}
			if (!empty($trackersync_lat)) {
				$tikilib->set_user_preference($trackersync_user, 'lat', $trackersync_lat);
			}
			if (!empty($trackersync_zoom)) {
				$tikilib->set_user_preference($trackersync_user, 'zoom', $trackersync_zoom);
			}
		}
		if ($trackersync && $prefs['user_trackersync_groups'] == 'y') {
			$sig_catids = $categlib->get_category_descendants($prefs['user_trackersync_parentgroup']);
			$sig_add = array_intersect($sig_catids, $new_categs);
			$sig_del = array_intersect($sig_catids, $del_categs);
			$groupList = $userlib->list_all_groups();
			foreach ($sig_add as $c) {
				$groupName = $categlib->get_category_name($c, true);
				if (in_array($groupName, $groupList)) {
					$userlib->assign_user_to_group($trackersync_user, $groupName);
				}
			}
			foreach ($sig_del as $c) {
				$groupName = $categlib->get_category_name($c, true);
				if (in_array($groupName, $groupList)) {
					$userlib->remove_user_from_group($trackersync_user, $groupName);
				}
			}
		}
		if (!empty($parsed)) {
			$this->object_post_save( array('type'=>'trackeritem', 'object'=>$itemId, 'name' => "Tracker Item $itemId", 'href'=>"tiki-view_tracker_item.php?itemId=$itemId"), array( 'content' => $parsed ));
		}

		if (!empty($geoattribute) && $itemId) {
			$attributelib->set_attribute('trackeritem', $itemId, 'tiki.geo.lon', $geoattribute[0]);
			$attributelib->set_attribute('trackeritem', $itemId, 'tiki.geo.lat', $geoattribute[1]);
			if (!empty($geoattribute[2])) {
				$attributelib->set_attribute('trackeritem', $itemId, 'tiki.geo.google.zoom', $geoattribute[2]);
			}
		}
		return $itemId;
	}

	function groupName($tracker_info, $itemId, &$groupInc) {
		if (empty($tracker_info['autoCreateGroupInc'])) {
			$groupName = $tracker_info['name'];
		} else {
			global $userlib;
			$group_info = $userlib->get_groupId_info($tracker_info['autoCreateGroupInc']);
			$groupInc = $groupName = $group_info['groupName'];
		}
		return "$groupName $itemId";
	}

	function _format_data($field, $data) {
		$data = trim($data);
		if($field['type'] == 'a') {
			if(isset($field["options_array"][3]) and $field["options_array"][3] > 0 and strlen($data) > $field["options_array"][3]) {
				$data = substr($data,0,$field["options_array"][3])." (...)";
			}
		} elseif ($field['type'] == 'c') {
			if($data != 'y') $data = 'n';
		}
		return $data;
	}

	/* Experimental feature.
	 * PHP's execution time limit of 30 seconds may have to be extended when
	 * importing large files ( > 1000 items).
	 */
	function import_items($trackerId, $indexField, $csvHandle, $csvDelimiter = "," , $replace = true) {

		// Read the first line.  It contains the names of the fields to import
		if (($data = fgetcsv($csvHandle, 4096, $csvDelimiter)) === FALSE) return -1;
		$nColumns = count($data);
		for ($i = 0; $i < $nColumns; $i++) {
			$data[$i] = trim($data[$i]);
		}
		$fields = $this->list_tracker_fields($trackerId, 0, -1, 'position_asc', '');
		$temp_max = count($fields["data"]);
		$indexId = -1;
		for ($i = 0; $i < $temp_max; $i++) {
			$column[$i] = -1;
			for ($j = 0; $j < $nColumns; $j++) {
				if($fields["data"][$i]['name'] == $data[$j]) {
					$column[$i] = $j;
				}
				if($indexField == $data[$j]) {
					$indexId = $j;
				}
			}
		}

		// If a primary key was specified, check that it was found among the columns of the file
		if($indexField && $indexId == -1) return -1;

		$total = 0;
		while (($data = fgetcsv($csvHandle, 4096, $csvDelimiter)) !== FALSE) {
			$status = array_shift($data);
			$itemId = array_shift($data);
			for ($i = 0; $i < $temp_max-2; $i++) {
				if (isset($data[$i])) {
					$fields["data"][$i]['value'] = $data[$i];
				} else {
					$fields["data"][$i]['value'] = "";
				}
			}
			$this->replace_item($trackerId, $itemId, $fields, $status, array(), true);
			$total++;
		}

		// TODO: Send a notification indicating that an import has been done on this tracker

		return $total;
	}

	/**
	 * Called from tiki-admin_trackers.php import button
	 * 
	 * @param int		$trackerId
	 * @param resource	$csvHandle 		file handle to import
	 * @param bool		$replace_rows 	make new items for those with existing itemId
	 * @param string	$dateFormat 	used for item fields of type date
	 * @param string	$encoding 		defaults "UTF8"
	 * @param string	$csvDelimiter 	defaults to ","
	 * @return number	items imported
	 */
	function import_csv($trackerId, $csvHandle, $replace_rows = true, $dateFormat='', $encoding='UTF8', $csvDelimiter=',') {
		global $tikilib;
		$tracker_info = $this->get_tracker_options($trackerId);
		if (($header = fgetcsv($csvHandle,100000,  $csvDelimiter)) === FALSE) {
			return 'Illegal first line';
		}
		if ($encoding == 'UTF-8') {
			// See en.wikipedia.org/wiki/Byte_order_mark
			if (substr($header[0],0,3) == "\xef\xbb\xbf") {
				$header[0] = substr($header[0],3);
			}
		}
		$max = count($header);
		for ($i = 0; $i < $max; $i++) {
			if ($encoding == 'ISO-8859-1') {
				$header[$i] = utf8_encode($header[$i]);
			}
			$header[$i] = preg_replace('/ -- [0-9]*$/', ' -- ', $header[$i]);
		}
		if (count($header) != count(array_unique($header))) {
			return 'Duplicate header names';
		}
		$total = 0;
		$need_reindex = array();
		$fields = $this->list_tracker_fields($trackerId, 0, -1, 'position_asc', '');
		while (($data = fgetcsv($csvHandle,100000,  $csvDelimiter)) !== FALSE) {
			$status = 'o';
			$itemId = 0;
			$created = $tikilib->now;
			$lastModif = $created;
			$cats = '';
			for ($i = 0; $i < $max; $i++) {
				if ($encoding == 'ISO-8859-1') {
					$data[$i] = utf8_encode($data[$i]);
				}
				if ($header[$i] == 'status') {
					if ($data[$i] == 'o' || $data[$i] =='p' || $data[$i] == 'c')
						$status = $data[$i];
				} elseif ($header[$i] == 'itemId') {
					$itemId = $data[$i];
				} elseif ($header[$i] == 'created' && is_numeric($data[$i])) {
					$created = $data[$i];
				} elseif ($header[$i] == 'lastModif' && is_numeric($data[$i])) {
					$lastModif = $data[$i];
				} elseif ($header[$i] == 'categs') { // for old compatibility
					$cats = preg_split('/,/',trim($data[$i]));
				}
			}
			if ($itemId && ($t = $this->get_tracker_for_item($itemId)) && $t == $trackerId && $replace_rows) {
				$query = "update `tiki_tracker_items` set `created`=?, `lastModif`=?, `status`=? where `itemId`=?";
				$this->query($query, array((int)$created, (int)$lastModif, $status, (int)$itemId));
				$replace = true;
			} elseif ($itemId && !$t) {
				$query = "insert into `tiki_tracker_items`(`trackerId`,`created`,`lastModif`,`status`, `itemId`) values(?,?,?,?,?)";
				$this->query($query,array((int)$trackerId, (int)$created,(int)$lastModif, $status, (int)$itemId));
				$replace = false;
			} else {
				$query = "insert into `tiki_tracker_items`(`trackerId`,`created`,`lastModif`,`status`) values(?,?,?,?)";
				$this->query($query,array((int)$trackerId, (int)$created,(int)$lastModif, $status));
				$query = "select max(`itemId`) from `tiki_tracker_items` where `trackerId`=? and `created`=? and `lastModif`=? and `status`=?";
				$itemId = $this->getOne($query, array((int)$trackerId, (int)$created,(int)$lastModif, $status));
				if (empty($itemId) || $itemId < 1) {
					return "Problem inserting tracker item: trackerId=$trackerId, created=$created, lastModif=$lastModif, status=$status";
				}
				$replace = false;
			}
			$need_reindex[] = $itemId;
			if (!empty($cats)) {
				$this->categorized_item($trackerId, $itemId, "item $itemId", $cats);
			}
			$query = "insert into `tiki_tracker_item_fields`(`itemId`,`fieldId`,`value`) values(?,?,?)";
			$query2 = "update `tiki_tracker_item_fields` set `value`=? where `itemId`=? and `fieldId`=?";
			for ($i = 0; $i < $max; ++$i) {
				if (!preg_match('/ -- $/', $header[$i])) {
					continue;
				}
				$h = preg_replace('/ -- $/', '', $header[$i]);
				foreach ($fields['data'] as $field) {
					if ($field['name'] == $h) {
						if ($field['type'] == 'p' && $field['options_array'][0] == 'password') {
							//$userlib->change_user_password($user, $ins_fields['data'][$i]['value']);
							continue;
						} elseif ($field['type'] == 'e') {
							$cats = preg_split('/%%%/', trim($data[$i]));
							$catIds = array();
							if (!empty($cats)) {
								foreach ($cats as $c) {
									global $categlib; include_once('lib/categories/categlib.php');
									if ($cId = $categlib->get_category_id(trim($c)))
										$catIds[] = $cId;
								}
								if (!empty($catIds)) {
									$this->categorized_item($trackerId, $itemId, "item $itemId", $catIds);
								}
							}
							$data[$i] = '';
						} elseif ($field['type'] == 's') {
							$data[$i] = '';
						} elseif ($field['type'] == 'a') {
							$data[$i] = preg_replace('/\%\%\%/',"\r\n",$data[$i]);
						} elseif ($field['type'] == 'c') {
							if (strtolower($data[$i]) == 'yes' || strtolower($data[$i]) == 'on')
								$data[$i] = 'y';
							elseif (strtolower($data[$i]) == 'no')
								$data[$i] = 'n';
						} elseif ($field['type'] == 'f' || $field['type'] == 'j') {
							if ($dateFormat == 'mm/dd/yyyy') {
								list($m, $d, $y) = preg_split('#/#', $data[$i]);
								$data[$i] = $tikilib->make_time(0, 0, 0, $m, $d, $y);
							} elseif ($dateFormat == 'dd/mm/yyyy') {
								list($d, $m, $y) = preg_split('#/#', $data[$i]);
								$data[$i] = $tikilib->make_time(0, 0, 0, $m, $d, $y);
							}
						} elseif ($field['type'] == 'q') {
							$data[$i] = $itemId;
						}
						if ($this->get_item_value($trackerId, $itemId, $field['fieldId']) !== false) {
							$this->query($query2, array($data[$i], (int)$itemId,(int)$field['fieldId']));	// update
						} else {
							$this->query($query, array((int)$itemId,(int)$field['fieldId'], $data[$i]));	// insert
						}
						break;
					}
				}
			}
			$total++;
		}

		if ( $prefs['feature_search'] == 'y' && $prefs['feature_search_fulltext'] != 'y' && $prefs['search_refresh_index_mode'] == 'normal' && is_array($need_reindex) ) {
			require_once('lib/search/refresh-functions.php');
			foreach ( $need_reindex as $id ) refresh_index('tracker_items', $id);
			unset($need_reindex);
		}
		$cant_items = $this->getOne("select count(*) from `tiki_tracker_items` where `trackerId`=?",array((int) $trackerId));
		$query = "update `tiki_trackers` set `items`=?,`lastModif`=?  where `trackerId`=?";
		$result = $this->query($query,array((int)$cant_items,(int) $this->now,(int) $trackerId));
		return $total;
	}

	
	function dump_tracker_csv($trackerId) {
		global $tikilib;
		$tracker_info = $this->get_tracker_options($trackerId);
		$fields = $this->list_tracker_fields($trackerId, 0, -1, 'position_asc', '');
		
		$trackerId = (int)$trackerId;
		
		// write out file header
		session_write_close();
		$this->write_export_header();
		
		// then "field names -- index" as first line
		$str = '';
		$str .= 'itemId,status,created,lastModif,';	// these headings weren't quoted in the previous export function
		if (count($fields['data']) > 0) {
			foreach ($fields['data'] as $field) {
				$str .= '"'.$field['name'].' -- '.$field['fieldId'].'",';
			}
		}
		echo $str;
		
		// prepare queries
		$mid = ' WHERE tti.`trackerId` = ? ';
		$bindvars = array($trackerId);
		$join = '';
		
		$query_items =	'SELECT tti.itemId, tti.status, tti.created, tti.lastModif'
						.' FROM  `tiki_tracker_items` tti'
						.$mid
						.' ORDER BY tti.`itemId` ASC';
		$query_fields =  'SELECT tti.itemId, ttif.`value`, ttf.`type`'
						.' FROM ('
						.' `tiki_tracker_items` tti'
						.' INNER JOIN `tiki_tracker_item_fields` ttif ON tti.`itemId` = ttif.`itemId`'
						.' INNER JOIN `tiki_tracker_fields` ttf ON ttf.`fieldId` = ttif.`fieldId`'
						.')'
						.$mid
						.' ORDER BY tti.`itemId` ASC, ttf.`position` ASC';
		$base_tables = '('
			.' `tiki_tracker_items` tti'
			.' INNER JOIN `tiki_tracker_item_fields` ttif ON tti.`itemId` = ttif.`itemId`'
			.' INNER JOIN `tiki_tracker_fields` ttf ON ttf.`fieldId` = ttif.`fieldId`'
			.')'.$join;
	
						
		$query_cant = 'SELECT count(DISTINCT ttif.`itemId`) FROM '.$base_tables.$mid;
		$cant = $this->getOne($query_cant, $bindvars);
		
		$avail_mem = $tikilib->get_memory_avail();
		$maxrecords_items = intval(($avail_mem - 10 * 1024 * 1025) / 5000);		// depends on size of items table (fixed)
		if ($maxrecords_items < 0) {	// cope with memory_limit = -1
			$maxrecords_items = -1;
		}
		$offset_items = 0;
		
		$items = $this->get_dump_items_array($query_items, $bindvars, $maxrecords_items, $offset_items);
		
		$avail_mem = $tikilib->get_memory_avail();							// update avail after getting first batch of items
		$maxrecords = (int)($avail_mem / 40000) * count($fields['data']);	// depends on number of fields
		if ($maxrecords < 0) {	// cope with memory_limit = -1
			$maxrecords = $cant * count($fields['data']);
		}
		$canto = $cant * count($fields['data']);
		$offset = 0;
		$lastItem = -1;
		$count = 0; $icount = 0;
		$field_values = array();
		
		// write out rows
		for ($offset = 0; $offset < $canto; $offset = $offset + $maxrecords) {
			$field_values = $this->fetchAll($query_fields, $bindvars, $maxrecords, $offset);
			$mem = memory_get_usage(true);
			
			foreach ( $field_values as $res ) {
				if ($lastItem != $res['itemId']) {
					$lastItem = $res['itemId'];
					echo "\n".$items[$lastItem]['itemId'].','.$items[$lastItem]['status'].','.$items[$lastItem]['created'].','.$items[$lastItem]['lastModif'].',';	// also these fields weren't traditionally escaped
					$count++;
					$icount++;
					if ($icount > $maxrecords_items && $maxrecords_items > 0) {
						$offset_items += $maxrecords_items;
						$items = $this->get_dump_items_array($query_items, $bindvars, $maxrecords_items, $offset_items);
						$icount = 0;
					}
				}
				echo '"' . str_replace(array('"', "\r\n", "\n"), array('\\"', '%%%', '%%%'), $res['value']) . '",';
			}
			ob_flush();
			flush();
			//if ($offset == 0) { $maxrecords = 1000 * count($fields['data']); }
		}
		echo "\n";
		ob_end_flush();
	}
	
	function get_dump_items_array($query, $bindvars, $maxrecords, $offset) {
		$items_array = $this->fetchAll($query, $bindvars, $maxrecords, $offset);
		$items = array();
		foreach ($items_array as $item) {
			$items[$item['itemId']] = $item;
		}
		unset($items_array);
		return $items;
	}

	function write_export_header() {
		header("Content-type: text/comma-separated-values; charset:".$_REQUEST['encoding']);
		if (!empty($_REQUEST['file'])) {
			if (preg_match('/.csv$/', $_REQUEST['file'])) {
				$file = $_REQUEST['file'];
			} else {
				$file = $_REQUEST['file'].'.csv';
			}
		} else {
			$file = tra('tracker').'_'.$_REQUEST['trackerId'].'.csv';
		}
		header("Content-Disposition: attachment; filename=$file");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}
	
	function _describe_category_list($categs) {
	    global $categlib;
	    $res = '';
	    foreach ($categs as $cid) {
		$info = $categlib->get_category($cid);
		$res .= '    ' . $info['name'] . "\n";
	    }
	    return $res;
	}

	// check the validity of each field values of a tracker item
	// and the presence of mandatory fields
	function check_field_values($ins_fields, $categorized_fields='', $trackerId='', $itemId='') {
		global $prefs;
		$mandatory_fields = array();
		$erroneous_values = array();
        if (isset($ins_fields)&&isset($ins_fields['data']))
		foreach($ins_fields['data'] as $f) {

			if ($f['type'] != 'q' and isset($f['isMandatory']) && $f['isMandatory'] == 'y') {

				if (isset($f['type']) &&  $f['type'] == 'e') {
					if (!in_array($f['fieldId'], $categorized_fields)) {
						$mandatory_fields[] = $f;
					}
				} elseif (isset($f['type']) &&  ($f['type'] == 'a' || $f['type'] == 't') && ($this->is_multilingual($f['fieldId']) == 'y')) {
					if (!isset($multi_languages)) {
						$multi_languages=$prefs['available_languages'];
					}
					//Check recipient
					if (isset($f['lingualvalue']) ) {
						foreach ($f['lingualvalue'] as $val) {
							foreach ($multi_languages as $num=>$tmplang) {	//Check if trad is empty
								if (!isset($val['lang']) ||!isset($val['value']) ||(($val['lang']==$tmplang) && strlen($val['value'])==0)) {
									$mandatory_fields[] = $f;
								}
							}
						}
					} else {
						$mandatory_fields[] = $f;
					}
				} elseif (isset($f['type']) &&  ($f['type'] == 'u' || $f['type'] == 'g') && $f['options_array'][0] == 1) {
					;
				} elseif ($f['type'] == 'c' && (empty($f['value']) || $f['value'] == 'n')) {
					$mandatory_fields[] = $f;
				} elseif ($f['type'] == 'A' && !empty($itemId) && empty($f['value'])) {
					$val = $this->get_item_value($trackerId, $itemId, $f['fieldId']);
					if (empty($val)) {
						$mandatory_fields[] = $f;
					}
				} elseif (!isset($f['value']) or strlen($f['value']) == 0) {
					$mandatory_fields[] = $f;
				}
			}
			if (!empty($f['value']) && isset($f['type'])) {

				switch ($f['type']) {
				// IP address (only for IPv4)
				case 'I':
					if (!$this->isValidIP($f['value'])) {
						$erroneous_values[] = $f;
					}
					break;
				// numeric
				case 'n':
					if(!is_numeric($f['value'])) {
						$f['error'] = tra('Field is not numeric');
						$erroneous_values[] = $f;
					}
					break;

				// email
				case 'm':
					if(!validate_email($f['value'],$prefs['validateEmail'])) {
						$erroneous_values[] = $f;
					}
					break;

				// password					
				case 'p':
				if ($f['options_array'][0] == 'password') {
					global $userlib;
					if (($e = $userlib->check_password_policy($f['value'])) != '') {
						 $erroneous_values[] = $f;
					}
				} elseif ($f['options_array'][0] == 'email') {
					if (!validate_email($f['value'])) {
						$erroneous_values[] = $f;
					}
				}
				break;
				case 'a':
					if (isset($f['options_array'][5]) &&  $f['options_array'][5] > 0) {
						if (count(preg_split('/\s+/', trim($f['value']))) > $f['options_array'][5]) {
							$erroneous_values[] = $f;
						}
					}
					if (isset($f['options_array'][6]) &&  $f['options_array'][6] == 'y') {
						if (in_array($f['value'], $this->list_tracker_field_values($trackerId, $f['fieldId'], 'opc', 'y', '', $itemId))) {
							$erroneous_values[] = $f;
						}
					}
					break;
				}
			}
		}

		$res = array();
		$res['err_mandatory'] = $mandatory_fields;
		$res['err_value'] = $erroneous_values;
		return $res;
	}

	function remove_tracker_item($itemId) {
		global $user, $prefs;
		$query = "select * from `tiki_tracker_items` where `itemId`=?";
		$result = $this->query($query, array((int) $itemId));
		$res = $result->fetchRow();
		$trackerId = $res['trackerId'];
		$status = $res['status'];

		// ---- save image list before sql query ---------------------------------
		$fieldList = $this->list_tracker_fields($trackerId, 0, -1, 'name_asc', '');
		$imgList = array();
		foreach($fieldList['data'] as $f) {
			if( $f['type'] == 'i' ) {
				$imgList[] = $this->get_item_value($trackerId, $itemId, $f['fieldId']);
			}
		}
		$watchers = $this->get_notification_emails($trackerId, $itemId, $this->get_tracker_options( $trackerId));
		if (count($watchers > 0)) {
			global $smarty;
			$trackerName = $this->getOne("select `name` from `tiki_trackers` where `trackerId`=?",array((int) $trackerId));
			$smarty->assign('mail_date', $this->now);
			$smarty->assign('mail_user', $user);
			$smarty->assign('mail_action', 'deleted');
			$smarty->assign('mail_itemId', $itemId);
			$smarty->assign('mail_trackerId', $trackerId);
			$smarty->assign('mail_trackerName', $trackerName);
			$smarty->assign('mail_data', '');
			$foo = parse_url($_SERVER["REQUEST_URI"]);
			$machine = $this->httpPrefix( true ). $foo["path"];
			$smarty->assign('mail_machine', $machine);
			$parts = explode('/', $foo['path']);
			if (count($parts) > 1)
				unset ($parts[count($parts) - 1]);
			$smarty->assign('mail_machine_raw', $this->httpPrefix( true ). implode('/', $parts));
			if (!isset($_SERVER["SERVER_NAME"])) {
				$_SERVER["SERVER_NAME"] = $_SERVER["HTTP_HOST"];
			}
			include_once ('lib/webmail/tikimaillib.php');
			$smarty->assign('server_name', $_SERVER['SERVER_NAME']);
			foreach ($watchers as $w) {
				$mail = new TikiMail($w['user']);
				$mail->setHeader("From", $prefs['sender_email']);
				$mail->setSubject($smarty->fetchLang($w['language'], 'mail/tracker_changed_notification_subject.tpl'));
				$mail->setText($smarty->fetchLang($w['language'], 'mail/tracker_changed_notification.tpl'));
				$mail->send(array($w['email']));
			}
		}

		$query = "update `tiki_trackers` set `lastModif`=? where `trackerId`=?";
		$result = $this->query($query,array((int) $this->now,(int) $trackerId));
		$query = "update `tiki_trackers` set `items`=`items`-1 where `trackerId`=?";
		$result = $this->query($query,array((int) $trackerId));
		$query = "delete from `tiki_tracker_item_fields` where `itemId`=?";
		$result = $this->query($query,array((int) $itemId));
		$query = "delete from `tiki_tracker_items` where `itemId`=?";
		$result = $this->query($query,array((int) $itemId));
		$query = "delete from `tiki_tracker_item_comments` where `itemId`=?";
		$result = $this->query($query,array((int) $itemId));
		$query = "delete from `tiki_tracker_item_attachments` where `itemId`=?";
		$result = $this->query($query,array((int) $itemId));

		// ---- delete image from disk -------------------------------------
		foreach($imgList as $img) {
			if( file_exists($img) ) {
				unlink( $img );
			}
		}

		global $cachelib;
		require_once('lib/cache/cachelib.php');
		$cachelib->invalidate('trackerItemLabel'.$itemId);
		foreach($fieldList['data'] as $f) {
			$cachelib->invalidate(md5('trackerfield'.$f['fieldId'].$status));
			$cachelib->invalidate(md5('trackerfield'.$f['fieldId'].'opc'));
			$cachelib->invalidate(md5('trackerfield'.$f['fieldId'].'opc'));
			if ($status == 'o') {
				$cachelib->invalidate(md5('trackerfield'.$f['fieldId'].'op'));
				$cachelib->invalidate(md5('trackerfield'.$f['fieldId'].'oc'));
			} elseif ($status == 'c') {
				$cachelib->invalidate(md5('trackerfield'.$f['fieldId'].'oc'));
				$cachelib->invalidate(md5('trackerfield'.$f['fieldId'].'pc'));
			} elseif ($status == 'p') {
				$cachelib->invalidate(md5('trackerfield'.$f['fieldId'].'op'));
				$cachelib->invalidate(md5('trackerfield'.$f['fieldId'].'pc'));
			}
		}

		$options=$this->get_tracker_options($trackerId);
		if (isset ($option) && isset($option['autoCreateCategories']) && $option['autoCreateCategories']=='y') {
			$currentCategId=$categlib->get_category_id("Tracker Item $itemId");
			$categlib->remove_category($currentCategId);
		}
		$this->remove_object("trackeritem", $itemId);
		if (isset($options['autoCreateGroup']) && $options['autoCreateGroup'] == 'y') {
			global $userlib;
			$groupName = $this->groupName($options, $itemId, $groupInc);
			$userlib->remove_group($groupName);
		}
		$this->remove_item_log($itemId);
		global $todolib; include_once('lib/todolib.php');
		$todolib->delObjectTodo('trackeritem', $itemId);
		return true;
	}

	// filter examples: array('fieldId'=>array(1,2,3)) to look for a list of fields
	// array('or'=>array('isSearchable'=>'y', 'isTplVisible'=>'y')) for fields that are visible ou searchable
	// array('not'=>array('isHidden'=>'y')) for fields that are not hidden
	function parse_filter($filter, &$mids, &$bindvars) {
		global $tikilib;
		foreach ($filter as $type=>$val) {
			if ($type == 'or') {
				$midors = array();
				$this->parse_filter($val, $midors, $bindvars);
				$mids[] = '('.implode(' or ', $midors).')';
			} elseif ($type == 'not') {
				$midors = array();
				$this->parse_filter($val, $midors, $bindvars);
				$mids[] = '!('.implode(' and ', $midors).')';
			} elseif ($type == 'createdBefore') {
				$mids[] = 'tti.`created` < ?';
				$bindvars[] = $val;
			} elseif ($type == 'lastModifBefore') {
				$mids[] = 'tti.`lastModif` < ?';
				$bindvars[] = $val;
			} elseif ($type == 'notItemId') {
				$mids[] = 'tti.`itemId` NOT IN('.implode(",",array_fill(0,count($val),'?')).')';
				$bindvars = $val; 
			} elseif (is_array($val)) {
				if (count($val) > 0) {
					if (!strstr($type, '`')) $type = "`$type`";
					$mids[] = "$type in (".implode(",",array_fill(0,count($val),'?')).')';
					$bindvars = array_merge($bindvars, $val);
				}
			} else {
				if (!strstr($type, '`')) $type = "`$type`";
				$mids[] = "$type=?";
				$bindvars[] = $val;
			}
		}
	}

	// Lists all the fields for an existing tracker
	function list_tracker_fields($trackerId, $offset=0, $maxRecords=-1, $sort_mode='position_asc', $find='', $tra_name=true, $filter='', $fields='') {
		global $prefs, $smarty;
		if ($find) {
			$findesc = '%' . $find . '%';
			$mid = " where `trackerId`=? and (`name` like ?)";
			$bindvars=array((int) $trackerId,$findesc);
		} else {
			$mid = " where `trackerId`=? ";
			$bindvars=array((int) $trackerId);
		}
		if (!empty($fields)) {
			$mid .= " AND `fieldId` in (".implode(',', array_fill(0,count($fields),'?')).')';
			$bindvars = array_merge($bindvars, $fields);
		}

		if (!empty($filter)) {
			$mids = array();
			$this->parse_filter($filter, $mids, $bindvars);
			$mid .= 'and '.implode(' and ', $mids);
		}

		$query = "select * from `tiki_tracker_fields` $mid order by ".$this->convertSortMode($sort_mode);
		$query_cant = "select count(*) from `tiki_tracker_fields` $mid";
		$result = $this->fetchAll($query,$bindvars,$maxRecords,$offset);
		$cant = $this->getOne($query_cant,$bindvars);
		$ret = array();

		foreach( $result as $res ) {
			$res['options_array'] = preg_split('/\s*,\s*/', trim($res['options']));
			$res['itemChoices'] = ( $res['itemChoices'] != '' ) ? unserialize($res['itemChoices']) : array();
			$res['visibleBy'] = ($res['visibleBy'] != '') ? unserialize($res['visibleBy']) : array();
			$res['editableBy'] = ($res['editableBy'] != '') ? unserialize($res['editableBy']) : array();
			if ($tra_name && $prefs['feature_multilingual'] == 'y' && $prefs['language'] != 'en')
				$res['name'] = tra($res['name']);
			if ($res['type'] == 'd' || $res['type'] == 'D' || $res['type'] == 'R') { // drop down
				if ($prefs['feature_multilingual'] == 'y') {
					foreach ($res['options_array'] as $key=>$l) {
						$res['options_array'][$key] = $l;
					}
				}
				$res = $this->set_default_dropdown_option($res);
			}
			if ($res['type'] == 'l' || $res['type'] == 'r') { // get the last field type
				if (!empty($res['options_array'][3])) {
					if (is_numeric($res['options_array'][3]))
						$fieldId = $res['options_array'][3];
					else
						$fieldId = 0;
				} elseif (is_numeric($res['options_array'][1])) {
					$fieldId = $res['options_array'][1];
				} elseif ($fields = preg_split('/:/', $res['options_array'][1])) {
					$fieldId = $fields[count($fields) - 1];
				}
				if (!empty($fieldId)) {
					$res['otherField'] = $this->get_tracker_field($fieldId);
				}
			}
			if ($res['type'] == 'p' && $res['options_array'][0] == 'language') {
				$smarty->assign('languages', $this->list_languages());	
			}
			$ret[] = $res;
		}
		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}

	// Inserts or updates a tracker
	function replace_tracker($trackerId, $name, $description, $options, $descriptionIsParsed) {
		if ($trackerId === false && !empty($name)) {	// called from profiles - update not replace
			$trackerId = $this->getOne('select max(`trackerId`) from `tiki_trackers` where `name`=?',array($name));
		}
		if ($trackerId) {
			$old = $this->getOne('select count(*) from `tiki_trackers` where `trackerId`=?',array((int)$trackerId));
			if ($old) {
				$query = "update `tiki_trackers` set `name`=?,`description`=?,`descriptionIsParsed`=?,`lastModif`=? where `trackerId`=?";
				$this->query($query,array($name,$description,$descriptionIsParsed,(int)$this->now,(int) $trackerId));
			} else {
				$query = "insert into `tiki_trackers` (`name`,`description`,`descriptionIsParsed`,`lastModif`,`trackerId`, `items`) values (?,?,?,?,?,?)";
				$this->query($query,array($name,$description,$descriptionIsParsed,(int)$this->now,(int) $trackerId, 0));
			}
		} else {
			$query = "insert into `tiki_trackers`(`name`,`description`,`descriptionIsParsed`,`created`,`lastModif`) values(?,?,?,?,?)";
			$this->query($query,array($name,$description,$descriptionIsParsed,(int) $this->now,(int) $this->now));
			$trackerId = $this->getOne("select max(`trackerId`) from `tiki_trackers` where `name`=? and `created`=?",array($name,(int) $this->now));
		}
		$this->query("delete from `tiki_tracker_options` where `trackerId`=?",array((int)$trackerId));
		$rating = false;
		foreach ($options as $kopt=>$opt) {
			$this->query("insert into `tiki_tracker_options`(`trackerId`,`name`,`value`) values(?,?,?)",array((int)$trackerId,$kopt,$opt));
			if ($kopt == 'useRatings' and $opt == 'y') {
				$rating = true;
			} elseif ($kopt == 'ratingOptions') {
				$ratingoptions = $opt;
			} elseif ($kopt == 'showRatings') {
				$showratings = $opt;
			}
		}
		$ratingId = $this->get_field_id_from_type($trackerId, 's', null, true, 'Rating');
		if ($rating) {
			if (!$ratingId) $ratingId = 0;
			if (!isset($ratingoptions)) $ratingoptions = '';
			if (!isset($showratings)) $showratings = 'n';
			$this->replace_tracker_field($trackerId,$ratingId,'Rating','s','-','-',$showratings,'y','n','-',0,$ratingoptions);
		} else {
			$this->query('delete from `tiki_tracker_fields` where `fieldId`=?',array((int)$ratingId));
		}
		$this->clear_tracker_cache($trackerId);

		global $prefs;
		if ( $prefs['feature_search'] == 'y' && $prefs['feature_search_fulltext'] != 'y' && $prefs['search_refresh_index_mode'] == 'normal' ) {
			require_once('lib/search/refresh-functions.php');
			refresh_index('trackers', $trackerId);
		}
		if ($descriptionIsParsed == 'y') {
			$this->object_post_save(array('type'=>'tracker', 'object'=>$trackerId, 'href'=>"tiki-view_tracker.php?trackerId=$trackerId", 'description'=>$description), array( 'content' => $description ));
		}

		return $trackerId;
	}

	function clear_tracker_cache($trackerId) {
		$query = "select `itemId` from `tiki_tracker_items` where `trackerId`=?";
		$result = $this->query($query,array((int)$trackerId));

		global $cachelib;
		require_once('lib/cache/cachelib.php');

		while ($res = $result->fetchRow()) {
		    $cachelib->invalidate('trackerItemLabel'.$res['itemId']);
		}
	}


	function replace_tracker_field($trackerId, $fieldId, $name, $type, $isMain, $isSearchable, $isTblVisible, $isPublic, $isHidden, $isMandatory, $position, $options, $description='',$isMultilingual='', $itemChoices=null, $errorMsg='', $visibleBy=null, $editableBy=null, $descriptionIsParsed='n', $validation='', $validationParam='', $validationMessage='') {
		// Serialize choosed items array (items of the tracker field to be displayed in the list proposed to the user)
		if ( is_array($itemChoices) && count($itemChoices) > 0 && !empty($itemChoices[0]) ) {
			$itemChoices = serialize($itemChoices);
		} else {
			$itemChoices = '';
		}
		if (is_array($visibleBy) && count($visibleBy) > 0 && !empty($visibleBy[0])) {
			$visibleBy = serialize($visibleBy);
		} else {
			$visibleBy = '';
		}
		if (is_array($editableBy) && count($editableBy) > 0 && !empty($editableBy[0])) {
			$editableBy = serialize($editableBy);
		} else {
			$editableBy = '';
		}

		if ($fieldId === false && $trackerId && !empty($name)) {	// called from profiles - update not replace
			$fieldId = $this->getOne("select max(`fieldId`) from `tiki_tracker_fields` where `trackerId`=? and `name`=?",array((int) $trackerId,$name));
		}

		if ($fieldId) {
			// -------------------------------------
			// remove images when needed
			$old_field = $this->get_tracker_field($fieldId);
			if ($old_field) {
				if( $old_field['type'] == 'i' && $type != 'i' ) {
					$this->remove_field_images( $fieldId );
				}
				$query = "update `tiki_tracker_fields` set `name`=? ,`type`=?,`isMain`=?,`isSearchable`=?,
					`isTblVisible`=?,`isPublic`=?,`isHidden`=?,`isMandatory`=?,`position`=?,`options`=?,`isMultilingual`=?, `description`=?, `itemChoices`=?, `errorMsg`=?, visibleBy=?, editableBy=?, `descriptionIsParsed`=?, `validation`=?, `validationParam`=?, `validationMessage`=? where `fieldId`=?";
				$bindvars=array($name,$type,$isMain,$isSearchable,$isTblVisible,$isPublic,$isHidden,$isMandatory,(int)$position,$options,$isMultilingual,$description, $itemChoices, $errorMsg, $visibleBy, $editableBy, $descriptionIsParsed, $validation, $validationParam, $validationMessage, (int) $fieldId);
			} else {
				$query = "insert into `tiki_tracker_fields` (`trackerId`,`name`,`type`,`isMain`,`isSearchable`,
					`isTblVisible`, `isPublic`, `isHidden`, `isMandatory`, `position`, `options`, `fieldId`, `isMultilingual`, `description`, `itemChoices`, `errorMsg`, `visibleBy`, `editableBy`, `descriptionIsParsed`, `validation`, `validationParam`, `validationMessage`) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
				$bindvars=array((int) $trackerId,$name,$type,$isMain,$isSearchable,$isTblVisible,$isPublic,$isHidden,$isMandatory,(int)$position,$options,(int) $fieldId,$isMultilingual, $description, $itemChoices, $errorMsg, $visibleBy, $editableBy, $descriptionIsParsed, $validation, $validationParam, $validationMessage);
			}
			$result = $this->query($query, $bindvars);
		} else {
			$query = "insert into `tiki_tracker_fields`(`trackerId`,`name`,`type`,`isMain`,`isSearchable`,`isTblVisible`,`isPublic`,`isHidden`,`isMandatory`,`position`,`options`,`description`,`isMultilingual`, `itemChoices`, `errorMsg`, `visibleBy`, `editableBy`, `descriptionIsParsed`, `validation`, `validationParam`, `validationMessage`)
                values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

			$result = $this->query($query,array((int) $trackerId,$name,$type,$isMain,$isSearchable,$isTblVisible,$isPublic,$isHidden,$isMandatory,$position,$options,$description,$isMultilingual, $itemChoices, $errorMsg, $visibleBy, $editableBy, $descriptionIsParsed, $validation, $validationParam, $validationMessage));
			$fieldId = $this->getOne("select max(`fieldId`) from `tiki_tracker_fields` where `trackerId`=? and `name`=?",array((int) $trackerId,$name));
			// Now add the field to all the existing items
			$query = "select `itemId` from `tiki_tracker_items` where `trackerId`=?";
			$result = $this->query($query,array((int) $trackerId));

			while ($res = $result->fetchRow()) {
				$itemId = $res['itemId'];
				$this->query("delete from `tiki_tracker_item_fields` where `itemId`=? and `fieldId`=?",
					array((int) $itemId,(int) $fieldId),-1,-1,false);

				$query2 = "insert into `tiki_tracker_item_fields`(`itemId`,`fieldId`,`value`) values(?,?,?)";
				$this->query($query2,array((int) $itemId,(int) $fieldId,''));
			}
		}
		$this->clear_tracker_cache($trackerId);
		return $fieldId;
	}

	function replace_rating($trackerId,$itemId,$fieldId,$user,$new_rate) {
		global $tiki_p_tracker_vote_ratings, $tiki_p_tracker_revote_ratings;
		if ($tiki_p_tracker_vote_ratings != 'y') {
			return;
		}
		$key = "tracker.$trackerId.$itemId";
		if ($tiki_p_tracker_revote_ratings != 'y' && (($v = $this->get_user_vote($key, $user)) !== null && $v !== false)) {
			return;
		}
		$count = $this->getOne("select count(*) from `tiki_tracker_item_fields` where `itemId`=? and `fieldId`=?", array((int)$itemId,(int)$fieldId));
		if (!$count) {
			$query = "insert into `tiki_tracker_item_fields`(`value`,`itemId`,`fieldId`) values (?,?,?)";
			$newval = $new_rate;
			//echo "$newval";die;
		} else {
			$val = $this->getOne("select value from `tiki_tracker_item_fields` where `itemId`=? and `fieldId`=?", array((int)$itemId,(int)$fieldId));
			$query = "update `tiki_tracker_item_fields` set `value`=? where `itemId`=? and `fieldId`=?";
			$olrate = $this->get_user_vote("tracker.$trackerId.$itemId",$user);
			if ($olrate === NULL) $olrate = 0;
			if ($new_rate === NULL) {
				$newval = $val - $olrate;
			} else {
				$newval = $val - $olrate + $new_rate;
			}
			//echo "$val - $olrate + $new_rate = $newval";die;
		}
		$this->query($query,array((int)$newval,(int)$itemId,(int)$fieldId));
		$this->register_user_vote( $user, $key, $new_rate, array(), true );
		return $newval;
	}
	function replace_star($userValue, $trackerId, $itemId, &$field, $user, $updateField=true) {
		global $tiki_p_tracker_vote_ratings, $tiki_p_tracker_revote_ratings; 
		if ($field['type'] != '*') {
			return;
		}
		if ($userValue != 'NULL' && !in_array($userValue, $field['options_array'])) {
			return;
		}
		if ($tiki_p_tracker_vote_ratings != 'y') {
			return;
		}
		$key = "tracker.$trackerId.$itemId.".$field['fieldId'];
		if ($tiki_p_tracker_revote_ratings != 'y' && (($v = $this->get_user_vote($key, $user)) !== null && $v !== false)) {
			return;
		}
		$result = $this->query("select `value` from `tiki_tracker_item_fields` where `itemId`=? and `fieldId`=?", array((int)$itemId,(int)$field['fieldId']));
		$this->register_user_vote($user, $key, $userValue, array(), true);
		$field['my_rate'] = $userValue;
		if (!$result->numRows()) {
			$field['voteavg'] = $field['value'] = $userValue;
			$field['numvotes'] = 1;
			$query = 'insert into `tiki_tracker_item_fields`(`value`,`itemId`,`fieldId`) values (?,?,?)';
		} else {
			$field['numvotes'] = $this->getOne('select count(*) from `tiki_user_votings` where `id` = ?', array($key));
			$sumvotes = $this->getOne('select sum(`optionId`) from `tiki_user_votings` where `id` = ?', array($key));
			$field['voteavg'] = $field['value'] = $sumvotes/$field['numvotes'];
			$query = 'update `tiki_tracker_item_fields` set `value`=? where `itemId`=? and `fieldId`=?';
		}
		$this->query($query, array($field['value'], (int)$itemId, (int)$field['fieldId']));
	}
	function update_star_field($trackerId, $itemId, &$field) {
		global $user;
		if ($field['type'] == 's' && $field['name'] == 'Rating') { // global rating to an item - value is the sum of the votes
			$key = 'tracker.'.$trackerId.'.'.$itemId;
			$field['numvotes'] = $this->getOne('select count(*) from `tiki_user_votings` where `id` = ?', array($key));
			$field['voteavg'] = ( $field['numvotes'] > 0 ) ? round(($field['value'] / $field['numvotes'])) : '';
		} elseif ($field['type'] == '*') { // field rating - value is the average of the votes
			$key = "tracker.$trackerId.$itemId.".$field['fieldId'];
			$field['numvotes'] = $this->getOne('select count(*) from `tiki_user_votings` where `id` = ?', array($key));
			$field['voteavg'] = isset($field['value'])? round($field['value']):'';
		}
		// be careful optionId is the value - not the optionId
		$field['my_rate'] = $this->getOne('select `optionId` from `tiki_user_votings` where `id`=? and `user` = ?', array($key, $user));
	}

	function remove_tracker($trackerId) {

		// ---- delete image from disk -------------------------------------
		$fieldList = $this->list_tracker_fields($trackerId, 0, -1, 'name_asc', '');
		foreach($fieldList['data'] as $f) {
			if( $f['type'] == 'i' ) {
				$this->remove_field_images($f['fieldId']);
			}
		}

		$bindvars=array((int) $trackerId);
		$query = "delete from `tiki_trackers` where `trackerId`=?";

		$result = $this->query($query,$bindvars);
		// Remove the fields
		$query = "delete from `tiki_tracker_fields` where `trackerId`=?";
		$result = $this->query($query,$bindvars);
		// Remove the items (Remove fields for each item for this tracker)
		$query = "select `itemId` from `tiki_tracker_items` where `trackerId`=?";
		$result = $this->query($query,$bindvars);

		while ($res = $result->fetchRow()) {
			$this->remove_tracker_item($res['itemId']);
		}

		$query = "delete from `tiki_tracker_options` where `trackerId`=?";
		$result = $this->query($query,$bindvars);

		$this->remove_object('tracker', $trackerId);

		$this->clear_tracker_cache($trackerId);

		$options=$this->get_tracker_options($trackerId);
		if (isset ($option) && isset($option['autoCreateCategories']) && $option['autoCreateCategories']=='y') {
			global $categlib; include_once('lib/categories/categlib.php');
			$currentCategId=$categlib->get_category_id("Tracker $trackerId");
			$categlib->remove_category($currentCategId);
		}
		global $todolib; include_once('lib/todolib.php');
		$todolib->delObjectTodo('trackeritem', $itemId);
		return true;
	}

	function remove_tracker_field($fieldId,$trackerId) {
		global $cachelib; include_once ('lib/cache/cachelib.php');
		global $logslib; include_once('lib/logs/logslib.php');

		// -------------------------------------
		// remove images when needed
		$field = $this->get_tracker_field($fieldId);
		if( $field['type'] == 'i' ) {
			$this->remove_field_images($fieldId);
		}

		$query = "delete from `tiki_tracker_fields` where `fieldId`=?";
		$bindvars=array((int) $fieldId);
		$result = $this->query($query,$bindvars);
		$query = "delete from `tiki_tracker_item_fields` where `fieldId`=?";
		$result = $this->query($query,$bindvars);
		$cachelib->invalidate(md5('trackerfield'.$fieldId.'o'));
		$cachelib->invalidate(md5('trackerfield'.$fieldId.'p'));
		$cachelib->invalidate(md5('trackerfield'.$fieldId.'c'));
		$cachelib->invalidate(md5('trackerfield'.$fieldId.'op'));
		$cachelib->invalidate(md5('trackerfield'.$fieldId.'oc'));
		$cachelib->invalidate(md5('trackerfield'.$fieldId.'pc'));
		$cachelib->invalidate(md5('trackerfield'.$fieldId.'opc'));
		$cachelib->invalidate(md5('trackerfield'.$fieldId.'poc'));

		$this->clear_tracker_cache($trackerId);
		$logslib->add_log('admintrackerfields', 'removed tracker field ' . $fieldId . ' from tracker ' . $trackerId);

		return true;
	}

	/**
	 * Returns the trackerId of the tracker possessing the item ($itemId)
	 */
	function get_tracker_for_item($itemId) {
		$query = "select t1.`trackerId` from `tiki_trackers` t1, `tiki_tracker_items` t2 where t1.`trackerId`=t2.`trackerId` and `itemId`=?";
		return $this->getOne($query,array((int) $itemId));
	}

	function get_tracker_options($trackerId) {
		$query = "select * from `tiki_tracker_options` where `trackerId`=?";
		$result = $this->query($query,array((int) $trackerId));
		if (!$result->numRows()) return array();
		$res = array();
		while ($opt = $result->fetchRow()) {
			$res["{$opt['name']}"] = $opt['value'];
		}
		return $res;
	}

	function get_trackers_options($trackerId, $option='', $find='', $not='') {
		$where = array();
		$bindvars = array();
		if (!empty($trackerId)) {
			$where[] = '`trackerId`=?';
			$bindvars[] = (int)$trackerId;
		}
		if (!empty($option)) {
			$where[] = '`name`=?';
			$bindvars[] = $option;
		}
		if (!empty($find)) {
			$where[] = '`value` like ?';
			$bindvars[] = "%$find%";
		}
		if ($not == 'null') {
			$where[] = '`value` is not null';
		} else if ($not == 'empty') {
			$where[] = "`value` != ''";
		}
		$query = 'select * from `tiki_tracker_options` where '. implode(' and ', $where);
		return $this->fetchAll($query, $bindvars);
	}

	function get_tracker_field($fieldId) {
		$query = "select * from `tiki_tracker_fields` where `fieldId`=?";
		$result = $this->query($query,array((int) $fieldId));
		if (!$result->numRows())
			return false;
		$res = $result->fetchRow();
		$res['options_array'] = preg_split('/,/', $res['options']);
		$res['itemChoices'] = ( $res['itemChoices'] != '' ) ? unserialize($res['itemChoices']) : array();
		$res['visibleBy'] = ($res['visibleBy'] != '') ? unserialize($res['visibleBy']) : array();
		$res['editableBy'] = ($res['editableBy'] != '') ? unserialize($res['editableBy']) : array();
		return $res;
	}

	function get_field_id($trackerId,$name) {
		return $this->getOne("select `fieldId` from `tiki_tracker_fields` where `trackerId`=? and `name`=?",array((int)$trackerId,$name));
	}

	function get_field_id_from_type($trackerId, $type, $option=NULL, $first=true, $name=null) {
		static $memo;
		if (!is_array($type) && isset($memo[$trackerId][$type][$option])) {
			return $memo[$trackerId][$type][$option];
		}
		if (is_array($type)) {
			$mid = 'binary `type` in ('.implode(',', array_fill(0,count($type),'?')).')';
			$bindvars = $type;
		} else {
			$mid = '`type`=? ';
			$bindvars[] = $type;
		}
		$mid .= ' and `trackerId`=?';
		$bindvars[] = (int)$trackerId;
		if (!empty($option)) {
			if (strstr($option, '%') === false) {
				$mid .= ' and `options`=? ';
			} else {
				$mid .= ' and `options` like ? ';
			}
			$bindvars[] = $option;
		}
		if (!empty($name)) {
			$mid .= ' and `name`=?';
			$bindvars[] = $name;
		}
		$query = "select `fieldId` from `tiki_tracker_fields` where $mid";
		if ($first) {
			$fieldId = $this->getOne($query, $bindvars);
			$memo[$trackerId][$type][$option] = $fieldId;
			return $fieldId;
		} else {
			$fields = $this->fetchAll($query, $bindvars);
			foreach ($fields as $k=>$f) {
				$fields[$k] = $f['fieldId'];
			}
			return $fields;
		}
	}

/*
** function only used for the popup for more infos on attachements
*  returns an array with field=>value
*/
	function get_moreinfo($attId) {
		$query = "select o.`value`, o.`trackerId` from `tiki_tracker_options` o";
		$query.= " left join `tiki_tracker_items` i on o.`trackerId`=i.`trackerId` ";
		$query.= " left join `tiki_tracker_item_attachments` a on i.`itemId`=a.`itemId` ";
		$query.= " where a.`attId`=? and o.`name`=?";
		$result = $this->query($query,array((int)$attId, 'orderAttachments'));
		$resu = $result->fetchRow();
		if ($resu) {
			$resu['orderAttachments'] = $resu['value'];
		} else {
			$query = "select `orderAttachments`, t.`trackerId` from `tiki_trackers` t ";
			$query.= " left join `tiki_tracker_items` i on t.`trackerId`=i.`trackerId` ";
			$query.= " left join `tiki_tracker_item_attachments` a on i.`itemId`=a.`itemId` ";
			$query.= " where a.`attId`=? ";
			$result = $this->query($query,array((int)$attId));
			$resu = $result->fetchRow();
		}
		if (strstr($resu['orderAttachments'],'|')) {
			$fields = preg_split('/,/',substr($resu['orderAttachments'],strpos($resu['orderAttachments'],'|')+1));
			$query = "select `".implode("`,`",$fields)."` from `tiki_tracker_item_attachments` where `attId`=?";
			$result = $this->query($query,array((int)$attId));
			$res = $result->fetchRow();
			$res["trackerId"] = $resu['trackerId'];
			$res["longdesc"] = isset($res['longdesc'])?$this->parse_data($res['longdesc']):'';
		} else {
			$res = array(tra("Message") => tra("No extra information for that attached file. "));
			$res['trackerId'] = 0;
		}
		return $res;
	}

	function field_types() {

		global $userlib;
		$tmp = $userlib->list_all_users();
		foreach ( $tmp as $u ) $all_users[$u] = $u;
		$tmp = $userlib->list_all_groups();
		foreach ( $tmp as $u ) $all_groups[$u] = $u;
		unset($tmp);

		// 'label' => represents what shows up in the field type drop-down selector
		// 'opt' => true|false - not sure what this does
		// 'options' => not quite sure what this does either
		// 'help' => help text that appears in the left side of the field type selector
		$type['t'] = array(
			'label'=>tra('text field'),
			'opt'=>true,
			'options'=>array(
				'half'=>array('type'=>'bool','label'=>tra('half column')),
				'size'=>array('type'=>'int','label'=>tra('size')),
				'prepend'=>array('type'=>'str','label'=>tra('prepend')),
				'append'=>array('type'=>'str','label'=>tra('append')),
				'max'=>array('type'=>'int','label'=>tra('max')),
			),
			'help'=>tra('<dl>
				<dt>Function: Allows alphanumeric text input in a one-line field of arbitrary size.
				<dt>Usage: <strong>samerow,size,prepend,append,max,autocomplete</strong>
				<dt>Example: 0,80,$,,80
				<dt>Description:
				<dd><strong>[samerow]</strong> will display the next field or checkbox in the same row if a 1 is specified;
				<dd><strong>[size]</strong> is the visible length of the field in characters;
				<dd><strong>[prepend]</strong> is text that will be displayed before the field;
				<dd><strong>[append]</strong> is text that will be displayed just after the field;
				<dd><strong>[max]</strong> is the maximum number of characters that can be saved;
				<dd><strong>[autocomplete]</strong> if y autocomplete while typing;
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['a'] = array(
			'label'=>tra('textarea'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows alphanumeric text input in a multi-line field of arbitrary size.
				<dt>Usage: <strong>toolbars,width,height,max,listmax,wordmax,distinct</strong>
				<dt>Example: 0,80,5,200,30
				<dt>Description:
				<dd><strong>[toolbars]</strong> enables toolbars if a 1 is specified;
				<dd><strong>[width]</strong> is the width of the box, in chars;
				<dd><strong>[height]</strong> is the number of visible lines in the box;
				<dd><strong>[max]</strong> is the maximum number of characters that can be saved;
				<dd><strong>[listmax]</strong> is the maximum number of characters that are displayed in list mode;
				<dd><strong>[wordmax]</strong> will alert if word count exceeded with a positive number (1+) or display a word count with a negative number (-1);
				<dd><strong>[distinct]</strong> is y or n. y = all values of the field must be different
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['c'] = array(
			'label'=>tra('checkbox'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Provides a checkbox field for yes/no, on/off input.
				<dt>Usage: <strong>samerow</strong>
				<dt>Example: 1
				<dt>Description:
				<dd><strong>[samerow]</strong> will display the next field on the same row if a 1 is specified.
				</dl>'));
		$type['n'] = array(
			'label'=>tra('numeric field'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Provides a one-line field for numeric input only.  Prepend or append values may be alphanumeric.
				<dt>Usage: <strong>samerow,size,prepend,append,decimals,dec_point,thousands</strong>
				<dt>Example: 0,60,,hours
				<dt>Description:
				<dd><strong>[samerow]</strong> will display the next field or checkbox in the same row if a 1 is specified;
				<dd><strong>[size]</strong> is the visible size of the field in characters;
				<dd><strong>[prepend]</strong> is text that will be displayed before the field;
				<dd><strong>[append]</strong> is text that will be displayed just after the field;
				<dd><strong>[decimals]</strong> sets the number of decimal places;
				<dd><strong>[dec_point]</strong> sets the separator for the decimal point (decimals must also be set). Use c for comma and s for space;
				<dd><strong>[thousands]</strong> sets the thousands separator. Use c for comma and s for space. Setting only commas will result in no decimals 
													and commas as the thousands seprator;<br/><br/>
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['b'] = array(
			'label'=>tra('currency amount'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Provides a one-line field for numeric input only.  Prepend or append values may be alphanumeric.
				<dt>Usage: <strong>samerow,size,prepend,append,locale,symbol,first</strong>
				<dt>Example: 0,60,,per item
				<dt>Description:
				<dd><strong>[samerow]</strong> will display the next field or checkbox in the same row if a 1 is specified;
				<dd><strong>[size]</strong> is the visible size of the field in characters;
				<dd><strong>[prepend]</strong> is text that will be displayed before the field;
				<dd><strong>[append]</strong> is text that will be displayed just after the field;
				<dd><strong>[locale]</strong> set locale for currency formatting, for example en_US or en_US.UTF-8 or en_US.ISO-8559-1 (default=en_US);
				<dd><strong>[currency]</strong> The 3-letter ISO 4217 currency code indicating the currency to use (default=USD);
				<dd><strong>[symbol]</strong> i for international symbol, n for local (default=n);
				<dd><strong>[all_symbol]</strong> set to 1 to show symbol for every item (default only shows currency symbol on first item in a list) ;
				<br/><br/>
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['d'] = array(
			'label'=>tra('drop down'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows users to select only from a specified set of options in a drop-down bar.
				<dt>Usage: <strong>list_of_items</strong>
				<dt>Example: yes,no
				<dt>Description:
				<dd><strong>[list_of_items]</strong> is the list of all values you want in the drop-down, separated by commas;
				<dd>if you wish to specify a default value other than the first item, enter the value twice, consecutively, and it will appear once in the list as the default selection.
				</dl>'));
		$type['D'] = array(
			'label'=>tra('drop down with other textfield'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows users to select from a specified set of options in a drop-down bar, or provide an alternate selection in a one-line text field.
				<dt>Usage: <strong>list_of_items</strong>
				<dt>Example: yes,no
				<dt>Description:
				<dd><strong>[list_of_items]</strong> is the list of all values you want in the drop-down, separated by commas;
				<dd>if you wish to specify a default value other than the first item, enter the value twice, consecutively, and it will appear once in the list as the default selection.
				</dl>'));
		$type['R'] = array(
			'label'=>tra('radio buttons'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Provides a multiple-choice-style set of options from which a user may only choose one.
				<dt>Usage: <strong>list_of_items</strong>
				<dt>Example: yes,no
				<dt>Description:
				<dd><strong>[list of items]</strong> is the list of all values you want in the set, separated by commas;
				<dd>if you wish to specify a default value other than the first item, enter the value twice, consecutively, and it will appear as the one selected.
				<dd>If first option is &lt;br&gt;, options will be separated with a carriage return
				</dl>'));
		$type['u'] = array(
			'label'=>tra('user selector'),
			'opt'=>true,
			'itemChoicesList' => $all_users,
			'help'=>tra('<dl>
				<dt>Function: Allows a selection from a specified list of usernames.
				<dt>Usage: <strong>auto-assign,email_notify</strong>
				<dt>Example: 1,1
				<dt>Description:
				<dd><strong>[auto-assign]</strong> will auto-assign the creator of the item if set to 1, or will set the selection to the user who last modified the item if set to 2, or will give the choice between all the users for other values;
				<dd><strong>[email_notify]</strong> will send an email to the assigned user when the item is saved;
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['g'] = array(
			'label'=>tra('group selector'),
			'opt'=>true,
			'itemChoicesList' => $all_groups,
			'help'=>tra('<dl>
				<dt>Function: Allows a selection from a specified list of usergroups.
				<dt>Usage: <strong>auto-assign</strong>
				<dt>Example: 1
				<dt>Description:
				<dd><strong>[auto-assign]</strong> will auto-assign the field to the usergroup of the creator if set to 1, or will set the selection to the group of the user who last modified the item if set to 2, or will give the choice between all the groups for other values;
				<dd>if the user does not have a default group set, the first group the user belongs to will be chosen, otherwise Registered group will be used.
				</dl>'));
		$type['I'] = array(
			'label'=>tra('IP selector'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Provides a field for entering an IP address.
				<dt>Usage: <strong>auto-assign</strong>
				<dt>Example: 1
				<dt>Description:
				<dd><strong>[auto-assign]</strong> will auto-populate the field with the IP address of the user who created the item if set to 1, or will set the field to the IP of the user who last modified the item if set to 2, or will be a free IP for other values.
				</dl>'));
		$type['k'] = array(
			'label'=>tra('page selector'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows a selection from the list of pages.
				<dt>Usage: <strong>auto-assign, size, create</strong>
				<dt>Example: 1
				<dt>Description:
				<dd><strong>[auto-assign]</strong> will auto-assign the creator of the item if set to 1
				<dd><strong>[size]</strong> is the visible input length of the field in characters (<=0 not limited);
				<dd><strong>[create]</strong> will create the page if not exits copy of the page with name value of this param.which pagename is the value of this param
				<dd><strong>[link]</strong> will not display the link to the page if equals to n 
				<dd>
				</dl>'));
		$type['y'] = array(
			'label'=>tra('country selector'),
			'opt'=>true,
			'itemChoicesList' => $this->get_flags(true, true, true),
			'help'=>tra('<dl>
				<dt>Function: Allows a selection from a specified list of countries.
				<dt>Usage: <strong>name_flag,sort</strong>
				<dt>Example: 1,0
				<dt>Description:
				<dd><strong>[name_flag]</strong> default is 0 and will display both the country name and its flag, 1 will display only the country name, while 2 will show only the country flag;
				<dd><strong>[sortorder]</strong> specifies the order the country list should be displayed in, where 0 is the default and sorts according to the translated name, and 1 sorts according to the english name;
				<dd>if the country names are translated and option 1 is selected for the sort order, the countries will still appear translated, but will merely be in english order.
				</dl>'));
		$type['f'] = array(
			'label'=>tra('date and time'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Provides drop-down options to accurately select a date and/or time.
				<dt>Usage: <strong>datetime,startyear,endyear,blankdate</strong>
				<dt>Example: d,2000,,blank
				<dt>Description:
				<dd><strong>[datetime]</strong> will only allow a date to be selected if set to "d", and allows a full date and time selection if set to "dt", defaulting to "dt";
				<dd><strong>[startyear]</strong> allows you to specify a custom first year in the date range (eg. 1987), default is current year;
				<dd><strong>[endyear]</strong> allows you to specify a custom end year in the date range (eg. 2020), default is 4 years from now;
				<dd><strong>[blankdate]</strong> when set to "blank" will default the initial date field to an empty date, and allow selection of empty dates;
				<dd>blankdate is overridden if the field isMandatory;
				<dd>when set to "empty" will allow selection of empty date but default to current date
				<dd>multiple options must appear in the order specified, separated by commas.
				<dt>Example: "d,2000,2009,blank"
				<dd>sets a date only field from 2000 through 2009, allowing blank dates.
				</dl>'));
		$type['j'] = array(
			'label'=>tra('jscalendar'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Provides a javascript graphical date selector to select a date and/or time.
				<dt>Usage: <strong>datetime</strong>
				<dt>Example: dt
				<dt>Description:
				<dd><strong>[datetime]</strong> will only allow a date to be selected if set to "d", and allows a full date and time selection if set to "dt", defaulting to "dt".
				</dl>'));
		$type['i'] = array(
			'label'=>tra('image'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows user to upload an image into the tracker item.
				<dt>Usage: <strong>xListSize,yListSize,xDetailsSize,yDetailsSize,uploadLimitScale,shadowBox</strong>
				<dt>Example: 30,30,100,100,1000,item
				<dt>Description:
				<dd><strong>[xListSize]</strong> sets the pixel width of the image in the list view;
				<dd><strong>[yListSize]</strong> sets the pixel height of the image in the list view;
				<dd><strong>[xDetailSize]</strong> sets the pixel width of the image in the item view;
				<dd><strong>[yDetailSize]</strong> sets the pixel height of the image in the item view;
				<dd><strong>[uploadLimitScale]</strong> sets the maximum total size of the image, in pixels (width or height);
				<dd><strong>[shadowbox]</strong> actives a shadowbox(if feature on) = \'item\': to use the same shadowbox for an item, =\'individual\': to use a shadowbox only for this image, other value= to set the group of images of the shadowbox ;
				<dd>images are stored in img/trackers;
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['x'] = array(
			'label'=>tra('action'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: ?
				<dt>Usage: <strong>label,post,tiki-index.php,page:fieldname,highlight=test</strong>
				<dt>Example:
				<dt>Description:
				<dd><strong>[label]</strong> needs explanation;
				<dd><strong>[post]</strong> needs explanation;
				<dd><strong>[tiki-index.php]</strong> needs explanation;
				<dd><strong>[page:fieldname]</strong> needs explanation;
				<dd><strong>[highlight=test]</strong> needs explanation;
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['h'] = array(
			'label'=>tra('header'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: will display the field name as a html header h2;
				<dt>Usage: <strong>level</strong>
				<dt>Example: 2
				<dt>Description:
				<dd><strong>[level]</strong> level of the html header (default 2)
				</dl>'));
		$type['S'] = array(
			'label'=>tra('static text'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows insertion of a static block of text into a tracker to augment input fields. (non-editable)
				<dt>Usage: <strong>wikiparse,max</strong>
				<dt>Example: 1,20
				<dt>Description:
				<dd><strong>[wikiparse]</strong> will allow wiki syntax to be parsed if set to 1, otherwise default is 0 to only support line-breaks;
				<dd><strong>[max]</strong> is the maximum number of characters that are displayed in list mode;
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['e'] = array(
			'label'=>tra('category'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows one or more categories under a main category to be assigned to the tracker item.
				<dt>Usage: <strong>parentId,inputtype,selectall,descendants</strong>
				<dt>Example: 12,radio,1
				<dt>Description:
				<dd><strong>[parentId]</strong> is the ID of the main category, categories in the list will be children of this;
				<dd><strong>[inputtype]</strong> is one of [d|m|radio|checkbox], where d is a drop-down list, m is a multiple-selection drop-down list, radio and checkbox are self-explanatory;
				<dd><strong>[selectall]</strong> will provide a checkbox to automatically select all categories in the list if set to 1, default is 0;
				<dd><strong>[descendants]</strong> All descendant categories (not just first level children) will be included if set to 1, default is 0;
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['r'] = array(
			'label'=>tra('item link'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Provides a way to choose a value from another tracker (eventually with a link).
				<dt>Usage: <strong>trackerId,fieldId,linkToItem,displayedFieldsList</strong>
				<dt>Example: 3,5,0,6|8,opc,PageName
				<dt>Description:
				<dd><strong>[trackerId]</strong> is the tracker ID of the fields you want to display;
				<dd><strong>[fieldId]</strong> is the field in [trackerId] from which you can select a value among all the field values of the items of [trackerId];
				<dd><strong>[linkToItem]</strong> if set to 0 will simply display the value, but if set to 1 will provide a link directly to the item in the other tracker;
				<dd><strong>[displayedFieldsList]</strong> is a list of fields in [trackerId] to display instead of [fieldId], multiple fields can be separated with a |;
				<dd><strong>[status]</strong> filter on status (o, p, c, op, oc, pc or opc);
				<dd><strong>[linkPage]</strong> is the name of the wiki page to link to with trackerlist plugin in it; 
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['l'] = array(
			'label'=>tra('items list'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Displays a list of field values from another tracker that has a relation with this tracker(eventually with a link).
				<dt>Usage: <strong>trackerId,fieldIdThere,fieldIdHere,displayFieldIdThere,linkToItems</strong>
				<dt>Example: 5,3,4,10|11
				<dt>Description:
				<dd><strong>[trackerId]</strong> is the tracker ID of the fields you want to display;
				<dd><strong>[fieldIdThere]</strong> is the field (multiple fields can be separated with a ":") you want to link with;
				<dd><strong>[fieldIdHere]</strong> is the field in this tracker you want to link with;
				<dd><strong>[displayFieldIdThere]</strong> the field(s) in [trackerId] you want to display, multiple fields can be separated by "|";
				<dd><strong>[linkToItems]</strong> if set to 0 will simply display the value, but if set to 1 will provide a link directly to that values item in the other tracker;
				<dd><strong>[status]</strong> filter on status (o, p, c, op, oc, pc or opc);
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['w'] = array(
			'label'=>tra('dynamic items list'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Dynamically updates a selection list based on linked data from another tracker.
				<dt>Usage: <strong>trackerId,filterFieldIdThere,filterFieldIdHere,listFieldIdThere,statusThere</strong>
				<dt>Description:
				<dd><strong>[trackerId]</strong> is the ID of the tracker to link with;
				<dd><strong>[filterFieldIdThere]</strong> is the field you want to link with in that tracker;
				<dd><strong>[filterFieldIdHere]</strong> is the field you want to link with in the current tracker;
				<dd><strong>[listFieldIdThere]</strong> is the field ID you wish to pull the selection list from, based on the value selected in fiterFieldIdHere matching field(s) in filterFieldIdThere;
				<dd><strong>[statusThere]</strong> restricts values appearing in the list to those coming from records in the other tracker that meet specified statuses of [o|p|c] or in combination (op, opc);
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['m'] = array(
			'label'=>tra('email'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows users to enter an email address with option of making it active.
				<dt>Usage: <strong>link,watchopen,watchpending,watchclosed</strong>
				<dt>Example: 0,o
				<dt>Description:
				<dd><strong>[link]</strong> may be one of [0|1|2] and specifies how to display the email address, defaulting to 0 as plain text, 1 as an encoded hex mailto link, or 2 as a standard mailto link;
				<dd><strong>[watchopen]</strong> if set to "o" will email the address every time the status of the item changes to open;
				<dd><strong>[watchpending]</strong> if set to "p" will email the address every time the status of the item changes to pending;
				<dd><strong>[watchclosed]</strong> if set to "c" will email the address every time the status of the item changes to closed;
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['L'] = array(
			'label'=>tra('url'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows users to enter an url in a wiki syntax.
				</dl>'));
		$type['q'] = array(
			'label'=>tra('auto-increment'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows an incrementing value field, or itemId field. (non-editable)
				<dt>Usage: <strong>start,prepend,append,itemId</strong>
				<dt>Example: 1,,,itemId
				<dt>Description:
				<dd><strong>[start]</strong> is the starting value for the field, defaults to 1;
				<dd><strong>[prepend]</strong> is text that will be displayed before the field;
				<dd><strong>[append]</strong> is text that will be displayed after the field;
				<dd><strong>[itemId]</strong> if set to "itemId" will set this field to match the value of the actual database itemId field value;
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['U'] = array(
			'label'=>tra('user subscription'),
			'opt'=>false,
			'help'=>tra('<dl>
				<dt>Function: Allow registered users to subscribe themselves to a tracker item (think Evite.com).
				<dt>Description:
				<dd>Use this field as you would to have people sign up for an event. It is best if the tracker is only editable by its creator or the admin.  To set the max number of subscribers, edit the tracker item and put the number at the beginning of the field.
				<dt>Example:
				<dd>Old field may have "#" or "#2[0]" in it.  Making it "20#2[0]" will set the max number to 20.
				</dl>'));
		$type['G'] = array(
			'label'=>tra('Google Maps'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Use Google Maps.
				<dt>Will display a Google Maps around a point.
				<dt>Usage: <strong>use_as_item_location,bubble_fieldId,icon</strong>
				<dt>Example: y,23|24,img/icons2/icn_members.gif
				<dt>Description:
				<dd><strong>[use_as_item_location]</strong> if set to y, this google map field will be used as item location and object geo attributes are set when field value is changed.
				<dd><strong>[bubble_fieldid]</strong> is the fieldId(s) (separated by |) that contains the text that will be displayed in the bubble of the map marker. The first field will be used as the link.
				<dd><strong>[icon]</strong> is the url of the default icon to use for markers of items on the map.
				</dl>'));
		$type['s'] = array(
			'label'=>tra('system'),
			'opt'=>false,
			'help'=>tra('<dl>
				<dt>Function: System only.
				<dt>Usage: None
				<dt>Description:
				<dd>Needs a description.
				</dl>'));
		$type['C'] = array(
			'label'=>tra('computed field'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Provides a computed value based on numeric field values.
				<dt>Usage: <strong>formula</strong>
				<dt>Description:
				<dd><strong>[formula]</strong> is the formula you wish to compute, using numeric values, operators "+ - * / ( )", and tracker fields identified with a leading #;
				<dt>Example: "#3*(#4+5)"
				<dd>adds the numeric value in item 4 by 5, and multiplies it by the numeric value in item 3.
				</dl>'));
		$type['p'] = array(
			'label'=>tra('user preference'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows user preference changes from a tracker.
				<dt>Usage: <strong>type</strong>
				<dt>Example: password
				<dt>Description:
				<dd><strong>[type]</strong> if value is password, will allow to change the user password, if value is email, will display/allow to change the user email, other values possible: language;
				</dl>'));
		$type['usergroups'] = array(
			'label'=>tra('user groups'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows to display the user groups.
				</dl>'));
		$type['A'] = array(
			'label'=>tra('attachment'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows a file to be attached to the tracker item.
				<dt>Usage: <strong>listview</strong>
				<dt>Example: nu
				<dt>Description:
				<dd><strong>[listview]</strong> may be one of [n|t|s|u|m] on their own or in any combination (n, t, ns, nts), allowing you to see the attachment in the item list view as its name (n), its type (t), its size (n), the username of the uploader (u), or the mediaplayer plugin(m);
				note that this option will cost an extra query to the database for each attachment and can severely impact performance with several attachments.
				<dd>
				</dl>'));
		$type['F'] = array(
			'label'=>tra('freetags'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows freetags to be shown or added for tracker item
				<dt>Usage: <strong>size</strong>
				<dt>Example: 80,n,n
				<dt>Description:
				<dd><strong>[size]</strong> is the visible length of the field in characters;
				<dd><strong>[hidehelp]</strong> if y, do not show help text when entering tags;
				<dd><strong>[hidesuggest]</strong> if y, do not show suggested tags when entering tags;  
				<dd>multiple options must appear in the order specified, separated by commas.
				</dl>'));
		$type['N'] = array(
			'label'=>tra('in group'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Allows to display if a item user is in a group and when he was assigned to the group (needs a user selector field)
				<dt>Usage: <strong>groupName,date</strong>
				<dt>Example: Members,date
				<dt>Description:
				<dd><strong>GroupName</strong> Group to test. <strong>date</strong> displays the date the user was assigned in the group (if known), otherwise will display yes/no.
				<dd>
				</dl>'));
		$type['*'] = array(
			'label'=>tra('stars'),
			'opt'=>true,
			'help'=>tra('<dl>
				<dt>Function: Display stars
				<dt>Usage: <strong>list options (positive increasing numbers</strong>
				<dt>Example: 1,2,3,4
				<dt>Description:
				<dd>Like the rating
				<dd>
				</dl>'));

		return $type;
	}

	function status_types() {
		$status['o'] = array('label'=>tra('open'),'perm'=>'tiki_p_view_trackers','image'=>'img/icons2/status_open.gif');
		$status['p'] = array('label'=>tra('pending'),'perm'=>'tiki_p_view_trackers_pending','image'=>'img/icons2/status_pending.gif');
		$status['c'] = array('label'=>tra('closed'),'perm'=>'tiki_p_view_trackers_closed','image'=>'img/icons2/status_closed.gif');
		return $status;
	}

	function get_isMain_value($trackerId, $itemId) {
	    global $prefs;

	    $query = "select tif.`value` from `tiki_tracker_item_fields` tif, `tiki_tracker_items` i, `tiki_tracker_fields` tf where i.`itemId`=? and i.`itemId`=tif.`itemId` and tf.`fieldId`=tif.`fieldId` and tf.`isMain`=? and tif.`lang`=? ";
		$result = $this->getOne($query, array( (int)$itemId, "y", $prefs['language']));
		if(isset($result) && $result!='')
		  return $result;

		$query = "select tif.`value` from `tiki_tracker_item_fields` tif, `tiki_tracker_items` i, `tiki_tracker_fields` tf where i.`itemId`=? and i.`itemId`=tif.`itemId` and tf.`fieldId`=tif.`fieldId` and tf.`isMain`=?  ";
		$result = $this->getOne($query, array((int)$itemId, "y"));
		return $result;
	}
	function get_main_field($trackerId) {
		$query = 'select `fieldId` from `tiki_tracker_fields` where `isMain`=? and `trackerId`=?';
		return $this->getOne($query, array('y', $trackerId));
	}

	function categorized_item($trackerId, $itemId, $mainfield, $ins_categs) {
		global $categlib; include_once('lib/categories/categlib.php');
		$cat_type = "trackeritem";
		$cat_objid = $itemId;
		$cat_desc = '';
		if (empty($mainfield))
				$cat_name = $itemId;
		else
				$cat_name = $mainfield;
		$cat_href = "tiki-view_tracker_item.php?trackerId=$trackerId&itemId=$itemId";
		// The following needed to ensure category field exist for item (to be readable by list_items)
		$tracker_fields_info = $this->list_tracker_fields($trackerId);
		foreach($tracker_fields_info['data'] as $t) {
			if ( $t['type'] == 'e' ) {
				$this->query("insert ignore into `tiki_tracker_item_fields` (`itemId`,`fieldId`,`value`) values(?,?,?)", array($itemId, $t['fieldId'], ''));
			}
		}
		$categlib->update_object_categories($ins_categs, $cat_objid, $cat_type, $cat_desc, $cat_name, $cat_href);
	}
	function move_up_last_fields($trackerId, $fieldId, $delta=1) {
		$query = 'update `tiki_tracker_fields`set `position`= `position`+ ? where `trackerId` = ? and `fieldId` = ?';
		$result = $this->query( $query, array((int)$delta, (int)$trackerId, (int)$fieldId) );
	}
	/* list all the values of a field
	 */
	function list_tracker_field_values($trackerId, $fieldId, $status='o', $distinct='y', $lang='', $exceptItemId='') {
		$mid = '';
		$bindvars[] = (int)$fieldId;
		if (!$this->getSqlStatus($status, $mid, $bindvars, $trackerId)) {
			return null;
		}
		$sort_mode = "value_asc";
		$distinct = $distinct == 'y'?'distinct': '';
		if (!empty($lang)) {
			$mid .= ' and `lang`=? ';
			$bindvars[] = $lang;
		}
		if (!empty($exceptItemId)) {
			$mid .= ' and ttif.`itemId` != ? ';
			$bindvars[] = $exceptItemId;
		}
		$query = "select $distinct(ttif.`value`) from `tiki_tracker_item_fields` ttif, `tiki_tracker_items` tti where tti.`itemId`= ttif.`itemId`and ttif.`fieldId`=? $mid order by ".$this->convertSortMode($sort_mode);
		$result = $this->query( $query, $bindvars);
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res['value'];
		}
		return $ret;
	}

	/* tests if a value exists in a field
	 */
	function check_field_value_exists($value, $fieldId, $exceptItemId = 0) {
		$bindvars[] = (int) $fieldId;

		$mid = ' AND ttif.`value`=?';
		$bindvars[] = $value;
		
		if ($exceptItemId > 0) {
			$mid .= ' AND ttif.`itemId` != ? ';
			$bindvars[] = (int) $exceptItemId;
		}
		$query = "SELECT COUNT(*) FROM `tiki_tracker_item_fields` ttif WHERE ttif.`fieldId`=? $mid";
		$result = $this->getOne($query, $bindvars);
		return $result > 0;
	}

	function is_multilingual($fieldId){
	         if ($fieldId<1)
	           return 'n';
	         global $prefs;
	         if ( $prefs['feature_multilingual'] !='y')
	           return 'n';
	         $query = "select `isMultilingual` from `tiki_tracker_fields` where `fieldId`=?";
	         $res=$this->getOne($query,(int)$fieldId);
	         if ($res === NULL || $res=='n' || $res=='')
	           return 'n';
	         else
		   return "y";
	}

	/* return the values of $fieldIdOut of an item that has a value $value for $fieldId */
	function get_filtered_item_values($fieldId, $value, $fieldIdOut) {
		$query = "select ttifOut.`value` from `tiki_tracker_item_fields` ttifOut, `tiki_tracker_item_fields` ttif
			where ttifOut.`itemId`= ttif.`itemId`and ttif.`fieldId`=? and ttif.`value`=? and ttifOut.`fieldId`=?";
		$bindvars = array($fieldId, $value, $fieldIdOut);
		$result = $this->query($query, $bindvars);
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res['value'];
		}
		return $ret;
	}
	/* look if a tracker has only one item per user and if an item has already being created for the user  or the IP*/
	function get_user_item(&$trackerId, $trackerOptions, $userparam=null, $user= null, $status='') {
		global $prefs, $tikilib;
		if (empty($user)) {
			$user = $GLOBALS['user'];
		}
		if (empty($trackerId) && $prefs['userTracker'] == 'y') {
			$utid = $userlib->get_tracker_usergroup($user);
			if (!empty($utid['usersTrackerId'])) {
				$trackerId = $utid['usersTrackerId'];
				$itemId = $this->get_item_id($trackerId, $utid['usersFieldId'], $user);
			}
			return $itemId;
		}
		$userreal=$userparam!=null?$userparam:$user;
		if (!empty($userreal)) {
			if ($fieldId = $this->get_field_id_from_type($trackerId, 'u', '1%')) { // user creator field
				$value = $userreal;
				$items = $this->get_items_list($trackerId, $fieldId, $value, $status);
				if (!empty($items))
					return $items[0];
			}
		}
		if ($fieldId = $this->get_field_id_from_type($trackerId, 'I', '1')) { // IP creator field
			$IP = $tikilib->get_ip_address();
			$items = $this->get_items_list($trackerId, $fieldId, $IP, $status);
			if (!empty($items))
				return $items[0];
			else
				return 0;
		}
	}
	function get_item_creator($trackerId, $itemId) {
		if ($fieldId = $this->get_field_id_from_type($trackerId, 'u', '1%')) { // user creator field
			return $this->get_item_value($trackerId, $itemId, $fieldId);
		} else {
			return null;
		}
	}
	function get_item_group_creator($trackerId, $itemId) {
		if ($fieldId = $this->get_field_id_from_type($trackerId, 'g', '1%')) { // group creator field
			return $this->get_item_value($trackerId, $itemId, $fieldId);
		} else {
			return null;
		}
	}
	/* find the best fieldwhere you can do a filter on the initial
	 * 1) if sort_mode and sort_mode is a text and the field is visible
	 * 2) the first main taht is text
	 */
	function get_initial_field($list_fields, $sort_mode) {
		if (preg_match('/^f_([^_]*)_?.*/', $sort_mode, $matches)) {
			if (isset($list_fields[$matches[1]])) {
				$type = $list_fields[$matches[1]]['type'];
				if ($type == 't' || $type == 'a' || $type == 'm')
					return $matches[1];
			}
		}
		foreach($list_fields as $fieldId=>$field) {
			if ($field['isMain'] == 'y' && ($field['type'] == 't' || $field['type'] == 'a' || $field['type'] == 'm'))
				return $fieldId;
		}
	}
	function get_nb_items($trackerId) {
		return $this->getOne("select count(*) from `tiki_tracker_items` where `trackerId`=?",array((int) $trackerId));
	}
	function duplicate_tracker($trackerId, $name, $description = '', $descriptionIsParsed = 'n') {
		$tracker_info = $this->get_tracker($trackerId);
		if ($t = $this->get_tracker_options($trackerId))
			$tracker_info = array_merge($tracker_info,$t);
		$newTrackerId = $this->replace_tracker(0, $name, $description, array(), $descriptionIsParsed);
		$query = "select * from `tiki_tracker_options` where `trackerId`=?";
		$result = $this->query($query, array($trackerId));
		while ($res = $result->fetchRow()) {
			$options[$res['name']] = $res['value'];
		}
		$fields = $this->list_tracker_fields($trackerId, 0, -1, 'position_asc', '');
		foreach($fields['data'] as $field) {
			$newFieldId = $this->replace_tracker_field($newTrackerId, 0, $field['name'], $field['type'], $field['isMain'], $field['isSearchable'], $field['isTblVisible'], $field['isPublic'], $field['isHidden'], $field['isMandatory'], $field['position'], $field['options'], $field['description'], $field['isMultilingual'], $field['itemChoices']);
			if ($options['defaultOrderKey'] == $field['fieldId']) {
				$options['defaultOrderKey'] = $newFieldId;
			}
		}
		$query = "insert into `tiki_tracker_options`(`trackerId`,`name`,`value`) values(?,?,?)";
		foreach ($options as $name=>$val) {
			$this->query($query, array($newTrackerId, $name, $val));
		}
		return $newTrackerId;
	}
	// look for default value: a default value is 2 consecutive same value
	function set_default_dropdown_option($field) {
		for ($io = 0; $io < count($field['options_array']); ++$io) {
			if ($io > 0 && $field['options_array'][$io] == $field['options_array'][$io - 1]) {
				$field['defaultvalue'] = $field['options_array'][$io];
				for (; $io < count($field['options_array']) - 1; ++$io) {
					$field['options_array'][$io] = $field['options_array'][$io + 1];
				}
				unset($field['options_array'][$io]);
				break;
			}
		}
		return $field;
	}
	function get_notification_emails($trackerId, $itemId, $options, $newItemId=0, $status='', $oldStatus='') {
		global $prefs;
		$watchers_global = $this->get_event_watches('tracker_modified',$trackerId);
		$watchers_local = $this->get_local_notifications($itemId, $newItemId, $status, $oldStatus);
		$watchers_item = $itemId? $this->get_event_watches('tracker_item_modified',$itemId, array('trackerId'=>$trackerId)): array();
		$watchers_outbound = array();
		if( array_key_exists( "outboundEmail", $options ) && $options["outboundEmail"] ) {
			$emails3 = preg_split('/,/', $options['outboundEmail']);
			foreach ($emails3 as $w) {
				global $userlib, $user_preferences;
				$u = $userlib->get_user_by_email($w);
				$this->get_user_preferences($u, array('user', 'language', 'mailCharset'));
				if (empty($user_preferences[$u]['language'])) $user_preferences[$u]['language'] = $prefs['site_language'];
				if (empty($user_preferences[$u]['mailCharset'])) $user_preferences[$u]['mailCharset'] = $prefs['users_prefs_mailCharset'];
				$watchers_outbound[] = array('email'=>$w, 'user'=>$u, 'language'=>$user_preferences[$u]['language'], 'mailCharset'=>$user_preferences[$u]['mailCharset']);
			}
		}
		//echo "<pre>GLOBAL ";print_r($watchers_global);echo 'LOCAL ';print_r($watchers_local); echo 'ITEM ';print_r($watchers_item); echo 'OUTBOUND ';print_r($watchers_outbound);
		$emails = array();
		$watchers = array();
		foreach (array('watchers_global', 'watchers_local', 'watchers_item', 'watchers_outbound') as $ws) {
			if (!empty($$ws)) {
				foreach($$ws as $w) {
					$wl = strtolower($w['email']);
					if (!in_array($wl, $emails)) {
						$emails[] = $wl;
						$watchers[] = $w;
					}
				}
			}
		}
		return $watchers;
	}
	/* sort allFileds function of a list of fields */
	function sort_fields($allFields, $listFields) {
		$tmp = array();
		foreach ($listFields as $fieldId) {
			if (substr($fieldId, 0, 1) == '-') {
				$fieldId = substr($fieldId, 1);
			}
			foreach ($allFields['data'] as $i=>$field) {
				if ($field['fieldId'] == $fieldId && $field['fieldId']) {
					$tmp[] = $field;
					$allFields['data'][$i]['fieldId'] = 0;
					break;
				}
			}
		}
		// do not forget the admin fields like user selector
		foreach ($allFields['data'] as $field) {
			if ($field['fieldId']) {
				$tmp[] = $field;
			}
		}
		$allFields['data'] = $tmp;
		$allFields['cant'] = count($tmp);
		return $allFields;
	}
	/* return all the values+field options  of an item for a type field (ex: return all the user selector value for an item) */
	function get_item_values_by_type($itemId, $typeField) {
		$query = "select ttif.`value`, ttf.`options` from `tiki_tracker_fields` ttf, `tiki_tracker_item_fields` ttif";
		$query .= " where ttif.`itemId`=? and ttf.`type`=? and ttf.`fieldId`=ttif.`fieldId`";
		$ret = $this->fetchAll($query, array($itemId, $typeField));
		foreach ( $ret as &$res ) {
			$res['options_array'] = preg_split('/,/', $res['options']);
		}
		return $ret;
	}
	/* return all the emails that are locally watching an item */
	function get_local_notifications($itemId, $newItemId=0, $status='', $oldStatus='') {
		global $tikilib, $userlib, $user_preferences, $prefs;
		$emails = array();
		// user field watching item
		$res = $this->get_item_values_by_type($itemId?$itemId:$newItemId, 'u');
		if (is_array($res)) {
			foreach ($res as $f) {
				if (isset($f['options_array'][1]) && $f['options_array'][1] == 1) {
					$tikilib->get_user_preferences($f['value'], array('email', 'user', 'language', 'mailCharset'));
					$emails[] = array('email'=>$userlib->get_user_email($f['value']), 'user'=>$f['value'], 'language'=>$user_preferences[$f['value']]['language'], 'mailCharset'=>$user_preferences[$f['value']]['mailCharset']);
				}
			}
		}
		// email field watching status change
		if ($status != $oldStatus) {
			$res = $this->get_item_values_by_type($itemId?$itemId:$newItemId, 'm');
			if (is_array($res)) {
				foreach ($res as $f) {
					if ((isset($f['options_array'][1]) && $f['options_array'][1] == 'o' && $status == 'o')
						|| (isset($f['options_array'][2]) && $f['options_array'][2] == 'p' && $status == 'p')
						|| (isset($f['options_array'][3]) && $f['options_array'][3] == 'c' && $status == 'c')) {
						$emails[] = array('email'=> $f['value'], 'user'=>'', 'language'=>$prefs['language'], 'mailCharset'=>$prefs['users_prefs_mailCharset'], 'action'=>'status');
					}
				}
			}
		}
		return $emails;
	}
	function get_join_values($trackerId, $itemId, $fieldIds, $finalTrackerId='', $finalFields='', $separator=' ', $status='') {
		global $smarty;
		$select[] = "`tiki_tracker_item_fields` t0";
		$where[] = " t0.`itemId`=?";
		$bindVars[] = $itemId;
		$mid = '';
		for ($i = 0, $tmp_count = count($fieldIds) - 1 ; $i < $tmp_count ; $i += 2) {
			$j = $i + 1;
			$k = $j + 1;
			$select[] = "`tiki_tracker_item_fields` t$j";
			$select[] = "`tiki_tracker_item_fields` t$k";
			$where[] = "t$i.`value`=t$j.`value` and t$i.`fieldId`=? and t$j.`fieldId`=?";
			$bindVars[] = $fieldIds[$i];
			$bindVars[] = $fieldIds[$j];
			$where[] = "t$j.`itemId`=t$k.`itemId` and t$k.`fieldId`=?";
			$bindVars[] = $fieldIds[$k];
		}
		if (!empty($status)) {
			$this->getSqlStatus($status, $mid, $bindVars, $trackerId);
			$select[] = '`tiki_tracker_items` tti';
			$mid .= " and tti.`itemId`=t$k.`itemId`";
		}
		$query = "select t$k.* from ".implode(',',$select).' where '.implode(' and ',$where).$mid;
		$result = $this->query($query, $bindVars);
		$ret = array();
		while ($res = $result->fetchRow()) {
			$field_value = $this->get_tracker_field($res['fieldId']);
			$field_value['value'] = $res['value'];
			$field_value['showlinks'] = 'n';
			$smarty->assign('field_value', $field_value);
			$ret[$res['itemId']] = $smarty->fetch('tracker_item_field_value.tpl');
			if (is_array($finalFields) && count($finalFields)) {
				$i = 0;
				foreach ($finalFields as $f) {
					if (!$i++)
						continue;
					$field_value = $this->get_tracker_field($f);
					$ff = $this->get_item_value($finalTrackerId, $res['itemId'], $f);;
					$field_value['value'] = $ff;
					$field_value['showlinks'] = 'n';
					$smarty->assign('field_value', $field_value);
					$ret[$res['itemId']] .= $separator.$smarty->fetch('tracker_item_field_value.tpl');
				}
			}
		}
		return $ret;
	}
	function get_left_join_sql($fieldIds) {
		$sql = '';
		for ($i = 0, $tmp_count = count($fieldIds); $i < $tmp_count; $i += 3) {
			$j = $i + 1;
			$k = $j + 1;
			$tti = $i ? "t$i" : 'tti';
			$sttif = $k < $tmp_count - 1 ? "t$k" : 'sttif';
			$sql .= " LEFT JOIN (`tiki_tracker_item_fields` t$i) ON ($tti.`itemId`= t$i.`itemId` and t$i.`fieldId`=".$fieldIds[$i].")";
			$sql .= " LEFT JOIN (`tiki_tracker_item_fields` t$j) ON (t$i.`value`= t$j.`value` and t$j.`fieldId`=".$fieldIds[$j].")";
			$sql .= " LEFT JOIN (`tiki_tracker_item_fields` $sttif) ON (t$j.`itemId`= $sttif.`itemId` and $sttif.`fieldId`=".$fieldIds[$k].")";
		}
		return $sql;
	}
	function get_item_info($itemId) {
		$query = 'select * from `tiki_tracker_items` where `itemId`=?';
		$result = $this->query($query, array((int) $itemId));
		if ($res = $result->fetchRow()) {
			return $res;
		} else {
			return NULL;
		}
	}
	function rename_page($old, $new) {
		$query = "update `tiki_tracker_item_fields` ttif left join `tiki_tracker_fields` ttf on (ttif.fieldId = ttf.fieldId) set ttif.`value`=? where ttif.`value`=? and ttf.`type` = ?";
		$this->query($query, array($new, $old, 'k'));
	}
	function build_date($input, $field, $ins_id) {
		global $tikilib;
		$value = '';
		$monthIsNull = empty($input[$ins_id.'Month']) || $input[$ins_id.'Month'] == null || $input[$ins_id.'Month'] == 'null'|| $input[$ins_id.'Month'] == '';
		$dayIsNull = empty($input[$ins_id.'Day']) || $input[$ins_id.'Day'] == null || $input[$ins_id.'Day'] == 'null' || $input[$ins_id.'Day'] == '';
		$yearIsNull = empty($input[$ins_id.'Year']) || $input[$ins_id.'Year'] == null || $input[$ins_id.'Year'] == 'null' || $input[$ins_id.'Year'] == '';
		$hourIsNull = !isset($input[$ins_id.'Hour']) || $input[$ins_id.'Hour'] == null || $input[$ins_id.'Hour'] == 'null' || $input[$ins_id.'Hour'] == ''|| $input[$ins_id.'Hour'] == ' ';
		$minuteIsNull = empty($input[$ins_id.'Minute']) || $input[$ins_id.'Minute'] == null || $input[$ins_id.'Minute'] == 'null' || $input[$ins_id.'Minute'] == '' || $input[$ins_id.'Minute'] == ' ';
		if ($field['options_array'][0] == 'd') {
			if ($monthIsNull || $dayIsNull || $yearIsNull) { // all the values must be blank
				$value = '';
			} else {
				$value = $tikilib->make_time(0, 0, 0, $input[$ins_id.'Month'], $input[$ins_id.'Day'], $input[$ins_id.'Year']);
			}
		} elseif ($field['options_array'][0] == 't') { // all the values must be blank
			if ($hourIsNull || $minuteIsNull) {
				$value = '';
			} else {
				//if (isset($input[$ins_id.'Meridian']) && $input[$ins_id.'Meridian'] == 'pm') $input[$ins_id.'Hour'] += 12;
				$now = $tikilib->now;
				$value = $tikilib->make_time($input[$ins_id.'Hour'], $input[$ins_id.'Minute'], 0, $tikilib->date_format("%m", $now), $tikilib->date_format("%d", $now), $tikilib->date_format("%Y", $now));
			}
		} else {
			if ($monthIsNull || $dayIsNull || $yearIsNull || $hourIsNull || $minuteIsNull) { // all the values must be blank
				$value = '';
			} else {
				//if (isset($input[$ins_id.'Meridian']) && $input[$ins_id.'Meridian'] == 'pm') $input[$ins_id.'Hour'] += 12;
				$value = $tikilib->make_time($input[$ins_id.'Hour'], $input[$ins_id.'Minute'], 0, $input[$ins_id.'Month'], $input[$ins_id.'Day'], $input[$ins_id.'Year']);
			}
		}
		return $value;
	}
	/* get the fields from the pretty tracker template
	* return a list of fieldIds */
	function get_pretty_fieldIds($resource, $type='wiki') {
		global $tikilib, $smarty;
		if ($type == 'wiki') {
			$wiki_info = $tikilib->get_page_info($resource);
			if (!empty($wiki_info)) {
				$f = $wiki_info['data'];
			}
		} else {
			$resource_name = $smarty->get_filename($resource);
			$f = $smarty->_read_file($resource_name);
		}
		if (!empty($f)) {
			preg_match_all('/\$f_([0-9]+)/', $f, $matches);
			return $matches[1];
		}
		return array();
	}
	
	/**
	 * @param mixed $value		string or array to process
	 */
	function replace_pretty_tracker_refs( &$value ) {
		global $smarty;
		
		if( is_array( $value ) ) {
			foreach( $value as &$v ) {
				$this->replace_pretty_tracker_refs( $v );
			}
		} else {
			// array syntax for callback function needed for some versions of PHP (5.2.0?) - thanks to mariush on http://php.net/preg_replace_callback
			$value = preg_replace_callback('/\{\$(f_\d+)\}/', array( &$this, '_pretty_tracker_replace_value'), $value);
		}
	}
	
	static function _pretty_tracker_replace_value($matches) {
		global $smarty;
		$s_var = null;
		if (!empty($matches[1])) { 
			$s_var = $smarty->get_template_vars($matches[1]);
		}
		if (!is_null($s_var)) {
			$r = $s_var;
		} else {
			$r = $matches[0];
		}
		return $r;
	}

	function nbComments($user) {
		$query = 'select count(*) from `tiki_tracker_item_comments` where `user`=?';
		return $this->getOne($query, array($user));
	}
	function lastModif($trackerId) {
		$bindvars = array($trackerId);
		$mid = '`trackerId` = ? ';
		$query = "select max(`lastmodif`) from `tiki_tracker_items` where $mid";
		return $this->getOne($query, $bindvars);
	}
	function get_field($fieldId, $fields) {
		foreach ($fields as $f) {
			if ($f['fieldId'] == $fieldId) {
				return $f;
			}
		}
		return false;
	}
	function fieldId_is_editable($field, $item) {
		global $tiki_p_admin_trackers;
		if ($tiki_p_admin_trackers == 'y') {
			return true;
		}
		if ($field['type'] == 'u' || $field['type'] == 'g' || $field['type'] == 'I') {
			return false;
		}
		if (empty($field['isHidden']) || $field['isHidden'] == 'n') {
			return true;
		}
		if ($field['isHidden'] == 'p' || $field['isHidden'] == 'y') {
			return false;
		}
		if (isset($item['createdBy']) && $user == $item['createdBy'] && $field['isHidden'] == 'ec') {
			return true;
		}
		return false;
	}
	/* $fields are all the fields, $field is the dynamic item list field('w') */
	function prepare_dynamic_items_list($field, &$fields) {
		$refFieldId = $field['options_array'][2];
        $refFieldOnTheForm = false;
		$fieldIdx = 0;
		foreach ($fields as $i=>$ff) { // get the item link field
			if ($ff['fieldId'] == $refFieldId) {
				$refFieldOnTheForm = true;
				$refFieldId = $i;
			}
			if ($ff['fieldId'] == $field['fieldId']) {
				$fieldIdx = $i;
			}
		}
        if (!$refFieldOnTheForm) {
            // we pretend it was an item list
			// the $fields[$fieldIdx]['list'] is to be filled in
			$fields[$fieldIdx]['type'] = 'r';
		}
		else {
			if (!isset($fields[$refFieldId]['http_request'])) {
				$fields[$refFieldId]['http_request'] = array('', '', '', '', '', '', '', '', '');
			}
			for ($i = 0; $i < 5; $i++) {
				if (!empty($fields[$refFieldId]['http_request'][$i])) {
					$fields[$refFieldId]['http_request'][$i] .= ',';
				}
				if (!empty($field['options_array'][$i])) {
					$fields[$refFieldId]['http_request'][$i] .= $field['options_array'][$i];
				}
			}
			$fields[$refFieldId]['http_request'][5] .=
					($fields[$refFieldId]['http_request'][5] ? ",":"") .
					$field['fieldId'];
			$fields[$refFieldId]['http_request'][6] .=
					($fields[$refFieldId]['http_request'][6] ? "," : "") .
					$field['isMandatory'];
			$fields[$refFieldId]['http_request'][7] .= $fields[$refFieldId]['value'];
			$fields[$refFieldId]['http_request'][8] .= ($fields[$refFieldId]['http_request'][8] ? "," : "") . $field['value'];
		}
		/* the list of potential value is calculated by a javascript call to selectValues at the end of the tpl */
	}
	function flaten($fields) {
		$new = array();
		if (empty($fields))
			return $new;
		foreach ($fields as $field) {
			if (is_array($field)) {
				$new = array_merge($new, $this->flaten($field));
			} else {
				$new[] = $field;
			}
		}		
		return $new;
	}
	function test_field_type($fields, $types) {
		$new = $this->flaten($fields);
		$query = 'select count(*) from `tiki_tracker_fields` where `fieldId` in ('. implode(',', array_fill(0,count($new),'?')).') and `type` in ('. implode(',', array_fill(0,count($types),'?')).')';
		return $this->getOne($query, array_merge($new, $types));
	}
	function get_computed_info($options, $trackerId=0, &$fields=null) {
		preg_match_all('/#([0-9]+)/', $options, $matches);
		$nbDates = 0;
		foreach($matches[1] as $k => $match) {
			if (empty($fields)) {
				$allfields = $this->list_tracker_fields($trackerId, 0, -1, 'position_asc', '');
				$fields = $allfields['data'];
			}
			foreach($fields as $k => $field) {
				if ($field['fieldId'] == $match && ($field['type'] == 'f' || $field['type'] == 'j')) {
					++$nbDates;
					$info = $field;
					break;
				} else if ($field['fieldId'] == $match && $field['type'] == 'C') {
					$info = $this-> get_computed_info($field['options'], $trackerId, $fields);
					if (!empty($info) && ($info['computedtype'] == 'f' || $info['computedtype'] == 'j')) {
						++$nbDates;
						break;
					}
				}
			}
		}
		if ($nbDates == 0) {
			return null;
		} elseif ($nbDates % 2 == 0) {
			return array('computedtype'=>'duration', 'options'=>$info['options'] ,'options_array'=>$info['options_array']);
		} else {
			return array('computedtype'=>'f', 'options'=>$info['options'] ,'options_array'=>$info['options_array']);
		}
	}
	function update_item_link_value($trackerId, $fieldId, $old, $new) {
		if ($old == $new || empty($old)) {
			return;
		}
		static $fields_used_in_item_links;
		if (!isset($fields_used_in_item_links)) {
			$query = 'select `fieldId`, `options` from `tiki_tracker_fields` where `type`=?';
			$fields = $this->fetchAll($query, array('r'));
			foreach ($fields as $field) {
				$field['options_array'] = preg_split('/\s*,\s*/', $field['options']);
				$fields_used_in_item_links[$field['options_array'][1]][] = $field['fieldId'];
			}
		}
		if (empty($fields_used_in_item_links[$fieldId])) {// field not use in a ref of item link
			return;
		}
		$query = 'update `tiki_tracker_item_fields` set `value`=? where `value`=? and `fieldId` in ('.implode(',', array_fill(0, count($fields_used_in_item_links[$fieldId]), '?')).')';
		$bindvars = array($new, $old);
		$bindvars = array_merge($bindvars, $fields_used_in_item_links[$fieldId]);
		$this->query($query, $bindvars);
	}
	function change_status($items, $status) {
		if (!count($items)) {
			return;
		}
		$bindvars[] = $status;
		$query = 'update `tiki_tracker_items` set `status`=? where `itemId` in ('.implode(',', array_fill(0,count($items),'?')).')';
		foreach ($items as $item) {
			$bindvars[] = $item['itemId'];
		}
		$this->query($query, $bindvars);
	}
	function log($version, $itemId, $fieldId, $value='', $lang='') {
		if (empty($version)) {
		   return;
		}
		$query = 'insert into `tiki_tracker_item_field_logs` (`version`, `itemId`, `fieldId`, `value`, `lang`) values(?,?,?,?,?)';
		if (is_array($value)) {
			foreach ($value as $v) {
				$this->query($query, array($version, $itemId, $fieldId, $v, $lang));	
			}
		} else {
			$this->query($query, array($version, $itemId, $fieldId, $value, $lang));
		}
	}
	function last_log_version($itemId) {
		$query = 'select max(`version`) from `tiki_tracker_item_field_logs` where `itemId` = ?';
		return $this->getOne($query, array($itemId));
	}
	function remove_item_log($itemId) {
		$query = 'delete from `tiki_tracker_item_field_logs` where `itemId`=?';
		$this->query($query, $itemId); 
	}
	function get_item_history($item_info=null, $fieldId=0, $filter='', $offset=0, $max=-1) {
		global $prefs;
		if (!empty($fieldId)) {
			$mid2[] = $mid[] = 'ttifl.`fieldId`=?';
			$bindvars[] = $fieldId;
		}
		if (!empty($item_info['itemId'])) {
			$mid[] = 'ttifl.`itemId`=?';
			$bindvars[] = $item_info['itemId'];
			if ($prefs['feature_categories'] == 'y') {
				global $categlib; include_once('lib/categories/categlib.php');
				$item_categs = $categlib->get_object_categories('trackeritem', $item_info['itemId']);
				}
			}
		$query = 'select ttifl.*, ttf.* from `tiki_tracker_item_fields` ttifl left join `tiki_tracker_fields` ttf on (ttf.`fieldId`=ttifl.`fieldId`) where '.implode(' and ', $mid);
		$all = $this->fetchAll($query, $bindvars, -1, 0);
		foreach ($all as $f) {
			if (!empty($item_categs) && $f['type'] == 'e') {//category
				$f['options_array'] = explode(',',$f['options']);
				$all_descends = (isset($f['options_array'][3]) && $f['options_array'][3] == 1);
				$field_categs = $categlib->get_child_categories($f['options_array'][0], $all_descends);
				$aux = array();
				foreach ($field_categs as $cat) {
					$aux[] = $cat['categId'];
				}
				$field_categs = $aux;
				$f['value'] = implode(',', array_intersect($field_categs, $item_categs)); 
			}
			$last[$f['fieldId'].$f['lang']] = $f['value'];	
		}
		
		$last[-1] = $item_info['status']; 
		$mid[] = 'ta.`objectType`=?';
		$bindvars[] = 'trackeritem';
		if (!empty($filter)) {
			foreach ($filter as $key=>$f) {
		 		switch($key) {
					case 'version':
						$mid[] = 'ttifl.`version`=?';
						$bindvars[] = $f;
				}  		
			}
		}
		$query = 'select * from `tiki_tracker_item_field_logs` ttifl left join `tiki_actionlog` ta on (ta.`comment`=ttifl.`version` and ta.`object`=ttifl.`itemId`) where '.implode(' and ', $mid).' order by ttifl.`itemId` asc, ttifl.`version` desc, ttifl.`fieldId` asc';
		$all = $this->fetchAll($query, $bindvars, -1, 0);
		$history['cant'] = count($all);
		$history['data'] = array();
		$i = 0;
		foreach ($all as $hist) {
			if ($i >= $offset && ($max == -1 || $i < $offset + $max)) {
				$hist['new'] = isset($last[$hist['fieldId'].$hist['lang']])? $last[$hist['fieldId'].$hist['lang']]: '';
				$history['data'][] = $hist;
			}
			$last[$hist['fieldId'].$hist['lang']] = $hist['value'];
			++$i;
		}
		return $history;	
	}
	function move_item($trackerId, $itemId, $newTrackerId) {
		global $tikilib;
		$now = $tikilib->now;
		$newFields = $this->list_tracker_fields($newTrackerId, 0, -1, 'name_asc');
		foreach ($newFields['data'] as $field) {
			$translation[$field['name']] = $field;
		}
		$query = 'update `tiki_tracker_items` set `trackerId`=? where `itemId`=?';
		$this->query($query, array($newTrackerId, $itemId));
		$query = 'update `tiki_trackers` set `items`=`items`-1, `lastModif`=? where `trackerId`=?';
		$this->query($query, array($now, $trackerId));
		$query = 'update `tiki_trackers` set `items`=`items`+1, `lastModif`=? where `trackerId`=?';
		$this->query($query, array($now, $newTrackerId));
		$newFields = $this->list_tracker_fields($newTrackerId, 0, -1, 'name_asc');
		$query = 'select ttif.*, ttf.`name`, ttf.`type`, ttf.`options` from `tiki_tracker_item_fields` ttif, `tiki_tracker_fields` ttf where ttif.itemId=? and ttif.`fieldId`=ttf.`fieldId`';
		$fields = $this->fetchAll($query, array($itemId));
		$delete = 'delete from `tiki_tracker_item_fields` where `itemId`=? and `fieldId`=?';
		$move = 'update `tiki_tracker_item_fields` set `fieldId`=? where `itemId`=? and `fieldId`=?';
		foreach ($fields as $field) {
			if (empty($translation[$field['name']]) || $field['type'] != $translation[$field['name']]['type'] || $field['options'] != $translation[$field['name']]['options']) { // delete the field
				$this->query($delete, array($field['itemId'], $field['fieldId']));
			} else { // transfer
				$this->query($move, array($translation[$field['name']]['fieldId'], $field['itemId'], $field['fieldId']));
			}
		}
	}
	/* copy the fields of one item ($from) to another one ($to) of the same tracker - except/only for some fields */
	/* note: can not use the generic function as they return not all the multilingual fields */
	function copy_item($from, $to, $except=null, $only=null) {
		global $user, $prefs;
		$query = 'select * from `tiki_tracker_items` where `itemId`=?';
		$result = $this->query($query, array($from));
		$res = $result->fetchRow();
		$trackerId = $res['trackerId'];
		$query = 'select ttif.*, ttf.`type`, ttf.`options` from `tiki_tracker_item_fields` ttif left join `tiki_tracker_fields` ttf on (ttif.`fieldId` = ttf.`fieldId`) where `itemId`=?';
		$result = $this->query($query, array($from));
		if ($prefs['feature_categories'] == 'y') {
			global $categlib; include_once('lib/categories/categlib.php');
			$cats = $categlib->get_object_categories('trackeritem', $from);
		}
		$clean = array();
		while ($res = $result->fetchRow()) {
			$res['options_array'] = preg_split('/\s*,\s*/', $res['options']);
			if ($prefs['feature_categories'] == 'y' && $res['type'] == 'e') {//category
				if ((!empty($except) && in_array($res['fieldId'], $except))
					|| (!empty($only) && !in_array($res['fieldId'], $only))) {// take away the categories from $cats
					$childs = $categlib->get_child_categories($res['options_array'][0]);
					$local = array();
					foreach ($childs as $child) {
						$local[] = $child['categId'];
					}
					$cats = array_diff($cats, $local);
				}
			}
			
			if ((!empty($except) && in_array($res['fieldId'], $except))
				|| (!empty($only) && !in_array($res['fieldId'], $only))
				|| ($res['type'] == 'u' && $res['options_array'][0] == 1)
				|| ($res['type'] == 'g' && $res['options_array'][0] == 1)
				|| ($res['type'] == 'I' && $res['options_array'][0] == 1)
				|| ($res['type'] == 'q')
				) {
				continue;
			}
			if ($res['type'] == 'A' || $res['type'] == 'N') {// attachment - image
				continue; //not done yet
			}
			//echo "duplic".$res['fieldId'].' '. $res['value'].'<br>';
			if (!in_array($res['fieldId'], $clean)) {
				$this->query('delete from `tiki_tracker_item_fields` where `itemId`=? and `fieldId`=?', array($to, $res['fieldId']));
				$clean[] = $res['fieldId'];
			}
			if (empty($res['lang'])) {
				$this->query('insert into `tiki_tracker_item_fields` (`itemId`,`fieldId`,`value`) values(?,?,?)', array($to, $res['fieldId'], $res['value']));
			} else {
				$this->query('insert into `tiki_tracker_item_fields` (`itemId`,`fieldId`,`value`, `lang`) values(?,?,?,?)', array($to, $res['fieldId'], $res['value'], $res['lang']));
			}
		}
		if (!empty($cats)) {
			$this->categorized_item($trackerId, $to, "item $to", $cats);
		}
	}
	function export_attachment($itemId, $archive) {
		global $prefs;
		$files = $this->list_item_attachments( $itemId, 0, -1, 'attId_asc' );
		foreach( $files['data'] as $file ) {
			$localZip = "item_$itemId/".$file['filename'];
			$complete = $this->get_item_attachment( $file['attId'] );
			if (!empty($complete['path']) && file_exists($prefs['t_use_dir'].$complete['path'])) {
				if (!$archive->addFile($prefs['t_use_dir'].$complete['path'], $localZip))
					return false;
			} elseif (!empty($complete['data'])) {
				if (!$archive->addFromString($localZip, $complete['data']))
					return false;
			}
		}
		return true;
	}
	/* fill a calendar structure with items
	 * fieldIds contains one date or 2 dates
	 */
	function fillTableViewCell($items, $fieldIds, &$cell) {
		global $smarty;
		if (empty($items)) {
			return;
		}
		foreach ($items[0]['field_values'] as $i => $field) {
			if ($field['fieldId'] == $fieldIds[0]) {
				$iStart = $i;
			} elseif (count($fieldIds) > 1 && $field['fieldId'] == $fieldIds[1]) {
				$iEnd = $i;
			}
		}
		foreach ($cell as $i => $line) {
			foreach ($line as $j => $day) {
				if (!$day['focus']) {
					continue;
				}
				$overs = array();
				foreach ($items as $item) {
					$endDay = TikiLib::make_time(23,59,59, $day['month'], $day['day'], $day['year']);
					if ((count($fieldIds) == 1 && $item['field_values'][$iStart]['value'] >= $day['date'] && $item['field_values'][$iStart]['value'] <= $endDay)
						|| (count($fieldIds) > 1 && $item['field_values'][$iStart]['value'] <= $endDay && $item['field_values'][$iEnd]['value'] >= $day['date'])) {
							$cell[$i][$j]['items'][] = $item;
							$overs[] = preg_replace('|(<br /> *)*$|m', '', $item['over']);
					}
				}
				if (!empty($overs)) {
					$smarty->assign_by_ref('overs', $overs);
					$cell[$i][$j]['over'] = $smarty->fetch('tracker_calendar_over.tpl');
				}
			}
		}
		//echo '<pre>'; print_r($cell); echo '</pre>';
	}
	function get_tracker_by_name($name) {
		return $this->getOne('select `trackerId` from `tiki_trackers` where `name`=?', array($name));
	}

}

global $trklib;
$trklib = new TrackerLib;
