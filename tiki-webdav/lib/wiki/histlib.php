<?php
// (c) Copyright 2002-2010 by authors of the Tiki Wiki/CMS/Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}

class HistLib extends TikiLib
{

	/* 
		*	Removes a specific version of a page
		*
		*/
	function remove_version($page, $version, $historyId = '') {
		global $prefs;
		if ($prefs['feature_contribution'] == 'y') {
			global $contributionlib; include_once('lib/contribution/contributionlib.php');
			if ($historyId == '') {
				$query = 'select `historyId` from `tiki_history` where `pageName`=? and `version`=?';
				$historyId = $this->getOne($query, array($page, $version));
			}
			$contributionlib->remove_history($historyId);
		}
		$query = "delete from `tiki_history` where `pageName`=? and `version`=?";
		$result = $this->query($query,array($page,$version));
		global $logslib; include_once('lib/logs/logslib.php');
		$logslib->add_action("Removed version", $page, 'wiki page', "version=$version");
		//get_strings tra("Removed version $version")
		return true;
	}

	function use_version($page, $version, $comment = '') {
		$this->invalidate_cache($page);
		
		// Store the current page in tiki_history before rolling back
		if (strtolower($page) != 'sandbox') {
			$info = $this->get_page_info($page);
			$old_version = $this->get_page_latest_version($page) + 1;
		    $lastModif = $info["lastModif"];
		    $user = $info["user"];
		    $ip = $info["ip"];
		    $comment = $info["comment"];
		    $data = $info["data"];
		    $description = $info["description"];
			$query = "insert into `tiki_history`(`pageName`, `version`, `version_minor`, `lastModif`, `user`, `ip`, `comment`, `data`, `description`,`is_html`) values(?,?,?,?,?,?,?,?,?,?)";
		    $this->query($query,array($page,(int) $old_version, (int) $info["version_minor"],(int) $lastModif,$user,$ip,$comment,$data,$description, $info["is_html"]));
		}
		
		$query = "select * from `tiki_history` where `pageName`=? and `version`=?";
		$result = $this->query($query,array($page,$version));

		if (!$result->numRows())
			return false;

		$res = $result->fetchRow();
		
		global $prefs;
		if ($prefs['feature_wikiapproval'] == 'y') {
			// for approval and staging feature to work properly, one has to use real commit time of rollbacks
			//TODO: make this feature to set rollback time as current time as more general optional feature
			$res["lastModif"] = time();
		}
		// add rollback comment to existing one (after truncating if needed)
		$ver_comment = " [" . tra("rollback version ") . $version . "]";
		$too_long = 200 - strlen($res["comment"] . $ver_comment);
		if ($too_long < 0) {
			$too_long -= 4;
			$res["comment"] = substr($res["comment"], 0, $too_long) . '...';
		}
		$res["comment"] = $res["comment"] . $ver_comment; 		
		
		$query = "update `tiki_pages` set `data`=?,`lastModif`=?,`user`=?,`comment`=?,`version`=`version`+1,`ip`=?, `description`=?, `is_html`=?";
		$bindvars = array($res['data'], $res['lastModif'], $res['user'], $res['comment'], $res['ip'], $res['description'], $res['is_html']);
		
		// handle rolling back once page has been edited in a different editor (wiki or wysiwyg) based on is_html in history
		if ($prefs['feature_wysiwyg'] == 'y' && $prefs['wysiwyg_optional'] == 'y' && $prefs['wysiwyg_memo'] == 'y') {
			if ($res['is_html'] == 1) {
				$bindvars[] = 'y';
			} else {
				$bindvars[] = 'n';
			}
			$query .= ', `wysiwyg`=?';
		}
		$query .= ' where `pageName`=?';
		$bindvars[] = $page;
		$result = $this->query($query, $bindvars);
		$query = "delete from `tiki_links` where `fromPage` = ?";
		$result = $this->query($query,array($page));
		$this->clear_links($page);
		$pages = $this->get_pages($res["data"]);

		foreach ($pages as $a_page) {
			$this->replace_link($page, $a_page);
		}

		global $prefs;
		if ($prefs['feature_actionlog'] == 'y') {
			global $logslib; include_once('lib/logs/logslib.php');
			$logslib->add_action("Rollback", $page, 'wiki page', "version=$version");
		}
		//get_strings tra("Changed actual version to $version");
		return true;
	}

