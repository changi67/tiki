<?php
/** \file
 * $Header: /cvsroot/tikiwiki/tiki/lib/categories/categlib.php,v 1.63 2004-10-25 12:20:28 chealer Exp $
 *
 * \brief Categories support class
 *
 */

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}

class CategLib extends TikiLib {

	function CategLib($db) {
		# this is probably unneeded now
		if (!$db) {
			die ("Invalid db object passed to CategLib constructor");
		}

		$this->db = $db;
	}

	function list_categs() {
		global $cachelib;
		if (!$cachelib->isCached("allcategs")) {
			$query = "select * from `tiki_categories`";
			$result = $this->query($query,array());
			$ret = array();
			while ($res = $result->fetchRow()) {
				$catpath = $this->get_category_path($res["categId"]);
				$tepath = array();	
				foreach ($catpath as $cat) {
					$tepath[] = $cat['name'];
				}
				$categpath = implode("::",$tepath);
				$res["categpath"] = $categpath;
				$res["tepath"] = $tepath;
				$ret["$categpath"] = $res;
			}
			ksort($ret);
			$back = array_values($ret);
			$cachelib->cacheItem("allcategs",serialize($back));
			return $back;
		} else {
			return unserialize($cachelib->getCached("allcategs"));
		}
	}
	
	function get_category_path($categId) {
		$info = $this->get_category($categId);
		$i=999999;
		$path[$i--] = array('categId'=>$info["categId"],'name'=>$info["name"]);
		while ($info["parentId"] != 0) {
			$info = $this->get_category($info["parentId"]);
			$path[$i--] = array('categId'=>$info["categId"],'name'=>$info["name"]);
		}
		ksort($path);
		return array_values($path);
	}

	function get_category($categId) {
		$query = "select * from `tiki_categories` where `categId`=?";
		$result = $this->query($query,array((int) $categId));
		if (!$result->numRows()) return false;
		$res = $result->fetchRow();
		return $res;
	}
	
	function get_category_name($categId) {
		$query = "select `name` from `tiki_categories` where `categId`=?";
		return $this->getOne($query,array((int) $categId));
	}
	
	function remove_category($categId) {
		global $cachelib;

		$query = "delete from `tiki_categories` where `categId`=?";
		$result = $this->query($query,array((int) $categId));
		$query = "select `catObjectId` from `tiki_category_objects` where `categId`=?";
		$result = $this->query($query,array((int) $categId));

		while ($res = $result->fetchRow()) {
			$object = $res["catObjectId"];

			$query_cant = "select count(*) from `tiki_category_objects` where `catObjectId`=?";
			$cant = $this->getOne($query_cant,array($object));
			if ($cant <= 1) {
			$query2 = "delete from `tiki_categorized_objects` where `catObjectId`=?";
			$result2 = $this->query($query2,array($object));
			}
		}
		
		// remove any permissions assigned to this category
		$type = 'category';
		$object = $type . $categId;
		$query = "delete from `users_objectpermissions` where `objectId`=? and `objectType`=?";
		$result = $this->query($query,array(md5($object),$type));

		$query = "delete from `tiki_category_objects` where `categId`=?";
		$result = $this->query($query,array((int) $categId));
		$query = "select `categId` from `tiki_categories` where `parentId`=?";
		$result = $this->query($query,array((int) $categId));

		while ($res = $result->fetchRow()) {
			// Recursively remove the subcategory
			$this->remove_category($res["categId"]);
		}
		$cachelib->invalidate('allcategs');
		return true;
	}

	function update_category($categId, $name, $description, $parentId) {
		global $cachelib;
		$query = "update `tiki_categories` set `name`=?, `parentId`=?, `description`=? where `categId`=?";
		$result = $this->query($query,array($name,(int) $parentId,$description,(int) $categId));
		$cachelib->invalidate('allcategs');
	}