	function get_user_versions($user) {
		$query
			= "select `pageName`,`version`, `lastModif`, `user`, `ip`, `comment` from `tiki_history` where `user`=? order by `lastModif` desc";

		$result = $this->query($query,array($user));
		$ret = array();

		while ($res = $result->fetchRow()) {
			$aux = array();

			$aux["pageName"] = $res["pageName"];
			$aux["version"] = $res["version"];
			$aux["lastModif"] = $res["lastModif"];
			$aux["ip"] = $res["ip"];
			$aux["comment"] = $res["comment"];
			$ret[] = $aux;
		}

		return $ret;
	}

	// Returns information about a specific version of a page
	function get_version($page, $version) {

		$query = "select * from `tiki_history` where `pageName`=? and `version`=?";
		$result = $this->query($query,array($page,$version));
		$res = $result->fetchRow();
		return $res;
	}

	// Returns all the versions for this page
	// without the data itself
	function get_page_history($page, $fetchdata=true, $offset = 0, $limit = -1) {
		global $prefs;

		$query = "select * from `tiki_history` where `pageName`=? order by `version` desc";
		$result = $this->query($query,array($page), $limit, $offset);
		$ret = array();

		while ($res = $result->fetchRow()) {
			$aux = array();

			$aux["version"] = $res["version"];
			$aux["lastModif"] = $res["lastModif"];
			$aux["user"] = $res["user"];
			$aux["ip"] = $res["ip"];
			if ($fetchdata==true) $aux["data"] = $res["data"];
			$aux["pageName"] = $res["pageName"];
			$aux["description"] = $res["description"];
			$aux["comment"] = $res["comment"];
			//$aux["percent"] = levenshtein($res["data"],$actual);
			if ($prefs['feature_contribution'] == 'y') {
				global $contributionlib; include_once('lib/contribution/contributionlib.php');
				$aux['contributions'] = $contributionlib->get_assigned_contributions($res['historyId'], 'history');
				global $logslib; include_once('lib/logs/logslib.php');
				$aux['contributors'] = $logslib->get_wiki_contributors($aux);
			}
			$ret[] = $aux;
		}

		return $ret;
	}

	// Returns one version of the page from the history
	// without the data itself (version = 0 now returns data from current version)
	function get_page_from_history($page,$version,$fetchdata=false) {

		if ($fetchdata==true) {
			if ($version > 0)
				$query = "select `pageName`, `description`, `version`, `lastModif`, `user`, `ip`, `data`, `comment`, `is_html` from `tiki_history` where `pageName`=? and `version`=?";				
			else
				$query = "select `pageName`, `description`, `version`, `lastModif`, `user`, `ip`, `data`, `comment`, `is_html` from `tiki_pages` where `pageName`=?";
		} else {
			if ($version > 0)
				$query = "select `pageName`, `description`, `version`, `lastModif`, `user`, `ip`, `comment`, `is_html` from `tiki_history` where `pageName`=? and `version`=?";
			else
				$query = "select `pageName`, `description`, `version`, `lastModif`, `user`, `ip`, `comment`, `is_html` from `tiki_pages` where `pageName`=?";
		}
		if ($version > 0)
			$result = $this->query($query,array($page,$version));
		else
			$result = $this->query($query,array($page));
			
		$ret = array();

		while ($res = $result->fetchRow()) {
			$aux = array();

			$aux["version"] = $res["version"];
			$aux["lastModif"] = $res["lastModif"];
			$aux["user"] = $res["user"];
			$aux["ip"] = $res["ip"];
			if ($fetchdata==true) $aux["data"] = $res["data"];
			$aux["pageName"] = $res["pageName"];
			$aux["description"] = $res["description"];
			$aux["comment"] = $res["comment"];
			$aux["is_html"] = $res["is_html"];
			//$aux["percent"] = levenshtein($res["data"],$actual);
			$ret[] = $aux;
		}

		return empty($ret)?$ret: $ret[0];
	}
	
	// note that this function returns the latest version in the
	// history db table, which is one less than the current version 
	function get_page_latest_version($page, $sort_mode='version_desc') {

		$query = "select `version` from `tiki_history` where `pageName`=? order by ".$this->convertSortMode($sort_mode);
		$result = $this->query($query,array($page),1);
		$ret = array();
		
		if ($res = $result->fetchRow()) {
			$ret = $res['version'];
		} else {
			$ret = FALSE;
		}

		return $ret;
	}

	function version_exists($pageName, $version) {

		$query = "select `pageName` from `tiki_history` where `pageName` = ? and `version`=?";
		$result = $this->query($query,array($pageName,$version));
		return $result->numRows();
	}

	// This function get the last changes from pages from the last $days days
	// if days is 0 this gets all the registers
	// function parameters modified by ramiro_v on 11/03/2002
	function get_last_changes($days, $offset = 0, $limit = -1, $sort_mode = 'lastModif_desc', $findwhat = '') {
	        global $user;

		$bindvars = array();
		$categories = $this->get_jail();
		if (!isset($categjoin)) $categjoin = '';
		if ($categories) {
			$categjoin .= "inner join `tiki_objects` as tob on (tob.`itemId`= ta.`object` and tob.`type`= ?) inner join `tiki_category_objects` as tc on (tc.`catObjectId`=tob.`objectId` and tc.`categId` IN(" . implode(', ', array_fill(0, count($categories), '?')) . ")) ";
			$bindvars = array_merge(array('wiki page'), $categories);
		}

		$where = "where true ";
		if ($findwhat) {
			$findstr='%' . $findwhat . '%';
			$where.= " and ta.`object` like ? or ta.`user` like ? or ta.`comment` like ?";
			$bindvars = array_merge($bindvars, array($findstr,$findstr,$findstr));
		}

		if ($days) {
			$toTime = $this->make_time(23, 59, 59, $this->date_format("%m"), $this->date_format("%d"), $this->date_format("%Y"));
			$fromTime = $toTime - (24 * 60 * 60 * $days);
			$where .= " and ta.`lastModif`>=? and ta.`lastModif`<=? ";
			$bindvars[] = $fromTime;
			$bindvars[] = $toTime;
		}

		$query = "select distinct ta.`action`, ta.`lastModif`, ta.`user`, ta.`ip`, ta.`object`, thf.`comment`, thf.`version`, thf.`versionlast` from `tiki_actionlog` ta 
			inner join (select NULL as version, `comment`, `pageName`, `lastModif`, '1' as versionlast from tiki_pages union select `version`, `comment`, `pageName`, `lastModif`, '0' as versionlast from `tiki_history`) as thf on ta.`object`=thf.`pageName` and ta.`lastModif`=thf.`lastModif` and ta.`objectType`='wiki page' " . $categjoin . $where . " order by ta.".$this->convertSortMode($sort_mode);

		$query_cant = "select count(distinct ta.`action`, ta.`lastModif`, ta.`user`, ta.`object`, thf.`versionlast`) from `tiki_actionlog` ta 
			inner join (select `pageName`, `lastModif`, '1' as versionlast from tiki_pages union select `pageName`, `lastModif`, '0' as versionlast from `tiki_history`) as thf on ta.`object`=thf.`pageName` and ta.`lastModif`=thf.`lastModif` and ta.`objectType`='wiki page' " . $categjoin . $where;

		$result = $this->fetchAll($query,$bindvars,$limit,$offset);
		$result = Perms::filter( array( 'type' => 'wiki page' ), 'object', $result, array( 'object' => 'object' ), 'view' );
		$cant = $this->getOne($query_cant,$bindvars);
		$ret = array();
		$retval = array();
		foreach( $result as $res ) {
			$res['pageName'] = $res['object'];
			$ret[] = $res;
		}
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}
	function get_nb_history($page) {
		$query_cant = "select count(*) from `tiki_history` where `pageName` = ?";
		$cant = $this->getOne($query_cant, array($page));
		return $cant;
	}
	