	function add_category($parentId, $name, $description) {
		global $cachelib;
		$query = "insert into `tiki_categories`(`name`,`description`,`parentId`,`hits`) values(?,?,?,?)";
		$result = $this->query($query,array($name,$description,(int) $parentId,0));
		$query = "select `categId` from `tiki_categories` where `name`=? and `parentId`=?";
		$id = $this->getOne($query,array($name,(int) $parentId));
		$cachelib->invalidate('allcategs');
		return $id;
	}

	function is_categorized($type, $objId) {
		$query = "select `catObjectId` from `tiki_categorized_objects` where `type`=? and `objId`=?";
		$bindvars=array($type,$objId);
		settype($bindvars["1"],"string");
		$result = $this->query($query,$bindvars);
		if ($result->numRows()) {
			$res = $result->fetchRow();
			return $res["catObjectId"];
		} else {
			return 0;
		}
	}

	function add_categorized_object($type, $objId, $description, $name, $href) {
		global $cachelib;

		$description = strip_tags($description);
		$name = strip_tags($name);
		$now = date("U");
		$query = "insert into `tiki_categorized_objects`(`type`,`objId`,`description`,`name`,`href`,`created`,`hits`)
    values(?,?,?,?,?,?,?)";
		$result = $this->query($query,array($type,(string) $objId,$description,$name,$href,(int) $now,0));
		$query = "select `catObjectId` from `tiki_categorized_objects` where `created`=? and `type`=? and `objId`=?";
		$id = $this->getOne($query,array((int) $now,$type,(string) $objId));
		$cachelib->invalidate('allcategs');
		return $id;
	}

	function categorize($catObjectId, $categId) {
		$query = "delete from `tiki_category_objects` where `catObjectId`=? and `categId`=?";
		$result = $this->query($query,array((int) $catObjectId,(int) $categId),-1,-1,false);
	        
		$query = "insert into `tiki_category_objects`(`catObjectId`,`categId`) values(?,?)";
		$result = $this->query($query,array((int) $catObjectId,(int) $categId));
	}

	function get_category_descendants($categId) {
		$query = "select `categId` from `tiki_categories` where `parentId`=?";

		$result = $this->query($query,array((int) $categId));
		$ret = array($categId);

		while ($res = $result->fetchRow()) {
			$ret[] = $res["categId"];

			$aux = $this->get_category_descendants($res["categId"]);
			$ret = array_merge($ret, $aux);
		}

		$ret = array_unique($ret);
		return $ret;
	}

	function list_category_objects_deep($categId, $offset, $maxRecords, $sort_mode = 'pageName_asc', $find) {

		$des = $this->get_category_descendants($categId);
		if (count($des)>0) {
			$cond = "and tbl1.`categId` in (".str_repeat("?,",count($des)-1)."?)";
		} else {
			$cond = "";
		}

		if ($find) {
			$findesc = '%' . $find . '%';
			$des[]=$findesc;
			$des[]=$findesc;
			$mid = " and (`name` like ? or `description` like ?)";
		} else {
			$mid = "";
		}

		$query = "select * from `tiki_category_objects` tbl1,`tiki_categorized_objects` tbl2 where tbl1.`catObjectId`=tbl2.`catObjectId` $cond $mid order by ".$this->convert_sortmode($sort_mode);
		$query_cant = "select distinct tbl1.`catObjectId` from `tiki_category_objects` tbl1,`tiki_categorized_objects` tbl2 where tbl1.`catObjectId`=tbl2.`catObjectId` $cond $mid";
		$result = $this->query($query,$des,$maxRecords,$offset);
		$result2 = $this->query($query_cant,$des);
		$cant = $result2->numRows();
		$cant2
			= $this->getOne("select count(*) from `tiki_category_objects` tbl1,`tiki_categorized_objects` tbl2 where tbl1.`catObjectId`=tbl2.`catObjectId` $cond $mid",$des);
		$ret = array();
		$objs = array();

		while ($res = $result->fetchRow()) {
			if (!in_array($res["catObjectId"], $objs)) {
				$ret[] = $res;

				$objs[] = $res["catObjectId"];
			}
		}

		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		$retval["cant2"] = $cant2;
		return $retval;
	}