	// This function gets the version number of the version before or after the time specified
	// (note that current version is not included in search)
	function get_version_by_time($page, $unixtimestamp, $before_or_after = 'before', $include_minor = true) {
		$query = "select `version`, `version_minor`, `lastModif` from `tiki_history` where `pageName`=? order by `version` desc";
		$result = $this->query($query,array($page));
		$ret = array();
		$version = 0;
		while ($res = $result->fetchRow()) {
			$aux = array();
			$aux["version"] = $res["version"];
			$aux["version_minor"] = $res["version_minor"];
			$aux["lastModif"] = $res["lastModif"];
			$ret[] = $aux;
		}
		foreach ($ret as $ver) {
			if ($ver["lastModif"] <= $unixtimestamp && ($include_minor || $ver["version_minor"] == 0)) {
				if ($before_or_after == 'before') { 
					$version = (int) $ver["version"];
					break;
				} elseif ($before_or_after == 'after') {
					break;
				}
			}
			if ($before_or_after == 'after' && ($include_minor || $ver["version_minor"] == 0)) {
				$version = (int) $ver["version"];				
			}		
		}
		return max(0, $version);		
	}
}

$histlib = new HistLib;

function histlib_helper_setup_diff( $page, $oldver, $newver )
{
	global $smarty, $histlib, $tikilib, $prefs;
	$prefs['wiki_edit_section'] = 'n';
	
	$info = $tikilib->get_page_info( $page );

	if ($oldver == 0 || $oldver == $info["version"]) {
		$old = & $info;
		$smarty->assign_by_ref('old', $info);
	} else {
		// fetch the required page from history, including its content
		while( $oldver > 0 && ! ($exists = $histlib->version_exists($page, $oldver) ) )
			--$oldver;

		if ( $exists ) {
			$old = $histlib->get_page_from_history($page,$oldver,true);
			$smarty->assign_by_ref('old', $old);
		}
	}
	if ($newver == 0 || $newver >= $info["version"]) {
		$new =& $info;
		$smarty->assign_by_ref('new', $info);
	} else {
		// fetch the required page from history, including its content
		while( $newver > 0 && ! ($exists = $histlib->version_exists($page, $newver) ) )
			--$newver;

		if ( $exists ) {
			$new = $histlib->get_page_from_history($page,$newver,true);
			$smarty->assign_by_ref('new', $new);
		}
	}

	$oldver_mod = $oldver;
	if ($oldver == 0) {
		$oldver_mod = 1;
	}

	$query = "SELECT `comment`, `version` from `tiki_history` WHERE `pageName`=? and `version` BETWEEN ? AND ? ORDER BY `version` DESC";
	$result = $histlib->query($query,array($page,$oldver_mod,$newver));
	$diff_summaries = array();

	if ($oldver == 0) {
		$diff_summaries[] = $old['comment'];
	}

	while ($res = $result->fetchRow()) {
		$aux = array();

		$aux["comment"] = $res["comment"];
		$aux["version"] = $res["version"];
		$diff_summaries[] = $aux;
	}

	$smarty->assign('diff_summaries', $diff_summaries);
	
	if (!isset($_REQUEST["diff_style"]) || $_REQUEST["diff_style"] == "old") {
		$_REQUEST["diff_style"] = 'unidiff';
	}

	$smarty->assign('diff_style', $_REQUEST["diff_style"]);
	if ($_REQUEST["diff_style"] == "sideview") {
		$old["data"] = $tikilib->parse_data($old["data"], array('preview_mode' => true));
		$new["data"] = $tikilib->parse_data($new["data"], array('preview_mode' => true));
	} else {
		require_once('lib/diff/difflib.php');
		if ($info['is_html'] == 1 and $_REQUEST["diff_style"] != "htmldiff") {
			$search[] = "~</(table|td|th|div|p)>~";
			$replace[] = "\n";
			$search[] = "~<(hr|br) />~";
			$replace[] = "\n";
			$old['data'] = strip_tags(preg_replace($search,$replace,$old['data']),'<h1><h2><h3><h4><b><i><u><span>');
			$new['data'] = strip_tags(preg_replace($search,$replace,$new['data']),'<h1><h2><h3><h4><b><i><u><span>');
		}
		if ($_REQUEST["diff_style"] == "htmldiff") {
			$oldp = $prefs['wiki_edit_plugin'];
			$olds = $prefs['wiki_edit_section'];

			$prefs['wiki_edit_plugin'] = 'n';
			$prefs['wiki_edit_section'] = 'n';
			$parse_options = array('is_html' => ($old['is_html'] == 1), 'noheadinc' => true, 'preview_mode' => true);
			$old["data"] = $tikilib->parse_data($old["data"], $parse_options);

			$parse_options = array('is_html' => ($new['is_html'] == 1), 'noheadinc' => true);
			$new["data"] = $tikilib->parse_data($new["data"], $parse_options);

			$prefs['wiki_edit_plugin'] = $oldp;
			$prefs['wiki_edit_section'] = $olds;

			$old['data'] = histlib_strip_irrelevant( $old['data'] );
			$new['data'] = histlib_strip_irrelevant( $new['data'] );
		}
		$html = diff2($old["data"], $new["data"], $_REQUEST["diff_style"]);
		$smarty->assign_by_ref('diffdata', $html);
	}
}

function histlib_strip_irrelevant( $data )
{
	$data = preg_replace( "/<(h1|h2|h3|h4|h5|h6|h7)\s+([^\\\\>]+)>/i", '<$1>', $data );
	return $data;
}

function rollback_page_to_version($page, $version, $check_key = true, $keep_lastModif = false) {
	global $prefs, $histlib, $tikilib, $categlib;
	$area = 'delrollbackpage';
	if (!$check_key or $prefs['feature_ticketlib2'] != 'y' or (isset($_POST['daconfirm']) and isset($_SESSION["ticket_$area"]))) {
		if ($check_key) key_check($area);
		$histlib->use_version($page, $version, '', $keep_lastModif);
		
		if ( ($approved = $tikilib->get_approved_page($page)) && $prefs['wikiapproval_outofsync_category'] > 0) {
			
			$approved_page = $histlib->get_page_from_history($approved, 0, true);
			$staging_page = $histlib->get_page_from_history($page, $version, true);
			$cat_type='wiki page';	
			$staging_cats = $categlib->get_object_categories($cat_type, $page);
			$s_cat_desc = ($prefs['feature_wiki_description'] == 'y') ? substr($staging_info["description"],0,200) : '';
			$s_cat_objid = $page;
			$s_cat_name = $page;
			$s_cat_href="tiki-index.php?page=".urlencode($s_cat_objid);
			
			//Instead of firing up diff, just check if the pages share the same exact data, drop the staging
			//copy out of the review category if so
			if ( $approved_page["data"] != $staging_page["data"] ) //compare these only once
			$pages_diff = true;
			if ( in_array($prefs['wikiapproval_outofsync_category'], $staging_cats) )
			$in_staging_cat = true;
	
			if ( !$pages_diff && $in_staging_cat ) {
				$staging_cats = array_diff($staging_cats,Array($prefs['wikiapproval_outofsync_category']));
				$categlib->update_object_categories($staging_cats, $s_cat_objid, $cat_type, $s_cat_desc, $s_cat_name, $s_cat_href);	
			} elseif ( $pages_diff && !$in_staging_cat ) {
				$staging_cats[] = $prefs['wikiapproval_outofsync_category'];
				$categlib->update_object_categories($staging_cats, $s_cat_objid, $cat_type, $s_cat_desc, $s_cat_name, $s_cat_href);	
			}
		}
	} else {
		key_get($area);
	}
	
	$tikilib->invalidate_cache( $page );
}