	function list_category_objects($categId, $offset, $maxRecords, $sort_mode = 'pageName_asc', $find) {
		if ($find) {
			$findesc = '%' . $find . '%';
			$bindvars=array((int) $categId,$findesc,$findesc);
			$mid = " and (tbl2.`name` like ? or tbl2.`description` like ?)";
		} else {
			$mid = "";
			$bindvars=array((int) $categId);
		}

		$query = "select tbl1.`catObjectId`,`categId`,`type`,`objId`,`description`, `created`,`name`,`href`,`hits`
			from `tiki_category_objects` tbl1,`tiki_categorized_objects` tbl2 where tbl1.`catObjectId`=tbl2.`catObjectId` and tbl1.`categId`=? $mid order by tbl2.".$this->convert_sortmode($sort_mode);
		$query_cant = "select distinct tbl1.`catObjectId` from `tiki_category_objects` tbl1,`tiki_categorized_objects` tbl2 where tbl1.`catObjectId`=tbl2.`catObjectId` and tbl1.`categId`=? $mid";
		$result = $this->query($query,$bindvars,$maxRecords,$offset);
		$result2 = $this->query($query_cant,$bindvars);
		$cant = $result2->numRows();
		$cant2 = $this->getOne("select count(*) from `tiki_category_objects` tbl1,`tiki_categorized_objects` tbl2 where tbl1.`catObjectId`=tbl2.`catObjectId` and tbl1.`categId`=? $mid",$bindvars);
		$ret = array();

		while ($res = $result->fetchRow()) {
			$ret[] = $res;
		}

		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		$retval["cant2"] = $cant2;
		return $retval;
	}

	// get the parent categories of an object
	function get_object_categories($type, $objId) {

		$query = "select `categId` from `tiki_category_objects` tco, `tiki_categorized_objects` tto
    where tco.`catObjectId`=tto.`catObjectId` and `type`=? and `objId`=?";
		//settype($objId,"string"); //objId is defined as varchar
		$bindvars=array($type,$objId);
		settype($bindvars["1"],"string");
		$result = $this->query($query,$bindvars);
		$ret = array();

		while ($res = $result->fetchRow()) {
			$ret[] = $res["categId"];
		}

		return $ret;
	}
	
	// get the permissions assigned to the parent categories of an object
	function get_object_categories_perms($user, $type, $objId, $group = NULL) {	// the last parameter $group is optional, used when we specify a group but not a user
		$is_categorized = $this->is_categorized("$type",$objId);
		if ($is_categorized) {
			global $cachelib;
			global $userlib;
			global $tiki_p_admin;
			
			$parents = $this->get_object_categories("$type", $objId);
			$return_perms = array(); // initialize array for storing perms to be returned

			if (!$cachelib->isCached("categories_permission_names")) {
				$perms = $userlib->get_permissions(0, -1, 'permName_desc', 'categories');
				$cachelib->cacheItem("categories_permission_names",serialize($perms));
			} else {
				$perms = unserialize($cachelib->getCached("categories_permission_names"));
			}

			foreach ($perms["data"] as $perm) {
				$perm = $perm["permName"];
				if ($tiki_p_admin == 'y') {
					$return_perms["$perm"] = 'y';
				} else {
					foreach ($parents as $categId) {
						if ($userlib->object_has_one_permission($categId, 'category')) {
							if ($userlib->object_has_permission($user, $categId, 'category', $perm, $group)) {
								$return_perms["$perm"] = 'y';
							} else {
								$return_perms["$perm"] = 'n';
								// better-sorry-than-safe approach:
								// if a user lacks a given permission regarding a particular category,
								// that category takes precedence when considering if user has that permission
								break 1;
								// break out of one FOREACH loop
							}
						} else {
							$categpath = $this->get_category_path($categId);
							$arraysize = count($categpath);
							for ($i=$arraysize-2; $i>=0; $i--) {
								if ($userlib->object_has_one_permission($categpath[$i]['categId'], 'category')) {
									if ($userlib->object_has_permission($user, $categpath[$i]['categId'], 'category', $perm, $group)) {
										$return_perms["$perm"] = 'y';
								   		break 1;
									} else {
										$return_perms["$perm"] = 'n';
										// better-sorry-than-safe approach:
										// if a user lacks a given permission regarding a particular category,
										// that category takes precedence when considering if user has that permission
										break 2;
										// break out of one FOR loop and one FOREACH loop
									}
								}
							}
						}
					}
				}
			}
			return $return_perms;
		} else {
			return FALSE;
		}
		
	}

	function get_category_objects($categId) {
		// Get all the objects in a category
		$query = "select * from `tiki_category_objects` tbl1,`tiki_categorized_objects` tbl2 where tbl1.`catObjectId`=tbl2.`catObjectId` and `categId`=?";

		$result = $this->query($query,array((int) $categId));
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res;
		}

		return $ret;
	}

	function remove_object_from_category($catObjectId, $categId) {
		global $cachelib;
		$query = "delete from `tiki_category_objects` where `catObjectId`=? and `categId`=?";
		$result = $this->query($query,array($catObjectId,$categId));
		$query = "select count(*) from `tiki_category_objects` where `catObjectId`=?";
		$cant = $this->getOne($query,array((int) $catObjectId));
		if (!$cant) {
			$query = "delete from `tiki_categorized_objects` where `catObjectId`=?";
			$result = $this->query($query,array((int) $catObjectId));
		}
		$cachelib->invalidate('allcategs');
	}

	// FUNCTIONS TO CATEGORIZE SPECIFIC OBJECTS ////
	function categorize_page($pageName, $categId) {
		// Check if we already have this object in the tiki_categorized_objects page

		$catObjectId = $this->is_categorized('wiki page', $pageName);

		if (!$catObjectId) {
			// The page is not cateorized
			$info = $this->get_page_info($pageName);

			$href = 'tiki-index.php?page=' . urlencode($pageName);
			$catObjectId = $this->add_categorized_object('wiki page', $pageName, substr($info["description"], 0, 200), $pageName, $href);
		}

		$this->categorize($catObjectId, $categId);
	}
	
	function categorize_tracker($trackerId, $categId) {
		// Check if we already have this object in the tiki_categorized_objects page

		$catObjectId = $this->is_categorized('tracker', $trackerId);

		if (!$catObjectId) {
			global $trklib;
			if (!is_object($trklib)) {
				require_once('lib/trackers/trackerlib.php');
			}
			// The page is not cateorized
			$info = $trklib->get_tracker($trackerId);

			$href = 'tiki-view_tracker.php?trackerId=' . $trackerId;
			$catObjectId = $this->add_categorized_object('tracker', $trackerId, substr($info["description"], 0, 200),$info["name"] , $href);
		}

		$this->categorize($catObjectId, $categId);
	}

	function categorize_quiz($quizId, $categId) {
		// Check if we already have this object in the tiki_categorized_objects page
		$catObjectId = $this->is_categorized('quiz', $quizId);

		if (!$catObjectId) {
			// The page is not cateorized
			global $quizlib;
			if (!is_object($quizlib)) {
				require_once('lib/quizzes/quizlib.php');
			}
			$info = $quizlib->get_quiz($quizId);

			$href = 'tiki-take_quiz.php?quizId=' . $quizId;
			$catObjectId
				= $this->add_categorized_object('quiz', $quizId, substr($info["description"], 0, 200), $info["name"], $href);
		}

		$this->categorize($catObjectId, $categId);
	}

	function categorize_article($articleId, $categId) {
		// Check if we already have this object in the tiki_categorized_objects page
		$catObjectId = $this->is_categorized('article', $articleId);

		if (!$catObjectId) {
			// The page is not cateorized
			$info = $this->get_article($articleId);

			$href = 'tiki-read_article.php?articleId=' . $articleId;
			$catObjectId = $this->add_categorized_object('article', $articleId, $info["heading"], $info["title"], $href);
		}

		$this->categorize($catObjectId, $categId);
	}

	function categorize_faq($faqId, $categId) {
		// Check if we already have this object in the tiki_categorized_objects page
		$catObjectId = $this->is_categorized('faq', $faqId);

		if (!$catObjectId) {
			global $faqlib;
			if (!is_object($faqlib)) {
				include_once('lib/faqs/faqlib.php');
			}
			// The page is not cateorized
			$info = $faqlib->get_faq($faqId);

			$href = 'tiki-view_faq.php?faqId=' . $faqId;
			$catObjectId = $this->add_categorized_object('faq', $faqId, $info["description"], $info["title"], $href);
		}

		$this->categorize($catObjectId, $categId);
	}

	function categorize_blog($blogId, $categId) {
		// Check if we already have this object in the tiki_categorized_objects page
		$catObjectId = $this->is_categorized('blog', $blogId);

		if (!$catObjectId) {
			global $bloglib;
			if (!is_object($bloglib)) {
				include_once('lib/blogs/bloglib.php');
			}
			// The page is not cateorized
			$info = $bloglib->get_blog($blogId);

			$href = 'tiki-view_blog.php?blogId=' . $blogId;
			$catObjectId = $this->add_categorized_object('blog', $blogId, $info["description"], $info["title"], $href);
		}

		$this->categorize($catObjectId, $categId);
	}

	function categorize_directory($directoryId, $categId) {
		// Check if we already have this object in the tiki_categorized_objects page
		$catObjectId = $this->is_categorized('directory', $directoryId);

		if (!$catObjectId) {
			// The page is not cateorized
			$info = $this->get_directory($directoryId);

			$href = 'tiki-directory_browse.php?parent=' . $directoryId;
			$catObjectId = $this->add_categorized_object('directory', $directoryId, $info["description"], $info["name"], $href);
		}

		$this->categorize($catObjectId, $categId);
	}

	function categorize_gallery($galleryId, $categId) {
		// Check if we already have this object in the tiki_categorized_objects page
		$catObjectId = $this->is_categorized('image gallery', $galleryId);

		if (!$catObjectId) {
			global $imagegallib;
			if (!is_object($imagegallib)) {
				require_once('lib/imagegals/imagegallib.php');
			}
			// The page is not cateorized
			$info = $imagegallib->get_gallery($galleryId);

			$href = 'tiki-browse_gallery.php?galleryId=' . $galleryId;
			$catObjectId = $this->add_categorized_object('image gallery', $galleryId, $info["description"], $info["name"], $href);
		}

		$this->categorize($catObjectId, $categId);
	}

	function categorize_file_gallery($galleryId, $categId) {
		// Check if we already have this object in the tiki_categorized_objects page
		$catObjectId = $this->is_categorized('file gallery', $galleryId);

		if (!$catObjectId) {
			global $filegallib;
			if (!is_object($filegallib)) {
				include_once ('lib/filegals/filegallib.php');
			}
			// The page is not cateorized
			$info = $filegallib->get_file_gallery($galleryId);

			$href = 'tiki-list_file_gallery.php?galleryId=' . $galleryId;
			$catObjectId = $this->add_categorized_object('file gallery', $galleryId, $info["description"], $info["name"], $href);
		}

		$this->categorize($catObjectId, $categId);
	}

	function categorize_forum($forumId, $categId) {
		// Check if we already have this object in the tiki_categorized_objects page
		$catObjectId = $this->is_categorized('forum', $forumId);

		if (!$catObjectId) {
			global $commentslib;
			if (!is_object($commentslib)) {
				require_once('lib/commentslib.php');
			}
			// The page is not cateorized
			$info = $commentslib->get_forum($forumId);

			$href = 'tiki-view_forum.php?forumId=' . $forumId;
			$catObjectId = $this->add_categorized_object('forum', $forumId, $info["description"], $info["name"], $href);
		}

		$this->categorize($catObjectId, $categId);
	}

	function categorize_poll($pollId, $categId) {
		global $polllib;
		// Check if we already have this object in the tiki_categorized_objects page
		$catObjectId = $this->is_categorized('poll', $pollId);
		if (!$catObjectId) {
			if (!is_object($polllib)) {
				require_once('lib/polls/polllib_shared.php');
			}
			// The page is not cateorized
			$info = $pollib->get_poll($pollId);

			$href = 'tiki-poll_form.php?pollId=' . $pollId;
			$catObjectId = $this->add_categorized_object('poll', $pollId, $info["title"], $info["title"], $href);
		}

		$this->categorize($catObjectId, $categId);
	}
	// FUNCTIONS TO CATEGORIZE SPECIFIC OBJECTS END ////
	function get_child_categories($categId) {
		global $cachelib;
		if (!$cachelib->isCached("childcategs$categId")) {
			$ret = array();
			$query = "select * from `tiki_categories` where `parentId`=?";
			$result = $this->query($query,array($categId));
			while ($res = $result->fetchRow()) {
				$id = $res["categId"];
				$query = "select count(*) from `tiki_categories` where `parentId`=?";
				$res["children"] = $this->getOne($query,array($id));
				$query = "select count(*) from `tiki_category_objects` where `categId`=?";
				$res["objects"] = $this->getOne($query,array($id));
				$ret[] = $res;
			}
			$cachelib->cacheItem("childcategs$categId",serialize($ret));
		} else {
			$ret = unserialize($cachelib->getCached("childcategs$categId"));
		}
		return $ret;
	}

	function get_all_categories() {
		global $cachelib;
		if (!$cachelib->isCached("allcategs")) {
			$ret = array();
			$query = "select * from `tiki_categories` order by `name`";
			$result = $this->query($query,array());
			while ($res = $result->fetchRow()) {
				$id = $res["categId"];
				$query = "select count(*) from `tiki_categories` where `parentId`=?";
				$res["children"] = $this->getOne($query,array($id));
				$query = "select count(*) from `tiki_category_objects` where `categId`=?";
				$res["objects"] = $this->getOne($query,array($id));
				$ret[] = $res;
			}
			$cachelib->cacheItem("allcategs",serialize($ret));
		} else {
			$ret = unserialize($cachelib->getCached("allcategs"));
		}
		return $ret;
	}

	function get_all_categories_respect_perms($user, $perm) {
		global $cachelib;
		global $userlib;
		global $tiki_p_admin;
		
//		if (!$cachelib->isCached("allcategs")) {
			$ret = array();
			$query = "select * from `tiki_categories` order by `name`";
			$result = $this->query($query,array());
			while ($res = $result->fetchRow()) {

				$add = TRUE;
				if ($tiki_p_admin != 'y' && $userlib->object_has_one_permission($res['categId'], 'category')) {
					if (!$userlib->object_has_permission($user, $res['categId'], 'category', $perm)) {
						$add = FALSE;
					}
				} elseif ($tiki_p_admin != 'y') {
					$categpath = $this->get_category_path($res['categId']);
					$arraysize = count($categpath);
					for ($i=$arraysize-2; $i>=0; $i--) {
						if ($userlib->object_has_one_permission($categpath[$i]['categId'], 'category')) {
							if ($userlib->object_has_permission($user, $categpath[$i]['categId'], 'category', $perm)) {
								$add = TRUE;
								break 1;
								// break out of one FOR loop
							} else {
								$add = FALSE;
								break 1;
								// break out of one FOR loop
							}
						}
					}
				}
				
				if ($add) {
					$id = $res["categId"];
					$query = "select count(*) from `tiki_categories` where `parentId`=?";
					$res["children"] = $this->getOne($query,array($id));
					$query = "select count(*) from `tiki_category_objects` where `categId`=?";
					$res["objects"] = $this->getOne($query,array($id));
					$ret[] = $res;
				}
			}
//			$cachelib->cacheItem("allcategs.$user.$perm",serialize($ret));
//		} else {
//			$ret = unserialize($cachelib->getCached("allcategs.$user.$perm"));
//		}
		return $ret;
	}

	// get categories related to a link. For Whats related module.
	function get_link_categories($link) {
		$ret=array();
		$parsed=parse_url($link);
		$parsed["path"]=end(split("/",$parsed["path"]));
		if(!isset($parsed["query"])) return($ret);
		/* not yet used. will be used to get the "base href" of a page
		$params=array();
		$a = explode('&', $parsed["query"]);
		for ($i=0; $i < count($a);$i++) {
			$b = split('=', $a[$i]);
			$params[htmlspecialchars(urldecode($b[0]))]=htmlspecialchars(urldecode($b[1]));
		}
		*/
		$query="select distinct co.`categId` from `tiki_categorized_objects` cdo, `tiki_category_objects` co  where cdo.`href`=? and cdo.`catObjectId`=co.`catObjectId`";
		$result=$this->query($query,array($parsed["path"]."?".$parsed["query"]));
		while ($res = $result->fetchRow()) {
		  $ret[]=$res["categId"];
		}
		return($ret);
	}

	// input is a array of category id's and return is a array of 
	// maxRows related links with description
	function get_related($categories,$maxRows=10) {
		if(count($categories)==0) return (array());
		$quarr=implode(",",array_fill(0,count($categories),'?'));
		$query="select distinct cdo.`type`, cdo.`description`, cdo.`objId`,cdo.`href` from `tiki_categorized_objects` cdo, `tiki_category_objects` co  where co.`categId` in (".$quarr.") and co.`catObjectId`=cdo.`catObjectId`";
		$result=$this->query($query,$categories);
		$ret=array();
		while ($res = $result->fetchRow()) {
			if (empty($res["description"])) {
				$ret[$res["href"]]=$res["type"].": ".$res["objId"];
			} else {
				$ret[$res["href"]]=$res["type"].": ".$res["description"];
			}
		}
		if (count($ret)>$maxRows) {
			$ret2=array();
			$rand_keys = array_rand ($ret,$maxRows);
			foreach($rand_keys as $value) {
				$ret2[$value]=$ret[$value];
			}
			return($ret2);
		}
		return($ret);
	}
	
	// combines the two functions above
	function get_link_related($link,$maxRows=10) {
		return ($this->get_related($this->get_link_categories($link),$maxRows));
	}
	
	// Moved from tikilib.php
	function uncategorize_object($type, $id) {
		// Fixed query. -rlpowell
		$query = "select `catObjectId`  from `tiki_categorized_objects` where `type`=? and `objId`=?";
		$catObjectId = $this->getOne($query, array((string) $type,(string) $id));

		if ($catObjectId) {
		    $query = "delete from `tiki_category_objects` where `catObjectId`=?";
		    $result = $this->query($query,array((int) $catObjectId));
		    $query = "delete from `tiki_categorized_objects` where `catObjectId`=?";
		    $result = $this->query($query,array((int) $catObjectId));
		}
    }

    // Moved from tikilib.php
    function get_categorypath($cats) {
			global $smarty;
			global $feature_categories;

			$catpath = '';
			$catp = array();
			foreach ($cats as $categId) {
				$info = $this->get_category($categId);
				$catp["{$info['categId']}"] = $info["name"];
				while ($info["parentId"] != 0) {
					$info = $this->get_category($info["parentId"]);
					$catp["{$info['categId']}"] = $info["name"];
				}
				$smarty->assign('catp',array_reverse($catp,true));
				$catpath.= $smarty->fetch('categpath.tpl');
			}
			return $catpath;
    }
    
    //Moved from tikilib.php
    function get_categoryobjects($catids,$types="*",$sort='created_desc',$split=true,$sub=false,$and=false) {
			global $smarty;
			global $feature_categories;

		$typetokens = array(
			"article" => "article",
			"blog" => "blog",
			"directory" => "directory",
			"faq" => "faq",
			"fgal" => "file gallery",
			"forum" => "forum",
			"igal" => "image gallery",
			"newsletter" => "newsletter",
			"poll" => "poll",
			"quiz" => "quiz",
			"survey" => "survey",
			"tracker" => "tracker",
			"wiki" => "wiki page",
			"img" => "image"
		);
			
		$typetitles = array(
			"article" => "Articles",
			"blog" => "Blogs",
			"directory" => "Directories",
			"faq" => "FAQs",
			"file gallery" => "File Galleries",
			"forum" => "Forums",
			"image gallery" => "Image Galleries",
			"newsletter" => "Newsletters",
			"poll" => "Polls",
			"quiz" => "Quizzes",
			"survey" => "Surveys",
			"tracker" => "Trackers",
			"wiki page" => "Wiki",
			"image" => "Image"
		);

		$out = "";
		$listcat = $allcats = array();
		$title = '';
		$find = "";
		$offset = 0;
		$firstpassed = false;
		$maxRecords = 500;
		$typesallowed = array();
		if ($and) {
			$split = false;
		}
		if ($types == '*') {
			$typesallowed = array_keys($typetitles);
		} elseif (strpos($types,'+')) {
			$alltypes = split('\+',$types);
			foreach ($alltypes as $t) {
				if (isset($typetokens["$t"])) {
					$typesallowed[] = $typetokens["$t"];
				} elseif (isset($typetitles["$t"])) {
					$typesallowed[] = $t;
				}
			}
		} elseif (isset($typetokens["$types"])) {
			$typesallowed = array($typetokens["$types"]);
		} elseif (isset($typetitles["$types"])) {
			$typesallowed = array($types);
		}
		
		foreach ($catids as $id) {
			$titles["$id"] = $this->get_category_name($id);
			$objectcat = array();
			if ($sub) {
				$objectcat = $this->list_category_objects_deep($id, $offset, $maxRecords, $sort, $find);
			} else {
				$objectcat = $this->list_category_objects($id, $offset, $maxRecords, $sort, $find);
			}
			$acats = $andcat = array();
			foreach ($objectcat["data"] as $obj) {
				$type = $obj["type"];
				if (($types == '*') || in_array($type,$typesallowed)) {
					if ($split or !$firstpassed) {
						$listcat["$type"][] = $obj;
						$cats[] = $type.'.'.$obj['name'];
					} elseif ($and) {
						if (in_array($type.'.'.$obj['name'], $cats)) {
							$andcat["$type"][] = $obj;
							$acats[] = $type.'.'.$obj['name'];
						}
					} else {
						if (!in_array($type.'.'.$obj['name'], $cats)) {
							$listcat["$type"][] = $obj;
							$cats[] = $type.'.'.$obj['name'];
						}
					}
				}
			}
			if ($split) {
				$smarty->assign("id", $id);
				$smarty->assign("titles", $titles);
				$smarty->assign("listcat", $listcat);
				$out .= $smarty->fetch("categobjects.tpl");
				$listcat = array();
				$titles = array();
				$cats = array();
			} elseif ($and and $firstpassed) {
				$listcat = $andcat;
				$cats = $acats;
			}
			$firstpassed = true;
		}
		if (!$split) {
			$smarty->assign("id", $id);
			$smarty->assign("titles", $titles);
			$smarty->assign("listcat", $listcat);
			$out = $smarty->fetch("categobjects.tpl");
		}
		return $out;
	}
	
	//Moved from tikilib.php
    function last_category_objects($categId, $maxRecords, $type="") {
		$mid = "and tbl1.`categId`=?";
		$bindvars = array((int)$categId);
		if ($type) {
		    $mid.= " and tbl2.`type`=?";
		    $bindvars[] = $type;
		}
		$sort_mode = "created_desc";
		$query = "select tbl1.`catObjectId`,`categId`,`type`,`name`,`href` from `tiki_category_objects` tbl1,`tiki_categorized_objects` tbl2 ";
		$query.= " where tbl1.`catObjectId`=tbl2.`catObjectId` $mid order by tbl2.".$this->convert_sortmode($sort_mode);
		$result = $this->query($query,$bindvars,$maxRecords,0);

		$ret = array('data'=>array());
		while ($res = $result->fetchRow()) {
		    $ret['data'][] = $res;
		}
		return $ret;
    }

}

global $dbTiki;
$categlib = new CategLib($dbTiki);

?>
